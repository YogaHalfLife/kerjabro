@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Daftar Pekerjaan (Semua)'])
    <div class="container-fluid py-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="card shadow-sm mb-4">
            <div class="card-header pb-0">
                <h6 class="mb-0">Filter</h6>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('trans.pekerjaan.daftar') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label text-sm mb-1">Cari</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control"
                                placeholder="Detail / Nama pegawai...">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-sm mb-1">Bulan</label>
                        <input type="month" name="bulan" value="{{ $bulan ?? '' }}" class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-sm mb-1">Divisi</label>
                        <select name="id_divisi" class="form-select">
                            <option value="">Semua divisi</option>
                            @foreach ($divisis as $d)
                                <option value="{{ $d->id_divisi }}"
                                    {{ ($divisi ?? '') == $d->id_divisi ? 'selected' : '' }}>
                                    {{ $d->nama_divisi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-1 d-grid">
                        <button class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Semua Pekerjaan</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
<<<<<<< HEAD
                    <table id="table-pekerjaan-riwayat" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>

                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 col-judul-riwayat">
                                    Judul
                                </th>

                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 col-detail-riwayat">
                                    Detail
                                </th>

                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pegawai</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Divisi</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Tanggal
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Foto
                                </th>
=======
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Judul
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Detail
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pegawai
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Divisi
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Tanggal</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Foto</th>
>>>>>>> 2a21b73f38bfed8ceefd0a213e3d435d49607660
                                @if ($isAdmin)
                                    <th class="text-secondary opacity-7"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $i => $row)
                                <tr>
                                    <td>
                                        <h6 class="mb-0 text-sm">
                                            {{ method_exists($data, 'currentPage') ? ($data->currentPage() - 1) * $data->perPage() + $i + 1 : $i + 1 }}
                                        </h6>
                                    </td>
<<<<<<< HEAD

                                    <td class="col-judul-riwayat">
                                        <div class="text-sm lh-sm clamp-2"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
=======
                                    
                                    <td style="max-width:420px;">
                                        <div class="text-sm lh-sm clamp-1" data-bs-toggle="tooltip" data-bs-placement="top"
>>>>>>> 2a21b73f38bfed8ceefd0a213e3d435d49607660
                                            title="{{ trim($row->judul_pekerjaan) }}">
                                            {{ $row->judul_pekerjaan }}
                                        </div>
                                        <small class="text-xs text-secondary">ID: {{ $row->id }}</small>
                                    </td>
<<<<<<< HEAD

                                    <td class="col-detail-riwayat">
                                        <div class="text-sm lh-sm clamp-2"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{ trim($row->detail_pekerjaan) }}">
                                            {{ $row->detail_pekerjaan }}
                                        </div>
                                        <button type="button"
                                                class="btn btn-link p-0 text-xs mt-1 detail-pekerjaan-view"
                                                data-detail="{{ e($row->detail_pekerjaan) }}">
                                            Lihat selengkapnya
                                        </button>
                                    </td>

=======
                                    
                                    <td style="max-width:420px;">
                                        <div class="text-sm lh-sm clamp-2" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ trim($row->detail_pekerjaan) }}">
                                            {{ $row->detail_pekerjaan }}
                                        </div>
                                        <button type="button" class="btn btn-link p-0 text-xs mt-1 detail-pekerjaan-view"
                                            data-detail="{{ e($row->detail_pekerjaan) }}">
                                            Lihat selengkapnya
                                        </button>
                                    </td>
                                    
