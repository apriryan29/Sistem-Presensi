<x-filament-panels::page.simple>
    <style>
        .fi-simple-header {
            display: none;
        }
        .fi-simple-layout {
            background:
                radial-gradient(circle at 15% 10%, rgba(245, 158, 11, 0.25), transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(217, 119, 6, 0.2), transparent 40%);
        }

        .login-school {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            margin-bottom: 8px;
            text-align: center;
        }

        .login-school strong { font-size: 20px; font-weight: 700; }
        .login-school span { font-size: 16px; opacity: .65; }

        .login-back {
            display: block;
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            opacity: .65;
            text-decoration: none;
        }
        .login-back:hover { opacity: 1; text-decoration: underline; }
                
        .login-options {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 12px;
            margin-top: 22px !important;
            margin-bottom: 8px !important;
            width: 100%;
        }

        .login-remember {
            display: inline-flex !important;
            align-items: center !important;
            gap: 9px !important;
            margin: 0 !important;
            color: #64748b;
            font-size: 13px;
            cursor: pointer;
            user-select: none;
        }

        .login-remember input {
            width: 16px !important;
            height: 16px !important;
            margin: 0 !important;
            accent-color: #f59e0b;
            cursor: pointer;
        }

        .login-forgot {
            color: #ea580c !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            white-space: nowrap;
            transition: color .2s ease;
        }

        .login-forgot:hover {
            color: #c2410c !important;
            text-decoration: underline !important;
        }

    </style>

    <div class="login-school">
        <img
            src="{{ asset('images/logo.png') }}"
            alt="Logo SD Negeri 1 Pancasan"
            style="
                width: 82px;
                height: 82px;
                object-fit: contain;
                border-radius: 12px;
                background: white;
                padding: 4px;
            "
        >
        <strong>SISTEM PRESENSI</strong>
        <span>SD Negeri 1 Pancasan - Ajibarang</span>
    </div>

    <form wire:submit="authenticate">
        {{ $this->form }}
      
        <div class="login-options">
            <label class="login-remember">
                <input type="checkbox" name="remember" value="1">
                <span>Ingat saya</span>
            </label>

            <a href="#" class="login-forgot">
                Lupa password?
            </a>
        </div>

        <x-filament::button type="submit" size="lg" class="w-full" style="width: 100%; margin-top: 20px;">
            Masuk
        </x-filament::button>
    </form>
</x-filament-panels::page.simple>