<?php

namespace App\Http\Controllers;

use App\Models\MasterDivisi;
use App\Models\MasterPegawai;
use App\Models\TransPekerjaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $isAdmin = $user && $user->username === 'admin';

        // Ringkasan angka
        $totalDivisi  = MasterDivisi::count();
        $totalPegawai = MasterPegawai::count();

        // === Bulan ini (pakai rentang tanggal) ===
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        $qBase = TransPekerjaan::query()
            ->whereBetween('bulan', [$startMonth->toDateString(), $endMonth->toDateString()]);

        $totalPekerjaanBulanIni = (clone $qBase)->count();

        // Pekerjaan saya
        $totalPekerjaanSaya = (function () use ($isAdmin, $user, $qBase) {
            if ($isAdmin) {
                return (clone $qBase)->count();
            }
            $peg = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);

            return $peg ? (clone $qBase)->where('pegawai_id', $peg->id)->count() : 0;
        })();

        // === Grafik 12 bulan terakhir ===
        $start = Carbon::now()->startOfMonth()->subMonths(11);
        $end   = Carbon::now()->endOfMonth();

        // Group by bulan (YYYY-mm) dari kolom tanggal
        $rows = TransPekerjaan::select(
            DB::raw("DATE_FORMAT(bulan, '%Y-%m') as ym"),
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('bulan', [$start->toDateString(), $end->toDateString()])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $values = [];
        for ($i = 0; $i < 12; $i++) {
            $m   = (clone $start)->addMonths($i);
            $ym  = $m->format('Y-m');
            $labels[] = $m->translatedFormat('M Y'); // contoh: Okt 2025
            $values[] = (int) ($rows[$ym]->total ?? 0);
        }

        // Aktivitas terbaru
        $recent = TransPekerjaan::with(['pegawai', 'divisi', 'fotos' => fn($q) => $q->orderBy('sort')])
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalDivisi',
            'totalPegawai',
            'totalPekerjaanBulanIni',
            'totalPekerjaanSaya',
            'labels',
            'values',
            'recent',
            'isAdmin'
        ));
    }
}
