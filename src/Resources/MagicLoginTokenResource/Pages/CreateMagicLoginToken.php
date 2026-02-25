<?php

namespace SpykApp\FilamentPasswordlessLogin\Resources\MagicLoginTokenResource\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use SpykApp\FilamentPasswordlessLogin\Resources\MagicLoginTokenResource;
use SpykApp\PasswordlessLogin\Exceptions\ThrottleException;
use SpykApp\PasswordlessLogin\Facades\PasswordlessLogin;

class CreateMagicLoginToken extends CreateRecord
{
    protected static string $resource = MagicLoginTokenResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $model = config('passwordless-login.user_model', \App\Models\User::class);
        $user = $model::findOrFail($data['user_email']);

        $builder = PasswordlessLogin::forUser($user);

        if (! empty($data['guard'])) {
            $builder->guard($data['guard']);
        }

        if (! empty($data['redirect_url'])) {
            $builder->redirectTo($data['redirect_url']);
        }

        if (! empty($data['expiry_minutes'])) {
            $builder->expiresIn((int) $data['expiry_minutes']);
        }

        if (isset($data['max_uses'])) {
            $builder->maxUses((int) $data['max_uses']);
        }

        if (empty($data['send_notification'])) {
            $builder->withoutNotification();
        }

        try {
            $result = $builder->generate(request());

            Notification::make()
                ->title(__('filament-passwordless-login::filament.generate_success'))
                ->success()
                ->send();

            return $result['token'];
        } catch (ThrottleException $e) {
            Notification::make()
                ->title(__('filament-passwordless-login::filament.action_throttled'))
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
