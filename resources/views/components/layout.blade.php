<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICON</title>
    <link rel="icon" href="{{ asset('icon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-5.3.7/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons-1.13.1/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-1757641885.css') }}">
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
    <script>
        const overlay = document.getElementById("loading-overlay");
    </script>


    @if ($showNavbar)
        <x-navbar />
    @endif

    {{ $slot }}

    @if ($showBottomNavbar)
        <x-bottom-navbar />
    @endif

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
        <div id="appToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.getElementById('appToast');
            const toastMessageEl = document.getElementById('toastMessage');

            // Fungsi untuk menampilkan toast
            function showToast(message, type) {
                toastMessageEl.innerHTML = message;

                // Hapus semua kelas warna yang ada
                toastEl.classList.remove('bg-success-subtle', 'bg-danger-subtle');

                // Tambahkan kelas warna sesuai tipe
                if (type === 'success') {
                    toastEl.classList.add('bg-success-subtle');
                } else if (type === 'error') {
                    toastEl.classList.add('bg-danger-subtle');
                }

                const toast = new bootstrap.Toast(toastEl, {
                    delay: 3000
                });
                toast.show();
            }

            // 1. Cek jika ada flash message dari Controller (saat halaman di-reload)
            @if (session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if ($errors->any())
                let errorMessage = '';
                @foreach ($errors->all() as $error)
                    errorMessage += "{{ $error }}<br>";
                @endforeach
                showToast(errorMessage, 'error');
            @endif

            // 1. Livewire listener
            Livewire.on('show-toast', (event) => {
                showToast(event.message, event.type);
            });
        });
    </script>

    <script>
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
            overlay.classList.remove("d-none");
            overlay.classList.add("d-flex");
        });

        document.addEventListener('livewire:init', function() {
            // Tampilkan Loading saat Livewire request
            Livewire.hook('commit', () => {
                overlay.classList.remove("d-none");
                overlay.classList.add("d-flex");
            });

            Livewire.hook('morphed', () => {
                overlay.classList.remove("d-flex");
                overlay.classList.add("d-none");
            });

            // Livewire listener saat request
            Livewire.hook('request', ({
                fail
            }) => {
                // Saat request gagal/error
                fail(({
                    status,
                    preventDefault
                }) => {
                    // Bypass jika error 500
                    if (status === 500) {
                        preventDefault();
                        overlay.classList.remove("d-flex");
                        overlay.classList.add("d-none");
                        console.warn('Livewire request failed 500.');
                    }
                })
            })
        });
    </script>

    <script src="{{ asset('js/bootstrap-5.3.7/bootstrap.bundle.min.js') }}"></script>
    @livewireScripts
</body>

</html>
