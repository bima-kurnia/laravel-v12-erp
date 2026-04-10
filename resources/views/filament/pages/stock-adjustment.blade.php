<x-filament-panels::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Apply Adjustment
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>