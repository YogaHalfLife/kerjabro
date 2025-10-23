<?php

namespace App\Exports;

use App\Models\MasterPegawai;
use App\Models\TransPekerjaan;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Converter;

class TransPekerjaanWordExport
{
    protected ?string $q;
    protected ?string $bulan;   // "YYYY-MM" dari <input type="month">
    protected ?string $divisi;
    protected ?Authenticatable $user;

    protected $rows;

    public function __construct(?string $q = null, ?string $bulan = null, ?string $divisi = null, ?Authenticatable $user = null)
    {
        $this->q     = $q;
        $this->bulan = $bulan;
        $this->divisi = $divisi;
        $this->user  = $user ?: Auth::user();
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

        $q = TransPekerjaan::with(['pegawai', 'divisi', 'fotos' => fn($q) => $q->orderBy('kategori')->orderBy('sort')])
            ->when(!$isAdmin && $pegawaiLogin, fn($s) => $s->where('pegawai_id', $pegawaiLogin->id))
            ->when($this->q, function ($s) {
                $q = $this->q;
                $s->where(function ($w) use ($q) {
                    $w->where('judul_pekerjaan', 'like', "%{$q}%")
                        ->orWhere('detail_pekerjaan', 'like', "%{$q}%")
                        ->orWhereHas('pegawai', fn($p) => $p->where('nama_pegawai', 'like', "%{$q}%"));
                });
            })
            ->when($this->bulan, fn($s) => $s->where('bulan', 'like', $this->bulan . '%'))
            ->when($this->divisi, fn($s) => $s->where('id_divisi', $this->divisi))
            ->orderBy('bulan')->orderBy('id')
            ->get();

        // urut per pegawai biar rapi
        $this->rows = $q->sortBy(fn($r) => mb_strtolower(optional($r->pegawai)->nama_pegawai ?? '') . '|' . ($r->bulan ?? '') . '|' . $r->id)->values();
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

            // hitung skala: beri width maksimum, biar tinggi menyesuaikan (rasio ke-jaga)
            $imgSize = @getimagesize($path);
            if ($imgSize) {
                [$wPx, $hPx] = $imgSize;
                $targetW = min($wPx, $maxWidthPx);
                // beri hanya "width" → PhpWord otomatis jaga aspect ratio
                $section->addImage($path, [
                    'width'         => $targetW,
                    'wrappingStyle' => 'inline',
                    'alignment'     => Jc::LEFT,
                ]);
            } else {
                // fallback
                $section->addImage($path, [
                    'width'         => $maxWidthPx,
                    'wrappingStyle' => 'inline',
                    'alignment'     => Jc::LEFT,
                ]);
            }

            // spasi antar gambar
            $section->addTextBreak(1);
        }
    }

    /** Build dokumen Word (LANDSCAPE, non-table) */
    public function build(): PhpWord
    {
        $this->fetchRows();

        $phpWord = new PhpWord();
        $phpWord->getCompatibility()->setOoxmlVersion(15);

        // Section landscape
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft'   => 900,
            'marginRight'  => 900,
            'marginTop'    => 720,
            'marginBottom' => 720,
        ]);

        // Judul
        $section->addText('LAPORAN PEKERJAAN', ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);

        // Info filter
        $info = [];
        if ($this->bulan)  $info[] = "Bulan: {$this->bulan}";
        if ($this->divisi) $info[] = "Divisi: {$this->divisi}";
        if ($this->q)      $info[] = "Cari: {$this->q}";
        if ($info) {
            $section->addText(implode(' | ', $info), ['size' => 10, 'color' => '666666'], ['spaceAfter' => 200]);
        }

        // Lebar konten kira-kira (px) → halaman A4 landscape ~ 1122px lebar area tulis setelah margin (aproksimasi)
        $maxImageWidth = 180; // silakan sesuaikan kalau mau lebih kecil/besar

        // Loop data → tulis blok per pekerjaan
        foreach ($this->rows as $idx => $row) {
            // Judul
            $section->addText($row->judul_pekerjaan ?? '(Tanpa Judul)', ['bold' => true, 'size' => 12]);
            // Detail
            if (!empty($row->detail_pekerjaan)) {
                $section->addText($row->detail_pekerjaan, ['size' => 11], ['spaceAfter' => 120]);
            }

            // Meta
            $meta = sprintf(
                "Pegawai: %s\nDivisi: %s\nTanggal: %s\nID: %s",
                optional($row->pegawai)->nama_pegawai ?? '-',
                optional($row->divisi)->nama_divisi ?? '-',
                $row->bulan ?? '-',
                $row->id
            );
            $section->addText($meta, ['size' => 10, 'color' => '444444']);
            $section->addTextBreak(1);

            // Foto Sebelum
            $section->addText('Foto Sebelum', ['bold' => true, 'size' => 11], ['spaceAfter' => 80]);
            $pathsSeb = $row->fotos->where('kategori', 'sebelum')->pluck('path')->values()
                ->map(fn($p) => storage_path('app/public/' . $p))
                ->filter(fn($p) => is_string($p) && $p)
                ->toArray();
            $this->addImagesBlock($section, $pathsSeb, $maxImageWidth);

            // spasi antar blok
            $section->addTextBreak(1);

            // Foto Sesudah
            $section->addText('Foto Sesudah', ['bold' => true, 'size' => 11], ['spaceAfter' => 80]);
            $pathsSes = $row->fotos->where('kategori', 'sesudah')->pluck('path')->values()
                ->map(fn($p) => storage_path('app/public/' . $p))
                ->filter(fn($p) => is_string($p) && $p)
                ->toArray();
            $this->addImagesBlock($section, $pathsSes, $maxImageWidth);

            // Pembatas antar pekerjaan
            if ($idx < count($this->rows) - 1) {
                $section->addTextBreak(2);
                $section->addText(str_repeat('—', 80), ['color' => 'AAAAAA']);
                $section->addTextBreak(2);
            }
        }

        // Jika tidak ada data
        if (!$this->rows || $this->rows->isEmpty()) {
            $section->addText('Tidak ada data untuk filter yang dipilih.', ['italic' => true, 'color' => '888888']);
        }

        return $phpWord;
    }
}
