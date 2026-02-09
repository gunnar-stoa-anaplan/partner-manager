@extends('layouts.base', ['pageTitle' => 'SmartAdmin - Modern Admin Dashboard'])



@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark position-fixed w-100 py-3" style="z-index: 1000;">
        <div class="container">
            <a class="navbar-brand" href="{{ route('any', 'index') }}">
                <img alt="logo" src="/img/logo-light.svg" />
            </a>
            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-link text-white border-0 text-decoration-none" href="{{ route('second', [ 'auth' , 'login']) }}">Login</a>
                <a class="btn btn-link text-white border-0 text-decoration-none" href="{{ route('second', [ 'auth' , 'register']) }}">Register</a>
            </div>
        </div>
    </nav>
    <!-- Login Page -->
    <section class="hero-section position-relative overflow-hidden">
        <div class="container" style="position: relative; z-index: 1;">
            <div class="row justify-content-center">
                <div class="col-11 col-md-8 col-lg-6 col-xl-4">
                    <div class="login-card p-4 p-md-6 bg-dark bg-opacity-50 translucent-dark rounded-4">
                        <h2 class="text-center mb-4">Two-Factor Authentication</h2>
                        <p class="text-center text-white opacity-50 mb-4">
                            Enter the 6-digit code sent to your email or phone
                        </p>
                        <form>
                            <div class="mb-4">
                                <label class="form-label" for="otp">Authentication Code</label>
                                <input
                                    class="form-control form-control-lg text-white bg-dark border-light border-opacity-25 bg-opacity-25 text-center"
                                    id="otp" maxlength="6" placeholder="123456" required="" type="text" />
                            </div>
                            <div class="mb-3 d-grid">
                                <button class="btn btn-primary btn-lg bg-primary bg-opacity-75"
                                    type="submit">Verify</button>
                            </div>
                            <div class="text-center opacity-75">
                                Didn't receive a code? <a class="text-decoration-underline text-white fw-500"
                                    href="#!">Resend</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="net"></div>
    </section>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/auth-animation.js'])
@endsection
