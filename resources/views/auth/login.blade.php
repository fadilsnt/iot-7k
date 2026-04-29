<x-guest-layout>
    <div class="text-center mb-3">
        <h4 class="mb-1">Masuk ke Akun</h4>
        <p class="text-muted small mb-0">Akses panel IoT untuk memantau perangkat dan data real-time.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success small" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if (session('bypass_debug'))
        <div class="alert alert-info small" role="alert">
            <strong>Debug Info:</strong><br>
            <pre class="mb-0" style="font-size: 11px;">{{ session('bypass_debug') }}</pre>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger small" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-control" placeholder="nama@perusahaan.com">
        </div>

        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="form-control" placeholder="••••••••">
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me">Ingat saya</label>
            </div>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="small text-decoration-none">Daftar</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-100">Masuk</button>
    </form>


</x-guest-layout>
