<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /** أسماء ألوان Filament المدعومة في إعدادات المظهر */
    public const COLOR_MAP = [
        'indigo' => Color::Indigo,
        'blue' => Color::Blue,
        'sky' => Color::Sky,
        'cyan' => Color::Cyan,
        'teal' => Color::Teal,
        'emerald' => Color::Emerald,
        'green' => Color::Green,
        'amber' => Color::Amber,
        'orange' => Color::Orange,
        'red' => Color::Red,
        'rose' => Color::Rose,
        'pink' => Color::Pink,
        'purple' => Color::Purple,
        'violet' => Color::Violet,
        'slate' => Color::Slate,
    ];

    /** درجة غامقة من كل لون لخلفية السايد بار الداكن (تتبع لون الهوية) */
    public const SIDEBAR_SHADE = [
        'indigo' => '#312e81',
        'blue' => '#1e3a8a',
        'sky' => '#0c4a6e',
        'cyan' => '#164e63',
        'teal' => '#134e4a',
        'emerald' => '#064e3b',
        'green' => '#14532d',
        'amber' => '#78350f',
        'orange' => '#7c2d12',
        'red' => '#7f1d1d',
        'rose' => '#881337',
        'pink' => '#831843',
        'purple' => '#581c87',
        'violet' => '#4c1d95',
        'slate' => '#0f172a',
    ];

    public function panel(Panel $panel): Panel
    {
        // قراءة إعدادات المظهر من قاعدة البيانات بأمان
        $appName = 'نظام إدارة المشاريع';
        $primary = 'indigo';
        $sidebarDark = false;
        $logo = null;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::query()->first();
                if ($settings) {
                    $appName = $settings->app_name ?: $appName;
                    $primary = $settings->primary_color ?: $primary;
                    $sidebarDark = ($settings->sidebar_theme ?? 'light') === 'dark';
                    $logo = $settings->logo ?: null;
                }
            }
        } catch (\Throwable $e) {
            // أثناء الـ migrations أو قبل تجهيز القاعدة: استخدم الافتراضي
        }

        $primaryColor = self::COLOR_MAP[$primary] ?? Color::Indigo;

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName($appName)
            ->brandLogo(fn () => view('filament.brand', [
                'name' => $appName,
                'logo' => $logo,
                'dark' => $sidebarDark,
            ]))
            ->brandLogoHeight('2.5rem')
            ->font('Cairo')
            ->colors([
                'primary' => $primaryColor,
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Sky,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('إدارة المشاريع')->icon('heroicon-o-rectangle-stack'),
                NavigationGroup::make('الإدارة والإعدادات')->icon('heroicon-o-cog-6-tooth'),
            ])
            // جرس الإشعارات في الشريط العلوي
            ->renderHook('panels::topbar.end', fn (): string => view('filament.notification-bell')->render())
            // صفحة الدخول بخلفية بلون هوية النظام (تتغيّر مع المظهر تلقائياً)
            ->renderHook('panels::body.start', fn (): string => '<style>
                .fi-simple-layout { background: linear-gradient(135deg, rgb(var(--primary-500)) 0%, rgb(var(--primary-700)) 60%, rgb(var(--primary-900)) 100%) !important; }
                .fi-simple-main { box-shadow: 0 18px 50px rgba(0,0,0,.25) !important; border-radius: 1rem !important; }
                /* شعار صفحة الدخول: كبير وواضح واحترافي (مقصور على صفحة الدخول فقط) */
                .fi-simple-layout .fi-logo { color: #fff !important; width: 100%; justify-content: center; margin-bottom: .25rem; }
                .fi-simple-layout .fi-logo > div { flex-direction: column !important; align-items: center !important; gap: .85rem !important; text-align: center; }
                .fi-simple-layout .fi-logo img { height: 5.5rem !important; width: auto !important; max-width: 240px !important; border-radius: 1rem !important; }
                .fi-simple-layout .fi-logo span { font-size: 1.5rem !important; white-space: normal !important; }
            </style>');

        // سايد بار داكن بلون الهوية المُختار (يتغيّر مع اللون)
        if ($sidebarDark) {
            $bg = self::SIDEBAR_SHADE[$primary] ?? '#1e293b';
            $css = '<style>'
                . '.fi-sidebar, .fi-sidebar-header { background-color:' . $bg . ' !important; border-color:rgba(255,255,255,.12) !important; }'
                . '.fi-sidebar-header { box-shadow:none !important; }'
                // عناوين المجموعات: أوضح وأكبر قليلاً
                . '.fi-sidebar .fi-sidebar-group-label { color:rgba(255,255,255,.92) !important; font-weight:700 !important; font-size:.9rem !important; letter-spacing:.01em; }'
                // عناصر القائمة: نص أبيض واضح وحجم أكبر قليلاً
                . '.fi-sidebar .fi-sidebar-item-button { color:#ffffff !important; border-radius:.5rem; }'
                . '.fi-sidebar .fi-sidebar-item-label { color:#ffffff !important; font-weight:500 !important; font-size:.95rem !important; }'
                . '.fi-sidebar .fi-sidebar-item-button:hover { background-color:rgba(255,255,255,.14) !important; }'
                . '.fi-sidebar .fi-sidebar-item-button:hover .fi-sidebar-item-label { color:#ffffff !important; }'
                . '.fi-sidebar .fi-sidebar-item-icon { color:rgba(255,255,255,.88) !important; }'
                . '.fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-button,'
                . '.fi-sidebar .fi-sidebar-item-button.fi-active { background-color:rgba(255,255,255,.22) !important; }'
                . '.fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label { color:#ffffff !important; font-weight:700 !important; }'
                . '.fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-icon { color:#ffffff !important; }'
                . '</style>';

            $panel->renderHook('panels::head.end', fn (): string => $css);
        }

        return $panel
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
