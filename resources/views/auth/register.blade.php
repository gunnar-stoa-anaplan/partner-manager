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
                        <h2 class="text-center mb-4">Register</h2>
                        <p class="text-center text-white opacity-50 mb-4">Keep it all together and you'll be free</p>
                        <form>
                            <div class="mb-3">
                                <label class="form-label" for="email">Email or Phone</label>
                                <input
                                    class="form-control form-control-lg text-white bg-dark border-light border-opacity-25 bg-opacity-25"
                                    id="email" required="" type="email" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group">
                                    <input
                                        class="form-control form-control-lg text-white bg-dark border-light border-opacity-25 bg-opacity-25"
                                        id="password" required="" type="password" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password-confirm">Confirm Password</label>
                                <div class="input-group">
                                    <input
                                        class="form-control form-control-lg text-white bg-dark border-light border-opacity-25 bg-opacity-25"
                                        id="password-confirm" required="" type="password" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input bg-dark border-light border-opacity-25 bg-opacity-25"
                                        id="terms" required="" type="checkbox" />
                                    <label class="form-check-label" for="terms">I agree to the <a
                                            class="text-decoration-underline text-white fw-500" href="#!">Terms of
                                            Service</a> and <a class="text-decoration-underline text-white fw-500"
                                            href="#!">Privacy Policy</a></label>
                                </div>
                            </div>
                            <div class="mb-3 d-grid">
                                <button class="btn btn-primary btn-lg bg-primary bg-opacity-75"
                                    type="submit">Register</button>
                            </div>
                            <div class="opacity-75">
                                Already have an account? <a class="text-decoration-underline text-white fw-500"
                                    href="{{ route('second', [ 'auth' , 'login']) }}">Login here</a>
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
