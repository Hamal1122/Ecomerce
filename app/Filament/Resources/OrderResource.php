<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Tables\Filters\SelectFilter;
use Faker\Core\Number;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\select;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2; //urutan list navigasi navbar

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Order Information')->schema([
                        Select::make('user_id')
                        ->label('Customer')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                        Select::make('payment_method')
                        ->options([
                            'stripe'=> 'Stripe',
                            'cod' => 'Cash On Delivery'
                        ])
                        -> required(),

                        Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed'
                        ])
                        ->default('pending')
                        ->required(),

                        ToggleButtons::make('status')
                        ->inline()
                        ->default('new')
                        ->required()
                        ->options([
                            'new' => 'New',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'canceled' => 'Canceled'
                        ])
                        ->colors([
                            'new' => 'info',
                            'processing' => 'warning',
                            'shipped' => 'success',
                            'delivered' => 'success',
                            'canceled' => 'danger'
                        ])
                        ->icons([
                            'new' => 'heroicon-m-sparkles',
                            'processing' => 'heroicon-m-arrow-path',
                            'shipped' => 'heroicon-m-truck',
                            'delivered' => 'heroicon-m-check-badge',
                            'canceled' => 'heroicon-m-x-circle'
                        ]),

                        Select::make('currency')
                        ->options([
                            'usd' => 'USD',
                            'idr' => 'IDR',
                            'eur' => 'EUR',
                        ])
                        ->default('idr')
                        ->required(),

                        Select::make('shipping_method')
                        ->options([
                            'jnt' => 'JNT',
                            'jne' => 'JNE',
                            'posindo' => 'Pos Indonesia'
                        ])
                        ->required(),

                        Textarea::make('notes')
                        ->columnSpanFull()
                    ])->columns(2),

                    Section::make('Order Items')->schema([
                        Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->distinct()
                            ->reactive()
                            ->columnSpan(4)
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(fn ($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0))
                            ->afterStateUpdated(fn ($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0)),

                        TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->reactive()
                        ->default(1)
                        ->columnSpan(2)
                        ->minValue(1)
                        ->afterStateUpdated(fn ($state, Set $set, Get $get ) => $set('total_amount', $state*$get('unit_amount'))),

                        TextInput::make('unit_amount')
                        ->numeric()
                        ->required()
                        ->columnSpan(3)
                        ->disabled()
                        ->dehydrated(),

                        TextInput::make('total_amount')
                        ->numeric()
                        ->columnSpan(3)
                        ->dehydrated()
                        ->required(),

                        ])->columns(12),

                        Placeholder::make('grand_total_placeholder')
                        ->label('Grand Total')
                        ->content(function(Get $get, Set $set){
                            $total = 0;
                            if (!$repeaters = $get('items')){
                                return 'IDR 0';
                            }

                            foreach($repeaters as $key => $repeater){
                                $total += $get("items.{$key}.total_amount");
                            }

                            $set('grand_total', $total);

                            return 'IDR ' . number_format($total, 2, '.', ',');
                        }),

                        Hidden::make('grand_total')
                        ->reactive()
                        ->default(0),
                    ])

                ])->columnSpan('full')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                ->sortable()
                ->label('Customer')
                ->searchable(),

                TextColumn::make('grand_total')
                ->numeric()
                ->sortable()
                ->money('IDR'),

                TextColumn::make('payment_method')
                ->searchable()
                ->sortable(),

                TextColumn::make('payment_status')
                ->searchable()
                ->sortable(),

                TextColumn::make('currency')
                ->searchable()
                ->sortable(),

                TextColumn::make('shipping_method')
                ->searchable()
                ->sortable(),

                SelectColumn::make('status')
                ->searchable()
                ->sortable()
                ->options([
                    'new' => 'New',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'canceled' => 'Canceled',
                    
                ])


                
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'Canceled',
                    ]),

                    SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed'
                    ]),
            ])

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                
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
            AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string{
        return static::getModel()::where('status', 'new')->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
