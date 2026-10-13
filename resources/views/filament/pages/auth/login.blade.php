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
        .fi-fo-field-wrp-error-message { display: none; }
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
        /* Banner error login: tampil, getar, lalu hilang otomatis */
        .login-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 13.5px;
            line-height: 1.4;
            overflow: hidden;
            animation:
                login-shake .4s ease,
                login-fade-out .5s ease 4s forwards;
        }

        .login-alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        @keyframes login-shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        @keyframes login-fade-out {
            0%   { opacity: 1; max-height: 120px; margin-bottom: 18px; padding: 12px 14px; border-width: 1px; }
            100% { opacity: 0; max-height: 0;     margin-bottom: 0;    padding: 0 14px;    border-width: 0; }
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
    @if ($loginError)
        <div class="login-alert" role="alert" wire:key="login-alert-{{ $loginErrorCount }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
            </svg>
            <span>{{ $loginError }}</span>
        </div>
    @endif

    <form wire:submit="authenticate">
        {{ $this->form }}
      
        <div class="login-options">
            <label class="login-remember">
                <input type="checkbox" wire:model="data.remember">
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