<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice {{ $supply->invoice_number }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1e293b; background: #fff; }

    .page { max-width: 720px; margin: 0 auto; padding: 32px; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid #16a34a; }
    .brand-name { font-size: 22px; font-weight: 800; color: #15803d; }
    .brand-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
    .invoice-title { text-align: right; }
    .invoice-title h2 { font-size: 18px; font-weight: 700; color: #0f172a; }
    .invoice-title .inv-num { font-size: 13px; color: #16a34a; font-weight: 600; margin-top: 2px; }
    .invoice-title .inv-date { font-size: 11px; color: #64748b; margin-top: 2px; }

    /* Info grid */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; }
    .info-box h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 8px; }
    .info-box p { font-size: 13px; color: #0f172a; font-weight: 500; margin-bottom: 3px; }
    .info-box .label { font-size: 11px; color: #94a3b8; font-weight: 400; }

    /* Items table */
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .items-table thead tr { background: #15803d; color: #fff; }
    .items-table th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    .items-table th:last-child, .items-table td:last-child { text-align: right; }
    .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }
    .items-table td { padding: 12px 14px; font-size: 13px; }
    .items-table tfoot tr { border-top: 2px solid #e2e8f0; background: #f8fafc; }
    .items-table tfoot td { padding: 10px 14px; font-weight: 600; }

    /* Totals */
    .totals { margin-left: auto; width: 260px; margin-bottom: 24px; }
    .totals-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .totals-row.total-row { border-top: 2px solid #e2e8f0; border-bottom: none; font-weight: 700; font-size: 15px; padding-top: 10px; margin-top: 4px; }
    .totals-row.due-row { color: #dc2626; font-weight: 700; font-size: 15px; }
    .totals-row.paid-row { color: #16a34a; }

    /* Status badge */
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-paid { background: #dcfce7; color: #15803d; }
    .status-partial { background: #fef9c3; color: #854d0e; }
    .status-unpaid { background: #fee2e2; color: #991b1b; }

    /* Payment history */
    .payments-section { margin-bottom: 24px; }
    .payments-section h3 { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 10px; }
    .payment-row { display: flex; justify-content: space-between; padding: 7px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 4px; font-size: 12px; }
    .payment-row .method { background: #dbeafe; color: #1d4ed8; padding: 1px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }

    /* Footer */
    .footer { border-top: 1px solid #e2e8f0; padding-top: 16px; display: flex; justify-content: space-between; align-items: flex-end; }
    .footer-note { font-size: 11px; color: #94a3b8; }
    .footer-brand { font-size: 12px; font-weight: 700; color: #15803d; }

    /* Print button (hidden when printing) */
    .print-bar { position: fixed; top: 0; left: 0; right: 0; background: #1e293b; color: #fff; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; z-index: 100; }
    .print-bar span { font-size: 13px; }
    .print-btn { background: #16a34a; color: #fff; border: none; padding: 7px 20px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .back-btn { background: transparent; color: #94a3b8; border: 1px solid #475569; padding: 7px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; text-decoration: none; }

    @media print {
      .print-bar { display: none !important; }
      .page { padding: 16px; }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>

  <!-- Print bar (hidden when printing) -->
  <div class="print-bar">
    <a href="{{ route('supply.show', $supply) }}" class="back-btn">← Back</a>
    <span>{{ $supply->invoice_number }} — {{ $supply->customer?->name }}</span>
    <button class="print-btn" onclick="window.print()">🖨️ Print / Save PDF</button>
  <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
  </div>

  <div class="page" style="margin-top: 50px;">

    <!-- Header -->
    <div class="header">
      <div>
        <div class="brand-name">Anwar Chicken Center</div>
        <div class="brand-sub">
          @php $phone = \App\Models\Setting::getValue('phone', ''); $address = \App\Models\Setting::getValue('address', 'Karachi'); @endphp
          {{ $address }}@if($phone) · {{ $phone }}@endif
        </div>
      </div>
      <div class="invoice-title">
        <h2>INVOICE</h2>
        <div class="inv-num">{{ $supply->invoice_number }}</div>
        <div class="inv-date">Date: {{ $supply->date->format('d M Y') }}</div>
        @if($supply->due_date)
        <div class="inv-date">Due: {{ $supply->due_date->format('d M Y') }}</div>
        @endif
      </div>
    </div>

    <!-- Bill To + Order Info -->
    <div class="info-grid">
      <div class="info-box">
        <h3>Bill To</h3>
        <p>{{ $supply->customer?->name ?? '—' }}</p>
        @if($supply->customer?->phone)
        <p class="label">{{ $supply->customer->phone }}</p>
        @endif
        @if($supply->customer?->address)
        <p class="label">{{ $supply->customer->address }}</p>
        @endif
        @if($supply->delivery_address)
        <p class="label" style="margin-top:4px">Delivery: {{ $supply->delivery_address }}</p>
        @endif
      </div>
      <div class="info-box">
        <h3>Order Info</h3>
        <p style="display:flex;justify-content:space-between"><span class="label">Order Date</span> {{ $supply->date->format('d M Y') }}</p>
        @if($supply->delivery_date)
        <p style="display:flex;justify-content:space-between;margin-top:4px"><span class="label">Delivery Date</span> {{ $supply->delivery_date->format('d M Y') }}</p>
        @endif
        @if($supply->due_date)
        <p style="display:flex;justify-content:space-between;margin-top:4px"><span class="label">Payment Due</span> {{ $supply->due_date->format('d M Y') }}</p>
        @endif
        <p style="display:flex;justify-content:space-between;margin-top:8px">
          <span class="label">Status</span>
          <span class="status-badge {{ $supply->payment_status === 'paid' ? 'status-paid' : ($supply->payment_status === 'partial' ? 'status-partial' : 'status-unpaid') }}">
            {{ ucfirst($supply->payment_status) }}
          </span>
        </p>
      </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
      <thead>
        <tr>
          <th>Description</th>
          <th style="text-align:center">Weight (kg)</th>
          <th style="text-align:center">Rate / kg</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>Dressed Chicken</strong>
            @if($supply->notes)<br><span style="font-size:11px;color:#64748b">{{ $supply->notes }}</span>@endif
          </td>
          <td style="text-align:center">{{ formatKg($supply->dressed_weight_kg) }}</td>
          <td style="text-align:center">PKR {{ formatKg($supply->rate_per_kg) }}</td>
          <td>{{ formatCurrency($supply->total_amount) }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
      <div class="totals-row">
        <span style="color:#64748b">Sub Total</span>
        <span>{{ formatCurrency($supply->total_amount) }}</span>
      </div>
      @if($supply->amount_paid > 0)
      <div class="totals-row paid-row">
        <span>Amount Paid</span>
        <span>− {{ formatCurrency($supply->amount_paid) }}</span>
      </div>
      @endif
      <div class="totals-row {{ $supply->amount_due > 0 ? 'due-row' : 'total-row' }}">
        <span>{{ $supply->amount_due > 0 ? 'Amount Due' : 'PAID IN FULL' }}</span>
        <span>{{ formatCurrency($supply->amount_due) }}</span>
      </div>
    </div>

    <!-- Payment History -->
    @if($supply->payments->count())
    <div class="payments-section">
      <h3>Payment History</h3>
      @foreach($supply->payments as $p)
      <div class="payment-row">
        <span>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</span>
        <span class="method">{{ ucfirst($p->method) }}</span>
        <span style="font-weight:600;color:#15803d">{{ formatCurrency($p->amount) }}</span>
        @if($p->note)<span style="color:#94a3b8">{{ $p->note }}</span>@endif
      </div>
      @endforeach
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
      <div class="footer-note">
        Shukriya! Aap ka koi sawaal ho to rabta karein.<br>
        Ye computer generated invoice hai.
      </div>
      <div class="footer-brand">Anwar Chicken Center</div>
    </div>

  </div>

</body>
</html>
