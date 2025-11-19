<?php

namespace App\Exports;

use App\Models\MasterPegawai;
use App\Models\TransPekerjaan;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as SharedDrawing;

class TransPekerjaanExport implements FromCollection, WithHeadings, WithMapping, WithDrawings, WithEvents
{
    protected ?string $q;
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?string $divisi;
    protected ?Authenticatable $user;

    /** @var \Illuminate\Support\Collection<\App\Models\TransPekerjaan> */
    protected $rows;
    protected int $thumbW = 110;
    protected int $thumbH = 110;
    protected int $gapX   = 8;
    protected int $padCol = 22;
    protected array $photoMap = [];
    protected string $colSebelum = 'F';
    protected string $colSesudah = 'G';
    protected int $startRow = 2;

    public function __construct(
        ?string $q = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $divisi = null,
        ?Authenticatable $user = null
    ) {
        $this->q         = $q;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->divisi    = $divisi;
        $this->user      = $user ?: Auth::user();
    }

    public function collection()
    {
        $user    = $this->user;
        $isAdmin = $user && $user->username === 'admin';

        $pegawaiLogin = null;
        if (!$isAdmin && $user) {
            $pegawaiLogin = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);
        }

        $query = TransPekerjaan::with([
            'pegawais:id,nama_pegawai,id_divisi',
            'divisi:id_divisi,nama_divisi',
            'divisis:id_divisi,nama_divisi',
            'fotos',
        ]);
        
        if (!$isAdmin && $pegawaiLogin) {
            $query->whereHas('pegawais', function ($q) use ($pegawaiLogin) {
                $q->where('master_pegawai.id', $pegawaiLogin->id);
            });
        }
        
