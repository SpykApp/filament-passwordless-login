<?php

namespace SpykApp\FilamentPasswordlessLogin\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;
use SpykApp\PasswordlessLogin\Models\MagicLoginToken;

class MagicLinkTopUsersWidget extends TableWidget
{
    protected static ?int $sort = 12;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('filament-passwordless-login::filament.widget_top_users_heading');
    }

    public function table(Table $table): Table
    {
        $days = $this->getChartDays();

        return $table
            ->query(
                MagicLoginToken::query()
                    ->select('authenticatable_type', 'authenticatable_id')
                    ->selectRaw('COUNT(*) as tokens_count')
                    ->selectRaw('SUM(CASE WHEN use_count > 0 THEN 1 ELSE 0 END) as used_count')
                    ->selectRaw('MAX(created_at) as latest_created')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->groupBy('authenticatable_type', 'authenticatable_id')
                    ->orderByDesc('tokens_count')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('authenticatable_id')
                    ->label(__('filament-passwordless-login::filament.column_user'))
                    ->formatStateUsing(function ($record) {
                        $user = $record->authenticatable;
                        if (! $user) {
                            return '—';
                        }
                        $emailCol = config('passwordless-login.email_column', 'email');

                        return $user->name ?? $user->{$emailCol} ?? "#{$record->authenticatable_id}";
                    }),

                TextColumn::make('tokens_count')
                    ->label(__('filament-passwordless-login::filament.widget_links_generated'))
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('used_count')
                    ->label(__('filament-passwordless-login::filament.widget_links_used'))
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('success_rate')
                    ->label(__('filament-passwordless-login::filament.widget_success_rate'))
                    ->getStateUsing(function ($record) {
                        if ($record->tokens_count == 0) {
                            return '0%';
                        }

                        return round(($record->used_count / $record->tokens_count) * 100) . '%';
                    }),

                TextColumn::make('latest_created')
                    ->label(__('filament-passwordless-login::filament.widget_last_generated'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }

    protected function getChartDays(): int
    {
        try {
            return FilamentPasswordlessLoginPlugin::get()->getChartDays();
        } catch (\Exception) {
            return config('filament-passwordless-login.widgets.chart_days', 30);
        }
    }
}
