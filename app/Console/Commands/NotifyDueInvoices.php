<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\User;
use App\Services\InvoiceStatusService;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NotifyDueInvoices extends Command
{
    protected $signature = 'inventories:notify-due-invoices';

    protected $description = 'Kirim notifikasi faktur inventory yang mendekati atau melewati jatuh tempo.';

    public function handle(): int
    {
        $users = $this->resolveNotifiableUsers();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user aktif yang dapat menerima notifikasi.');
            return self::SUCCESS;
        }

        $dueSoonInventories = Inventory::query()
            ->where('type', 'in')
            ->where('source', 'purchase_stock')
            ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
            ->whereNotNull('invoice_due_date')
            ->whereDate('invoice_due_date', '>=', today())
            ->whereDate('invoice_due_date', '<=', today()->addDays(7))
            ->whereNull('h7_notified_at')
            ->get();

        $overdueInventories = Inventory::query()
            ->where('type', 'in')
            ->where('source', 'purchase_stock')
            ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
            ->whereNotNull('invoice_due_date')
            ->whereDate('invoice_due_date', '<', today())
            ->whereNull('overdue_notified_at')
            ->get();

        foreach ($dueSoonInventories as $inventory) {
            Notification::make()
                ->title('Faktur mendekati jatuh tempo')
                ->body($this->buildNotificationBody($inventory))
                ->warning()
                ->persistent()
                ->sendToDatabase($users);

            $inventory->updateQuietly([
                'h7_notified_at' => now(),
            ]);
        }

        foreach ($overdueInventories as $inventory) {
            Notification::make()
                ->title('Faktur lewat jatuh tempo')
                ->body($this->buildNotificationBody($inventory))
                ->danger()
                ->persistent()
                ->sendToDatabase($users);

            $inventory->updateQuietly([
                'overdue_notified_at' => now(),
            ]);
        }

        $this->info("Notifikasi H-7 terkirim: {$dueSoonInventories->count()}");
        $this->info("Notifikasi overdue terkirim: {$overdueInventories->count()}");

        return self::SUCCESS;
    }

    private function resolveNotifiableUsers(): Collection
    {
        $roleUsers = User::query()
            ->active()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', [
                'owner',
                'super_admin',
                'admin',
            ]))
            ->get();

        if ($roleUsers->isNotEmpty()) {
            return $roleUsers;
        }

        return User::query()
            ->active()
            ->limit(5)
            ->get();
    }

    private function buildNotificationBody(Inventory $inventory): string
    {
        $document = $inventory->document_number ?: $inventory->reference_number;
        $dueDate = optional($inventory->invoice_due_date)->format('d/m/Y');

        return "Faktur {$document} jatuh tempo pada {$dueDate}. Segera cek menu Manajemen Inventori.";
    }
}
