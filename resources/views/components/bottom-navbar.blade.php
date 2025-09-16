<div class="bottom-nav">
    <a href="{{ route('dashboard.index') }}" class="{{ Route::is('dashboard.*') ? 'active' : '' }}">
        <i class="bi bi-house-door{{ Route::is('dashboard.*') ? '-fill' : '' }}"></i><br>
        <small class="fw-semibold">Beranda</small>
    </a>
    <a href="{{ route('ticket.index') }}" class="{{ Route::is('ticket.*') ? 'active' : '' }}">
        <i class="bi bi-ticket{{ Route::is('ticket.*') ? '-fill' : '' }}"></i><br>
        <small class="fw-semibold">Tiket</small>
    </a>
    <!-- Tombol Tengah -->
    <div class="add-btn-container">
        <a href="{{ route('ticket.create') }}" class="add-btn">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>
    <a href="{{ route('encyclopedia.index') }}" class="{{ Route::is('encyclopedia.*') ? 'active' : '' }}">
        <i class="bi bi-lightbulb{{ Route::is('encyclopedia.*') ? '-fill' : '' }} fs-4"></i><br>
        <small class="fw-semibold">Ensiklopedia</small>
    </a>
    <a href="{{ route('profile.index') }}" class="{{ Route::is('profile.*') ? 'active' : '' }}">
        <i class="bi bi-person{{ Route::is('profile.*') ? '-fill' : '' }} fs-4"></i><br>
        <small class="fw-semibold">Profil</small>
    </a>
</div>
