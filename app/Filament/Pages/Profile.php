<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'الملف الشخصي';

    protected static ?string $title = 'الملف الشخصي';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.profile';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->form->fill([
            'name' => $user->name,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('المعلومات الشخصية')
                    ->description('اسمك وصورتك الشخصية')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('الصورة الشخصية')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imageEditor(),
                        TextInput::make('name')->label('الاسم')->required()->maxLength(255),
                        TextInput::make('phone')
                            ->label('الهاتف أو البريد الإلكتروني')
                            ->helperText('يمكنك إدخال رقم هاتف أو بريد إلكتروني للتواصل')
                            ->maxLength(255)
                            // يقبل بريدًا إلكترونيًا أو رقم هاتف — أيّهما دون خطأ
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail): void {
                                    if (blank($value)) {
                                        return;
                                    }
                                    $isEmail = (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
                                    $isPhone = (bool) preg_match('/^[0-9+\-\s()]{6,}$/', $value);
                                    if (! $isEmail && ! $isPhone) {
                                        $fail('أدخل رقم هاتف صحيح أو بريدًا إلكترونيًا صحيحًا.');
                                    }
                                };
                            }),
                    ])->columns(2),

                Section::make('تغيير كلمة المرور')
                    ->description('اتركها فارغة إن لم ترغب بتغيير كلمة المرور')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('كلمة المرور الحالية')
                            ->password()->revealable()
                            ->autocomplete('current-password')
                            ->requiredWith('new_password')
                            ->dehydrated(false),
                        TextInput::make('new_password')
                            ->label('كلمة المرور الجديدة')
                            ->password()->revealable()
                            ->minLength(6)
                            ->confirmed()
                            ->autocomplete('new-password')
                            ->dehydrated(false),
                        TextInput::make('new_password_confirmation')
                            ->label('تأكيد كلمة المرور الجديدة')
                            ->password()->revealable()
                            ->dehydrated(false),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // تغيير كلمة المرور (إن طُلب) مع التحقق من الحالية
        if (filled($data['new_password'] ?? null)) {
            if (! Hash::check($data['current_password'] ?? '', $user->password)) {
                Notification::make()
                    ->title('كلمة المرور الحالية غير صحيحة')
                    ->danger()->send();

                return;
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        $user->avatar = $data['avatar'] ?? null;
        $user->save();

        // إفراغ حقول كلمة المرور بعد الحفظ
        $this->form->fill([
            'name' => $user->name,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
        ]);

        Notification::make()
            ->title('تم حفظ الملف الشخصي بنجاح')
            ->success()->send();
    }
}
