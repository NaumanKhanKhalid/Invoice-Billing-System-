<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice {{ $supply->invoice_number }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1e293b; background: #f1f5f9; }

    .print-bar { position: fixed; top: 0; left: 0; right: 0; background: #0f172a; color: #fff; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
    .print-bar-left { display: flex; align-items: center; gap: 12px; }
    .print-bar a { color: #94a3b8; text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 6px; }
    .print-bar a:hover { color: #fff; }
    .print-bar-title { color: #e2e8f0; font-size: 13px; font-weight: 500; }
    .print-btn { background: #16a34a; color: #fff; border: none; padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
    .print-btn:hover { background: #15803d; }

    .page-wrap { max-width: 800px; margin: 70px auto 40px; padding: 0 20px; }

    .invoice-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; }

    /* Top accent bar */
    .accent-bar { height: 6px; background: linear-gradient(90deg, #15803d, #22c55e); }

    .invoice-body { padding: 40px 48px; }

    /* Header */
    .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 36px; }
    .brand { }
    .brand-name { font-size: 26px; font-weight: 800; color: #15803d; letter-spacing: -0.5px; }
    .brand-tagline { font-size: 12px; color: #94a3b8; margin-top: 4px; }
    .brand-contact { font-size: 12px; color: #64748b; margin-top: 2px; }

    .inv-meta { text-align: right; }
    .inv-label { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: 2px; }
    .inv-number { font-size: 15px; font-weight: 700; color: #16a34a; margin-top: 6px; }
    .inv-dates { font-size: 12px; color: #64748b; margin-top: 4px; line-height: 1.8; }

    /* Divider */
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 0 0 28px; }

    /* Bill / Order info */
    .info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px; }
    .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; }
    .info-box-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 10px; }
    .info-box-name { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .info-box-detail { font-size: 12px; color: #64748b; line-height: 1.7; }
    .info-row-item { display: flex; justify-content: space-between; font-size: 12px; padding: 3px 0; }
    .info-row-item .key { color: #94a3b8; }
    .info-row-item .val { font-weight: 600; color: #1e293b; }

    /* Status badge */
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .badge-paid { background: #dcfce7; color: #15803d; }
    .badge-partial { background: #fef9c3; color: #854d0e; }
    .badge-unpaid { background: #fee2e2; color: #991b1b; }

    /* Items table */
    .table-wrap { margin-bottom: 24px; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; }
    table { width: 100%; border-collapse: collapse; }
    thead { background: #0f172a; }
    thead th { padding: 12px 16px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; text-align: left; }
    thead th:not(:first-child) { text-align: right; }
    tbody td { padding: 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
    tbody td:not(:first-child) { text-align: right; }
    tbody tr:last-child td { border-bottom: none; }
    .item-name { font-weight: 700; color: #0f172a; font-size: 14px; }
    .item-note { font-size: 11px; color: #94a3b8; margin-top: 3px; }

    /* Totals */
    .totals-section { display: flex; justify-content: flex-end; margin-bottom: 28px; }
    .totals-box { width: 280px; }
    .totals-line { display: flex; justify-content: space-between; padding: 7px 0; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
    .totals-line .tl-label { color: #64748b; }
    .totals-line .tl-val { font-weight: 600; color: #1e293b; }
    .totals-line.paid-line .tl-val { color: #16a34a; }
    .totals-final { display: flex; justify-content: space-between; padding: 12px 16px; background: #0f172a; border-radius: 10px; margin-top: 8px; }
    .totals-final .tl-label { color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
    .totals-final .tl-val { color: #fff; font-size: 18px; font-weight: 800; }
    .totals-final.all-paid { background: #15803d; }

    /* Payments */
    .payments-section { margin-bottom: 28px; }
    .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 10px; }
    .payment-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 6px; }
    .payment-item .p-date { font-size: 12px; color: #64748b; }
    .payment-item .p-method { font-size: 10px; font-weight: 700; background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; }
    .payment-item .p-amount { font-size: 13px; font-weight: 700; color: #15803d; }
    .payment-item .p-note { font-size: 11px; color: #94a3b8; }

    /* Footer */
    .inv-footer { border-top: 1px solid #e2e8f0; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; }
    .footer-note { font-size: 11px; color: #94a3b8; line-height: 1.7; }
    .footer-brand { font-size: 13px; font-weight: 700; color: #15803d; }

    @media print {
      body { background: #fff; }
      .print-bar { display: none !important; }
      .page-wrap { margin-top: 0; padding: 0; }
      .invoice-card { box-shadow: none; border-radius: 0; }
      .invoice-body { padding: 32px 40px; }
    }
  </style>
</head>
<body>

  <div class="print-bar">
    <div class="print-bar-left">
      <a href="{{ route('supply.show', $supply) }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back
      </a>
      <span class="print-bar-title">{{ $supply->invoice_number }} — {{ $supply->customer?->name }}</span>
    </div>
    <button class="print-btn" onclick="window.print()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      Print / Save PDF
    </button>
  </div>

  <div class="page-wrap">
    <div class="invoice-card">
      <div class="accent-bar"></div>
      <div class="invoice-body">

        {{-- Header --}}
        <div class="inv-header">
          <div class="brand">
            <div class="brand-name">Anwar Chicken Center</div>
            @php $phone = \App\Models\Setting::getValue('phone',''); $address = \App\Models\Setting::getValue('address','Karachi'); @endphp
            <div class="brand-tagline">Fresh Dressed Chicken Supplier</div>
            <div class="brand-contact">{{ $address }}@if($phone) · {{ $phone }}@endif</div>
          </div>
          <div class="inv-meta">
            <div class="inv-label">INVOICE</div>
            <div class="inv-number">{{ $supply->invoice_number }}</div>
            <div class="inv-dates">
              Issue Date: {{ $supply->date->format('d M Y') }}<br>
              @if($supply->due_date)Due Date: {{ $supply->due_date->format('d M Y') }}@endif
            </div>
          </div>
        </div>

        <hr class="divider">

        {{-- Bill To + Order Info --}}
        <div class="info-row">
          <div class="info-box">
            <div class="info-box-label">Bill To</div>
            <div class="info-box-name">{{ $supply->customer?->name ?? '—' }}</div>
            <div class="info-box-detail">
              @if($supply->customer?->phone){{ $supply->customer->phone }}<br>@endif
              @if($supply->customer?->address){{ $supply->customer->address }}<br>@endif
              @if($supply->delivery_address)Delivery: {{ $supply->delivery_address }}@endif
            </div>
          </div>
          <div class="info-box">
            <div class="info-box-label">Order Details</div>
            <div class="info-row-item"><span class="key">Issue Date</span><span class="val">{{ $supply->date->format('d M Y') }}</span></div>
            @if($supply->delivery_date)<div class="info-row-item"><span class="key">Delivery Date</span><span class="val">{{ $supply->delivery_date->format('d M Y') }}</span></div>@endif
            @if($supply->due_date)<div class="info-row-item"><span class="key">Payment Due</span><span class="val">{{ $supply->due_date->format('d M Y') }}</span></div>@endif
            <div class="info-row-item" style="margin-top:6px"><span class="key">Payment Status</span>
              <span class="badge {{ $supply->payment_status === 'paid' ? 'badge-paid' : ($supply->payment_status === 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                {{ ucfirst($supply->payment_status) }}
              </span>
            </div>
          </div>
        </div>

        {{-- Items Table --}}
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Description</th>
                <th>Weight (kg)</th>
                <th>Rate / kg</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <div class="item-name">Dressed Chicken</div>
                  @if($supply->notes)<div class="item-note">{{ $supply->notes }}</div>@endif
                </td>
                <td>{{ formatKg($supply->dressed_weight_kg) }} kg</td>
                <td>PKR {{ formatKg($supply->rate_per_kg) }}</td>
                <td><strong>{{ formatCurrency($supply->total_amount) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>

        {{-- Totals --}}
        <div class="totals-section">
          <div class="totals-box">
            <div class="totals-line">
              <span class="tl-label">Sub Total</span>
              <span class="tl-val">{{ formatCurrency($supply->total_amount) }}</span>
            </div>
            @if($supply->amount_paid > 0)
            <div class="totals-line paid-line">
              <span class="tl-label">Amount Paid</span>
              <span class="tl-val">− {{ formatCurrency($supply->amount_paid) }}</span>
            </div>
            @endif
            <div class="totals-final {{ $supply->amount_due <= 0 ? 'all-paid' : '' }}">
              <span class="tl-label">{{ $supply->amount_due > 0 ? 'Amount Due' : 'Paid in Full' }}</span>
              <span class="tl-val">{{ formatCurrency($supply->amount_due) }}</span>
            </div>
          </div>
        </div>

        {{-- Payment History --}}
        @if($supply->payments->count())
        <div class="payments-section">
          <div class="section-title">Payment History</div>
          @foreach($supply->payments as $p)
          <div class="payment-item">
            <span class="p-date">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</span>
            <span class="p-method">{{ $p->method }}</span>
            <span class="p-note">{{ $p->note ?? '' }}</span>
            <span class="p-amount">{{ formatCurrency($p->amount) }}</span>
          </div>
          @endforeach
        </div>
        @endif

        {{-- Footer --}}
        <div class="inv-footer">
          <div class="footer-note">
            Thank you for your business.<br>
            This is a computer generated invoice.
          </div>
          <div class="footer-brand">Anwar Chicken Center</div>
        </div>

      </div>
    </div>
  </div>

  <script></script>
</body>
</html>
