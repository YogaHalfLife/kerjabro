@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Your Profile'])

    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body p-3">
            <div class="row gx-4">
                <div class="col-auto">
                    <div class="avatar avatar-xl position-relative">
                        <img src="/img/user.png" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
                    </div>
                </div>
                <div class="col-auto my-auto">
                    <div class="h-100">
                        <h5 class="mb-1">
                            {{ auth()->user()->firstname ?? 'Firstname' }} {{ auth()->user()->lastname ?? 'Lastname' }}
                        </h5>
                        @php
                            $user = auth()->user();
                            $divisiText = '—';

                            if ($user) {
                                if ($user->username === 'admin') {
                                    $divisiText = 'Administrator';
                                } else {
                                    $pegawai =
                                        \App\Models\MasterPegawai::where('kode_pegawai', $user->username)->first() ??
                                        (isset($user->pegawai_id)
                                            ? \App\Models\MasterPegawai::find($user->pegawai_id)
                                            : null);

                                    if ($pegawai) {
                                        $divisi = \App\Models\MasterDivisi::find($pegawai->id_divisi);
                                        $divisiText = $divisi->nama_divisi ?? '—';
                                    }
                                }
                            }
                        @endphp

                        <p class="mb-0 font-weight-bold text-sm">
                            {{ $divisiText }}
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERTS: inline fallback + komponen --}}
    <div class="mx-4 mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('status') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('error') !!}
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
    </div>

    <div id="alert" class="mx-4">
        @include('components.alert')
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            {{-- Form: Info Profil --}}
            <div class="col-md-8">
                <div class="card">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <p class="mb-0">User Profile</p>
                                <button type="submit" class="btn btn-primary btn-sm ms-auto">Save</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">Username</label>
                                        <input class="form-control @error('username') is-invalid @enderror bg-gray-100"
                                            type="text" name="username"
                                            value="{{ old('username', auth()->user()->username) }}" readonly
                                            aria-readonly="true">
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">Email address</label>
                                        <input class="form-control @error('email') is-invalid @enderror" type="email"
                                            name="email" value="{{ old('email', auth()->user()->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">First name</label>
                                        <input class="form-control @error('firstname') is-invalid @enderror" type="text"
                                            name="firstname" value="{{ old('firstname', auth()->user()->firstname) }}">
                                        @error('firstname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">Last name</label>
                                        <input class="form-control @error('lastname') is-invalid @enderror" type="text"
                                            name="lastname" value="{{ old('lastname', auth()->user()->lastname) }}">
                                        @error('lastname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form: Ubah Password --}}
            <div class="col-md-4 mt-4 mt-md-0">
                <div class="card">
                    <form method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        <div class="card-header pb-0">
                            <p class="mb-0">Ubah Password</p>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-control-label">Password saat ini</label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    autocomplete="current-password">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-control-label">Password baru</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-control-label">Konfirmasi password baru</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    autocomplete="new-password">
                            </div>

                            <button class="btn btn-primary btn-sm w-100">Update Password</button>

                            <small class="text-muted d-block mt-2">
                                Minimal 8 karakter. Pastikan aman & jangan dibagikan.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
    <script>
        window.addEventListener('load', function() {
            document.querySelectorAll('.alert').forEach(function(el) {
                setTimeout(function() {
                    const dismiss = el.querySelector('[data-bs-dismiss="alert"]');
                    if (dismiss) dismiss.click();
                    else el.classList.remove('show');
                }, 4000);
            });
        });
    </script>
@endpush
