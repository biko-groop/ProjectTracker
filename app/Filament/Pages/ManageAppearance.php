<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageAppearance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'الإدارة والإعدادات';

    protected static ?string $navigationLabel = 'إعدادات المظهر';

    protected static ?string $title = 'إعدادات المظهر والهوية';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.manage-appearance';

    public ?array $data = [];

    public const COLORS = [
        'indigo' => 'بنفسجي (Indigo)',
        'blue' => 'أزرق (Blue)',
        'sky' => 'سماوي (Sky)',
        'cyan' => 'سيان (Cyan)',
        'teal' => 'فيروزي (Teal)',
        'emerald' => 'زمردي (Emerald)',
        'green' => 'أخضر (Green)',
        'amber' => 'كهرماني (Amber)',
        'orange' => 'برتقالي (Orange)',
        'red' => 'أحمر (Red)',
        'rose' => 'وردي غامق (Rose)',
        'pink' => 'وردي (Pink)',
        'purple' => 'أرجواني (Purple)',
        'violet' => 'بنفسجي فاتح (Violet)',
        'slate' => 'رمادي مزرق (Slate)',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        $s = Setting::current();
        $this->form->fill([
            'app_name' => $s->app_name,
            'primary_color' => $s->primary_color,
            'sidebar_theme' => $s->sidebar_theme,
            'logo' => $s->logo,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('الهوية العامة')
                    ->description('اسم الشركة/النظام والشعار — يظهران في صفحة الدخول وأعلى القائمة الجانبية')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('اسم الشركة / النظام')
                            ->helperText('يظهر في شريط العنوان وصفحة تسجيل الدخول')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('logo')
                            ->label('شعار النظام')
                            ->helperText('يُفضّل صورة PNG شفافة. يظهر بحجم كبير في صفحة الدخول. اتركه فارغًا لاستخدام أيقونة افتراضية.')
                            ->image()
                            ->disk('public')
                            ->directory('logos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imagePreviewHeight('80'),
                    ])->columns(2),

                Section::make('الألوان والنمط')
                    ->description('اختر لون الهوية ونمط السايد بار')
                    ->schema([
                        Select::make('primary_color')
                            ->label('اللون الأساسي')
                            ->options(self::COLORS)
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Select::make('sidebar_theme')
                            ->label('نمط السايد بار')
                            ->helperText('النمط الداكن يأخذ درجة غامقة من اللون الأساسي المختار')
                            ->options([
                                'light' => 'فاتح (Light)',
                                'dark' => 'داكن بلون الهوية',
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::current()->update($data);

        Notification::make()
            ->title('تم حفظ إعدادات المظهر')
            ->body('قد تحتاج إلى تحديث الصفحة (Ctrl+F5) لرؤية التغييرات.')
            ->success()
            ->send();
    }
}
