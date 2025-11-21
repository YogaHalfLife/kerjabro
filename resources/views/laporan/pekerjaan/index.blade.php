@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Laporan Pekerjaan'])
<div class="container-fluid py-4">

    <div class="card shadow-sm mb-4">
        <div class="card-header pb-0">
            <h6 class="mb-0">Filter Laporan & Export</h6>
        </div>

        <div class="card-body pt-3">
            <form method="GET">
                {{-- Baris 1: Filter --}}
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <label class="form-label text-sm mb-1">Cari</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                            <input type="text" name="q" value="{{ old('q', $q ?? '') }}" class="form-control"
                                placeholder="Detail / Nama pegawai...">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label text-sm mb-1">Bulan</label>
                        <input type="month" name="bulan" value="{{ old('bulan', $bulan ?? now()->format('Y-m')) }}"
                            class="form-control">
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label text-sm mb-1">Divisi</label>
                        <select name="id_divisi" class="form-select">
                            @php $selectedDivisi = $divisi ?? ''; @endphp
                            @if (!empty($isAdmin) && $isAdmin)
                            <option value="">Semua divisi</option>
                            @foreach ($divisis as $d)
                            <option value="{{ $d->id_divisi }}"
                                {{ $selectedDivisi == $d->id_divisi ? 'selected' : '' }}>
                                {{ $d->nama_divisi }}
                            </option>
                            @endforeach
                            @else
                            @foreach ($divisis->filter(fn($d) => strtolower($d->nama_divisi) === 'all' || ($divisiLogin && $d->id_divisi == $divisiLogin->id_divisi)) as $d)
                            <option value="{{ $d->id_divisi }}"
                                {{ $selectedDivisi == $d->id_divisi ? 'selected' : '' }}>
                                {{ $d->nama_divisi }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Baris 2: Tombol Export responsif --}}
                <div class="row mt-3 g-2">
                    <div class="col-12 col-md-6">
                        <button type="submit"
                            class="btn btn-primary w-100 d-flex align-items-center justify-content-center"
                            formaction="{{ route('laporan.pekerjaan.export') }}" formmethod="GET">
                            <i class="ni ni-cloud-download-95 me-2"></i>
                            <span>Export Excel</span>
                        </button>
                    </div>
                    <div class="col-12 col-md-6">
                        <button type="submit"
                            class="btn btn-secondary w-100 d-flex align-items-center justify-content-center"
                            formaction="{{ route('laporan.pekerjaan.exportWord') }}" formmethod="GET">
                            <i class="ni ni-single-copy-04 me-2"></i>
                            <span>Export Word</span>
                        </button>
                    </div>
                </div>
            </form>

            <p class="text-xs text-secondary mt-2 mb-0">
                Catatan: User non-admin otomatis mengekspor data miliknya sendiri. Admin mengekspor sesuai filter.
            </p>
        </div>
    </div>

    @include('layouts.footers.auth.footer')
</div>
@endsection