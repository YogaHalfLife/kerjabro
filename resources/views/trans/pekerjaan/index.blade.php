@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Transaksi Pekerjaan'])
<div class="container-fluid py-4">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">

            <div class="card mb-4">
                <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0">Tambah Pekerjaan</h6>
                        <p class="text-xs text-secondary mb-0">Drop / paste gambar untuk “Sebelum” & “Sesudah”.</p>
                    </div>
                </div>

                <div class="card-body pt-3 pb-4">
                    <form action="{{ route('trans.pekerjaan.store') }}" method="POST" enctype="multipart/form-data"
                        autocomplete="off">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-sm">Judul Pekerjaan</label>
                                <input type="text" name="judul_pekerjaan" value="{{ old('judul_pekerjaan') }}"
                                    class="form-control @error('judul_pekerjaan') is-invalid @enderror" required
                                    maxlength="200">
                                @error('judul_pekerjaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-sm">Detail Pekerjaan</label>
                                <textarea name="detail_pekerjaan" rows="3" class="form-control @error('detail_pekerjaan') is-invalid @enderror"
                                    placeholder="Deskripsikan pekerjaan...">{{ old('detail_pekerjaan') }}</textarea>
                                @error('detail_pekerjaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-sm">Pegawai</label>

                                @if ($isAdmin)
                                <select name="pegawai_id"
                                    class="form-select @error('pegawai_id') is-invalid @enderror" required>
                                    <option value="">Pilih Pegawai</option>
                                    @foreach ($pegawais as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_pegawai }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('pegawai_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @else
                                <input type="text" class="form-control"
                                    value="{{ $pegawaiLogin ? $pegawaiLogin->nama_pegawai : '—' }}" disabled>
                                @if ($pegawaiLogin)
                                <input type="hidden" name="pegawai_id" value="{{ $pegawaiLogin->id }}">
                                @endif
                                @endif
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-sm">Divisi</label>

                                @if ($isAdmin)
                                <select name="id_divisi"
                                    class="form-select @error('id_divisi') is-invalid @enderror" required>
                                    <option value="">Pilih Divisi</option>
                                    @foreach ($divisis as $d)
                                    <option value="{{ $d->id_divisi }}"
                                        {{ old('id_divisi') == $d->id_divisi ? 'selected' : '' }}>
                                        {{ $d->nama_divisi }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('id_divisi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @else
                                <input type="text" class="form-control"
                                    value="{{ $divisiLogin ? $divisiLogin->nama_divisi : '—' }}" disabled>
                                @if ($divisiLogin)
                                <input type="hidden" name="id_divisi" value="{{ $divisiLogin->id_divisi }}">
                                @endif
                                @endif
                            </div>

                            <div class="col-md-3">
                                <label for="bulan" class="form-label text-sm">Tanggal</label>
                                <input
                                    id="bulan"
                                    type="date"
                                    name="bulan"
                                    value="{{ old('bulan', now()->toDateString()) }}"
                                    max="{{ now()->toDateString() }}" {{-- tidak boleh lebih dari hari ini --}}
                                    class="form-control @error('bulan') is-invalid @enderror"
                                    required>
                                @error('bulan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>



                            <div class="col-12">
                                <label class="form-label text-sm mb-1">Foto Sebelum</label>
                                <div class="dropzone-arg border border-2 border-dashed p-3 rounded-3 text-center"
                                    data-input="#foto_sebelum_input">
                                    <i class="ni ni-image text-secondary d-block mb-2" style="font-size:24px;"></i>
                                    <div class="small text-secondary">
                                        Drop / paste gambar di sini<br>
                                        atau klik tombol di bawah untuk memilih dari galeri
                                    </div>

                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary dz-browse">
                                            Pilih dari galeri
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary dz-clear ms-2">
                                            Bersihkan
                                        </button>
                                    </div>

                                    <input id="foto_sebelum_input" type="file" name="foto_sebelum[]" class="d-none"
                                        accept=".jpg,.jpeg,.png,.webp" multiple>

                                    <div class="thumbs d-flex flex-wrap gap-2 mt-3"></div>
                                </div>

                                @error('foto_sebelum.*')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label text-sm mb-1">Foto Sesudah</label>
                                <div class="dropzone-arg border border-2 border-dashed p-3 rounded-3 text-center"
                                    data-input="#foto_sesudah_input">
                                    <i class="ni ni-image text-secondary d-block mb-2" style="font-size:24px;"></i>
                                    <div class="small text-secondary">
                                        Drop / paste gambar di sini<br>
                                        atau klik tombol di bawah untuk memilih dari galeri
                                    </div>

                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary dz-browse">
                                            Pilih dari galeri
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary dz-clear ms-2">
                                            Bersihkan
                                        </button>
                                    </div>

                                    <input id="foto_sesudah_input" type="file" name="foto_sesudah[]"
                                        class="d-none" accept=".jpg,.jpeg,.png,.webp" multiple>

                                    <div class="thumbs d-flex flex-wrap gap-2 mt-3"></div>
                                </div>

                                @error('foto_sesudah.*')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-grid d-md-block">
                                <button class="btn btn-primary"><i class="ni ni-check-bold me-1"></i> Simpan</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Daftar Pekerjaan</h6>
                    <form method="GET" action="{{ route('trans.pekerjaan.index') }}"
                        class="d-none d-sm-flex gap-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                            <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}"
                                placeholder="Cari detail/nama pegawai...">
                        </div>
                        <input type="month" name="bulan" value="{{ $bulan ?? '' }}"
                            class="form-control form-control-sm">
                        <select name="id_divisi" class="form-select form-select-sm" style="max-width:220px;">
                            @if ($isAdmin)
                            <option value="">-- PILIH --</option>
                            @endif
                            @foreach ($divisis as $d)
                            <option value="{{ $d->id_divisi }}"
                                {{ ($divisi ?? '') == $d->id_divisi ? 'selected' : '' }}>
                                {{ $d->nama_divisi }}
                            </option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-primary">Filter</button>
                    </form>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#
                                    </th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Judul</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Detail</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Pegawai</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Divisi</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Tanggal</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Sebelum</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Sesudah</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $i => $row)
                                @php
                                $no = method_exists($data, 'currentPage')
                                ? ($data->currentPage() - 1) * $data->perPage() + $i + 1
                                : $i + 1;
                                $listSebelum = $row->fotos
                                ->where('kategori', 'sebelum')
                                ->values()
                                ->map(fn($f) => asset('storage/' . $f->path));
                                $listSesudah = $row->fotos
                                ->where('kategori', 'sesudah')
                                ->values()
                                ->map(fn($f) => asset('storage/' . $f->path));
                                @endphp
                                <tr>
                                    {{-- No --}}
                                    <td>
                                        <h6 class="mb-0 text-sm">{{ $no }}</h6>
                                    </td>

                                    {{-- Judul --}}
                                    <td style="max-width: 260px;">
                                        <div class="d-block text-truncate" style="max-width: 260px;">
                                            <h6 class="mb-0 text-sm text-dark">{{ $row->judul_pekerjaan }}</h6>
                                        </div>
                                        <small class="text-xs text-secondary">ID: {{ $row->id }}</small>
                                    </td>

                                    {{-- Detail (truncate 2 lines) --}}
                                    <td style="max-width:360px;">
                                        {{-- ringkas 2 baris + tooltip hover --}}
                                        <div class="text-sm lh-sm clamp-2" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="{{ trim($row->detail_pekerjaan) }}">
                                            {{ $row->detail_pekerjaan }}
                                        </div>

                                        {{-- tombol buka modal untuk baca penuh --}}
                                        <button type="button"
                                            class="btn btn-link p-0 text-xs mt-1 detail-pekerjaan-view"
                                            data-detail="{{ e($row->detail_pekerjaan) }}">
                                            Lihat selengkapnya
                                        </button>
                                    </td>

                                    {{-- Pegawai --}}
                                    <td>
                                        <p class="text-sm mb-0">{{ optional($row->pegawai)->nama_pegawai }}</p>
                                    </td>

                                    {{-- Divisi --}}
                                    <td>
                                        <p class="text-sm mb-0">{{ optional($row->divisi)->nama_divisi }}</p>
                                    </td>

                                    {{-- Bulan --}}
                                    <td class="text-center">
                                        <span class="text-xs text-secondary">{{ $row->bulan }}</span>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary view-fotos"
                                            data-type="Sebelum" data-id="{{ $row->id }}"
                                            data-fotos='@json($listSebelum)'>
                                            <i class="fas fa-eye me-1"></i> Lihat
                                            <span class="badge bg-primary ms-1">{{ $listSebelum->count() }}</span>
                                        </button>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-success view-fotos"
                                            data-type="Sesudah" data-id="{{ $row->id }}"
                                            data-fotos='@json($listSesudah)'>
                                            <i class="fas fa-eye me-1"></i> Lihat
                                            <span class="badge bg-success ms-1">{{ $listSesudah->count() }}</span>
                                        </button>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="align-middle text-end pe-4">
                                        <a href="{{ route('trans.pekerjaan.edit', $row->id) }}"
                                            class="text-secondary font-weight-bold text-xs me-3">Edit</a>
                                        <form action="{{ route('trans.pekerjaan.destroy', $row->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-danger font-weight-bold text-xs bg-transparent border-0 p-0">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="ni ni-folder-17 text-secondary d-block mb-2"
                                            style="font-size:28px;"></i>
                                        <span class="text-secondary text-sm">Belum ada data.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>

                <div class="modal fade" id="fotosModal" tabindex="-1" aria-labelledby="fotosModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 class="modal-title" id="fotosModalLabel">Foto</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="fotosContainer" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="detailPekerjaanModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header py-2">
                                <h6 class="modal-title">Detail Pekerjaan</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <pre id="detailPekerjaanContent" class="mb-0" style="white-space:pre-wrap;font-family:inherit;"></pre>
                            </div>
                        </div>
                    </div>
                </div>

                @if (method_exists($data, 'links') && $data->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $data->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('layouts.footers.auth.footer')
</div>

<style>
    #fotosModal .modal-dialog {
        max-width: none;
    }

    #fotosContainer {
        display: grid;
        gap: 12px;
    }

    #fotosContainer a {
        display: block;
    }

    #fotosContainer img {
        width: 160px;
        height: 120px;
        object-fit: cover;
        border-radius: .5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        background: #f6f9fc;
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
    (function() {
        function syncInputFiles(input, fileObjs) {
            const dt = new DataTransfer();
            for (const fo of fileObjs) dt.items.add(fo.file);
            input.files = dt.files;
        }
        function renderThumbs(zone) {
            const thumbs = zone.querySelector('.thumbs');
            thumbs.innerHTML = '';
            (zone._files || []).forEach((fo) => {
                const wrap = document.createElement('div');
                wrap.className = 'position-relative';

                const img = document.createElement('img');
                img.className = 'rounded shadow-sm';
                img.style.width = '64px';
                img.style.height = '64px';
                img.style.objectFit = 'cover';
                img.src = URL.createObjectURL(fo.file);
                img.onload = () => URL.revokeObjectURL(img.src);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.title = 'Hapus';
                btn.textContent = '×';
                btn.className =
                    'btn btn-xs btn-danger p-0 d-flex align-items-center justify-content-center';
                btn.style.position = 'absolute';
                btn.style.top = '-6px';
                btn.style.right = '-6px';
                btn.style.width = '18px';
                btn.style.height = '18px';
                btn.style.borderRadius = '50%';
                btn.style.lineHeight = '1';

                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    zone._files = (zone._files || []).filter(x => x.id !== fo.id);
                    const input = zone.querySelector(zone.dataset.input) || zone.querySelector(
                        'input[type="file"]');
                    syncInputFiles(input, zone._files);
                    renderThumbs(zone);
                });

                wrap.appendChild(img);
                wrap.appendChild(btn);
                thumbs.appendChild(wrap);
            });
        }
        function addFiles(zone, files) {
            if (!zone._files) zone._files = [];
            const input = zone.querySelector(zone.dataset.input) || zone.querySelector('input[type="file"]');

            const list = Array.from(files || []).filter(f => f && f.type && f.type.startsWith('image/'));
            for (const f of list) {
                const id = Date.now().toString(36) + Math.random().toString(36).slice(2);
                zone._files.push({
                    id,
                    file: f
                });
            }
            syncInputFiles(input, zone._files);
            renderThumbs(zone);
        }
        document.querySelectorAll('.dropzone-arg').forEach(zone => {
            zone._files = [];

            const input = zone.querySelector(zone.dataset.input) || zone.querySelector(
                'input[type="file"]');
            const clearBtn = zone.querySelector('.dz-clear');
            const browseBtn = zone.querySelector('.dz-browse');
            if (browseBtn) {
                browseBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    input.click();
                });
            }
            input.addEventListener('change', (e) => {
                addFiles(zone, e.target.files);
            });
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('border-primary');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('border-primary'));
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('border-primary');
                addFiles(zone, e.dataTransfer.files);
            });
            zone.addEventListener('paste', (e) => {
                const items = e.clipboardData?.items || [];
                const files = [];
                for (const it of items) {
                    if (it.kind === 'file') {
                        const f = it.getAsFile();
                        if (f) files.push(f);
                    }
                }
                if (files.length) addFiles(zone, files);
            });
            if (clearBtn) {
                clearBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    zone._files = [];
                    syncInputFiles(input, zone._files);
                    renderThumbs(zone);
                });
            }
        });
    })();
