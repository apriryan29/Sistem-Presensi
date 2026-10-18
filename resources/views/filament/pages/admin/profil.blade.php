<x-filament-panels::page>
    <style>      
        .fi-input-wrp:has(input[readonly]) {
            background-color: #f3f4f6;
        }

        .fi-input-wrp input[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
        }
    </style>
    {{ $this->form }}
</x-filament-panels::page>