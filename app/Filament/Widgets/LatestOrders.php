<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Tables\Actions\Action as TablesActionsAction;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{


    protected int | string |array  $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                ->searchable()
                ->label('Order ID'),

                TextColumn::make('user.name')
                ->searchable()
                ->searchable()
                ->label('Name'),

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
            ->actions([
                TablesActionsAction::make('View Order')
                ->url(fn(Order $record): string => OrderResource::getUrl('view', ['record'=> $record])),
            ]);
    }
}
