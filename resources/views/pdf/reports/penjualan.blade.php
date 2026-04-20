<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan | Executive Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 32px 40px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #E8EDF2 0%, #DAE2EA 100%);
            color: #1A2C3E;
            line-height: 1.5;
        }

        /* Master container premium */
        .report-master {
            max-width: 1300px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            backdrop-filter: blur(0px);
            transition: all 0.2s;
        }

        /* inner content padding */
        .report-content {
            padding: 36px 40px 48px 40px;
        }

        /* ========= HEADER ELEGAN ========= */
        .premium-header {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
        }

        .logo-wrapper {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        .logo-wrapper img {
            max-width: 160px;
            max-height: 75px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.05));
        }

        .title-main {
            font-size: 2.4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #1E4A76 0%, #2C628F 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .title-main span {
            font-size: 1rem;
            font-weight: 500;
            background: #F0F4F9;
            color: #2C6B9E;
            padding: 6px 20px;
            border-radius: 60px;
            display: inline-block;
            margin-top: 12px;
            letter-spacing: normal;
        }

        .header-meta {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 16px;
            font-size: 0.75rem;
            color: #5F7D9C;
            border-top: 1px solid #E9EDF2;
            padding-top: 18px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        /* ========= ORDER CARD PREMIUM ========= */
        .order-luxury-card {
            background: #FFFFFF;
            border-radius: 28px;
            margin-bottom: 32px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #EFF3F9;
            overflow: hidden;
        }

        .order-luxury-card:hover {
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.1);
        }

        .card-header-gradient {
            background: linear-gradient(98deg, #F9FCFE 0%, #F5F9FF 100%);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            border-bottom: 1px solid #E9F0F5;
        }

        .trans-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #FFFFFF;
            padding: 8px 20px;
            border-radius: 60px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            font-weight: 700;
            font-size: 0.85rem;
            color: #1F5E8C;
        }

        .payment-badge-modern {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #E9F4EC;
            padding: 8px 20px;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.75rem;
            color: #1D6F3F;
        }

        /* tabel premium */
        .luxury-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .luxury-table th {
            text-align: center;
            padding: 14px 12px;
            background: #FBFDFF;
            color: #2A577B;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #E9EFF5;
        }

        .luxury-table td {
            padding: 14px 10px;
            text-align: center;
            border-bottom: 1px solid #F1F5F9;
            color: #234十五;
            font-weight: 500;
        }

        .luxury-table td:first-child {
            text-align: left;
            font-weight: 600;
            color: #1C3F5C;
            padding-left: 20px;
        }

        .row-subtotal td {
            background: #FEFAF2;
            font-weight: 800;
            border-top: 1px solid #FFE6C2;
            font-size: 0.85rem;
        }

        .row-subtotal td:first-child {
            text-align: right;
            font-weight: 800;
            color: #C56A1A;
        }

        .row-subtotal td:nth-child(5), .row-subtotal td:nth-child(6) {
            background: #FFF5E6;
            font-weight: 800;
        }

        /* ========= GRAND TOTAL ELEGAN ========= */
        .grand-summary {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            gap: 28px;
            flex-wrap: wrap;
            border-top: 2px solid #E9F0F6;
            padding-top: 36px;
        }

        .glass-card {
            background: linear-gradient(145deg, #FFFFFF 0%, #FCFDFF 100%);
            border-radius: 32px;
            padding: 20px 36px;
            min-width: 280px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.05);
            border: 1px solid #EAF0F8;
            transition: all 0.2s;
        }

        .glass-card .meta-label {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #6F8EAC;
            margin-bottom: 12px;
        }

        .glass-card .big-number {
            font-size: 2.3rem;
            font-weight: 800;
            color: #1C5A86;
            letter-spacing: -0.02em;
        }

        .glass-card.profit-card .big-number {
            color: #228B4C;
        }

        .glass-card.profit-card {
            border-left: 4px solid #2A9D5E;
        }

        .glass-card.revenue-card {
            border-left: 4px solid #2C6B9E;
        }

        /* footer premium */
        .footer-premium {
            margin-top: 48px;
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #EDF2F7;
            font-size: 0.7rem;
            color: #7E96B2;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        /* decorative ribbon */
        .ribbon {
            background: #F0F6FE;
            height: 6px;
            width: 100%;
        }

        /* responsive */
        @media (max-width: 780px) {
            body {
                padding: 16px;
            }
            .report-content {
                padding: 24px;
            }
            .title-main {
                font-size: 1.8rem;
            }
            .card-header-gradient {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            .grand-summary {
                justify-content: center;
            }
            .glass-card {
                padding: 16px 24px;
                min-width: 220px;
            }
            .luxury-table th, .luxury-table td {
                padding: 10px 6px;
                font-size: 0.7rem;
            }
        }

        /* custom helper */
        .text-rupiah {
            font-weight: 700;
        }
        .icon-em {
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
<div class="report-master">
    <div class="ribbon"></div>
    <div class="report-content">
        <!-- HEADER MEWAH -->
        <div class="premium-header">
            <div class="logo-wrapper">
                @if(isset($logo) && $logo)
                    <img src="{{ storage_path('app/public/' . $logo) }}" alt="Corporate Identity">
                @else
                    <div style="width: 140px; height: 55px; background: linear-gradient(145deg, #EFF3F8, #E2E9F0); border-radius: 60px;"></div>
                @endif
            </div>
            <div class="title-main">
                LAPORAN PENJUALAN PREMIUM<br>
                <span>{{ '(' . $fileName . ')' }}</span>
            </div>
            <div class="header-meta">
                <span>📅 Periode: Transaksi Aktif</span>
                <span>⚡ Sistem Terintegrasi</span>
                <span>🔒 Validasi Digital</span>
            </div>
        </div>

        <main>
            <?php $total_Order_amount = 0 ?>
            <?php $total_Profit_amount = 0 ?>

            @foreach($data as $order)
            <div class="order-luxury-card">
                <div class="card-header-gradient">
                    <div class="trans-badge">
                        <span class="icon-em">📄</span> No. Transaksi: <strong>{{ $order->transaction_number }}</strong>
                    </div>
                    <div class="payment-badge-modern">
                        <span class="icon-em">💳</span> {{ $order->paymentMethod->name ?? 'TUNAI' }}
                    </div>
                </div>

                <table class="luxury-table">
                    <thead>
                        <tr>
                            <th style="text-align:left; width:36%">Nama Produk</th>
                            <th style="width:15%">Harga Modal</th>
                            <th style="width:15%">Harga Jual</th>
                            <th style="width:8%">Qty</th>
                            <th style="width:18%">Total Bayar</th>
                            <th style="width:18%">Total Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total_profit_amount = 0 ?>
                        @foreach($order->transactionItems as $item)
                        <tr>
                            <td style="font-weight: 600;">{{ $item->product->name ?? 'Produk unggulan' }}</td>
                            <td>Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td><span style="background:#EFF3F8; padding:4px 10px; border-radius:40px;">{{ $item->quantity }}</span></td>
                            <td class="text-rupiah">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                            <td class="text-rupiah" style="color:#248F50;">Rp {{ number_format($item->total_profit, 0, ',', '.') }}</td>
                        </tr>
                        <?php $total_profit_amount += $item->total_profit ?>
                        @endforeach
                        <tr class="row-subtotal">
                            <td colspan="4" style="text-align:right; font-weight:800;">Subtotal Transaksi →</td>
                            <td style="font-weight:800;">Rp {{ number_format( $order->total, 0, ',', '.') }}</td>
                            <td style="font-weight:800;">Rp {{ number_format( $total_profit_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php $total_Order_amount += $order->total ?>
            <?php $total_Profit_amount += $total_profit_amount ?>
            @endforeach

            <!-- GRAND TOTAL ELEGAN -->
            <div class="grand-summary">
                <div class="glass-card revenue-card">
                    <div class="meta-label">💎 TOTAL PENDAPATAN KOTOR</div>
                    <div class="big-number">Rp {{ number_format( $total_Order_amount, 0, ',', '.') }}</div>
                    <div style="font-size: 9px; margin-top: 10px; color:#5B7C9E;">seluruh transaksi tercatat</div>
                </div>
                <div class="glass-card profit-card">
                    <div class="meta-label">📈 TOTAL KEUNTUNGAN BERSIH</div>
                    <div class="big-number">Rp {{ number_format( $total_Profit_amount, 0, ',', '.') }}</div>
                    <div style="font-size: 9px; margin-top: 10px; color:#2A7F4B;">margin profit keseluruhan</div>
                </div>
            </div>
        </main>

        <div class="footer-premium">
            <span>© Laporan otomatis · Tidak memerlukan tanda tangan basah</span>
            <span>🕘 Dicetak: {{ date('d/m/Y H:i:s') }}</span>
            <span>🔐 Laporan valid & terenkripsi</span>
        </div>
    </div>
</div>
</body>

</html>