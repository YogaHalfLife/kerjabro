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

        $totalDivisi  = MasterDivisi::count();
        $totalPegawai = MasterPegawai::count();

        $startMonth = Carbon::now()->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        $qBase = TransPekerjaan::query()
            ->whereBetween('bulan', [$startMonth->toDateString(), $endMonth->toDateString()]);

        $totalPekerjaanBulanIni = (clone $qBase)->count();

        $totalPekerjaanSaya = (function () use ($isAdmin, $user, $qBase) {
            if ($isAdmin) {
                return (clone $qBase)->count();
            }

            $peg = MasterPegawai::where('kode_pegawai', $user->username)->first()
                ?? (isset($user->pegawai_id) ? MasterPegawai::find($user->pegawai_id) : null);

            if (!$peg) {
                return 0;
            }

            return (clone $qBase)
                ->whereHas('pegawais', function ($q) use ($peg) {
                    $q->where('master_pegawai.id', $peg->id);
                })
                ->count();
        })();


        $start = Carbon::now()->startOfMonth()->subMonths(11);
        $end   = Carbon::now()->endOfMonth();

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
            $labels[] = $m->translatedFormat('M Y');
            $values[] = (int) ($rows[$ym]->total ?? 0);
        }

        $recent = TransPekerjaan::with([
            'pegawais:id,nama_pegawai,id_divisi',
            'divisi:id_divisi,nama_divisi',
            'divisis:id_divisi,nama_divisi',
            'fotos' => fn($q) => $q->orderBy('sort')
        ])
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
