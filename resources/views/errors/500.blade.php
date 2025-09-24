<x-layout>
    <div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center">
        <h2>500</h2>
        <h3>Server Bermasalah</h3>
        <p>Silahkan hubungi ICT</p>
        <div class="d-flex flex-row gap-2">
            <a href="{{ route('dashboard.index') }}" class="btn btn-primary">
                <i class="bi bi-house-door"></i> Beranda
            </a>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Coba lagi
            </button>
        </div>
    </div>
</x-layout>
