<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Filament\Resources\EquipmentResource\RelationManagers;
use Infrastructure\Persistence\Eloquent\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    
    protected static ?string $navigationGroup = 'Equipment Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('asset_tag')
                    ->label('Asset Tag')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                Forms\Components\Select::make('equipment_type')
                    ->label('Type')
                    ->options([
                        'Laptop' => 'Laptop',
                        'Desktop' => 'Desktop',
                        'Monitor' => 'Monitor',
                        'Phone' => 'Phone',
                        'Keyboard' => 'Keyboard',
                        'Mouse' => 'Mouse',
                        'Other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('brand')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('model')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('serial_number')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('status')
                    ->options([
                        'Available' => 'Available',
                        'Assigned' => 'Assigned',
                        'InMaintenance' => 'In Maintenance',
                        'Decommissioned' => 'Decommissioned',
                    ])
                    ->required()
                    ->default('Available'),
                Forms\Components\Select::make('condition')
                    ->options([
                        'New' => 'New',
                        'Good' => 'Good',
                        'Fair' => 'Fair',
                        'Poor' => 'Poor',
                    ])
                    ->required()
                    ->default('Good'),
                Forms\Components\DatePicker::make('purchase_date'),
                Forms\Components\TextInput::make('purchase_cost')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')
                    ->label('Asset Tag')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipment_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Available' => 'success',
                        'Assigned' => 'info',
                        'InMaintenance' => 'warning',
                        'Decommissioned' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('currentAssignee.full_name')
                    ->label('Assigned To')
                    ->default('-'),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Available' => 'Available',
                        'Assigned' => 'Assigned',
                        'InMaintenance' => 'In Maintenance',
                        'Decommissioned' => 'Decommissioned',
                    ]),
                Tables\Filters\SelectFilter::make('equipment_type')
                    ->label('Type')
                    ->options([
                        'Laptop' => 'Laptop',
                        'Desktop' => 'Desktop',
                        'Monitor' => 'Monitor',
                        'Phone' => 'Phone',
                        'Keyboard' => 'Keyboard',
                        'Mouse' => 'Mouse',
                        'Other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
