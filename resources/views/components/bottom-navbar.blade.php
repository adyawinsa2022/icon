<div class="bottom-nav">
    <a href="{{ route('ticket.index') }}" class="{{ Route::is('ticket.*') ? 'active' : '' }}">
        <i class="bi bi-ticket{{ Route::is('ticket.*') ? '-fill' : '' }}"></i><br>
        <small class="fw-semibold">Tiket</small>
    </a>
    <!-- Tombol Tengah -->
    <a href="{{ route('ticket.create') }}" class="add-btn">
        <i class="bi bi-plus-lg"></i>
    </a>
    <a href="{{ route('profile.index') }}" class="{{ Route::is('profile.*') ? 'active' : '' }}">
        <i class="bi bi-person{{ Route::is('profile.*') ? '-fill' : '' }} fs-4"></i><br>
        <small class="fw-semibold">Profil</small>
    </a>
</div>
