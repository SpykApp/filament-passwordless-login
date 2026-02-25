<?php

namespace SpykApp\FilamentPasswordlessLogin\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use SpykApp\FilamentPasswordlessLogin\Actions\SendMagicLinkAction;
use SpykApp\FilamentPasswordlessLogin\Enums\FilamentPasswordlessLoginActionPosition;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        $plugin = FilamentPasswordlessLoginPlugin::get();

        if ($plugin->hasLoginAction() && $plugin->getLoginActionPosition() === FilamentPasswordlessLoginActionPosition::EmailFieldHint) {
            $loginField = $this->getEmailFormComponent()->hintAction(SendMagicLinkAction::make());
        } else {
            $loginField = $this->getEmailFormComponent();
        }

        return $schema
            ->components([
                $loginField,
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    // Add this method
    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        $actions = parent::getAuthenticateFormAction();

        return $actions;
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();

        $plugin = FilamentPasswordlessLoginPlugin::get();

        if ($plugin->hasLoginAction() && $plugin->getLoginActionPosition() === FilamentPasswordlessLoginActionPosition::LoginFormEndButton) {
            $actions[] = SendMagicLinkAction::make()
                ->size('sm');
        }

        return $actions;
    }
}