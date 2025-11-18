@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Dashboard'])

    <div class="container-fluid py-4">

        {{-- KPI cards --}}
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Divisi</p>
                                    <h5 class="font-weight-bolder">{{ $totalDivisi }}</h5>
                                    <p class="mb-0"><span class="text-secondary text-sm">Total divisi</span></p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-bullet-list-67 text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Pegawai</p>
                                    <h5 class="font-weight-bolder">{{ $totalPegawai }}</h5>
                                    <p class="mb-0"><span class="text-secondary text-sm">Terdaftar aktif</span></p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="ni ni-single-02 text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Pekerjaan (bulan ini)</p>
                                    <h5 class="font-weight-bolder">{{ $totalPekerjaanBulanIni }}</h5>
                                    <p class="mb-0"><span
                                            class="text-secondary text-sm">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="ni ni-briefcase-24 text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">
                                        {{ $isAdmin ? 'Pekerjaan (semua)' : 'Pekerjaan saya' }}</p>
                                    <h5 class="font-weight-bolder">{{ $totalPekerjaanSaya }}</h5>
                                    <p class="mb-0"><span class="text-secondary text-sm">Bulan berjalan</span></p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                    <i class="ni ni-check-bold text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart + Recent --}}
        <div class="row mt-4">
            <div class="col-lg-7 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <h6 class="text-capitalize">Tren Pekerjaan 12 Bulan</h6>
                        <p class="text-sm mb-0">
                            <i class="fa fa-arrow-up text-success"></i>
                            <span class="font-weight-bold">Aktivitas</span> per bulan
                        </p>
                    </div>
                    <div class="card-body p-3">
                        <div class="chart">
                            <canvas id="chart-pekerjaan" class="chart-canvas" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Aktivitas Terbaru</h6>
                    </div>

                    <div class="card-body p-3 no-x-scroll">
                        <ul class="list-group">
                            @forelse($recent as $item)
                                @php
                                    $thumbsSeb = $item->fotos->where('kategori', 'sebelum')->pluck('path')->values();
                                    $thumbsSes = $item->fotos->where('kategori', 'sesudah')->pluck('path')->values();
                                    $allThumbs = $item->fotos->pluck('path')->values();
                                @endphp

                                <li
                                    class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    {{-- Kiri: tumpukan gambar + teks (boleh menyusut) --}}
                                    <div class="d-flex align-items-center flex-grow-1 min-w-0">
                                        <div class="me-3 thumb-stack">
                                            @foreach ($allThumbs->take(3) as $k => $p)
                                                <img src="{{ asset('storage/' . $p) }}"
                                                    class="rounded-circle shadow position-absolute"
                                                    style="left:{{ $k * 18 }}px; top:0;">
                                            @endforeach
                                            @if ($allThumbs->count() > 3)
                                                <span class="badge bg-gradient-secondary position-absolute"
                                                    style="left:56px; top:8px;">
                                                    +{{ $allThumbs->count() - 3 }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="d-flex flex-column min-w-0">
                                            <h6 class="mb-1 text-dark text-sm text-truncate" style="max-width: 100%;">
                                                {{ $item->judul_pekerjaan }}
                                            </h6>
                                            <span class="text-xs">
                                                @php $nama = $item->pegawais->pluck('nama_pegawai')->implode(', '); @endphp
                                                {{ $nama ?: '—' }} · {{ optional($item->divisi)->nama_divisi }} ·
                                                {{ $item->bulan }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Kanan: tombol (jangan menyusut → tidak mendorong konten) --}}
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                                        <button type="button"
                                            class="btn btn-primary btn-link btn-icon-only btn-rounded btn-sm my-auto"
                                            title="Lihat foto" data-bs-toggle="modal" data-bs-target="#modalFotoDash"
                                            data-sebelum='@json($thumbsSeb)'
                                            data-sesudah='@json($thumbsSes)'>
                                            <i class="ni ni-image" aria-hidden="true"></i>
                                        </button>

                                        @if (!empty($isAdmin) && $isAdmin)
                                            <a href="{{ route('trans.pekerjaan.edit', $item->id) }}"
                                                class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark my-auto"
                                                title="Edit">
                                                <i class="ni ni-bold-right" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item border-0">
                                    <span class="text-secondary text-sm">Belum ada aktivitas.</span>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>

            {{-- Modal Preview Foto --}}
            <div class="modal fade" id="modalFotoDash" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h6 class="modal-title">Foto Pekerjaan</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <h6 class="text-xs text-secondary mb-2">Sebelum</h6>
                                    <div class="d-flex flex-wrap gap-2" id="dash-wrap-sebelum"></div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <h6 class="text-xs text-secondary mb-2">Sesudah</h6>
                                    <div class="d-flex flex-wrap gap-2" id="dash-wrap-sesudah"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer py-2">
                            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>

    <style>
        /* cegah x-scroll di card ini */
        .card-body.no-x-scroll {
            overflow-x: hidden;
        }

        /* supaya teks bisa benar-benar ter-ellipsis di dalam flex */
        .min-w-0 {
            min-width: 0;
        }

        /* tumpukan thumbnail lebih rapi */
        .thumb-stack {
            position: relative;
            width: 78px;
            /* 3 foto * (36px - overlap 18px) + ruang badge */
            height: 36px;
        }

        .thumb-stack img {
            width: 36px;
            height: 36px;
            object-fit: cover;
        }
    </style>

