@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Master Pegawai'])
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

                {{-- Card: Tambah Pegawai --}}
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0">Tambah Pegawai</h6>
                            <p class="text-xs text-secondary mb-0">Simpan untuk otomatis membuat akun login</p>
                        </div>
                    </div>

                    <div class="card-body pt-3 pb-4">
                        <form action="{{ route('pegawai.store') }}" method="POST" autocomplete="off">
                            @csrf
                            <div class="row g-3 align-items-end">

                                <div class="col-md-3">
                                    <label class="form-label text-sm">Kode Pegawai</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ni ni-badge"></i></span>
                                        <input type="text" name="kode_pegawai" value="{{ old('kode_pegawai') }}"
                                            class="form-control @error('kode_pegawai') is-invalid @enderror"
                                            placeholder="PGW001" required>
                                    </div>
                                    @error('kode_pegawai')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-sm">Nama Pegawai</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ni ni-single-02"></i></span>
                                        <input type="text" name="nama_pegawai" value="{{ old('nama_pegawai') }}"
                                            class="form-control @error('nama_pegawai') is-invalid @enderror"
                                            placeholder="Nama lengkap" required>
                                    </div>
                                    @error('nama_pegawai')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label text-sm">Divisi</label>
                                    <select name="id_divisi" class="form-select @error('id_divisi') is-invalid @enderror"
                                        required>
                                        <option value="">Pilih Divisi</option>
                                        @foreach ($divisi as $d)
                                            <option value="{{ $d->id_divisi }}"
                                                {{ old('id_divisi') == $d->id_divisi ? 'selected' : '' }}>
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
                                            value="1" {{ old('isactive', '1') ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="isactive">Aktif</label>
                                    </div>
                                </div>

                                <div class="col-12 d-grid d-md-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ni ni-check-bold me-1"></i> Simpan & Buat Akun
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                {{-- Card: Daftar Pegawai --}}
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Daftar Pegawai</h6>

                        <form action="{{ route('pegawai.index') }}" method="GET" class="d-none d-sm-flex">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                                <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}"
                                    placeholder="Cari nama/kode...">
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
                                            Kode</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Nama Pegawai</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Divisi</th>
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
                                    @forelse($pegawai as $i => $row)
                                        <tr>
                                            <td>
                                                <h6 class="mb-0 text-sm">
                                                    {{ method_exists($pegawai, 'currentPage') ? ($pegawai->currentPage() - 1) * $pegawai->perPage() + $i + 1 : $i + 1 }}
                                                </h6>
                                            </td>
                                            <td><span class="text-sm font-weight-bold">{{ $row->kode_pegawai }}</span></td>
                                            <td>{{ $row->nama_pegawai }}</td>
                                            <td>{{ optional($row->divisi)->nama_divisi }}</td>
                                            <td class="align-middle text-center">
                                                @if ($row->isactive)
                                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Non Aktif</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <span
                                                    class="text-secondary text-xs font-weight-bold">{{ optional($row->created_at)->format('d/m/Y') }}</span>
                                            </td>
                                            <td class="align-middle text-end pe-4">
                                                <a href="{{ route('pegawai.edit', $row->id) }}"
                                                    class="text-secondary font-weight-bold text-xs me-3">Edit</a>

                                                <form action="{{ route('pegawai.destroy', $row->id) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="text-danger font-weight-bold text-xs bg-transparent border-0 p-0">Hapus</button>
                                                </form>

                                                <form action="{{ route('pegawai.toggle', $row->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button class="text-xs bg-transparent border-0 p-0 ms-3">
                                                        <span
                                                            class="badge {{ $row->isactive ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                            Toggle
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
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

                    @if (method_exists($pegawai, 'links') && $pegawai->hasPages())
                        <div class="card-footer d-flex justify-content-end">
                            {{ $pegawai->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

        @include('layouts.footers.auth.footer')
    </div>
@endsection
