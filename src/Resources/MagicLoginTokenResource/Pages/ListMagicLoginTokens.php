<?php

namespace SpykApp\FilamentPasswordlessLogin\Resources\MagicLoginTokenResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;
use SpykApp\FilamentPasswordlessLogin\Resources\MagicLoginTokenResource;
use SpykApp\FilamentPasswordlessLogin\Widgets\MagicLinkChartWidget;
use SpykApp\FilamentPasswordlessLogin\Widgets\MagicLinkStatsWidget;
use SpykApp\FilamentPasswordlessLogin\Widgets\MagicLinkTopUsersWidget;
use SpykApp\PasswordlessLogin\Models\MagicLoginToken;

class ListMagicLoginTokens extends ListRecords
{
    protected static string $resource = MagicLoginTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('cleanup')
                ->label(__('filament-passwordless-login::filament.action_cleanup'))
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription(__('filament-passwordless-login::filament.action_cleanup_confirm'))
                ->action(function () {
                    $count = MagicLoginToken::where('expires_at', '<=', now())->delete();

                    Notification::make()
                        ->title(__('filament-passwordless-login::filament.action_cleanup_success', ['count' => $count]))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $widgets = [];

        try {
            $plugin = FilamentPasswordlessLoginPlugin::get();

            if ($plugin->hasStatsWidget()) {
                $widgets[] = MagicLinkStatsWidget::class;
            }

            if ($plugin->hasChartsWidget()) {
                $widgets[] = MagicLinkChartWidget::class;
            }
        } catch (\Exception) {
            // Plugin not registered, show all
            $widgets = [
                MagicLinkStatsWidget::class,
                MagicLinkChartWidget::class,
            ];
        }

        return $widgets;
    }
}
