<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TransactionsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $tenantId;

    public function __construct(string $startDate, string $endDate, int $tenantId)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->tenantId  = $tenantId;
    }

    public function collection()
    {
        return Transaction::with(['user', 'items'])
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [
                $this->startDate . ' 00:00:00',
                $this->endDate   . ' 23:59:59',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Invoice',
            'Tanggal',
            'Kasir',
            'Metode Bayar',
            'Produk',
            'Subtotal',
            'Diskon',
            'Total',
            'Bayar',
            'Kembalian',
        ];
    }

    public function map($transaction): array
    {
        $produkList = $transaction->items->map(function ($item) {
            return "{$item->product_name} (x{$item->quantity})";
        })->implode(', ');

        return [
            $transaction->invoice_no,
            $transaction->created_at->format('d/m/Y H:i'),
            $transaction->user->name,
            strtoupper($transaction->payment_method),
            $produkList,
            $transaction->subtotal,
            $transaction->discount,
            $transaction->total,
            $transaction->paid_amount,
            $transaction->change_amount,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F6E56'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }
}
