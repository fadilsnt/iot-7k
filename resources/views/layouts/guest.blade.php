<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    </head>
    <body class="bg-light">
        <main class="min-vh-100 d-flex align-items-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                        <div class="text-center mb-4">
                            <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                <i class="fa-solid fa-temperature-high text-primary fs-1"></i>
                                <span class="fw-semibold text-dark">PT. Tuju Kuda Hitam Sakti</span>
                            </a>
                        </div>
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body p-4 p-sm-5">
                                {{ $slot }}
                            </div>
                        </div>
                        <p class="text-center text-muted small mt-3 mb-0">© {{ now()->year }} {{ config('app.name', 'IoT App') }}</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
