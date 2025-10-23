<?php

namespace App\Http\Controllers;

use App\Exports\TransPekerjaanExport;
use App\Exports\TransPekerjaanWordExport;
use App\Models\MasterDivisi;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanPekerjaanController extends Controller
{
    public function index(Request $request)
    {
        $q       = $request->get('q');
        $bulan   = $request->get('bulan');     // "YYYY-MM" dari <input type="month">
        $divisi  = $request->get('id_divisi');

        $isAdmin = Auth::check() && Auth::user()->username === 'admin';

        // Dapatkan pegawai & divisi user login (jika bukan admin)
        $pegawaiLogin = null;
        $divisiLogin  = null;
        if (!$isAdmin) {
            $user = Auth::user();
            $pegawaiLogin = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);
            $divisiLogin  = $pegawaiLogin ? MasterDivisi::find($pegawaiLogin->id_divisi) : null;
        }

        // Dropdown divisi:
        // - Admin: semua
        // - Non-admin: hanya divisinya + "all"
        $divisis = MasterDivisi::query()
            ->when(!$isAdmin && $divisiLogin, function ($q2) use ($divisiLogin) {
                $q2->where('id_divisi', $divisiLogin->id_divisi)
                   ->orWhereRaw('LOWER(nama_divisi) = ?', ['all']);
            })
            ->orderBy('nama_divisi')
            ->get(['id_divisi', 'nama_divisi']);

        // Jika non-admin mengirim id_divisi tak diizinkan → abaikan
        if (!$isAdmin && $divisi) {
            $allowedIds = $divisis->pluck('id_divisi')->all();
            if (!in_array($divisi, $allowedIds)) {
                $divisi = null;
            }
        }

        // Isi default bulan agar input tetap terisi
        $bulan = $bulan ?: now()->format('Y-m');

        return view('laporan.pekerjaan.index', compact(
            'q',
            'bulan',
            'divisi',
            'divisis',
            'isAdmin'
        ));
    }

    public function export(Request $request)
    {
        // Validasi input dari form laporan
        $request->validate([
            'bulan'     => ['nullable', 'regex:/^\d{4}-\d{2}$/'], // "YYYY-MM"
            'id_divisi' => ['nullable', 'string'],
            'q'         => ['nullable', 'string'],
        ]);

        $isAdmin = Auth::check() && Auth::user()->username === 'admin';

        // Normalisasi bulan → rentang tanggal (start & end) untuk dipakai di export
        $bulanRaw = $request->get('bulan'); // "YYYY-MM"
        $start = $end = null;
        if ($bulanRaw) {
            try {
                $start = Carbon::createFromFormat('Y-m', $bulanRaw)->startOfMonth()->toDateString();
                $end   = Carbon::createFromFormat('Y-m', $bulanRaw)->endOfMonth()->toDateString();
            } catch (\Throwable $e) {
                $start = $end = null; // kalau invalid, anggap tanpa filter bulan
            }
        }

        // Batasi divisi untuk non-admin (hanya divisinya + "all")
        $divisi = $request->get('id_divisi');
        if (!$isAdmin) {
            $user = $request->user();
            $pegawaiLogin = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);

            $divisisAllowed = MasterDivisi::query()
                ->when($pegawaiLogin, function ($q2) use ($pegawaiLogin) {
                    $q2->where('id_divisi', $pegawaiLogin->id_divisi)
                       ->orWhereRaw('LOWER(nama_divisi) = ?', ['all']);
                })
                ->pluck('id_divisi')
                ->all();

            if ($divisi && !in_array($divisi, $divisisAllowed)) {
                $divisi = null; // paksa kosong kalau tak diizinkan
            }
        }

        $fileName = 'Laporan_Pekerjaan_' . now()->format('Ymd_His') . '.xlsx';

        // Kirim parameter yang sudah dinormalisasi ke Export
        return Excel::download(
            new TransPekerjaanExport(
                q: $request->get('q'),
                // kirim rentang tanggal, biar Export bisa whereBetween('bulan', [$start, $end])
                startDate: $start,
                endDate: $end,
                divisi: $divisi,
                user: $request->user(),
            ),
            $fileName
        );
    }

    public function exportWord(Request $request)
    {
        $request->validate([
            'bulan'     => ['nullable', 'regex:/^\d{4}-\d{2}$/'], // dari <input type="month">
            'id_divisi' => ['nullable', 'string'],
            'q'         => ['nullable', 'string'],
        ]);

        $export = new TransPekerjaanWordExport(
            q: $request->get('q'),
            bulan: $request->get('bulan'),
            divisi: $request->get('id_divisi'),
            user: $request->user(),
        );

        $doc = $export->build(); // \PhpOffice\PhpWord\PhpWord instance (or temp file path)
        $fileName = 'Laporan_Pekerjaan_' . now()->format('Ymd_His') . '.docx';

        // simpan ke file sementara
        $tempPath = storage_path('app/tmp_' . uniqid() . '.docx');
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($doc, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
