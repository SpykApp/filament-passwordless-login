<?php

namespace SpykApp\FilamentPasswordlessLogin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;
use SpykApp\PasswordlessLogin\Models\MagicLoginToken;

class MagicLinkStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 10;

    protected function getHeading(): ?string
    {
        return __('filament-passwordless-login::filament.widget_stats_heading');
    }

    protected function getStats(): array
    {
        $days = $this->getChartDays();
        $since = now()->subDays($days);

        $totalGenerated = MagicLoginToken::where('created_at', '>=', $since)->count();
        $totalUsed = MagicLoginToken::where('created_at', '>=', $since)
            ->where('use_count', '>', 0)->count();
        $totalExpired = MagicLoginToken::where('created_at', '>=', $since)
            ->where('expires_at', '<=', now())
            ->where('use_count', 0)->count();
        $totalActive = MagicLoginToken::where('expires_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('use_count', '<', 'max_uses');
            })->count();

        $successRate = $totalGenerated > 0
            ? round(($totalUsed / $totalGenerated) * 100, 1)
            : 0;

        $last7 = MagicLoginToken::where('created_at', '>=', now()->subDays(7))->count();
        $prev7 = MagicLoginToken::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();
        $trend = $prev7 > 0 ? round((($last7 - $prev7) / $prev7) * 100) : 0;

        $trendDesc = $trend >= 0
            ? __('filament-passwordless-login::filament.widget_trend_up', ['percent' => $trend])
            : __('filament-passwordless-login::filament.widget_trend_down', ['percent' => $trend]);

        return [
            Stat::make(__('filament-passwordless-login::filament.widget_total_generated'), number_format($totalGenerated))
                ->description($trendDesc)
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'danger')
                ->chart($this->getDailyChart('generated', 7)),

            Stat::make(__('filament-passwordless-login::filament.widget_total_used'), number_format($totalUsed))
                ->description("{$successRate}% " . __('filament-passwordless-login::filament.widget_success_rate'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getDailyChart('used', 7)),

            Stat::make(__('filament-passwordless-login::filament.widget_total_expired'), number_format($totalExpired))
                ->description(__('filament-passwordless-login::filament.widget_expired_unused'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make(__('filament-passwordless-login::filament.widget_total_active'), number_format($totalActive))
                ->description(__('filament-passwordless-login::filament.widget_currently_valid'))
                ->descriptionIcon('heroicon-m-link')
                ->color('primary'),
        ];
    }

    protected function getDailyChart(string $type, int $days): array
    {
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $query = MagicLoginToken::whereDate('created_at', $date);

            if ($type === 'used') {
                $query->where('use_count', '>', 0);
            }

            $data[] = $query->count();
        }

        return $data;
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
