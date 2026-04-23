<?php

namespace App\Observers;

use App\Models\CashFlow;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportObserver
{
    /**
     * Handle the Report "creating" event.
     */
    public function creating(Report $report): void
    {
        $setting = Setting::first();
        $logo = $setting?->logo;

        $today = now()->format('Ymd');
        $countToday = Report::whereDate('created_at', today())->count() + 1;

        $fileName = 'LAPORAN-' . $today . '-' . str_pad($countToday, 2, '0', STR_PAD_LEFT) . '.pdf';
        $path = 'reports/' . $fileName;

        if ($report->report_type == 'inflow') {
            // Ambil data Inflow sesuai start_date dan end_date
            $data = CashFlow::query()->where('type', 'income')
                ->when($report->start_date, fn($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                ->when($report->end_date, fn($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                ->get();

            // Generate PDF
            $pdf = Pdf::loadView('pdf.reports.pemasukan', [
                'fileName' => $fileName,
                'data' => $data,
                'logo' => $logo,
            ])->setPaper('a4', 'portrait');
        } elseif ($report->report_type == 'outflow') {
            // Ambil data Outflow sesuai start_date dan end_date
            $data = CashFlow::query()->where('type', 'expense')
                ->when($report->start_date, fn($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                ->when($report->end_date, fn($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                ->get();

            // Generate PDF
            $pdf = Pdf::loadView('pdf.reports.pengeluaran', [
                'fileName' => $fileName,
                'data' => $data,
                'logo' => $logo,
            ])->setPaper('a4', 'portrait');
        } else {
            // Ambil data Outflow sesuai start_date dan end_date
            $data = Transaction::query()
                ->when($report->start_date, fn($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                ->when($report->end_date, fn($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                ->get();

            // Generate PDF
            $pdf = Pdf::loadView('pdf.reports.penjualan', [
                'fileName' => $fileName,
                'data' => $data,
                'logo' => $logo,
            ])->setPaper('a4', 'portrait');
        }

        // Pastikan folder 'storage/app/public/reports' ada
        $pathDirectory = storage_path('app/public/reports');
        if (!file_exists($pathDirectory)) {
            mkdir($pathDirectory, 0755, true);
        }

        Storage::disk('public')->makeDirectory('reports');
        $pdf->save(Storage::disk('public')->path($path));

        $report->name = $fileName;
        $report->path_file = $path;
    }

    /**
     * Handle the Report "update" event.
     */
    public function updated(Report $report): void
    {
        $setting = Setting::first();
        $logo = $setting?->logo;

        $path = 'reports/' . $report->name;

        if ($report->report_type == 'inflow') {
            $data = CashFlow::query()->where('type', 'income')
                ->when($report->start_date, fn($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                ->when($report->end_date, fn($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                ->get();

            $pdf = Pdf::loadView('pdf.reports.pemasukan', [
                'fileName' => $report->name,
                'data' => $data,
                'logo' => $logo,
            ])->setPaper('a4', 'portrait');
        } elseif ($report->report_type == 'outflow') {
            $data = CashFlow::query()->where('type', 'expense')
                ->when($report->start_date, fn($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                ->when($report->end_date, fn($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                ->get();

            $pdf = Pdf::loadView('pdf.reports.pengeluaran', [
                'fileName' => $report->name,
                'data' => $data,
                'logo' => $logo,
            ])->setPaper('a4', 'portrait');
        } else {
            $data = Transaction::query()
                ->when($report->start_date, fn($q) => $q->whereDate('updated_at', '>=', $report->start_date))
                ->when($report->end_date, fn($q) => $q->whereDate('updated_at', '<=', $report->end_date))
                ->get();

            $pdf = Pdf::loadView('pdf.reports.penjualan', [
                'fileName' => $report->name,
                'data' => $data,
                'logo' => $logo,
            ])->setPaper('a4', 'portrait');
        }

        Storage::disk('public')->makeDirectory('reports');
        $pdf->save(Storage::disk('public')->path($path));
    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        // Misal file PDF disimpan di storage/app/public/orders-pdf/{order_number}.pdf
        $pdfPath = 'public/reports/' . $report->name;

        if (Storage::exists($pdfPath)) {
            Storage::delete($pdfPath);
        }
    }
}
