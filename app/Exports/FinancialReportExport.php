<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FinancialReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $data;
    protected $period;

    public function __construct($data, $period)
    {
        $this->data = $data;
        $this->period = $period;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $rows = collect();

        // Özet Bilgiler
        $rows->push(['ÖZET BİLGİLER', '', '', '']);
        $rows->push(['Toplam Gelir', number_format($this->data['stats']['total_revenue'], 2) . ' ₺', '', '']);
        $rows->push(['Toplam Gider', number_format($this->data['stats']['total_expenses'], 2) . ' ₺', '', '']);
        $rows->push(['Net Kar', number_format($this->data['stats']['net_income'], 2) . ' ₺', '', '']);
        $rows->push(['Toplam Randevu', $this->data['stats']['total_appointments'], '', '']);
        $rows->push(['', '', '', '']);

        // Ödeme Yöntemleri
        $rows->push(['ÖDEME YÖNTEMLERİ', '', '', '']);
        $rows->push(['Yöntem', 'Toplam', 'İşlem Sayısı', '']);
        foreach ($this->data['paymentMethods'] as $method) {
            $methodName = $this->getPaymentMethodLabel($method['method']);
            $rows->push([
                $methodName,
                number_format($method['total'], 2) . ' ₺',
                $method['count'],
                ''
            ]);
        }
        $rows->push(['', '', '', '']);

        // Gider Kategorileri
        $rows->push(['GİDER KATEGORİLERİ', '', '', '']);
        $rows->push(['Kategori', 'Toplam', 'Gider Sayısı', '']);
        foreach ($this->data['expensesByCategory'] as $category) {
            $categoryName = $this->getCategoryLabel($category['category']);
            $rows->push([
                $categoryName,
                number_format($category['total'], 2) . ' ₺',
                $category['count'],
                ''
            ]);
        }
        $rows->push(['', '', '', '']);

        // Günlük Gelirler
        $rows->push(['GÜNLÜK GELİRLER', '', '', '']);
        $rows->push(['Tarih', 'Gelir', 'Randevu Sayısı', '']);
        foreach ($this->data['dailyRevenue'] as $day) {
            $rows->push([
                $day['date'],
                number_format($day['total'], 2) . ' ₺',
                $day['count'],
                ''
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            ['MALİ RAPOR - ' . $this->period],
            ['Tarih: ' . now()->format('d.m.Y H:i')],
            ['', '', '', ''],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Mali Rapor';
    }

    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'cash' => 'Nakit',
            'credit_card' => 'Kredi Kartı',
            'debit_card' => 'Banka Kartı',
            'online_payment' => 'Online Ödeme',
        ];
        return $labels[$method] ?? $method;
    }

    private function getCategoryLabel($category)
    {
        $labels = [
            'personel' => 'Personel Gideri',
            'kira' => 'Kira Gideri',
            'fatura' => 'Fatura',
            'diger' => 'Diğer Giderler',
        ];
        return $labels[$category] ?? $category;
    }
}
