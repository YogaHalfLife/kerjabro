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
    protected ?string $bulan;   // "YYYY-MM" dari <input type="month">
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

    /** Ambil data + filter (bulan support data "Y-m" atau "Y-m-d") */
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
            'pegawais:id,nama_pegawai,id_divisi',                     // 🔹 many-to-many pegawai
            'divisi:id_divisi,nama_divisi',                           // 🔹 divisi utama
            'divisis:id_divisi,nama_divisi',                          // 🔹 semua divisi via pivot
            'fotos' => fn($q) => $q->orderBy('kategori')->orderBy('sort'),
        ])
            // Non-admin: hanya pekerjaan yg mencantumkan dia sebagai pegawai
            ->when(!$isAdmin && $pegawaiLogin, function ($s) use ($pegawaiLogin) {
                $s->whereHas('pegawais', function ($p) use ($pegawaiLogin) {
                    $p->where('master_pegawai.id', $pegawaiLogin->id);
                });
            })
            // Filter q: judul / detail / nama pegawai
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
            // Filter bulan (YYYY-MM)
            ->when($this->bulan, function ($s) {
                $s->where('bulan', 'like', $this->bulan . '%');
            })
            // Filter divisi: header ATAU divisi pegawai
            ->when($this->divisi, function ($s) {
                $div = $this->divisi;
                $s->where(function ($w) use ($div) {
                    $w->where('id_divisi', $div)
                        ->orWhereHas('pegawais', function ($p) use ($div) {
                            $p->where('master_pegawai.id_divisi', $div);
                        });
                });
            })
            ->orderBy('bulan')
            ->orderBy('id')
            ->get();

        // Sort per nama pegawai pertama + bulan + id (biar rapi)
        $this->rows = $q->sortBy(function ($r) {
            $firstNama = $r->pegawais
                ->pluck('nama_pegawai')
                ->sort()
                ->first() ?? '';

            return mb_strtolower($firstNama) . '|' . ($r->bulan ?? '') . '|' . $r->id;
        })
            ->values();
    }

    /** Tambah kumpulan foto (vertikal) dengan rasio asli, dibatasi max width */
    protected function addImagesBlock($section, array $absPaths, int $maxWidthPx = 900): void
    {
        if (empty($absPaths)) {
            $section->addText('- Tidak ada foto -', ['italic' => true, 'color' => '666666']);
            return;
        }

        foreach ($absPaths as $path) {
            if (!is_file($path)) continue;

            $imgSize = @getimagesize($path);
            if ($imgSize) {
                [$wPx, $hPx] = $imgSize;
                $targetW = min($wPx, $maxWidthPx);
                $section->addImage($path, [
                    'width'         => $targetW,
                    'wrappingStyle' => 'inline',
                    'alignment'     => Jc::LEFT,
                ]);
            } else {
                $section->addImage($path, [
                    'width'         => $maxWidthPx,
                    'wrappingStyle' => 'inline',
                    'alignment'     => Jc::LEFT,
                ]);
            }

            $section->addTextBreak(1);
        }
    }

    /** Build dokumen Word (LANDSCAPE, non-table) */
    public function build(): PhpWord
    {
        $this->fetchRows();

        $phpWord = new PhpWord();
        $phpWord->getCompatibility()->setOoxmlVersion(15);

        $section = $phpWord->addSection([
            'orientation'  => 'landscape',
            'marginLeft'   => 900,
            'marginRight'  => 900,
            'marginTop'    => 720,
            'marginBottom' => 720,
        ]);

        // Judul
        $section->addText(
            'LAPORAN PEKERJAAN',
            ['bold' => true, 'size' => 16],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        // Info filter
        $info = [];
        if ($this->bulan)  $info[] = "Bulan: {$this->bulan}";
        if ($this->divisi) $info[] = "Divisi: {$this->divisi}";
        if ($this->q)      $info[] = "Cari: {$this->q}";
        if ($info) {
            $section->addText(
                implode(' | ', $info),
                ['size' => 10, 'color' => '666666'],
                ['spaceAfter' => 200]
            );
        }

        $maxImageWidth = 180; // lebar maksimal gambar per foto

        foreach ($this->rows as $idx => $row) {

            // Judul
            $section->addText(
                $row->judul_pekerjaan ?? '(Tanpa Judul)',
                ['bold' => true, 'size' => 12]
            );

            // Detail
            if (!empty($row->detail_pekerjaan)) {
                $section->addText(
                    $row->detail_pekerjaan,
                    ['size' => 11],
                    ['spaceAfter' => 120]
                );
            }

            // Pegawai (multi) & Divisi (multi)
            $pegawaiNama = $row->pegawais
                ? $row->pegawais->pluck('nama_pegawai')->implode(', ')
                : '';

            $divisiList = $row->divisis;
            if (!$divisiList->count() && $row->divisi) {
                $divisiList = collect([$row->divisi]);
            }
            $divisiNama = $divisiList->pluck('nama_divisi')->implode(', ');

            $meta = sprintf(
                "Pegawai: %s\nDivisi: %s\nTanggal: %s\nID: %s",
                $pegawaiNama ?: '-',
                $divisiNama ?: '-',
                $row->bulan ?? '-',
                $row->id
            );

            $section->addText(
                $meta,
                ['size' => 10, 'color' => '444444']
            );

            $section->addTextBreak(1);

            // Foto Sebelum
            $section->addText(
                'Foto Sebelum',
                ['bold' => true, 'size' => 11],
                ['spaceAfter' => 80]
            );

            $pathsSeb = $row->fotos->where('kategori', 'sebelum')->pluck('path')->values()
                ->map(fn($p) => storage_path('app/public/' . $p))
                ->filter(fn($p) => is_string($p) && $p)
                ->toArray();

            $this->addImagesBlock($section, $pathsSeb, $maxImageWidth);

            $section->addTextBreak(1);

            // Foto Sesudah
            $section->addText(
                'Foto Sesudah',
                ['bold' => true, 'size' => 11],
                ['spaceAfter' => 80]
            );

            $pathsSes = $row->fotos->where('kategori', 'sesudah')->pluck('path')->values()
                ->map(fn($p) => storage_path('app/public/' . $p))
                ->filter(fn($p) => is_string($p) && $p)
                ->toArray();

            $this->addImagesBlock($section, $pathsSes, $maxImageWidth);

            // Separator antar pekerjaan
            if ($idx < count($this->rows) - 1) {
                $section->addTextBreak(2);
                $section->addText(str_repeat('—', 80), ['color' => 'AAAAAA']);
                $section->addTextBreak(2);
            }
        }

        if (!$this->rows || $this->rows->isEmpty()) {
            $section->addText(
                'Tidak ada data untuk filter yang dipilih.',
                ['italic' => true, 'color' => '888888']
            );
        }

        return $phpWord;
    }
}
