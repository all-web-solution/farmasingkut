<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Inventory;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\InvoiceStatusService;
use App\Services\InventoryLabelService;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InventoryResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class InventoryResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete_any',
        ];
    }

    protected static ?string $model = Inventory::class;


    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationLabel = 'Manajemen Inventori';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Manajemen Produk';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\ToggleButtons::make('type')
                            ->label('Tipe Stok')
                            ->options(InventoryLabelService::getTypes())
                            ->colors([
                                'in' => 'success',
                                'out' => 'danger',
                                'adjustment' => 'info',
                            ])
                            ->default('in')
                            ->grouped()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('source', null);
                                $set('document_number', null);
                                $set('invoice_date', null);
                                $set('invoice_due_date', null);
                                $set('invoice_status', null);
                            }),
                        Forms\Components\Select::make('source')
                            ->label('Sumber')
                            ->required()
                            ->live()
                            ->options(fn(Get $get) => InventoryLabelService::getSourceOptionsByType($get('type')))
                            ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                if ($get('type') !== 'in' || $state !== 'purchase_stock') {
                                    $set('document_number', null);
                                    $set('invoice_date', null);
                                    $set('invoice_due_date', null);
                                    $set('invoice_status', null);
                                }
                            }),
                        Forms\Components\TextInput::make('total')
                            ->label('Total Modal')
                            ->prefix('Rp ')
                            ->required()
                            ->numeric()
                            ->readOnly(),
                    ])->columns(3),
                Forms\Components\Section::make('Faktur Pembelian')
                    ->description('Isi informasi faktur untuk stok masuk dari pembelian/penambahan stok.')
                    ->visible(fn(Get $get): bool => $get('type') === 'in' && $get('source') === 'purchase_stock')
                    ->schema([
                        Forms\Components\TextInput::make('document_number')
                            ->label('No. Dokumen / Faktur')
                            ->placeholder('Contoh: INV-2026-001')
                            ->maxLength(255)
                            ->helperText('Nomor invoice, nomor faktur, atau nomor dokumen dari supplier.')
                            ->columnSpan([
                                'default' => 12,
                                'md' => 3,
                            ]),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('Tanggal Faktur')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan([
                                'default' => 12,
                                'md' => 3,
                            ]),

                        Forms\Components\DatePicker::make('invoice_due_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(fn(Get $get) => $get('invoice_date'))
                            ->helperText('Akan digunakan untuk notifikasi H-7 dan lewat jatuh tempo.')
                            ->columnSpan([
                                'default' => 12,
                                'md' => 3,
                            ]),

                        Forms\Components\Select::make('invoice_status')
                            ->label('Status Faktur')
                            ->options(InvoiceStatusService::paymentStatusOptions())
                            ->default(InvoiceStatusService::STATUS_UNPAID)
                            ->native(false)
                            ->helperText('Ubah menjadi Lunas agar faktur tidak masuk notifikasi jatuh tempo.')
                            ->columnSpan([
                                'default' => 12,
                                'md' => 3,
                            ]),

                        Forms\Components\Placeholder::make('invoice_status_preview')
                            ->label('Status Jatuh Tempo')
                            ->content(function (Get $get): string {
                                $dueDate = $get('invoice_due_date');
                                $paymentStatus = $get('invoice_status');

                                if ($paymentStatus === InvoiceStatusService::STATUS_PAID) {
                                    return 'Lunas';
                                }

                                if ($paymentStatus === InvoiceStatusService::STATUS_CANCELLED) {
                                    return 'Dibatalkan';
                                }

                                if (!$dueDate) {
                                    return 'Belum ada tanggal jatuh tempo';
                                }

                                $days = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($dueDate), false);

                                return match (true) {
                                    $days < 0 => 'Lewat ' . abs($days) . ' hari',
                                    $days === 0 => 'Jatuh tempo hari ini',
                                    $days <= 7 => 'Mendekati jatuh tempo, sisa ' . $days . ' hari',
                                    default => 'Aman, sisa ' . $days . ' hari',
                                };
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(12),
                Forms\Components\Section::make('Pemilihan Produk')->schema([
                    self::getItemsRepeater(),
                ]),
                Forms\Components\Section::make('Catatan')->schema([
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('No.Referensi')
                    ->weight('semibold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'adjustment' => 'Penyesuaian',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'in' => 'heroicon-o-arrow-down-circle',
                        'out' => 'heroicon-o-arrow-up-circle',
                        'adjustment' => 'heroicon-o-arrow-path-rounded-square',

                    })
                    ->color(fn(string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'adjustment' => 'info',
                    }),
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->formatStateUsing(fn($state, $record) => InventoryLabelService::getSourceLabel($record->type, $state)),
                Tables\Columns\TextColumn::make('document_number')
                    ->label('No. Faktur')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Tgl Faktur')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice_due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice_status')
                    ->label('Pembayaran')
                    ->formatStateUsing(fn(?string $state): string => InvoiceStatusService::paymentStatusLabel($state))
                    ->badge()
                    ->color(fn(?string $state): string => InvoiceStatusService::paymentStatusColor($state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice_due_status')
                    ->label('Status Faktur')
                    ->getStateUsing(fn(Inventory $record): ?string => InvoiceStatusService::dueStatus($record))
                    ->formatStateUsing(fn(?string $state): string => InvoiceStatusService::dueStatusLabel($state))
                    ->description(fn(Inventory $record): ?string => InvoiceStatusService::dueStatusDescription($record))
                    ->badge()
                    ->color(fn(?string $state): string => InvoiceStatusService::dueStatusColor($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->prefix('Rp '),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Stok')
                    ->options(InventoryLabelService::getTypes()),

                Tables\Filters\SelectFilter::make('source')
                    ->label('Sumber')
                    ->options(InventoryLabelService::getSources()),

                Tables\Filters\SelectFilter::make('invoice_status')
                    ->label('Status Pembayaran Faktur')
                    ->options(InvoiceStatusService::paymentStatusOptions()),

                Tables\Filters\Filter::make('due_soon_invoice')
                    ->label('Faktur Mendekati Tempo')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('type', 'in')
                        ->where('source', 'purchase_stock')
                        ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
                        ->whereNotNull('invoice_due_date')
                        ->whereDate('invoice_due_date', '>=', now())
                        ->whereDate('invoice_due_date', '<=', now()->addDays(7))),

                Tables\Filters\Filter::make('overdue_invoice')
                    ->label('Faktur Lewat Tempo')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('type', 'in')
                        ->where('source', 'purchase_stock')
                        ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
                        ->whereNotNull('invoice_due_date')
                        ->whereDate('invoice_due_date', '<', now())),
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

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('inventoryItems')
            ->relationship()
            ->live()
            ->columns([
                'md' => 10,
            ])
            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                self::updateTotalPrice($get, $set);
            })
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->required()
                    ->searchable(['name', 'sku'])
                    ->searchPrompt('Cari nama atau sku produk')
                    ->preload()
                    ->relationship('product', 'name')
                    ->getOptionLabelFromRecordUsing(fn(Product $record) => "{$record->name}-({$record->stock})-{$record->sku}")
                    ->columnSpan([
                        'md' => 4
                    ])
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $product = Product::find($state);
                        $set('stock', $product->stock ?? 0);
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $product = Product::find($state);
                        $set('cost_price', $product->cost_price ?? 0);
                        $set('stock', $product->stock ?? 0);
                        self::updateTotalPrice($get, $set);
                    })
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                Forms\Components\TextInput::make('cost_price')
                    ->label('Harga Modal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp ')
                    ->readOnly()
                    ->columnSpan([
                        'md' => 2
                    ]),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok Saat Ini')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->columnSpan([
                        'md' => 2
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->columnSpan([
                        'md' => 2
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        self::updateTotalPrice($get, $set);
                    }),
            ]);
    }

    protected static function updateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $selectedProducts = collect($get('inventoryItems'))
            ->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        $productIds = $selectedProducts
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values();

        $prices = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('cost_price', 'id');

        $total = $selectedProducts->reduce(function (int $total, array $item) use ($prices): int {
            $productId = $item['product_id'];
            $quantity = (int) ($item['quantity'] ?? 0);
            $costPrice = (int) ($prices[$productId] ?? 0);

            return $total + ($costPrice * $quantity);
        }, 0);

        $set('total', $get('type') !== 'adjustment' ? $total : 0);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
        ];
    }
}