</script>
<script>
    (function() {
        const modalEl = document.getElementById('fotosModal');
        const dialogEl = modalEl.querySelector('.modal-dialog');
        const modal = new bootstrap.Modal(modalEl);
        const container = document.getElementById('fotosContainer');
        const titleEl = document.getElementById('fotosModalLabel');
        function layoutFor(count) {
            const thumbW = 160; // lebar thumbnail
            const gap = 12; // gap grid
            const pad = 64; // padding + border modal approx
            const margin = 48; // jarak aman dari kiri/kanan viewport
            const cols = Math.max(1, Math.min(count, 4));
            container.style.gridTemplateColumns = `repeat(${cols}, ${thumbW}px)`;
            const contentWidth = cols * thumbW + (cols - 1) * gap;
            const vw = window.innerWidth;
            const maxAllow = vw - margin;
            const minW = 420; // min modal width
            const maxW = Math.min(980, maxAllow); // cap maksimal
            const target = Math.max(minW, Math.min(contentWidth + pad, maxW));

            dialogEl.style.maxWidth = target + 'px';
            dialogEl.style.width = 'auto';
        }

        function renderImages(urls) {
            container.innerHTML = '';
            if (!urls.length) {
                container.innerHTML = '<p class="text-secondary text-sm mb-0">Tidak ada foto.</p>';
                layoutFor(1);
                return;
            }
            urls.forEach(url => {
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'foto';
                a.appendChild(img);
                container.appendChild(a);
            });
            layoutFor(urls.length);
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.view-fotos');
            if (!btn) return;

            const type = btn.getAttribute('data-type') || 'Foto';
            const id = btn.getAttribute('data-id') || '';
            let urls = [];
            try {
                urls = JSON.parse(btn.getAttribute('data-fotos') || '[]');
            } catch (_) {}

            titleEl.textContent = `Foto ${type} — #${id}`;
            renderImages(urls);
            modal.show();
        });

        window.addEventListener('resize', () => {
            if (modalEl.classList.contains('show')) {
                layoutFor(container.querySelectorAll('img').length || 1);
            }
        });
    })();
</script>
<script>
    const ttEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    ttEls.forEach(el => new bootstrap.Tooltip(el));
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.detail-pekerjaan-view');
        if (!btn) return;
        const detail = btn.getAttribute('data-detail') || '';
        document.getElementById('detailPekerjaanContent').textContent = detail;
        const modal = new bootstrap.Modal(document.getElementById('detailPekerjaanModal'));
        modal.show();
    });
</script>
@endpush