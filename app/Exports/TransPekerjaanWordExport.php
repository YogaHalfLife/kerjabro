<?php

namespace App\Exports;

use App\Models\MasterPegawai;
use App\Models\TransPekerjaan;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class TransPekerjaanWordExport
{
    protected ?string $q;
    protected ?string $bulan;
    protected ?string $divisi;
    protected ?Authenticatable $user;
    protected $rows;

    public function __construct(?string $q = null, ?string $bulan = null, ?string $divisi = null, ?Authenticatable $user = null)
    {
        $this->q      = $q;
        $this->bulan  = $bulan;
        $this->divisi = $divisi;
        $this->user   = $user ?: Auth::user();
    }

    protected function fetchRows(): void
    {
        $user    = $this->user;
        $isAdmin = $user && $user->username === 'admin';

        $pegawaiLogin = null;
        if (!$isAdmin && $user) {
            $pegawaiLogin = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);
        }

        $q = TransPekerjaan::with([
            'pegawais:id,nama_pegawai,id_divisi',
            'divisi:id_divisi,nama_divisi',
            'divisis:id_divisi,nama_divisi',
            'fotos' => fn($q) => $q->orderBy('kategori')->orderBy('sort'),
        ])
            ->when(!$isAdmin && $pegawaiLogin, function ($s) use ($pegawaiLogin) {
                $s->whereHas('pegawais', function ($p) use ($pegawaiLogin) {
                    $p->where('master_pegawai.id', $pegawaiLogin->id);
                });
            })
            ->when($this->q, function ($s) {
                $q = $this->q;
                $s->where(function ($w) use ($q) {
                    $w->where('judul_pekerjaan', 'like', "%{$q}%")
                        ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                        ->orWhereHas('pegawais', function ($p) use ($q) {
                            $p->where('nama_pegawai', 'like', "%{$q}%");
                        });
                });
            })
            ->when($this->bulan, fn($s) => $s->where('bulan', 'like', $this->bulan . '%'))
            ->when($this->divisi, function ($s) {
                $div = $this->divisi;
                $s->where(function ($w) use ($div) {
                    $w->where('id_divisi', $div)
                        ->orWhereHas('pegawais', fn($p) => $p->where('master_pegawai.id_divisi', $div));
                });
            })
            ->orderBy('bulan')
            ->orderBy('id')
            ->get();

        $this->rows = $q->sortBy(
            fn($r) =>
            strtolower($r->pegawais->pluck('nama_pegawai')->sort()->first() ?? '')
                . '|' . ($r->bulan ?? '') . '|' . $r->id
        )->values();
    }

    protected function addImagesBlock($section, array $paths, int $maxWidth = 180): void
    {
        if (empty($paths)) {
            $section->addText('Tidak ada foto', ['italic' => true, 'color' => '666666']);
            return;
        }

        foreach ($paths as $path) {
            if (!is_file($path)) continue;

            $section->addImage($path, [
                'width' => $maxWidth,
                'ratio' => true,
            ]);

            $section->addTextBreak(1);
        }
    }

    public function build(): PhpWord
    {
        $this->fetchRows();

        $phpWord = new PhpWord();
        $phpWord->getCompatibility()->setOoxmlVersion(15);
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 900,
            'marginRight' => 900,
            'marginTop' => 720,
            'marginBottom' => 720,
        ]);
        
        $section->addText(
            'LAPORAN PEKERJAAN',
            ['bold' => true, 'size' => 16],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);

        foreach ($this->rows as $idx => $row) {

            $section->addText($row->judul_pekerjaan ?? '(Tanpa Judul)', ['bold' => true]);

            if (!empty($row->detail_pekerjaan)) {
                $section->addText($row->detail_pekerjaan);
            }

            $pegawaiNama = $row->pegawais?->pluck('nama_pegawai')->implode(', ') ?: '-';
            $divisiNama  = $row->divisis->pluck('nama_divisi')->implode(', ')
                ?: ($row->divisi->nama_divisi ?? '-');
            $section->addText("Pegawai: {$pegawaiNama}", ['size' => 10]);
            $section->addText("Divisi: {$divisiNama}", ['size' => 10]);
            $section->addText("Tanggal: " . ($row->bulan ?? '-'), ['size' => 10]);
            $section->addText("ID: {$row->id}", ['size' => 10]);

            $section->addTextBreak(1);

            $section->addText('Foto Sebelum', ['bold' => true]);
            $pathsSeb = $row->fotos->where('kategori', 'sebelum')
                ->pluck('path')->map(fn($p) => storage_path('app/public/' . $p))
                ->toArray();
            $this->addImagesBlock($section, $pathsSeb);

            $section->addTextBreak(1);

            $section->addText('Foto Sesudah', ['bold' => true]);
            $pathsSes = $row->fotos->where('kategori', 'sesudah')
                ->pluck('path')->map(fn($p) => storage_path('app/public/' . $p))
                ->toArray();
            $this->addImagesBlock($section, $pathsSes);

            if ($idx < count($this->rows) - 1) {
                $section->addTextBreak(2);
                $section->addText(str_repeat('-', 80), ['color' => 'AAAAAA']);
                $section->addTextBreak(2);
            }
        }

        if ($this->rows->isEmpty()) {
            $section->addText('Tidak ada data.', ['italic' => true]);
        }

        return $phpWord;
    }
}
