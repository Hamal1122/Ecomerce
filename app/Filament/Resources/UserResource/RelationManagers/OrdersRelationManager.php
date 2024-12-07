<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\Modal\Actions\Action as ActionsAction;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as ComponentsActionsAction;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action as TablesActionsAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                ->searchable()
                ->label('Order ID'),

                TextColumn::make('grand_total')
                ->money('IDR'),

                TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match($state){
                    'new' => 'info',
                    'processing' => 'warning',
                    'shipped' => 'success',
                    'delivered' => 'success',
                    'canceled' => 'danger'
                }),

                TextColumn::make('payment_method')
                ->sortable()
                ->searchable(),

                TextColumn::make('payment_status')
                ->badge()
                ->color(fn (string $state): string => match($state){
                    'pending' => 'warning',
                    'paid' => 'success',
                    'failed' => 'danger'
                }),

                TextColumn::make('created_at')
                ->label('Order Date')
                ->dateTime(),



            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                TablesActionsAction::make('View Order')
                ->url(fn (Order $record):string => OrderResource::getUrl('view', ['record' => $record]))
                ->color('info')
                ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
