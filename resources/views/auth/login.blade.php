@extends('layout.app-full')

@section('content')

    <div class="login-wrapper">
        <h2>Login</h2>

        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/auth/login') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="input-wrapper">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}">
            </div>
            <div class="input-wrapper">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="input-wrapper">
                <div class="checkbox-wrapper">
                    <input type="checkbox" name="remember" id="remember"/>
                    <label for="remember">Remember me</label>
                </div>
                <a href="#" class="forgot-password">Forgot Password</a>
            </div>
            <button type="submit" class="btn login-btn">Login</button>
        </form>
    </div>

@endsection
