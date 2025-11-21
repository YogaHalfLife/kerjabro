@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit Pegawai'])
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
                        <h6>Edit Pegawai</h6>
                    </div>
                    <div class="card-body pt-3 pb-4">
                        <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" autocomplete="off">
                            @csrf @method('PUT')
                            <div class="row g-3 align-items-end">

                                <div class="col-md-3">
                                    <label class="form-label text-sm">Kode Pegawai</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ni ni-badge"></i></span>
                                        <input type="text" name="kode_pegawai"
                                            value="{{ old('kode_pegawai', $pegawai->kode_pegawai) }}"
                                            class="form-control @error('kode_pegawai') is-invalid @enderror" required>
                                    </div>
                                    @error('kode_pegawai')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-sm">Nama Pegawai</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ni ni-single-02"></i></span>
                                        <input type="text" name="nama_pegawai"
                                            value="{{ old('nama_pegawai', $pegawai->nama_pegawai) }}"
                                            class="form-control @error('nama_pegawai') is-invalid @enderror" required>
                                    </div>
                                    @error('nama_pegawai')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label text-sm">Divisi</label>
                                    <select name="id_divisi" class="form-select @error('id_divisi') is-invalid @enderror"
                                        required>
                                        @foreach ($divisi as $d)
                                            <option value="{{ $d->id_divisi }}"
                                                {{ old('id_divisi', $pegawai->id_divisi) == $d->id_divisi ? 'selected' : '' }}>
                                                {{ $d->nama_divisi }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_divisi')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label text-sm d-block">Status</label>
                                    <div class="form-check form-switch ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" id="isactive" name="isactive"
                                            value="1" {{ old('isactive', $pegawai->isactive) ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="isactive">Aktif</label>
                                    </div>
                                </div>

                                <div class="col-12 d-grid d-md-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ni ni-check-bold me-1"></i> Update
                                    </button>
                                    <a href="{{ route('pegawai.index') }}" class="btn btn-secondary ms-2">Kembali</a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        @include('layouts.footers.auth.footer')
    </div>
@endsection
