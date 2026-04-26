<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Models\Inventory;
use App\Services\InvoiceStatusService;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn (): int => Inventory::query()->count()),

            'stock_in' => Tab::make('Stok Masuk')
                ->icon('heroicon-o-arrow-down-circle')
                ->badge(fn (): int => Inventory::query()->where('type', 'in')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', 'in')),

            'stock_out' => Tab::make('Stok Keluar')
                ->icon('heroicon-o-arrow-up-circle')
                ->badge(fn (): int => Inventory::query()->where('type', 'out')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', 'out')),

            'adjustment' => Tab::make('Penyesuaian')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->badge(fn (): int => Inventory::query()->where('type', 'adjustment')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', 'adjustment')),

            'invoice_unpaid' => Tab::make('Faktur Belum Lunas')
                ->icon('heroicon-o-document-text')
                ->badge(fn (): int => Inventory::query()
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)),

            'invoice_due_soon' => Tab::make('Mendekati Tempo')
                ->icon('heroicon-o-clock')
                ->badge(fn (): int => Inventory::query()
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
                    ->whereNotNull('invoice_due_date')
                    ->whereDate('invoice_due_date', '>=', today())
                    ->whereDate('invoice_due_date', '<=', today()->addDays(7))
                    ->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
                    ->whereNotNull('invoice_due_date')
                    ->whereDate('invoice_due_date', '>=', today())
                    ->whereDate('invoice_due_date', '<=', today()->addDays(7))),

            'invoice_overdue' => Tab::make('Lewat Tempo')
                ->icon('heroicon-o-exclamation-triangle')
                ->badge(fn (): int => Inventory::query()
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
                    ->whereNotNull('invoice_due_date')
                    ->whereDate('invoice_due_date', '<', today())
                    ->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
                    ->whereNotNull('invoice_due_date')
                    ->whereDate('invoice_due_date', '<', today())),

            'invoice_paid' => Tab::make('Faktur Lunas')
                ->icon('heroicon-o-check-circle')
                ->badge(fn (): int => Inventory::query()
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_PAID)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('type', 'in')
                    ->where('source', 'purchase_stock')
                    ->where('invoice_status', InvoiceStatusService::STATUS_PAID)),
        ];
    }
}
