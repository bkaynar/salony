<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Mali Rapor - {{ $period }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4F46E5;
        }
        .header h1 {
            color: #4F46E5;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .summary-card h3 {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        .summary-card.revenue .value {
            color: #10b981;
        }
        .summary-card.expense .value {
            color: #ef4444;
        }
        .summary-card.profit .value {
            color: #3b82f6;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            background: #4F46E5;
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background: #f9fafb;
        }
        table tr:hover {
            background: #f3f4f6;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-yellow {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-gray {
            background: #f3f4f6;
            color: #374151;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MALİ RAPOR</h1>
        <p><strong>Dönem:</strong> {{ $period }}</p>
        <p><strong>Rapor Tarihi:</strong> {{ now()->format('d.m.Y H:i') }}</p>
        @if(isset($salon))
        <p><strong>Salon:</strong> {{ $salon->name }}</p>
        @endif
    </div>

    <!-- Özet Bilgiler -->
    <div class="summary-cards">
        <div class="summary-card revenue">
            <h3>Toplam Gelir</h3>
            <div class="value">{{ number_format($stats['total_revenue'], 2) }} ₺</div>
        </div>
        <div class="summary-card expense">
            <h3>Toplam Gider</h3>
            <div class="value">{{ number_format($stats['total_expenses'], 2) }} ₺</div>
        </div>
        <div class="summary-card profit">
            <h3>Net Kar</h3>
            <div class="value">{{ number_format($stats['net_income'], 2) }} ₺</div>
        </div>
        <div class="summary-card">
            <h3>Toplam Randevu</h3>
            <div class="value">{{ $stats['total_appointments'] }}</div>
        </div>
    </div>

    <!-- Ödeme Yöntemleri -->
    @if(count($paymentMethods) > 0)
    <div class="section">
        <div class="section-title">ÖDEME YÖNTEMLERİ</div>
        <table>
            <thead>
                <tr>
                    <th>Yöntem</th>
                    <th class="text-right">Toplam</th>
                    <th class="text-center">İşlem Sayısı</th>
                    <th class="text-right">Yüzde</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paymentMethods as $method)
                <tr>
                    <td>
                        @switch($method['method'])
                            @case('cash')
                                <span class="badge badge-green">Nakit</span>
                                @break
                            @case('credit_card')
                                <span class="badge badge-green">Kredi Kartı</span>
                                @break
                            @case('debit_card')
                                <span class="badge badge-green">Banka Kartı</span>
                                @break
                            @case('online_payment')
                                <span class="badge badge-green">Online Ödeme</span>
                                @break
                        @endswitch
                    </td>
                    <td class="text-right"><strong>{{ number_format($method['total'], 2) }} ₺</strong></td>
                    <td class="text-center">{{ $method['count'] }}</td>
                    <td class="text-right">{{ number_format(($method['total'] / $stats['total_revenue']) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Gider Kategorileri -->
    @if(count($expensesByCategory) > 0)
    <div class="section">
        <div class="section-title">GİDER KATEGORİLERİ</div>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th class="text-right">Toplam</th>
                    <th class="text-center">Gider Sayısı</th>
                    <th class="text-right">Yüzde</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $category)
                <tr>
                    <td>
                        @switch($category['category'])
                            @case('personel')
                                <span class="badge badge-red">Personel Gideri</span>
                                @break
                            @case('kira')
                                <span class="badge badge-red">Kira Gideri</span>
                                @break
                            @case('fatura')
                                <span class="badge badge-yellow">Fatura</span>
                                @break
                            @case('diger')
                                <span class="badge badge-gray">Diğer Giderler</span>
                                @break
                        @endswitch
                    </td>
                    <td class="text-right"><strong>{{ number_format($category['total'], 2) }} ₺</strong></td>
                    <td class="text-center">{{ $category['count'] }}</td>
                    <td class="text-right">{{ $stats['total_expenses'] > 0 ? number_format(($category['total'] / $stats['total_expenses']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="page-break"></div>

    <!-- Günlük Gelir Detayı -->
    @if(count($dailyRevenue) > 0)
    <div class="section">
        <div class="section-title">GÜNLÜK GELİR DETAYI</div>
        <table>
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th class="text-right">Gelir</th>
                    <th class="text-center">Randevu Sayısı</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyRevenue as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('d.m.Y') }}</td>
                    <td class="text-right"><strong>{{ number_format($day['total'], 2) }} ₺</strong></td>
                    <td class="text-center">{{ $day['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Aylık Trend -->
    @if(count($monthlyRevenue) > 0)
    <div class="section">
        <div class="section-title">AYLIK GELİR TRENDİ</div>
        <table>
            <thead>
                <tr>
                    <th>Ay</th>
                    <th class="text-right">Gelir</th>
                    <th class="text-center">Randevu Sayısı</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyRevenue as $month)
                <tr>
                    <td>{{ $month['month'] }}</td>
                    <td class="text-right"><strong>{{ number_format($month['revenue'], 2) }} ₺</strong></td>
                    <td class="text-center">{{ $month['appointments'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Bu rapor {{ config('app.name') }} tarafından otomatik olarak oluşturulmuştur.</p>
        <p>© {{ date('Y') }} - Tüm hakları saklıdır</p>
    </div>
</body>
</html>
