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

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }

    protected ?string $maxHeight = '450px';

    protected function getData(): array
    {
        $days = $this->getChartDays();
        $since = now()->subDays($days - 1)->startOfDay();

        $generated = MagicLoginToken::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $used = MagicLoginToken::where('created_at', '>=', $since)
            ->where('use_count', '>', 0)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $expired = MagicLoginToken::where('created_at', '>=', $since)
            ->where('expires_at', '<=', now())
            ->where('use_count', 0)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $generatedData = [];
        $usedData = [];
        $expiredData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();
            $labels[] = $date->format('M j');
            $generatedData[] = $generated[$dateString] ?? 0;
            $usedData[] = $used[$dateString] ?? 0;
            $expiredData[] = $expired[$dateString] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('filament-passwordless-login::filament.widget_chart_generated'),
                    'data' => $generatedData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => __('filament-passwordless-login::filament.widget_chart_used'),
                    'data' => $usedData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => __('filament-passwordless-login::filament.widget_chart_failed'),
                    'data' => $expiredData,
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
