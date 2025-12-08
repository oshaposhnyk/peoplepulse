<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use Infrastructure\Persistence\Eloquent\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Employee Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('middle_name')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required(),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->maxDate(now()->subYears(18)),
                    ])->columns(2),
                
                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\Select::make('position')
                            ->required()
                            ->options([
                                'Developer' => 'Developer',
                                'Senior Developer' => 'Senior Developer',
                                'Lead Developer' => 'Lead Developer',
                                'QA Engineer' => 'QA Engineer',
                                'DevOps Engineer' => 'DevOps Engineer',
                                'Designer' => 'Designer',
                                'Product Manager' => 'Product Manager',
                                'Engineering Manager' => 'Engineering Manager',
                            ]),
                        Forms\Components\Select::make('department')
                            ->options([
                                'Engineering' => 'Engineering',
                                'QA' => 'QA',
                                'DevOps' => 'DevOps',
                                'Design' => 'Design',
                                'Product' => 'Product',
                            ]),
                        Forms\Components\Select::make('employment_type')
                            ->options([
                                'Full-time' => 'Full-time',
                                'Part-time' => 'Part-time',
                                'Contract' => 'Contract',
                            ])
                            ->default('Full-time'),
                        Forms\Components\Select::make('office_location')
                            ->required()
                            ->options([
                                'San Francisco HQ' => 'San Francisco HQ',
                                'New York Office' => 'New York Office',
                                'Austin Office' => 'Austin Office',
                                'London Office' => 'London Office',
                                'Remote' => 'Remote',
                            ]),
                        Forms\Components\DatePicker::make('hire_date')
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\Select::make('employment_status')
                            ->options([
                                'Active' => 'Active',
                                'Terminated' => 'Terminated',
                                'OnLeave' => 'On Leave',
                            ])
                            ->default('Active'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Compensation')
                    ->schema([
                        Forms\Components\TextInput::make('salary_amount')
                            ->numeric()
                            ->required()
                            ->minValue(30000)
                            ->prefix('$'),
                        Forms\Components\Select::make('salary_currency')
                            ->options(['USD' => 'USD'])
                            ->default('USD'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department')
                    ->sortable(),
                Tables\Columns\TextColumn::make('office_location')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('employment_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Terminated',
                        'warning' => 'OnLeave',
                    ]),
                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employment_status')
                    ->options([
                        'Active' => 'Active',
                        'Terminated' => 'Terminated',
                        'OnLeave' => 'On Leave',
                    ]),
                Tables\Filters\SelectFilter::make('position'),
                Tables\Filters\SelectFilter::make('department'),
                Tables\Filters\SelectFilter::make('office_location'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('hire_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
