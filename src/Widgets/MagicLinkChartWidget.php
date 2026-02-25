<?php

namespace SpykApp\FilamentPasswordlessLogin\Widgets;

use Filament\Widgets\ChartWidget;
use SpykApp\FilamentPasswordlessLogin\FilamentPasswordlessLoginPlugin;
use SpykApp\PasswordlessLogin\Models\MagicLoginToken;

class MagicLinkChartWidget extends ChartWidget
{
    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('filament-passwordless-login::filament.widget_chart_heading');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = $this->getChartDays();

        $labels = [];
        $generated = [];
        $used = [];
        $expired = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();
            $labels[] = $date->format('M j');

            $generated[] = MagicLoginToken::whereDate('created_at', $dateString)->count();

            $used[] = MagicLoginToken::whereDate('created_at', $dateString)
                ->where('use_count', '>', 0)
                ->count();

            $expired[] = MagicLoginToken::whereDate('created_at', $dateString)
                ->where('expires_at', '<=', now())
                ->where('use_count', 0)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('filament-passwordless-login::filament.widget_chart_generated'),
                    'data' => $generated,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => __('filament-passwordless-login::filament.widget_chart_used'),
                    'data' => $used,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => __('filament-passwordless-login::filament.widget_chart_failed'),
                    'data' => $expired,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
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
