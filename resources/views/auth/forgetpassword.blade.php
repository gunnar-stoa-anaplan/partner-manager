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
    <section class="hero-section position-relative overflow-hidden">
        <div class="container position-relative z-1">
            <div class="row justify-content-center">
                <div class="col-11 col-md-8 col-lg-6 col-xl-6">
                    <div class="login-card p-4 p-md-6 bg-dark bg-opacity-50 translucent-dark rounded-4" id="regular-login">
                        <h2 class="text-center mb-4">Forgot Password</h2>
                        <p class="text-center text-white opacity-50 mb-4">Confirmation email will be sent to your email
                            address</p>
                        <form>
                            <div class="mb-3">
                                <label class="form-label" for="email">Email or Phone</label>
                                <input
                                    class="form-control form-control-lg text-white bg-dark border-light border-opacity-25 bg-opacity-25"
                                    id="email" required="" type="email" />
                            </div>
                            <div class="text-end">
                                <button class="btn btn-warning bg-opacity-50 border-dark btn-lg" id="switchToToken"
                                    type="button">
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="login-card d-none p-4 p-md-6 bg-dark bg-opacity-50 translucent-dark rounded-4"
                        id="token-login">
                        <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                            <h2 class="text-center mb-4">Sent Email</h2>
                            <p class="text-center text-white mb-4">Please check your email for the reset password link</p>
                            <div class="d-grid">
                                <a class="btn btn-dark bg-opacity-50 border-dark btn-lg" href="{{ route('second', [ 'auth' , 'login']) }}">
                                    Back to Login
                                </a>
                            </div>
                        </div>
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
