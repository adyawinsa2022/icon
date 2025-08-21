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
            <h4 class="text-center mb-4">Reset Password</h4>
            <form method="POST" action="{{ route('profile.reset_password.process') }}">
                @csrf
                <div class="mb-3">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password baru"
                        required autofocus>
                </div>
                <div class="mb-3">
                    <input type="password" id="confirm_password" name="password_confirmation" class="form-control"
                        placeholder="Konfirmasi Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan Password</button>
            </form>
        </div>
    </div>
</x-layout>
