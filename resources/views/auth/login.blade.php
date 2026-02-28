@extends('layouts.base', ['pageTitle' => 'Partner Manager - Login'])

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark position-fixed w-100 py-3" style="z-index: 1000;">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img alt="logo" src="/img/logo-light.svg"/>
            </a>
        </div>
    </nav>
    <section class="hero-section position-relative overflow-hidden">
        <div class="container" style="position: relative; z-index: 1;">
            <div class="row justify-content-center">
                <div class="col-11 col-md-8 col-lg-6 col-xl-4">
                    <div class="login-card p-4 p-md-6 bg-dark bg-opacity-50 translucent-dark rounded-4">
                        <h2 class="text-center mb-4">Login</h2>
                        <p class="text-center text-white opacity-50 mb-4">Partner Manager</p>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input
                                    class="form-control form-control-lg text-white bg-dark border-light border-opacity-25 bg-opacity-25 @error('email') is-invalid @enderror"
                                    id="email" name="email" type="email" value="{{ old('email') }}" required autofocus/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input
                                    class="form-control form-control-lg text-white bg-dark border-light border-opacity-25 bg-opacity-25 @error('password') is-invalid @enderror"
                                    id="password" name="password" type="password" required/>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            <div class="d-grid mb-3">
                                <button class="btn btn-primary btn-lg bg-primary bg-opacity-75" type="submit">Sign In</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-bg"></div>
    </section>
@endsection