        if ($this->q) {
            $q = $this->q;
            $query->where(function ($w) use ($q) {
                $w->where('judul_pekerjaan', 'like', "%{$q}%")
                    ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                    ->orWhereHas('pegawais', function ($p) use ($q) {
                        $p->where('nama_pegawai', 'like', "%{$q}%");
                    });
            });
        }
        
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('bulan', [$this->startDate, $this->endDate]);
        }
        
        if ($this->divisi) {
            $divisiId = $this->divisi;
            $query->where(function ($w) use ($divisiId) {
                $w->where('id_divisi', $divisiId)
                    ->orWhereHas('pegawais', function ($p) use ($divisiId) {
                        $p->where('master_pegawai.id_divisi', $divisiId);
                    });
            });
        }

        $this->rows = $query
            ->get()
            ->sortBy(function ($r) {
                $firstNama = $r->pegawais
                    ->pluck('nama_pegawai')
                    ->sort()
                    ->first() ?? 'ZZZ';

                return mb_strtolower($firstNama) . '|' . $r->id;
            })
            ->values();
        $this->photoMap = [];
        foreach ($this->rows as $i => $row) {
            $excelRow = $this->startRow + $i;

            $sebelum = $row->fotos->where('kategori', 'sebelum')->values();
            $sesudah = $row->fotos->where('kategori', 'sesudah')->values();

            if ($sebelum->count()) {
                $k = $this->colSebelum . $excelRow;
                $this->photoMap[$k] = [];
                foreach ($sebelum as $idx => $f) {
                    $path = $f->path ? storage_path('app/public/' . $f->path) : null;
                    if (!$path || !is_file($path)) continue;
                    $this->photoMap[$k][] = [
                        'path'    => $path,
                        'offsetX' => $idx * ($this->thumbW + $this->gapX),
                    ];
                }
            }

            if ($sesudah->count()) {
                $k = $this->colSesudah . $excelRow;
                $this->photoMap[$k] = $this->photoMap[$k] ?? [];
                foreach ($sesudah as $idx => $f) {
                    $path = $f->path ? storage_path('app/public/' . $f->path) : null;
                    if (!$path || !is_file($path)) continue;
                    $this->photoMap[$k][] = [
                        'path'    => $path,
                        'offsetX' => $idx * ($this->thumbW + $this->gapX),
                    ];
                }
            }
        }

        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Judul Pekerjaan',
            'Detail Pekerjaan',
            'Pegawai',
            'Divisi',
            'Bulan',
            'Foto Sebelum',
            'Foto Sesudah',
        ];
    }

    public function map($row): array
    {
        $pegawaiNama = $row->pegawais
            ? $row->pegawais->pluck('nama_pegawai')->implode(', ')
            : '';
        $divisiList = $row->divisis;
        if (!$divisiList->count() && $row->divisi) {
            $divisiList = collect([$row->divisi]);
        }
        $divisiNama = $divisiList->pluck('nama_divisi')->implode(', ');

        return [
            $row->judul_pekerjaan,
            $row->detail_pekerjaan,
            $pegawaiNama,
            $divisiNama,
            Str::of($row->bulan)->substr(0, 7),
            '',
            '',
        ];
    }

    public function drawings(): array
    {
        $out = [];
        foreach ($this->photoMap as $coord => $items) {
            foreach ($items as $i => $it) {
                $d = new Drawing();
                $d->setName('foto-' . $coord . '-' . $i);
                $d->setDescription('foto');
                $d->setPath($it['path']);
                $d->setWidth($this->thumbW);
                $d->setCoordinates($coord);
                $d->setOffsetX($it['offsetX']);
                $d->setOffsetY(2);
                $out[] = $d;
            }
        }
        return $out;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $pxToWidth = function (int $px) {
                    return method_exists(SharedDrawing::class, 'pixelsToColumnWidth')
                        ? SharedDrawing::pixelsToColumnWidth($px)
                        : max(8, $px / 5.4);
                };
                $pxToPoint = function (int $px) {
                    return method_exists(SharedDrawing::class, 'pixelsToPoints')
                        ? SharedDrawing::pixelsToPoints($px)
                        : $px * 0.75;
                };

                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                $maxSeb = 0;
                $maxSes = 0;
                foreach ($this->rows as $row) {
                    $maxSeb = max($maxSeb, $row->fotos->where('kategori', 'sebelum')->count());
                    $maxSes = max($maxSes, $row->fotos->where('kategori', 'sesudah')->count());
                }

                $needPx = function (int $n) {
                    if ($n <= 0) return 80;
                    return $n * $this->thumbW
                        + max(0, $n - 1) * $this->gapX
                        + $this->padCol;
                };
                
                $sheet->getColumnDimension($this->colSebelum)->setAutoSize(false);
                $sheet->getColumnDimension($this->colSesudah)->setAutoSize(false);
                $sheet->getColumnDimension($this->colSebelum)->setWidth($pxToWidth($needPx($maxSeb)));
                $sheet->getColumnDimension($this->colSesudah)->setWidth($pxToWidth($needPx($maxSes)));

                foreach ($this->rows as $i => $row) {
                    $excelRow = $this->startRow + $i;
                    $hasFoto  = $row->fotos->count() > 0;
                    $sheet->getRowDimension($excelRow)
                        ->setRowHeight($hasFoto ? (int) round($pxToPoint($this->thumbH + 12)) : -1);
                }

                $sheet->setShowSummaryBelow(false);

                $getNama = function ($r) {
                    return mb_strtolower(
                        $r->pegawais
                            ->pluck('nama_pegawai')
                            ->sort()
                            ->first() ?? ''
                    );
                };

                $n = count($this->rows);
                if ($n > 0) {
                    $groupStart = 0;
                    $cur = $getNama($this->rows[0]);
                    for ($idx = 1; $idx <= $n; $idx++) {
                        $now = ($idx < $n) ? $getNama($this->rows[$idx]) : null;
                        if ($idx === $n || $now !== $cur) {
                            $r1 = $this->startRow + $groupStart;
                            $r2 = $this->startRow + $idx - 1;
                            for ($r = $r1; $r <= $r2; $r++) {
                                $sheet->getRowDimension($r)->setOutlineLevel(1);
                            }
                            if ($idx < $n) {
                                $groupStart = $idx;
                                $cur = $now;
                            }
                        }
                    }
                }
                
                $sheet->getStyle('A1:G1')->getFont()->setBold(true);
                
                $sheet->getStyle('B2:B' . ($this->startRow + max(0, count($this->rows))))
                    ->getAlignment()
                    ->setWrapText(true);
            },
        ];
    }
}
