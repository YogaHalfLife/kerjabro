@extends('layouts.app')

@section('content')
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                @include('layouts.navbars.guest.navbar')
            </div>
        </div>
    </div>
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <h4 class="font-weight-bolder">Masuk</h4>
                                    <p class="mb-0">Masukkan <strong>username</strong> dan password anda</p>
                                </div>
                                <div class="card-body">
                                    <form role="form" method="POST" action="{{ route('login.perform') }}">
                                        @csrf
                                        @method('post')

                                        <div class="flex flex-col mb-3">
                                            <input type="text" name="username" class="form-control form-control-lg"
                                                value="{{ old('username') }}" placeholder="Username" aria-label="Username">
                                            @error('username')
                                                <p class="text-danger text-xs pt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="flex flex-col mb-3">
                                            <input type="password" name="password" class="form-control form-control-lg"
                                                placeholder="Password" aria-label="Password">
                                            @error('password')
                                                <p class="text-danger text-xs pt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg btn-primary w-100 mt-4 mb-0">Masuk</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image:url('https://pijarkepri.com/wp-content/uploads/2024/03/IMG_20240307_145554.jpg'); background-size:cover;">
                                <span class="mask bg-gradient-primary opacity-6"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">"Grandescunt aucta labore"</h4>
                                <p class="text-white position-relative">Dengan kerja keras, segala sesuatu bertambah dan tumbuh. </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
