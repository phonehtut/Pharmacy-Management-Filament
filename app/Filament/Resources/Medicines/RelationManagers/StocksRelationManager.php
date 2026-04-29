<?php

namespace App\Filament\Resources\Medicines\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StocksRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('batch_no')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('expiry_date')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('buy_price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('sell_price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ])
            ->columns(2);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('batch_no'),
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('expiry_date')
                    ->date(),
                TextEntry::make('quantity')
                    ->badge(),
                TextEntry::make('buy_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('sell_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_no')
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable(),
                TextColumn::make('batch_no')
                    ->searchable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 10 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('sell_price')
                    ->label('Sell')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
