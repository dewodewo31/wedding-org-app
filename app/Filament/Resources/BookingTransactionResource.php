<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use App\Models\BookingTransaction;
use App\Models\WeddingPackage;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Products & Price')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('wedding_package_id')
                                        ->relationship('weddingPackages', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {

                                            $weddingPackage = WeddingPackage::find($state);
                                            $price = $weddingPackage ? $weddingPackage->price : 0;

                                            $set('price', $price);

                                            $tax = 0.11;
                                            $totalTaxAmount = $tax * $price;

                                            $totalAmount = $price + $totalTaxAmount;
                                            $set('total_amount', number_format($totalAmount, 0, '', ''));
                                            $set('total_tax_amount', number_format($totalTaxAmount, 0, '', ''));
                                        }),

                                    Forms\Components\TextInput::make('price')
                                        ->required()
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('IDR'),

                                    Forms\Components\TextInput::make('total_amount')
                                        ->required()
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('IDR'),

                                    Forms\Components\TextInput::make('total_tax_amount')
                                        ->required()
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('IDR'),

                                    Forms\Components\DatePicker::make('started_at')
                                        ->required(),
                                ]),
                        ]),
                    Forms\Components\Wizard\Step::make('Customers Informations')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('phone')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('email')
                                        ->required()
                                        ->maxLength(255),

                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Payment Informations')
                        ->schema([
                            Forms\Components\TextInput::make('booking_trx_id')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\ToggleButtons::make('is_paid')
                                ->label('Apakah Sudah Membayar')
                                ->required()
                                ->boolean()
                                ->grouped()
                                ->icons([
                                    true => 'heroicon-o-check',
                                    false => 'heroicon-o-clock'
                                ]),

                            Forms\Components\FileUpload::make('proof')
                                ->required()
                                ->image(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->columns(1)
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('weddingPackages.thumbnail'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->searchable()
                    ->label('Invoice Code'),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-clock')
                    ->label('Payment Verifications'),

            ])
            ->filters([
                SelectFilter::make('wedding_package_id')
                    ->label('Wedding Package')
                    ->relationship('weddingPackages', 'name'),

            ])
            ->actions([
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
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }
}
