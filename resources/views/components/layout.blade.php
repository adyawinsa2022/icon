<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICON</title>
    <link rel="icon" href="{{ asset('icon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-1755576892.css') }}">
    @livewireStyles
</head>

<body class="bg-light d-flex flex-column" style="min-height: 100vh; padding-bottom: 100px;">

    <!-- Loading Spinner -->
    <div id="loading-overlay"
        class="position-fixed top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center bg-dark bg-opacity-50"
        style="z-index: 9999;">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>


    @if ($showNavbar)
        <x-navbar />
    @endif

    {{ $slot }}

    @if ($showBottomNavbar)
        <x-bottom-navbar />
    @endif

    @if (session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
            <div id="successToast" class="toast align-items-center bg-success-subtle border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const successToastEl = document.getElementById("successToast");
                const successToast = new bootstrap.Toast(successToastEl, {
                    delay: 3000
                }).show();
            });
        </script>
    @endif

    @if ($errors->any())
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
            <div id="errorToast" class="toast align-items-center bg-danger-subtle border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const errorToastEl = document.getElementById("errorToast");
                const errorToast = new bootstrap.Toast(errorToastEl, {
                    delay: 3000
                }).show();
            });
        </script>
    @endif

    <script>
        const overlay = document.getElementById("loading-overlay");
        window.addEventListener('load', () => {
            overlay.classList.remove("d-flex");
            overlay.classList.add("d-none");
        });
        window.addEventListener('pageshow', function() {
            overlay.classList.remove("d-flex");
            overlay.classList.add("d-none");
        });

        // Tampilkan loading saat form disubmit
        document.querySelectorAll("form").forEach(form => {
            form.addEventListener("submit", function() {
                console.log('loading');
                overlay.classList.remove("d-none");
                overlay.classList.add("d-flex");

                // Optional: disable semua tombol
                form.querySelectorAll("button").forEach(btn => btn.disabled = true);
            });
        });

        // Tampilkan loading saat klik link (menu navigasi)
        document.addEventListener('click', function(e) {
            // Periksa apakah elemen yang diklik adalah <a> atau anak dari <a>
            const link = e.target.closest('a');

            if (!link) {
                return;
            }

            const href = link.getAttribute("href");
            // Hindari external link, anchor, dan new tab
            if (!href || href.startsWith('#') || link.target === '_blank' || e.ctrlKey || e.metaKey) {
                return;
            }

            // Tampilkan loading
            console.log('loading');
            overlay.classList.remove("d-none");
            overlay.classList.add("d-flex");
        });

        // Tampilkan Loading saat Livewire request
        document.addEventListener('livewire:init', function() {
            Livewire.hook('commit', () => {
                overlay.classList.remove("d-none");
                overlay.classList.add("d-flex");
            });
            Livewire.hook('morph', () => {
                overlay.classList.remove("d-flex");
                overlay.classList.add("d-none");
            });
        });
    </script>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @livewireScripts
</body>

</html>