@endsection

@push('js')
    {{-- gunakan path asset sesuai layout kamu --}}
    <script src="/assets/js/plugins/chartjs.min.js"></script>
    <script>
        (function() {
            const ctx = document.getElementById('chart-pekerjaan').getContext('2d');
            const labels = @json($labels);
            const dataVal = @json($values);

            const grad = ctx.createLinearGradient(0, 230, 0, 50);
            grad.addColorStop(1, 'rgba(61, 141, 122, 0.2)'); // hijau KERJA BRO lembut
            grad.addColorStop(0.2, 'rgba(61, 141, 122, 0.0)');
            grad.addColorStop(0, 'rgba(61, 141, 122, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pekerjaan',
                        tension: 0.4,
                        pointRadius: 0,
                        borderColor: '#3D8D7A',
                        backgroundColor: grad,
                        borderWidth: 3,
                        fill: true,
                        data: dataVal,
                        maxBarThickness: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                drawTicks: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                display: true,
                                padding: 10,
                                color: '#6c757d',
                                font: {
                                    size: 11,
                                    family: 'Open Sans'
                                }
                            }
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: false,
                                drawOnChartArea: false,
                                drawTicks: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                display: true,
                                color: '#6c757d',
                                padding: 20,
                                font: {
                                    size: 11,
                                    family: 'Open Sans'
                                }
                            }
                        }
                    }
                }
            });
        })();
    </script>
    <script>
        (function() {
            const modal = document.getElementById('modalFotoDash');
            if (!modal) return;

            modal.addEventListener('show.bs.modal', function(ev) {
                const btn = ev.relatedTarget;
                const sebelum = JSON.parse(btn.getAttribute('data-sebelum') || '[]');
                const sesudah = JSON.parse(btn.getAttribute('data-sesudah') || '[]');

                const wrapS = modal.querySelector('#dash-wrap-sebelum');
                const wrapD = modal.querySelector('#dash-wrap-sesudah');
                wrapS.innerHTML = '';
                wrapD.innerHTML = '';

                const base = @json(asset('storage'));
                const addThumb = (wrap, path) => {
                    const a = document.createElement('a');
                    a.href = base + '/' + path;
                    a.target = '_blank';
                    const img = document.createElement('img');
                    img.src = base + '/' + path;
                    img.className = 'rounded shadow-sm';
                    img.style.width = '96px';
                    img.style.height = '96px';
                    img.style.objectFit = 'cover';
                    a.appendChild(img);
                    wrap.appendChild(a);
                };

                sebelum.forEach(p => addThumb(wrapS, p));
                sesudah.forEach(p => addThumb(wrapD, p));
            });
        })();
    </script>
@endpush
