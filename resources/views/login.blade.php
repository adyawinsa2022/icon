<x-layout :show-navbar="false" :show-bottom-navbar="false">
    <style>
        .login-card {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: #fff;
        }
    </style>

    <div class="container">
        <div class="login-card">
            <div class="d-flex flex-column justify-content-center align-items-center mb-3">
                <img src="{{ asset('images/logo-icon-blue.png') }}" alt="ICON" height="90">
            </div>
            <form method="POST" action="{{ route('login.process') }}">
                @csrf
                <div class="mb-3">
                    <input type="text" name="email" class="form-control" id="email" placeholder="Email"
                        autofocus>
                </div>
                <div class="mb-3">
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" id="password"
                            placeholder="Password">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                {{-- <div class="mb-3">
                    <a>Lupa Password?</a>
                </div> --}}
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");

            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            toggleIcon.classList.toggle("bi-eye");
            toggleIcon.classList.toggle("bi-eye-slash");
        }
    </script>
</x-layout>
