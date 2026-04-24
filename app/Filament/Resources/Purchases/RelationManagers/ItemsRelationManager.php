<?php

namespace App\Filament\Resources\Purchases\RelationManagers;

use App\Models\Stock;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medicine_id')
                    ->relationship('medicine', 'name')
                    ->required()
                    ->live()
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if (blank($state)) {
                            return;
                        }

                        $latestStock = Stock::query()
                            ->where('medicine_id', (int) $state)
                            ->orderByDesc('id')
                            ->first();

                        if ($latestStock && blank($get('buy_price'))) {
                            $set('buy_price', (float) $latestStock->buy_price);
                        }

                        if (blank($get('batch_no'))) {
                            $set('batch_no', 'PO-'.now()->format('ymd').'-'.random_int(1000, 9999));
                        }
                    }),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('buy_price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                DatePicker::make('expiry_date')
                    ->required()
                    ->default(now()->addYear()),
                TextInput::make('batch_no')
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('medicine.name')
                    ->label('Medicine'),
                TextEntry::make('quantity'),
                TextEntry::make('buy_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('expiry_date')
                    ->date(),
                TextEntry::make('batch_no'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_no')
            ->columns([
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->sortable(),
                TextColumn::make('buy_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('batch_no')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->databaseTransaction()
                    ->successNotificationTitle('Purchase item added and stock updated'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->databaseTransaction()
                    ->successNotificationTitle('Purchase item updated and stock adjusted'),
                DeleteAction::make()
                    ->databaseTransaction()
                    ->successNotificationTitle('Purchase item deleted and stock adjusted'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
