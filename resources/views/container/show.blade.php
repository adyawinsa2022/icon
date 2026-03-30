<x-layout>
    <div class="container pt-3">
        <h5 class="fw-bold mb-3">Detail Kontainer</h5>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4 text-center">{{ $container->name }}</h5>
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="fw-semibold">Lokasi</td>
                            <td>{{ $container->location ?? 'Tidak tersedia' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Barang</td>
                            <td>
                                @if (!empty($container->items))
                                    <ul>
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
    </div>
</x-layout>
