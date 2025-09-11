<x-layout>
    <style>
        .profile-card {
            text-align: center;
            padding: 30px 20px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin: 20px;
        }

        .menu-list {
            margin-top: 20px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: #fff;
            margin-bottom: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .menu-item:hover {
            background: #f1f3f5;
        }
    </style>
    <div class="container">
        <div class="profile-card">
            <h4 class="mb-0">{{ $user['name'] }}</h4>
        </div>

        <div class="menu-list">
            {{-- Login bukan email adyawinsa --}}
            @if (!$user['adyawinsa'])
                <a class="menu-item text-decoration-none text-reset" href="{{ route('profile.reset_password') }}">
                    <span><i class="bi bi-key me-2"></i> Reset Password</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            @endif
            <a class="menu-item text-decoration-none text-reset" href="{{ route('logout') }}">
                <span><i class="bi bi-box-arrow-right me-2"></i> Logout</span>
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</x-layout>
