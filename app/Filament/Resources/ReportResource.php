<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Report;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\ReportResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class ReportResource extends Resource implements HasShieldPermissions
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Menejemen keuangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Laporan')
                    ->schema([
                        Forms\Components\ToggleButtons::make('report_type')
                            ->options([
                                'inflow' => 'Uang Masuk',
                                'outflow' => 'Uang Keluar',
                                'sales' => 'Penjualan'
                            ])
                            ->colors([
                                'inflow' => 'success',
                                'outflow' => 'danger',
                                'sales' => 'info'
                            ])
                            ->default('inflow')
                            ->grouped(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $start = $get('start_date');
                                if ($start && $state) {
                                    $diff = Carbon::parse($start)->diffInDays(Carbon::parse($state));
                                    if ($diff > 30) {
                                        Notification::make()
                                            ->title('Perhatian')
                                            ->body('Rentang tanggal tidak boleh lebih dari 30 hari.')
                                            ->danger()
                                            ->send();
                                        $set('end_date', null); // reset end date jika melebihi 30 hari
                                    }
                                }
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama/Kode Laporan')
                    ->weight('semibold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->label('Tipe Laporan')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'inflow' => 'Uang Masuk',
                        'outflow' => 'Uang Keluar',
                        'sales' => 'Penjualan',
                        default => 'Unknown',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'inflow' => 'heroicon-o-arrow-down-circle',
                        'outflow' => 'heroicon-o-arrow-up-circle',
                        'sales' => 'heroicon-o-arrow-down-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'inflow' => 'success',
                        'outflow' => 'danger',
                        'sales' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Dari Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sampai Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($record) {
                        $start = Carbon::parse($record->start_date);
                        $end = Carbon::parse($record->end_date);

                        if ($start->diffInDays($end) > 30) {
                            Notification::make()
                                ->title('Perhatian')
                                ->body('Rentang tanggal tidak boleh lebih dari 30 hari untuk laporan ini.')
                                ->danger()
                                ->send();
                            return;
                        }

                        return redirect(asset('storage/' . $record->path_file));
                    }),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
        ];
    }
}
