<?php

namespace App\Models;

use App\Services\InvoiceStatusService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'type',
        'source',
        'total',
        'notes',
        'document_number',
        'invoice_date',
        'invoice_due_date',
        'invoice_status',
        'h7_notified_at',
        'overdue_notified_at',
    ];

    protected $casts = [
        'total' => 'integer',
        'invoice_date' => 'date',
        'invoice_due_date' => 'date',
        'h7_notified_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
    ];

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function getInvoiceDueStatusAttribute(): ?string
    {
        return InvoiceStatusService::dueStatus($this);
    }

    public function scopePurchaseStock($query)
    {
        return $query
            ->where('type', 'in')
            ->where('source', 'purchase_stock');
    }

    public function scopeUnpaidInvoice($query)
    {
        return $query
            ->purchaseStock()
            ->where('invoice_status', InvoiceStatusService::STATUS_UNPAID)
            ->whereNotNull('invoice_due_date');
    }
}
