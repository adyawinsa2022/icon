<div>
    <h5 class="fw-bold mb-3">Kontainer</h5>
    <div class="mb-3">
        <form class="d-flex flex-row gap-2">
            <input type="text" class="form-control" wire:model.live.debounce.500ms="search" placeholder="Cari Barang...">
        </form>
    </div>

    @if ($search && strlen($search) < 2)
        <small class="text-gray-500">Ketik minimal 2 huruf</small>
    @endif

    <div class="mt-4">
        @forelse($containers as $container)
            <div class="card shadow-sm mb-3">
                <div class="card-body p-3">
                    <h5 class="card-title text-center">{{ $container->name }}</h5>
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td class="fw-semibold p-0">Lokasi</td>
                                <td class="p-0">{{ $container->location ?? 'Tidak tersedia' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold p-0">Barang</td>
                                <td class="p-0">
                                    @if (!empty($container->items))
                                        <ul class="p-0">
                                            @foreach ($container->items as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p><i>Kosong</i></p>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center gap-3 mt-5">
                        <a href="{{ route('container.edit', $container->name) }}" class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            @if (strlen($search) >= 2)
                <p class="text-gray-500">Tidak ada kontainer yang cocok.</p>
            @endif
        @endforelse
    </div>
</div>
