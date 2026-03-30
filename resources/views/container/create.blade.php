<x-layout>
    <style>
        .form-card {
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
    <div class="container pt-3">
        <div class="form-card">
            <h3 class="text-center mb-4">Buat Kontainer</h3>
            <form action="{{ route('container.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Judul -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                        required>
                </div>

                <!-- Lokasi -->
                <div class="mb-3">
                    <label for="location" class="form-label">Lokasi</label>
                    <input type="text" class="form-control" id="location" name="location"
                        value="{{ old('location') }}">
                </div>

                <!-- Deskripsi -->
                <div class="mb-3">
                    <label for="items" class="form-label">Barang (Pisahkan dengan koma)</label>
                    <textarea class="form-control" id="items" name="items" rows="5" required>{{ old('items') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>
    </div>
</x-layout>
