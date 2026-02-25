<?php

namespace SpykApp\FilamentPasswordlessLogin\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;
use SpykApp\PasswordlessLogin\Exceptions\ThrottleException;
use SpykApp\PasswordlessLogin\Facades\PasswordlessLogin;

class MagicLinkLogin extends BaseLogin
{
    public bool $magicLinkSent = false;

    public function getHeading(): string|Htmlable
    {
        if ($this->magicLinkSent) {
            return __('filament-passwordless-login::filament.link_sent_title');
        }

        return __('filament-passwordless-login::filament.login_heading');
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->magicLinkSent) {
            return __('filament-passwordless-login::filament.link_sent_body');
        }

        return __('filament-passwordless-login::filament.login_subheading');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): TextInput
    {
        return TextInput::make('email')
            ->label(__('filament-passwordless-login::filament.email_label'))
            ->placeholder(__('filament-passwordless-login::filament.email_placeholder'))
            ->email()
            ->required()
            ->autocomplete('email')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function getFormActions(): array
    {
        if ($this->magicLinkSent) {
            return [
                $this->getSendAnotherAction(),
            ];
        }

        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-passwordless-login::filament.send_link_button'))
            ->submit('authenticate');
    }

    protected function getSendAnotherAction(): Action
    {
        return Action::make('sendAnother')
            ->label(__('filament-passwordless-login::filament.send_link_button'))
            ->action('resetMagicLinkForm')
            ->color('gray')
            ->outlined();
    }

    /**
     * Override authenticate — this is what the base Login form submits to.
     * We hijack it to send a magic link instead of doing password auth.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-passwordless-login::filament.action_throttled'))
                ->body(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $user = PasswordlessLogin::findUserByEmail($data['email']);

        if ($user) {
            try {
                $builder = PasswordlessLogin::forUser($user);

                $plugin = $this->getPlugin();
                if ($plugin) {
                    $builder->redirectTo($plugin->getRedirectUrl());

                    if ($plugin->getMailable()) {
                        $builder->useMailable($plugin->getMailable());
                    }

                    if ($plugin->getNotification()) {
                        $builder->useNotification($plugin->getNotification());
                    }
                }

                $builder->generate(request());
            } catch (ThrottleException $e) {
                Notification::make()
                    ->title(__('filament-passwordless-login::filament.action_throttled'))
                    ->danger()
                    ->send();

                return null;
            }
        }

        $this->magicLinkSent = true;

        return null;
    }

    public function resetMagicLinkForm(): void
    {
        $this->magicLinkSent = false;
        $this->form->fill();
    }

    protected function getPlugin(): ?FilamentPasswordlessLoginPlugin
    {
        try {
            return Filament::getCurrentPanel()?->getPlugin('filament-passwordless-login');
        } catch (\Exception) {
            return null;
        }
    }
}
