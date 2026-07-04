<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'إدارة المشاريع';

    protected static ?string $navigationLabel = 'المهام';

    protected static ?string $modelLabel = 'مهمة';

    protected static ?string $pluralModelLabel = 'المهام';

    protected static ?int $navigationSort = 2;

    protected const STATUSES = [
        'pending' => 'قيد الانتظار',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى',
    ];

    protected const PRIORITIES = [
        'low' => 'منخفض',
        'medium' => 'متوسط',
        'high' => 'عالٍ',
        'urgent' => 'عاجل',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label(__('Title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('description')
                ->label(__('Description'))
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Select::make('project_id')
                ->label(__('Project'))
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('department_id')
                ->label(__('Department'))
                ->relationship('department', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('assigned_to')
                ->label(__('Assigned To'))
                ->relationship('assignedUser', 'name')
                ->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label)
                ->searchable(['name', 'job_title'])
                ->preload(),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options(self::STATUSES)
                ->default('pending')
                ->native(false)
                ->required()
                // منع البدء/الإنهاء إذا كانت هناك مهام تابعة غير منجزة
                ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                    if (! in_array($value, ['in_progress', 'completed'])) {
                        return;
                    }
                    $recordId = request()->route('record');
                    if ($recordId && ($task = \App\Models\Task::find($recordId)) && $task->is_blocked) {
                        $fail('لا يمكن بدء أو إنهاء هذه المهمة قبل إنجاز المهام التي تعتمد عليها.');
                    }
                }),
            Forms\Components\Select::make('priority')
                ->label(__('Priority'))
                ->options(self::PRIORITIES)
                ->default('medium')
                ->native(false)
                ->required(),
            Forms\Components\TextInput::make('progress')
                ->label(__('Progress') . ' %')
                ->numeric()->minValue(0)->maxValue(100)->default(0)->suffix('%'),
            Forms\Components\DatePicker::make('start_date')->label(__('Start Date')),
            Forms\Components\DatePicker::make('due_date')->label(__('Due Date')),
            Forms\Components\TextInput::make('estimated_hours')
                ->label(__('Estimated Hours'))->numeric()->minValue(0),
            Forms\Components\TextInput::make('actual_hours')
                ->label(__('Actual Hours'))->numeric()->minValue(0),
            Forms\Components\Select::make('created_by')
                ->label(__('Created By'))
                ->relationship('creator', 'name')
                ->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label)
                ->default(fn () => auth()->id())
                ->searchable(['name', 'job_title'])
                ->preload()
                ->required(),
            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Section::make('الاعتماديات (المهام السابقة)')
                ->description('المهام التي يجب إنهاؤها قبل بدء هذه المهمة')
                ->schema([
                    Forms\Components\Select::make('dependencies')
                        ->hiddenLabel()
                        ->relationship('dependencies', 'title', fn (Builder $query) => $query->whereKeyNot(request()->route('record')))
                        ->multiple()
                        ->searchable()
                        ->preload(),
                ])
                ->collapsible(),
            Forms\Components\Section::make('الأقسام المعنية')
                ->description('للمهام المشتركة بين أكثر من قسم: أضف كل قسم وحدّد نوع مسؤوليته (تنفيذ، قرار مالي، استشاري، موافقة...)')
                ->schema([
                    Forms\Components\Repeater::make('departmentLinks')
                        ->relationship()
                        ->hiddenLabel()
                        ->schema([
                            Forms\Components\Select::make('department_id')
                                ->label('القسم')
                                ->relationship('department', 'name')
                                ->required()->searchable()->preload(),
                            Forms\Components\Select::make('responsibility')
                                ->label('نوع المسؤولية')
                                ->options(\App\Models\TaskDepartment::RESPONSIBILITIES)
                                ->default('execution')->required()->native(false),
                            Forms\Components\TextInput::make('note')
                                ->label('ملاحظة / تفاصيل')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->addActionLabel('+ إضافة قسم معني')
                        ->itemLabel(fn (array $state): ?string => \App\Models\TaskDepartment::RESPONSIBILITIES[$state['responsibility'] ?? ''] ?? null)
                        ->collapsible()
                        ->cloneable()
                        ->defaultItems(0),
                ])
                ->collapsible(),
            Forms\Components\Section::make(__('Obstacles & Risks'))
                ->description(__('Fill in if any, otherwise leave as "None"'))
                ->schema([
                    Forms\Components\Textarea::make('obstacles')
                        ->label(__('Obstacles'))
                        ->rows(2)->columnSpanFull()
                        ->default('لا توجد'),
                    Forms\Components\Textarea::make('potential_risks')
                        ->label(__('Potential Risks'))
                        ->rows(2)->columnSpanFull()
                        ->default('لا توجد'),
                ])
                ->columns(1)
                ->collapsible(),
            Forms\Components\Section::make(__('Delay Reason'))
                ->description(__('Required when the task is delayed'))
                ->schema([
                    Forms\Components\Textarea::make('delay_reason')
                        ->label(__('Delay Reason'))
                        ->rows(2)->columnSpanFull()
                        // إلزامي عند إكمال مهمة تجاوزت تاريخ النهاية
                        ->required(fn (Get $get): bool => $get('status') === 'completed'
                            && filled($get('due_date'))
                            && \Illuminate\Support\Carbon::parse($get('due_date'))->isPast())
                        ->helperText(__('Required when closing a delayed task')),
                    Forms\Components\Toggle::make('delay_needs_support')->label(__('Needs Support')),
                    Forms\Components\Toggle::make('delay_needs_approval')->label(__('Needs Approval')),
                    Forms\Components\Toggle::make('delay_needs_budget')->label(__('Needs Budget')),
                    Forms\Components\Toggle::make('delay_needs_decision')->label(__('Needs Decision')),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('بيانات المهمة')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Infolists\Components\TextEntry::make('title')->label('العنوان')
                        ->weight('bold')->size('lg')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('project.name')->label('المشروع')->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('department.name')->label('القسم')->badge()->color('gray')->default('—'),
                    Infolists\Components\TextEntry::make('assignedUser')->label('المسؤول')
                        ->getStateUsing(fn ($record) => $record->assignedUser?->select_label ?? '—'),
                    Infolists\Components\TextEntry::make('creator.name')->label('المنشئ')->default('—'),
                    Infolists\Components\TextEntry::make('status')->label('الحالة')->badge()
                        ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                        ->color(fn ($state) => match ($state) { 'completed' => 'success', 'in_progress' => 'info', 'cancelled' => 'danger', default => 'warning' }),
                    Infolists\Components\TextEntry::make('priority')->label('الأولوية')->badge()
                        ->formatStateUsing(fn ($state) => self::PRIORITIES[$state] ?? $state)
                        ->color(fn ($state) => match ($state) { 'urgent' => 'danger', 'high' => 'warning', 'low' => 'gray', default => 'info' }),
                    Infolists\Components\TextEntry::make('progress')->label('نسبة الإنجاز')
                        ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')->badge()
                        ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'info' : 'gray')),
                    Infolists\Components\TextEntry::make('description')->label('الوصف')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('notes')->label('ملاحظات')->default('—')->columnSpanFull(),
                ])->columns(2),

            Infolists\Components\Section::make('التواريخ والوقت')
                ->icon('heroicon-o-calendar')
                ->schema([
                    Infolists\Components\TextEntry::make('start_date')->label('تاريخ البداية')->date()->placeholder('—'),
                    Infolists\Components\TextEntry::make('due_date')->label('تاريخ الاستحقاق')->date()->placeholder('—')
                        ->color(fn ($record) => $record->is_delayed ? 'danger' : null),
                    Infolists\Components\TextEntry::make('estimated_hours')->label('الساعات المتوقعة')->placeholder('—'),
                    Infolists\Components\TextEntry::make('actual_hours')->label('الساعات الفعلية')->placeholder('—'),
                    Infolists\Components\IconEntry::make('is_delayed')->label('متأخرة؟')->boolean()
                        ->trueIcon('heroicon-o-exclamation-triangle')->falseIcon('heroicon-o-check-circle')
                        ->trueColor('danger')->falseColor('success'),
                    Infolists\Components\TextEntry::make('days_delayed')->label('أيام التأخير')
                        ->visible(fn ($record) => $record->is_delayed)->badge()->color('danger'),
                ])->columns(3),

            Infolists\Components\Section::make('الأقسام المعنية والاعتماديات')
                ->icon('heroicon-o-link')
                ->schema([
                    Infolists\Components\TextEntry::make('departmentLinks')->label('الأقسام المعنية')
                        ->getStateUsing(fn ($record) => $record->departmentLinks->map(fn ($l) => ($l->department?->name) . ' (' . (\App\Models\TaskDepartment::RESPONSIBILITIES[$l->responsibility] ?? $l->responsibility) . ')')->implode('، ') ?: 'لا يوجد'),
                    Infolists\Components\TextEntry::make('dependencies')->label('يعتمد على')
                        ->getStateUsing(fn ($record) => $record->dependencies->pluck('title')->implode('، ') ?: 'لا يوجد'),
                ])->columns(2),

            Infolists\Components\Section::make('المعوقات والمخاطر')
                ->icon('heroicon-o-exclamation-triangle')
                ->schema([
                    Infolists\Components\TextEntry::make('obstacles')->label('المعوقات')->default('لا توجد'),
                    Infolists\Components\TextEntry::make('potential_risks')->label('المخاطر المحتملة')->default('لا توجد'),
                ])->columns(2)->collapsible(),

            Infolists\Components\Section::make('أسباب التأخير')
                ->icon('heroicon-o-clock')
                ->visible(fn ($record) => filled($record->delay_reason))
                ->schema([
                    Infolists\Components\TextEntry::make('delay_reason')->label('السبب')->columnSpanFull(),
                    Infolists\Components\IconEntry::make('delay_needs_support')->label('يحتاج دعماً')->boolean(),
                    Infolists\Components\IconEntry::make('delay_needs_approval')->label('يحتاج موافقة')->boolean(),
                    Infolists\Components\IconEntry::make('delay_needs_budget')->label('يحتاج ميزانية')->boolean(),
                    Infolists\Components\IconEntry::make('delay_needs_decision')->label('يحتاج قراراً')->boolean(),
                ])->columns(2)->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Task $record) => Pages\ViewTask::getUrl([$record]))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('Project'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::PRIORITIES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'low' => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label(__('Assigned To'))
                    ->default('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label(__('Progress'))
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'info' : 'gray')),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Due Date'))
                    ->date()
                    ->sortable()
                    ->description(fn ($record) => $record->is_delayed ? __('Delayed') . ' (' . $record->days_delayed . ' ' . __('days') . ')' : null)
                    ->color(fn ($record) => $record->is_delayed ? 'danger' : null),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('محجوبة')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('departmentLinks.department.name')
                    ->label('الأقسام المعنية')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('obstacles')
                    ->label(__('Obstacles'))
                    ->formatStateUsing(fn ($state) => filled($state) ? $state : 'لا توجد')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('potential_risks')
                    ->label(__('Potential Risks'))
                    ->formatStateUsing(fn ($state) => filled($state) ? $state : 'لا توجد')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(self::STATUSES),
                Tables\Filters\SelectFilter::make('priority')->label(__('Priority'))->options(self::PRIORITIES),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->relationship('project', 'name'),
                // فلتر القسم: يشمل القسم الرئيسي للمهمة + الأقسام المعنية المرتبطة بها
                Tables\Filters\SelectFilter::make('department')
                    ->label('القسم')
                    ->options(fn () => \App\Models\Department::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $id = $data['value'] ?? null;

                        if (blank($id)) {
                            return $query;
                        }

                        return $query->where(function (Builder $q) use ($id): void {
                            $q->where('department_id', $id)
                                ->orWhereHas('departmentLinks', fn (Builder $dl) => $dl->where('department_id', $id));
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // تثبيت الفلاتر والبحث والفرز في الجلسة: تبقى عند التنقّل/التحديث/العودة،
            // وتُمسح تلقائياً عند تسجيل الخروج (إبطال الجلسة). سلوك مشابه لأنظمة مثل Odoo.
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ObstaclesRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\FilesRelationManager::class,
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
