<?php

namespace SpykApp\FilamentPasswordlessLogin\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action as TableAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;
use SpykApp\FilamentPasswordlessLogin\Resources\MagicLoginTokenResource\Pages;
use SpykApp\PasswordlessLogin\Models\MagicLoginToken;

class MagicLoginTokenResource extends Resource
{
    protected static ?string $model = MagicLoginToken::class;

    public static function getSlug(?Panel $panel = null): string
    {
        try {
            return FilamentPasswordlessLoginPlugin::get()->getResourceSlug();
        } catch (\Exception) {
            return config('filament-passwordless-login.resource.slug', 'magic-login-tokens');
        }
    }

    public static function getNavigationIcon(): ?string
    {
        try {
            return FilamentPasswordlessLoginPlugin::get()->getNavigationIcon();
        } catch (\Exception) {
            return 'heroicon-o-link';
        }
    }

    public static function getNavigationGroup(): ?string
    {
        try {
            return FilamentPasswordlessLoginPlugin::get()->getNavigationGroup();
        } catch (\Exception) {
            return __('filament-passwordless-login::filament.navigation_group');
        }
    }

    public static function getNavigationSort(): ?int
    {
        try {
            return FilamentPasswordlessLoginPlugin::get()->getNavigationSort();
        } catch (\Exception) {
            return 100;
        }
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-passwordless-login::filament.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament-passwordless-login::filament.resource_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-passwordless-login::filament.resource_plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_email')
                    ->label(__('filament-passwordless-login::filament.form_user'))
                    ->options(function () {
                        $model = config('passwordless-login.user_model', \App\Models\User::class);
                        $emailCol = config('passwordless-login.email_column', 'email');

                        return $model::pluck($emailCol, 'id')->toArray();
                    })
                    ->searchable()
                    ->required(),

                Select::make('guard')
                    ->label(__('filament-passwordless-login::filament.form_guard'))
                    ->options(function () {
                        return collect(config('auth.guards', []))
                            ->mapWithKeys(fn ($v, $k) => [$k => ucfirst($k)])
                            ->toArray();
                    })
                    ->placeholder('Default')
                    ->nullable(),

                TextInput::make('redirect_url')
                    ->label(__('filament-passwordless-login::filament.form_redirect_url'))
                    ->nullable(),

                TextInput::make('expiry_minutes')
                    ->label(__('filament-passwordless-login::filament.form_expiry_minutes'))
                    ->numeric()
                    ->default(config('passwordless-login.expiry_minutes', 15))
                    ->required(),

                TextInput::make('max_uses')
                    ->label(__('filament-passwordless-login::filament.form_max_uses'))
                    ->numeric()
                    ->default(config('passwordless-login.max_uses', 1))
                    ->nullable(),

                Toggle::make('send_notification')
                    ->label(__('filament-passwordless-login::filament.form_send_notification'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $model = config('passwordless-login.user_model', \App\Models\User::class);
                        $emailCol = config('passwordless-login.email_column', 'email');
                        $userIds = $model::where($emailCol, 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->pluck('id');

                        return $query->where('authenticatable_type', $model)
                            ->whereIn('authenticatable_id', $userIds);
                    })
                    ->sortable(),

                TextColumn::make('guard')
                    ->label(__('filament-passwordless-login::filament.column_guard'))
                    ->badge()
                    ->placeholder('default')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('filament-passwordless-login::filament.column_status'))
                    ->badge()
                    ->getStateUsing(function (MagicLoginToken $record): string {
                        if ($record->isExpired()) {
                            return 'expired';
                        }
                        if ($record->isFullyUsed()) {
                            return 'used';
                        }

                        return 'active';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'used' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('filament-passwordless-login::filament.status_active'),
                        'expired' => __('filament-passwordless-login::filament.status_expired'),
                        'used' => __('filament-passwordless-login::filament.status_used'),
                        default => $state,
                    }),

                TextColumn::make('use_count')
                    ->label(__('filament-passwordless-login::filament.column_uses'))
                    ->formatStateUsing(function (MagicLoginToken $record): string {
                        $max = $record->max_uses ? "/{$record->max_uses}" : '/∞';

                        return "{$record->use_count}{$max}";
                    })
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label(__('filament-passwordless-login::filament.column_ip_address'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('expires_at')
                    ->label(__('filament-passwordless-login::filament.column_expires_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('last_used_at')
                    ->label(__('filament-passwordless-login::filament.column_last_used_at'))
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('filament-passwordless-login::filament.column_created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament-passwordless-login::filament.column_status'))
                    ->options([
                        'active' => __('filament-passwordless-login::filament.status_active'),
                        'expired' => __('filament-passwordless-login::filament.status_expired'),
                        'used' => __('filament-passwordless-login::filament.status_used'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'active' => $query->where('expires_at', '>', now())
                                ->where(function ($q) {
                                    $q->whereNull('max_uses')
                                        ->orWhereColumn('use_count', '<', 'max_uses');
                                }),
                            'expired' => $query->where('expires_at', '<=', now()),
                            'used' => $query->whereNotNull('max_uses')
                                ->whereColumn('use_count', '>=', 'max_uses'),
                            default => $query,
                        };
                    }),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label(__('filament-passwordless-login::filament.filter_from')),
                        DatePicker::make('until')->label(__('filament-passwordless-login::filament.filter_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                TableAction::make('invalidate')
                    ->label(__('filament-passwordless-login::filament.action_invalidate'))
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (MagicLoginToken $record) => $record->isValid())
                    ->action(function (MagicLoginToken $record) {
                        $record->update(['expires_at' => now()]);

                        Notification::make()
                            ->title(__('filament-passwordless-login::filament.invalidated_success'))
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMagicLoginTokens::route('/'),
            'create' => Pages\CreateMagicLoginToken::route('/create'),
        ];
    }
}
