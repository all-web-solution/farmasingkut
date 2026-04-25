<?php

namespace App\Services;

use App\Models\Transaction;

final class TransactionPaymentService
{
    public function normalizeForCheckout(
        int $grossTotal,
        int $cashReceived,
        int $change,
        bool $isBpjs
    ): array {
        if ($isBpjs) {
            return [
                'total' => 0,
                'cash_received' => 0,
                'change' => 0,
                'is_bpjs' => true,
            ];
        }

        return [
            'total' => max(0, $grossTotal),
            'cash_received' => max(0, $cashReceived),
            'change' => $change,
            'is_bpjs' => false,
        ];
    }

    public function formatTotalForTable(Transaction $transaction): string
    {
        if ($transaction->is_bpjs) {
            return 'BPJS';
        }

        return 'Rp ' . number_format((int) $transaction->total, 0, ',', '.');
    }

    public function totalBadgeColor(Transaction $transaction): ?string
    {
        return $transaction->is_bpjs ? 'info' : null;
    }

    public function totalBadgeIcon(Transaction $transaction): ?string
    {
        return $transaction->is_bpjs ? 'heroicon-o-shield-check' : null;
    }
}
