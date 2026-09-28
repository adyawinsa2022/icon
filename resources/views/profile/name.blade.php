<x-layout>
    <style>
        .form-card {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: #fff;
        }
    </style>

    <div class="container">
        <div class="form-card">
            <h4 class="text-center mb-4">Ubah Nama</h4>
            <form method="POST" action="{{ route('profile.name.process') }}" class="mb-4">
                @csrf
                <div class="mb-3">
                    <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Nama Depan" value="{{ old('firstname', $user['firstname'] ?? '') }}"
                        required autofocus>
                </div>
                <div class="mb-3">
                    <input type="text" id="realname" name="realname" class="form-control"
                        placeholder="Nama Belakang" value="{{ old('realname', $user['realname'] ?? '') }}">
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>
    </div>
</x-layout>
