@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Master Divisi'])
    <div class="container-fluid py-4">

        {{-- Flash --}}
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

                {{-- ===== Card: Tambah Divisi ===== --}}
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0">Tambah Divisi</h6>
                            <p class="text-xs text-secondary mb-0">Isi nama divisi lalu simpan</p>
                        </div>
                    </div>

                    <div class="card-body pt-3 pb-4">
                        <form action="{{ route('divisi.store') }}" method="POST" autocomplete="off">
                            @csrf
                            <div class="row g-3 align-items-end">

                                {{-- Nama Divisi (input-group Argon) --}}
                                <div class="col-md-6">
                                    <label class="form-label text-sm">Nama Divisi</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ni ni-archive-2"></i></span>
                                        <input type="text" name="nama_divisi" value="{{ old('nama_divisi') }}"
                                            class="form-control @error('nama_divisi') is-invalid @enderror" required>
                                        @error('nama_divisi')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Switch Aktif (gaya Argon) --}}
                                <div class="col-md-3">
                                    <label class="form-label text-sm d-block">Status</label>
                                    <div class="form-check form-switch ps-0">
                                        <input class="form-check-input ms-0" type="checkbox" id="isactive" name="isactive"
                                            value="1" {{ old('isactive', '1') ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="isactive">Aktif</label>
                                    </div>
                                </div>

                                {{-- Tombol Simpan --}}
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ni ni-check-bold me-1"></i> Simpan
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                {{-- ===== Card: Daftar Divisi ===== --}}
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Daftar Divisi</h6>

                        {{-- Search kecil (opsional) --}}
                        <form action="{{ route('divisi.index') }}" method="GET"
                            class="d-none d-sm-flex align-items-center">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                                <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}"
                                    placeholder="Cari nama divisi...">
                            </div>
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
                                            Nama Divisi</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Dibuat</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($divisi as $i => $row)
                                        <tr>
                                            <td>
                                                <h6 class="mb-0 text-sm">
                                                    @if (method_exists($divisi, 'currentPage'))
                                                        {{ ($divisi->currentPage() - 1) * $divisi->perPage() + $i + 1 }}
                                                    @else
                                                        {{ $i + 1 }}
                                                    @endif
                                                </h6>
                                            </td>

                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $row->nama_divisi }}</h6>
                                                        <p class="text-xs text-secondary mb-0">ID: {{ $row->id_divisi }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="align-middle text-center">
                                                @if ($row->isactive)
                                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Non Aktif</span>
                                                @endif
                                            </td>

                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ optional($row->created_at)->format('d/m/Y') }}
                                                </span>
                                            </td>

                                            <td class="align-middle text-end pe-4">
                                                <a href="{{ route('divisi.edit', $row->id_divisi) }}"
                                                    class="text-secondary font-weight-bold text-xs me-3">
                                                    Edit
                                                </a>
                                                <form action="{{ route('divisi.destroy', $row->id_divisi) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin hapus data ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="text-danger font-weight-bold text-xs bg-transparent border-0 p-0">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
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

                    @if (method_exists($divisi, 'links') && $divisi->hasPages())
                        <div class="card-footer d-flex justify-content-end">
                            {{ $divisi->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

        @include('layouts.footers.auth.footer')
    </div>
@endsection
