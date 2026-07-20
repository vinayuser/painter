<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Filament\Resources\PainterBookingResource\Pages;
use App\Models\PainterBooking;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PainterBookingResource extends Resource
{
    protected static ?string $model = PainterBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Painter Bookings';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('booking_number')->disabled(),
            Forms\Components\Select::make('customer_id')
                ->relationship('customer', 'name', fn ($query) => $query->where('role', UserRole::Customer))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('painter_id')
                ->label('Painter')
                ->options(User::query()->where('role', UserRole::Painter)->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\DatePicker::make('booking_date')->required(),
            Forms\Components\TimePicker::make('booking_time')->required(),
            Forms\Components\Textarea::make('address')->required()->columnSpanFull(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
            Forms\Components\Textarea::make('completion_notes')->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options(collect(BookingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_number')->searchable(),
                Tables\Columns\TextColumn::make('customer.name'),
                Tables\Columns\TextColumn::make('painter.name'),
                Tables\Columns\TextColumn::make('booking_date')->date(),
                Tables\Columns\TextColumn::make('booking_time'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(BookingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPainterBookings::route('/'),
            'edit' => Pages\EditPainterBooking::route('/{record}/edit'),
        ];
    }
}
