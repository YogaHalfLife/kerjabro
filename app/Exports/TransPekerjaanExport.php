<?php

namespace App\Exports;

use App\Models\MasterPegawai;
use App\Models\TransPekerjaan;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

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
    protected ?string $startDate; // "YYYY-MM-DD" (awal bulan)
    protected ?string $endDate;   // "YYYY-MM-DD" (akhir bulan)
    protected ?string $divisi;
    protected ?Authenticatable $user;

    /** @var \Illuminate\Support\Collection<\App\Models\TransPekerjaan> */
    protected $rows;
    protected int $thumbW = 110;  // lebar thumb
    protected int $thumbH = 110;  // tinggi thumb
    protected int $gapX   = 8;    // jarak antar thumb (horizontal)
    protected int $padCol = 22;   // padding kolom
    protected array $photoMap = []; // ['F3' => [['path'=>..., 'offsetX'=>...], ...], ... ]
    protected string $colSebelum = 'F';
    protected string $colSesudah = 'G';
    protected int $startRow = 2; // baris data pertama (1 = header)

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
        if (!$isAdmin) {
            $pegawaiLogin = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);
        }

        $this->rows = TransPekerjaan::with(['pegawai', 'divisi', 'fotos'])
            ->when(!$isAdmin && $pegawaiLogin, fn($s) => $s->where('pegawai_id', $pegawaiLogin->id))
            ->when($this->q, function ($s) {
                $q = $this->q;
                $s->where(function ($w) use ($q) {
                    $w->where('judul_pekerjaan', 'like', "%{$q}%")
                        ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                        ->orWhereHas('pegawai', fn($p) => $p->where('nama_pegawai', 'like', "%{$q}%"));
                });
            })
            ->when(
                $this->startDate && $this->endDate,
                fn($s) =>
                $s->whereBetween('bulan', [$this->startDate, $this->endDate])
            )
            ->when($this->divisi, fn($s) => $s->where('id_divisi', $this->divisi))
            ->get()
            ->sortBy(function ($r) {
                $nama = optional($r->pegawai)->nama_pegawai ?? 'ZZZ';
                return mb_strtolower($nama) . '|' . $r->id;
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
        return [
            $row->judul_pekerjaan,
            $row->detail_pekerjaan,
            optional($row->pegawai)?->nama_pegawai,
            optional($row->divisi)?->nama_divisi,
            \Illuminate\Support\Str::of($row->bulan)->substr(0, 7),
            '', // gambar via WithDrawings
            '', // gambar via WithDrawings
        ];
    }

    /** Sisipkan semua gambar */
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
                    if ($n <= 0) return 80; // minimal agar label terlihat
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
                $getNama = fn($r) => mb_strtolower(optional($r->pegawai)->nama_pegawai ?? '');
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
                $sheet->getStyle('B2:B' . ($this->startRow + max(0, count($this->rows))))->getAlignment()->setWrapText(true);
            },
        ];
    }
}
