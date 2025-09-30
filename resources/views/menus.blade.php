<x-layout>
    <div class="container pt-3">
        <h5 class="fw-bold mb-3">Semua Menu</h5>
        <div class="row g-3">
            <div class="col-4">
                <a href="{{ route('ticket.index') }}" class="card shadow-sm h-100 text-decoration-none">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center px-1">
                        <i class="bi bi-ticket fs-1"></i>
                        <span class="fw-semibold">Tiket</span>
                    </div>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('encyclopedia.index') }}" class="card shadow-sm h-100 text-decoration-none">
                    <div
                        class="card-body d-flex flex-column justify-content-center align-items-center text-center px-1">
                        <i class="bi bi-lightbulb fs-1"></i>
                        <span class="fw-semibold">Ensiklopedia</span>
                    </div>
                </a>
            </div>
            <div class="col-4">
                <a href="{{ route('copier.index') }}" class="card shadow-sm h-100 text-decoration-none">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <i class="bi bi-printer fs-1"></i>
                        <span class="fw-semibold">Fotokopi</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-layout>
