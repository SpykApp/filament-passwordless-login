<?php

namespace SpykApp\FilamentPasswordlessLogin\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;
use SpykApp\PasswordlessLogin\Exceptions\ThrottleException;
use SpykApp\PasswordlessLogin\Facades\PasswordlessLogin;

class SendMagicLinkAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'send-magic-link';
    }

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $plugin = FilamentPasswordlessLoginPlugin::get();
        } catch (\Exception) {
            $plugin = null;
        }

        // Label from lang
        $this->label(__('filament-passwordless-login::filament.action_label'));

        // Icon: plugin → config → default
        $this->icon(
            $plugin?->getLoginActionIcon()
            ?? config('filament-passwordless-login.login_action.icon', 'heroicon-m-link')
        );

        // Color: plugin → config → default
        $this->color(
            $plugin?->getLoginActionColor()
            ?? config('filament-passwordless-login.login_action.color', 'primary')
        );

        // Modal
        $this->modalHeading(__('filament-passwordless-login::filament.action_modal_heading'));
        $this->modalDescription(__('filament-passwordless-login::filament.action_modal_description'));
        $this->modalSubmitActionLabel(__('filament-passwordless-login::filament.send_link_button'));
        $this->modalWidth(config('filament-passwordless-login.login_action.width', 'md'));

        // Slideover: plugin → config → false
        if ($plugin?->isSlideover() ?? config('filament-passwordless-login.login_action.slideover', false)) {
            $this->slideOver();
        }

        // Form
        $this->schema([
            TextInput::make('email')
                ->label(__('filament-passwordless-login::filament.email_label'))
                ->placeholder(__('filament-passwordless-login::filament.email_placeholder'))
                ->email()
                ->required()
                ->autofocus(),
        ]);

        // Action handler
        $this->action(function (array $data): void {
            $this->handleSendMagicLink($data);
        });
    }

    protected function handleSendMagicLink(array $data): void
    {
        try {
            $plugin = FilamentPasswordlessLoginPlugin::get();
        } catch (\Exception) {
            $plugin = null;
        }

        $user = PasswordlessLogin::findUserByEmail($data['email']);

        if (! $user) {
            Notification::make()
                ->title(__('filament-passwordless-login::filament.action_user_not_found'))
                ->danger()
                ->send();

            return;
        }

        try {
            $builder = PasswordlessLogin::forUser($user);

            if ($plugin?->getRedirectUrl()) {
                $builder->redirectTo($plugin->getRedirectUrl());
            }

            if ($plugin?->getMailable()) {
                $builder->useMailable($plugin->getMailable());
            }

            if ($plugin?->getNotification()) {
                $builder->useNotification($plugin->getNotification());
            }

            $builder->generate(request());

            Notification::make()
                ->title(__('filament-passwordless-login::filament.action_success'))
                ->success()
                ->send();
        } catch (ThrottleException $e) {
            Notification::make()
                ->title(__('filament-passwordless-login::filament.action_throttled'))
                ->danger()
                ->send();
        }
    }

    public function asSlideover(bool $condition = true): static
    {
        if ($condition) {
            $this->slideOver();
        }

        return $this;
    }
}