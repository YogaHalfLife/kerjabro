@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit Divisi'])
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
                        <h6>Edit Divisi</h6>
                    </div>
                    <div class="card-body pt-3 pb-4">
                        <form action="{{ route('divisi.update', $divisi->id_divisi) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label text-sm">Nama Divisi</label>
                                    <input type="text" name="nama_divisi"
                                        value="{{ old('nama_divisi', $divisi->nama_divisi) }}"
                                        class="form-control @error('nama_divisi') is-invalid @enderror" required>
                                    @error('nama_divisi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="isactive" name="isactive"
                                            value="1" {{ old('isactive', $divisi->isactive) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isactive">Aktif</label>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary mb-0">Update</button>
                                    <a href="{{ route('divisi.index') }}" class="btn btn-secondary mb-0">Kembali</a>
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
