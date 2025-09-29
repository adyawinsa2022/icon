<x-layout>
    <div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center text-center">
        <h2>404</h2>
        <h3>Halaman tidak ditemukan</h3>
        <p>Halaman atau Data yang anda cari tidak ada atau sudah dihapus</p>
        <a href="{{ route('dashboard.index') }}" class="btn btn-primary">
            <i class="bi bi-house-door"></i> Beranda
        </a>
    </div>
</x-layout>
