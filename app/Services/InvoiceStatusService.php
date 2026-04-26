<?php

namespace App\Services;

use App\Models\Inventory;

final class InvoiceStatusService
{
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public static function paymentStatusOptions(): array
    {
        return [
            self::STATUS_UNPAID => 'Belum Lunas',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function paymentStatusLabel(?string $status): string
    {
        return self::paymentStatusOptions()[$status] ?? '-';
    }

    public static function paymentStatusColor(?string $status): string
    {
        return match ($status) {
            self::STATUS_UNPAID => 'warning',
            self::STATUS_PAID => 'success',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }

    public static function dueStatus(Inventory $inventory): ?string
    {
        if ($inventory->type !== 'in') {
            return null;
        }

        if ($inventory->source !== 'purchase_stock') {
            return null;
        }

        if (! $inventory->invoice_due_date) {
            return null;
        }

        if ($inventory->invoice_status === self::STATUS_PAID) {
            return 'paid';
        }

        if ($inventory->invoice_status === self::STATUS_CANCELLED) {
            return 'cancelled';
        }

        $days = now()->startOfDay()->diffInDays($inventory->invoice_due_date, false);

        return match (true) {
            $days < 0 => 'overdue',
            $days <= 7 => 'due_soon',
            default => 'safe',
        };
    }

    public static function dueStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            'safe' => 'Aman',
            'due_soon' => 'Mendekati Tempo',
            'overdue' => 'Lewat Tempo',
            default => '-',
        };
    }

    public static function dueStatusColor(?string $status): string
    {
        return match ($status) {
            'paid', 'safe' => 'success',
            'due_soon' => 'warning',
            'overdue' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public static function dueStatusDescription(Inventory $inventory): ?string
    {
        if (! $inventory->invoice_due_date) {
            return null;
        }

        if ($inventory->invoice_status === self::STATUS_PAID) {
            return 'Faktur sudah lunas';
        }

        if ($inventory->invoice_status === self::STATUS_CANCELLED) {
            return 'Faktur dibatalkan';
        }

        $days = now()->startOfDay()->diffInDays($inventory->invoice_due_date, false);

        return match (true) {
            $days < 0 => 'Lewat ' . abs($days) . ' hari',
            $days === 0 => 'Jatuh tempo hari ini',
            default => 'Sisa ' . $days . ' hari',
        };
    }
}