>>>>>>> 2a21b73f38bfed8ceefd0a213e3d435d49607660
                                    <td style="max-width:260px;">
                                        @if ($row->pegawais->count())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($row->pegawais as $pg)
                                                    <span class="badge bg-gradient-secondary text-xs">
                                                        {{ $pg->nama_pegawai }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
<<<<<<< HEAD

                                    <td style="max-width:260px;">
                                        @php
                                            $divisiList = $row->divisis;

=======
                                    
                                    <td style="max-width:260px;">
                                        @php
                                            $divisiList = $row->divisis;
                                            
>>>>>>> 2a21b73f38bfed8ceefd0a213e3d435d49607660
                                            if (!$divisiList->count() && $row->divisi) {
                                                $divisiList = collect([$row->divisi]);
                                            }
                                        @endphp

                                        @if ($divisiList->count())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($divisiList as $dv)
                                                    <span class="badge bg-gradient-info text-xs">
                                                        {{ $dv->nama_divisi }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span class="text-xs text-secondary">{{ $row->bulan }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
<<<<<<< HEAD
                                                data-bs-toggle="modal" data-bs-target="#modalFoto"
                                                data-sebelum='@json($row->fotos->where('kategori', 'sebelum')->pluck('path')->values())'
                                                data-sesudah='@json($row->fotos->where('kategori', 'sesudah')->pluck('path')->values())'>
=======
                                            data-bs-toggle="modal" data-bs-target="#modalFoto"
                                            data-sebelum='@json($row->fotos->where('kategori', 'sebelum')->pluck('path')->values())'
                                            data-sesudah='@json($row->fotos->where('kategori', 'sesudah')->pluck('path')->values())'>
>>>>>>> 2a21b73f38bfed8ceefd0a213e3d435d49607660
                                            <i class="ni ni-watch-time me-1"></i>
                                            Lihat
                                        </button>
                                    </td>

                                    @if ($isAdmin)
                                        <td class="align-middle text-end pe-4">
                                            <a href="{{ route('trans.pekerjaan.edit', $row->id) }}"
                                                class="text-secondary font-weight-bold text-xs me-3">Edit</a>
                                            <form action="{{ route('trans.pekerjaan.destroy', $row->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="text-danger font-weight-bold text-xs bg-transparent border-0 p-0">Hapus</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center py-5">
                                        <i class="ni ni-folder-17 text-secondary d-block mb-2" style="font-size:28px;"></i>
                                        <span class="text-secondary text-sm">Belum ada data.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (method_exists($data, 'links') && $data->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $data->links() }}
                </div>
            @endif
        </div>

        @include('layouts.footers.auth.footer')
    </div>
    
    <div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
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
                            <div class="d-flex flex-wrap gap-2" id="wrap-sebelum"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <h6 class="text-xs text-secondary mb-2">Sesudah</h6>
                            <div class="d-flex flex-wrap gap-2" id="wrap-sesudah"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="detailPekerjaanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Detail Pekerjaan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <pre id="detailPekerjaanContent" class="mb-0" style="white-space:pre-wrap;font-family:inherit;"></pre>
                </div>
            </div>
        </div>
    </div>


    <style>
        .clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endsection

@push('js')
    <script>
        const modalFoto = document.getElementById('modalFoto');
        if (modalFoto) {
            modalFoto.addEventListener('show.bs.modal', function(ev) {
                const btn = ev.relatedTarget;
                const sebelum = JSON.parse(btn.getAttribute('data-sebelum') || '[]');
                const sesudah = JSON.parse(btn.getAttribute('data-sesudah') || '[]');

                const wS = modalFoto.querySelector('#wrap-sebelum');
                const wD = modalFoto.querySelector('#wrap-sesudah');
                wS.innerHTML = '';
                wD.innerHTML = '';

                const addThumb = (wrap, path) => {
                    const a = document.createElement('a');
                    a.href = '{{ asset('storage') }}/' + path;
                    a.target = '_blank';
                    const img = document.createElement('img');
                    img.src = '{{ asset('storage') }}/' + path;
                    img.className = 'rounded shadow-sm';
                    img.style.width = '96px';
                    img.style.height = '96px';
                    img.style.objectFit = 'cover';
                    a.appendChild(img);
                    wrap.appendChild(a);
                };

                sebelum.forEach(p => addThumb(wS, p));
                sesudah.forEach(p => addThumb(wD, p));
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tts = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tts.forEach(el => new bootstrap.Tooltip(el));
        });
        document.addEventListener('mouseover', (e) => {
            const t = e.target.closest('[data-bs-toggle="tooltip"]');
            if (t && !bootstrap.Tooltip.getInstance(t)) {
                new bootstrap.Tooltip(t);
            }
        });
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.detail-pekerjaan-view');
            if (!btn) return;
            const detail = btn.getAttribute('data-detail') || '';
            document.getElementById('detailPekerjaanContent').textContent = detail;
            const modal = new bootstrap.Modal(document.getElementById('detailPekerjaanModal'));
            modal.show();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('table-pekerjaan-riwayat');
            if (!table) return;

            const ths = table.querySelectorAll('thead th');

            ths.forEach((th, index) => {
                // Kalau mau skip kolom terakhir (misal kolom aksi), bisa un-comment ini:
                // if (index === ths.length - 1) return;

                const resizer = document.createElement('div');
                resizer.classList.add('resizer');
                th.appendChild(resizer);

                let startX, startWidth;

                resizer.addEventListener('mousedown', function (e) {
                    e.preventDefault();

                    startX = e.pageX;
                    startWidth = th.offsetWidth;

                    const onMouseMove = function (eMove) {
                        const delta = eMove.pageX - startX;
                        let newWidth = startWidth + delta;

                        const minWidth = 60; // lebar minimum kolom
                        if (newWidth < minWidth) newWidth = minWidth;

                        // Set lebar TH
                        th.style.width = newWidth + 'px';
                        th.style.minWidth = newWidth + 'px';
                        th.style.maxWidth = newWidth + 'px';

                        // Set lebar semua TD di kolom yang sama
                        const columnIndex = Array.prototype.indexOf.call(th.parentNode.children, th);
                        table.querySelectorAll('tbody tr').forEach(function (tr) {
                            const td = tr.children[columnIndex];
                            if (td) {
                                td.style.width = newWidth + 'px';
                                td.style.minWidth = newWidth + 'px';
                                td.style.maxWidth = newWidth + 'px';
                            }
                        });
                    };

                    const onMouseUp = function () {
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });
        });
    </script>
@endpush
