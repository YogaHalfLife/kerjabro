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
        $bulan   = $request->get('bulan');
        $divisi  = $request->get('id_divisi');

        $isAdmin = Auth::check() && Auth::user()->username === 'admin';

        $pegawaiLogin = null;
        $divisiLogin  = null;
        if (!$isAdmin) {
            $user = Auth::user();
            $pegawaiLogin = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);
            $divisiLogin  = $pegawaiLogin ? MasterDivisi::find($pegawaiLogin->id_divisi) : null;
        }

        $divisis = MasterDivisi::query()
            ->when(!$isAdmin && $divisiLogin, function ($q2) use ($divisiLogin) {
                $q2->where('id_divisi', $divisiLogin->id_divisi)
                    ->orWhereRaw('LOWER(nama_divisi) = ?', ['all']);
            })
            ->orderBy('nama_divisi')
            ->get(['id_divisi', 'nama_divisi']);

        if (!$isAdmin && $divisi) {
            $allowedIds = $divisis->pluck('id_divisi')->all();
            if (!in_array($divisi, $allowedIds)) {
                $divisi = null;
            }
        }

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
        $request->validate([
            'bulan'     => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'id_divisi' => ['nullable', 'string'],
            'q'         => ['nullable', 'string'],
        ]);

        $isAdmin = Auth::check() && Auth::user()->username === 'admin';

        $bulanRaw = $request->get('bulan');
        $start = $end = null;
        if ($bulanRaw) {
            try {
                $start = Carbon::createFromFormat('Y-m', $bulanRaw)->startOfMonth()->toDateString();
                $end   = Carbon::createFromFormat('Y-m', $bulanRaw)->endOfMonth()->toDateString();
            } catch (\Throwable $e) {
                $start = $end = null;
            }
        }

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
                $divisi = null;
            }
        }

        $fileName = 'Laporan_Pekerjaan_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new TransPekerjaanExport(
                q: $request->get('q'),
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
            'bulan'     => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'id_divisi' => ['nullable', 'string'],
            'q'         => ['nullable', 'string'],
        ]);

        $export = new TransPekerjaanWordExport(
            q: $request->get('q'),
            bulan: $request->get('bulan'),
            divisi: $request->get('id_divisi'),
            user: $request->user(),
        );

        $doc = $export->build();
        $fileName = 'Laporan_Pekerjaan_' . now()->format('Ymd_His') . '.docx';

        $tempPath = storage_path('app/tmp_' . uniqid() . '.docx');
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($doc, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
