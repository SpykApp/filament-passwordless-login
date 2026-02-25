<div class="mt-4 flex justify-center">
    <x-filament::button
            color="{{ app(SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin::class)->getLoginActionColor() }}"
            icon="{{ app(SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin::class)->getLoginActionIcon() }}"
            outlined
            size="sm"
            x-on:click="$dispatch('open-modal', { id: 'magic-link-modal' })"
    >
        {{ __('filament-passwordless-login::filament.login_action_label') }}
    </x-filament::button>
</div>