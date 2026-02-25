<?php

namespace SpykApp\FilamentPasswordlessLogin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use SpykApp\FilamentPasswordlessLogin\Enums\FilamentPasswordlessLoginActionPosition;
use SpykApp\FilamentPasswordlessLogin\Pages\Login;
use SpykApp\FilamentPasswordlessLogin\Pages\MagicLinkLogin;
use SpykApp\FilamentPasswordlessLogin\Resources\MagicLoginTokenResource;

class FilamentPasswordlessLoginPlugin implements Plugin
{
    protected bool $loginPageEnabled = true;
    protected bool $showPasswordLoginLink = true;
    protected bool $loginActionEnabled = false;
    protected FilamentPasswordlessLoginActionPosition $loginActionPosition = FilamentPasswordlessLoginActionPosition::EmailFieldHint;
    protected bool $slideover = false;
    protected bool $resourceEnabled = true;
    protected bool $canCreateTokens = true;
    protected bool $canDeleteTokens = true;
    protected bool $statsWidgetEnabled = true;
    protected bool $chartsWidgetEnabled = true;
    protected ?string $loginPageClass = null;
    protected ?string $loginActionIcon = null;
    protected ?string $loginActionColor = null;
    protected ?string $navigationGroup = null;
    protected ?string $navigationIcon = null;
    protected ?int $navigationSort = null;
    protected ?string $resourceSlug = null;
    protected ?int $chartDays = null;
    protected ?string $mailable = null;
    protected ?string $notificationClass = null;
    protected ?string $redirectUrl = null;
    protected ?string $failureUrl = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-passwordless-login';
    }

    // ── Login Page ──────────────────────────────────────────

    public function loginPage(bool $enabled = true): static
    {
        $this->loginPageEnabled = $enabled;

        return $this;
    }

    public function hasLoginPage(): bool
    {
        return $this->loginPageEnabled;
    }

    public function login(?string $pageClass = null): static
    {
        $this->loginPageEnabled = true;

        if ($pageClass) {
            $this->loginPageClass = $pageClass;
        }

        return $this;
    }

    public function getLoginPageClass(): string
    {
        return $this->loginPageClass ?? MagicLinkLogin::class;
    }

    public function showPasswordLoginLink(bool $show = true): static
    {
        $this->showPasswordLoginLink = $show;

        return $this;
    }

    public function hasPasswordLoginLink(): bool
    {
        return $this->showPasswordLoginLink;
    }

    // ── Login Action ────────────────────────────────────────

    public function loginAction(bool $enabled = true): static
    {
        $this->loginActionEnabled = $enabled;

        return $this;
    }

    public function hasLoginAction(): bool
    {
        return $this->loginActionEnabled;
    }

    public function loginActionPosition(FilamentPasswordlessLoginActionPosition $position): static
    {
        $this->loginActionPosition = $position;

        return $this;
    }

    public function getLoginActionPosition(): FilamentPasswordlessLoginActionPosition
    {
        return $this->loginActionPosition;
    }

    public function loginActionIcon(?string $icon): static
    {
        $this->loginActionIcon = $icon;

        return $this;
    }

    public function getLoginActionIcon(): string
    {
        return $this->loginActionIcon
            ?? config('filament-passwordless-login.login_action.icon', 'heroicon-m-link');
    }

    public function loginActionColor(?string $color): static
    {
        $this->loginActionColor = $color;

        return $this;
    }

    public function getLoginActionColor(): string
    {
        return $this->loginActionColor
            ?? config('filament-passwordless-login.login_action.color', 'primary');
    }

    // ── Action Modal ────────────────────────────────────────

    public function slideover(bool $slideover = true): static
    {
        $this->slideover = $slideover;

        return $this;
    }

    public function isSlideover(): bool
    {
        return $this->slideover;
    }

    public function mailable(?string $mailable): static
    {
        $this->mailable = $mailable;

        return $this;
    }

    public function getMailable(): ?string
    {
        return $this->mailable;
    }

    public function notification(?string $notificationClass): static
    {
        $this->notificationClass = $notificationClass;

        return $this;
    }

    public function getNotification(): ?string
    {
        return $this->notificationClass;
    }

    public function redirectUrl(?string $url): static
    {
        $this->redirectUrl = $url;

        return $this;
    }

    public function getRedirectUrl(): string
    {
        // Plugin → config → current panel's URL
        return $this->redirectUrl
            ?? config('passwordless-login.redirect.on_success')
            ?? filament()->getUrl();
    }

    public function failureUrl(?string $url): static
    {
        $this->failureUrl = $url;

        return $this;
    }

    public function getFailureUrl(): string
    {
        // Plugin → config → current panel's login URL
        return $this->failureUrl
            ?? config('passwordless-login.redirect.on_failure')
            ?? filament()->getLoginUrl();
    }

    // ── Resource ────────────────────────────────────────────

    public function resource(bool $enabled = true): static
    {
        $this->resourceEnabled = $enabled;

        return $this;
    }

    public function hasResource(): bool
    {
        return $this->resourceEnabled;
    }

    public function canCreateTokens(bool $can = true): static
    {
        $this->canCreateTokens = $can;

        return $this;
    }

    public function getCanCreateTokens(): bool
    {
        return $this->canCreateTokens;
    }

    public function canDeleteTokens(bool $can = true): static
    {
        $this->canDeleteTokens = $can;

        return $this;
    }

    public function getCanDeleteTokens(): bool
    {
        return $this->canDeleteTokens;
    }

    public function resourceSlug(?string $slug): static
    {
        $this->resourceSlug = $slug;

        return $this;
    }

    public function getResourceSlug(): string
    {
        return $this->resourceSlug
            ?? config('filament-passwordless-login.resource.slug', 'magic-login-tokens');
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup
            ?? __('filament-passwordless-login::filament.navigation_group');
    }

    public function navigationIcon(?string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon
            ?? config('filament-passwordless-login.resource.navigation_icon', 'heroicon-o-link');
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort ?? 100;
    }

    // ── Widgets ─────────────────────────────────────────────

    public function statsWidget(bool $enabled = true): static
    {
        $this->statsWidgetEnabled = $enabled;

        return $this;
    }

    public function hasStatsWidget(): bool
    {
        return $this->statsWidgetEnabled;
    }

    public function chartsWidget(bool $enabled = true): static
    {
        $this->chartsWidgetEnabled = $enabled;

        return $this;
    }

    public function hasChartsWidget(): bool
    {
        return $this->chartsWidgetEnabled;
    }

    public function chartDays(?int $days): static
    {
        $this->chartDays = $days;

        return $this;
    }

    public function getChartDays(): int
    {
        return $this->chartDays
            ?? config('filament-passwordless-login.widgets.chart_days', 30);
    }

    // ── Registration ────────────────────────────────────────

    public function register(Panel $panel): void
    {
        if ($this->loginPageEnabled) {
            $panel->login($this->getLoginPageClass());
        }

        if ($this->loginActionEnabled) {
            $panel->login(Login::class);
        }

        if ($this->resourceEnabled) {
            $panel->resources([
                MagicLoginTokenResource::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {

    }
}