@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Edit Pekerjaan'])
<div class="container-fluid py-4">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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
                <div class="card-header pb-0">
                    <h6>Edit Pekerjaan</h6>
                </div>
                <div class="card-body pt-3 pb-4">
                    <form action="{{ route('trans.pekerjaan.update', $pekerjaan->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-sm">Judul Pekerjaan</label>
                                <input type="text" name="judul_pekerjaan"
                                    value="{{ old('judul_pekerjaan', $pekerjaan->judul_pekerjaan) }}"
                                    class="form-control @error('judul_pekerjaan') is-invalid @enderror" required
                                    maxlength="200">
                                @error('judul_pekerjaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-sm">Detail Pekerjaan</label>
                                <textarea name="detail_pekerjaan" rows="3" class="form-control @error('detail_pekerjaan') is-invalid @enderror"
                                    required>{{ old('detail_pekerjaan', $pekerjaan->detail_pekerjaan) }}</textarea>
                                @error('detail_pekerjaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-sm">Pegawai</label>
                                @if ($isAdmin)
                                <select name="pegawai_id"
                                    class="form-select @error('pegawai_id') is-invalid @enderror" required>
                                    @foreach ($pegawais as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('pegawai_id', $pekerjaan->pegawai_id) == $p->id ? 'selected' : '' }}>
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
                                    @foreach ($divisis as $d)
                                    <option value="{{ $d->id_divisi }}"
                                        {{ old('id_divisi', $pekerjaan->id_divisi) == $d->id_divisi ? 'selected' : '' }}>
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

                            @php
                            use Carbon\Carbon;
                            $tglEdit = old('bulan', optional(Carbon::parse($pekerjaan->bulan))->toDateString());
                            @endphp

                            <div class="col-md-3">
                                <label for="bulan" class="form-label text-sm">Tanggal</label>
                                <input
                                    id="bulan"
                                    type="date"
                                    name="bulan"
                                    value="{{ $tglEdit }}"
                                    max="{{ now()->toDateString() }}"
                                    class="form-control @error('bulan') is-invalid @enderror"
                                    required>
                                @error('bulan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-12">
                                <label class="form-label text-sm mb-1">Foto Sebelum</label>
                                @if ($pekerjaan->fotosSebelum->count())
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @foreach ($pekerjaan->fotosSebelum as $f)
                                    <div class="position-relative">
                                        <a href="{{ asset('storage/' . $f->path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $f->path) }}"
                                                class="rounded shadow-sm"
                                                style="width:80px;height:80px;object-fit:cover;">
                                        </a>

                                        <button type="button"
                                            class="btn btn-xs btn-danger p-1 position-absolute top-0 end-0"
                                            title="Hapus" onclick="submitDeleteFoto({{ $f->id }})">
                                            &times;
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                <div class="dropzone-arg border border-2 border-dashed p-3 rounded-3 text-center"
                                    data-input="#edit_foto_sebelum_input">
                                    <i class="ni ni-image text-secondary d-block mb-2" style="font-size:24px;"></i>
                                    <span class="text-xs text-secondary">Drop/paste gambar untuk menambah</span>
                                    <input id="edit_foto_sebelum_input" type="file" name="foto_sebelum[]"
                                        class="d-none" accept=".jpg,.jpeg,.png,.webp" multiple>
                                    <div class="thumbs d-flex flex-wrap gap-2 mt-3"></div>
                                </div>
                                @error('foto_sebelum.*')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label text-sm mb-1">Foto Sesudah</label>
                                @if ($pekerjaan->fotosSesudah->count())
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @foreach ($pekerjaan->fotosSesudah as $f)
                                    <div class="position-relative">
                                        <a href="{{ asset('storage/' . $f->path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $f->path) }}"
                                                class="rounded shadow-sm"
                                                style="width:80px;height:80px;object-fit:cover;">
                                        </a>

                                        <button type="button"
                                            class="btn btn-xs btn-danger p-1 position-absolute top-0 end-0"
                                            title="Hapus" onclick="submitDeleteFoto({{ $f->id }})">
                                            &times;
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                <div class="dropzone-arg border border-2 border-dashed p-3 rounded-3 text-center"
                                    data-input="#edit_foto_sesudah_input">
                                    <i class="ni ni-image text-secondary d-block mb-2" style="font-size:24px;"></i>
                                    <span class="text-xs text-secondary">Drop/paste gambar untuk menambah</span>
                                    <input id="edit_foto_sesudah_input" type="file" name="foto_sesudah[]"
                                        class="d-none" accept=".jpg,.jpeg,.png,.webp" multiple>
                                    <div class="thumbs d-flex flex-wrap gap-2 mt-3"></div>
                                </div>
                                @error('foto_sesudah.*')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-grid d-md-block">
                                <button class="btn btn-primary"><i class="ni ni-check-bold me-1"></i> Update</button>
                                <a href="{{ route('trans.pekerjaan.index') }}"
                                    class="btn btn-secondary ms-2">Kembali</a>
                            </div>

                        </div>
                    </form>

                    <form id="delete-foto-form" method="POST" class="d-none"
                        data-template="{{ route('trans.pekerjaan.foto.destroy', 'ID_PLACEHOLDER') }}">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

        </div>
    </div>

    @include('layouts.footers.auth.footer')
</div>
@endsection

@push('js')
<script>
    function submitDeleteFoto(fotoId) {
        if (!confirm('Hapus foto ini?')) return;
        const form = document.getElementById('delete-foto-form');
        const urlTemplate = form.getAttribute('data-template');
        form.action = urlTemplate.replace('ID_PLACEHOLDER', String(fotoId));
        form.submit();
    }

    (function() {
        function appendFilesToInput(input, newFiles) {
            const dt = new DataTransfer();
            for (const f of input.files) dt.items.add(f);
            for (const f of newFiles) dt.items.add(f);
            input.files = dt.files;
        }

        function previewThumbs(zone, files) {
            const thumbs = zone.querySelector('.thumbs');
            for (const file of files) {
                if (!file.type.startsWith('image/')) continue;
                const url = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = url;
                img.className = 'rounded shadow-sm';
                img.style.width = '64px';
                img.style.height = '64px';
                img.style.objectFit = 'cover';
                thumbs.appendChild(img);
                img.onload = () => URL.revokeObjectURL(url);
            }
        }

        document.querySelectorAll('.dropzone-arg').forEach(zone => {
            const input = document.querySelector(zone.dataset.input);

            zone.addEventListener('click', (e) => {
                if (e.target.tagName.toLowerCase() === 'img' || e.target.closest('button')) return;
                input.value =
                    null;
                input.click();
            });

            input.addEventListener('change', (e) => previewThumbs(zone, e.target.files));

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('border-primary');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('border-primary'));
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('border-primary');
                const files = Array.from(e.dataTransfer.files || []);
                if (!files.length) return;
                appendFilesToInput(input, files);
                previewThumbs(zone, files);
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
                if (!files.length) return;
                appendFilesToInput(input, files);
                previewThumbs(zone, files);
            });
        });
    })();
</script>
@endpush