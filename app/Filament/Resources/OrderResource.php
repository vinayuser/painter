<?php

namespace App\Filament\Resources;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('order_number')->disabled(),
            Forms\Components\Select::make('customer_id')
                ->relationship('customer', 'name', fn ($query) => $query->where('role', UserRole::Customer))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('delivery_agent_id')
                ->label('Delivery Agent')
                ->options(User::query()->where('role', UserRole::DeliveryAgent)->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\TextInput::make('total_amount')->numeric()->prefix('₹')->required(),
            Forms\Components\Select::make('payment_status')
                ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            Forms\Components\Select::make('order_status')
                ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            Forms\Components\Select::make('delivery_status')
                ->options(collect(DeliveryStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            Forms\Components\Textarea::make('shipping_address')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('shipping_phone'),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('INR'),
                Tables\Columns\TextColumn::make('payment_status')->badge(),
                Tables\Columns\TextColumn::make('order_status')->badge(),
                Tables\Columns\TextColumn::make('delivery_status')->badge(),
                Tables\Columns\TextColumn::make('deliveryAgent.name')->label('Agent'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
