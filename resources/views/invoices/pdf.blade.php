<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
  .container { padding: 40px; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
  .logo-area h1 { font-size: 22px; font-weight: 700; color: #0f172a; }
  .logo-area p { color: #64748b; font-size: 11px; margin-top: 2px; }
  .invoice-meta { text-align: right; }
  .invoice-meta .invoice-number { font-size: 18px; font-weight: 700; color: #6366f1; }
  .invoice-meta p { color: #64748b; font-size: 11px; margin-top: 3px; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
  .badge-paid { background: #dcfce7; color: #16a34a; }
  .badge-sent { background: #dbeafe; color: #2563eb; }
  .badge-draft { background: #f1f5f9; color: #64748b; }
  .badge-overdue { background: #fee2e2; color: #dc2626; }
  .parties { display: flex; justify-content: space-between; margin-bottom: 32px; }
  .party h3 { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 8px; }
  .party p { font-size: 12px; color: #334155; margin-bottom: 3px; }
  .party .name { font-size: 14px; font-weight: 600; color: #0f172a; }
  .dates-row { display: flex; gap: 24px; margin-bottom: 32px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
  .date-item p:first-child { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; }
  .date-item p:last-child { font-size: 13px; font-weight: 600; color: #0f172a; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  table thead tr { background: #0f172a; }
  table thead th { padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }
  table thead th:last-child { text-align: right; }
  table tbody tr { border-bottom: 1px solid #f1f5f9; }
  table tbody td { padding: 10px 12px; font-size: 12px; color: #334155; }
  table tbody td:last-child { text-align: right; font-weight: 600; }
  table tbody tr:nth-child(even) { background: #f8fafc; }
  .totals { display: flex; justify-content: flex-end; margin-bottom: 32px; }
  .totals-box { width: 240px; }
  .totals-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 12px; color: #64748b; }
  .totals-row.total-line { border-top: 2px solid #e2e8f0; margin-top: 6px; padding-top: 10px; font-size: 14px; font-weight: 700; color: #0f172a; }
  .totals-row.due-line { font-size: 14px; font-weight: 700; color: #dc2626; }
  .notes-section { margin-top: 20px; display: flex; gap: 24px; }
  .note-box { flex: 1; padding: 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
  .note-box h4 { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 6px; }
  .note-box p { font-size: 11px; color: #475569; line-height: 1.6; }
  .footer { text-align: center; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; }
</style>
</head>
<body>
<div class="container">
  <!-- Header -->
  <div class="header">
    <div class="logo-area">
      <h1>InvoicePro</h1>
      <p>Professional Billing System</p>
      <p>Karachi, Pakistan</p>
    </div>
    <div class="invoice-meta">
      <div class="invoice-number">{{ $invoice->invoice_number }}</div>
      <p>
        <span class="badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
      </p>
    </div>
  </div>

  <!-- Parties -->
  <div class="parties">
    <div class="party">
      <h3>From</h3>
      <p class="name">InvoicePro Ltd.</p>
      <p>contact@invoicepro.pk</p>
      <p>+92 21 1234567</p>
      <p>Karachi, Pakistan</p>
    </div>
    <div class="party" style="text-align:right;">
      <h3>Bill To</h3>
      <p class="name">{{ $invoice->client->name }}</p>
      @if($invoice->client->company_name)
      <p>{{ $invoice->client->company_name }}</p>
      @endif
      <p>{{ $invoice->client->email }}</p>
      @if($invoice->client->phone)
      <p>{{ $invoice->client->phone }}</p>
      @endif
      @if($invoice->client->address)
      <p>{{ $invoice->client->address }}</p>
      @endif
    </div>
  </div>

  <!-- Dates -->
  <div class="dates-row">
    <div class="date-item">
      <p>Issue Date</p>
      <p>{{ $invoice->issue_date->format('d M Y') }}</p>
    </div>
    <div class="date-item">
      <p>Due Date</p>
      <p>{{ $invoice->due_date->format('d M Y') }}</p>
    </div>
    <div class="date-item">
      <p>Total Amount</p>
      <p>PKR {{ number_format($invoice->total, 2) }}</p>
    </div>
    <div class="date-item">
      <p>Amount Due</p>
      <p>PKR {{ number_format($invoice->amount_due, 2) }}</p>
    </div>
  </div>

  <!-- Items table -->
  <table>
    <thead>
      <tr>
        <th style="width:40%">Description</th>
        <th style="width:10%; text-align:right;">Qty</th>
        <th style="width:18%; text-align:right;">Unit Price</th>
        <th style="width:12%; text-align:right;">Tax %</th>
        <th style="width:20%">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $item)
      <tr>
        <td>{{ $item->description }}</td>
        <td style="text-align:right;">{{ $item->quantity }}</td>
        <td style="text-align:right;">PKR {{ number_format($item->unit_price, 2) }}</td>
        <td style="text-align:right;">{{ $item->tax_rate }}%</td>
        <td>PKR {{ number_format($item->amount, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals">
    <div class="totals-box">
      <div class="totals-row"><span>Subtotal</span><span>PKR {{ number_format($invoice->subtotal, 2) }}</span></div>
      <div class="totals-row"><span>Tax</span><span>PKR {{ number_format($invoice->tax_amount, 2) }}</span></div>
      @if($invoice->discount_amount > 0)
      <div class="totals-row"><span>Discount</span><span>-PKR {{ number_format($invoice->discount_amount, 2) }}</span></div>
      @endif
      <div class="totals-row total-line"><span>Total</span><span>PKR {{ number_format($invoice->total, 2) }}</span></div>
      @if($invoice->amount_paid > 0)
      <div class="totals-row"><span>Paid</span><span>PKR {{ number_format($invoice->amount_paid, 2) }}</span></div>
      <div class="totals-row due-line"><span>Amount Due</span><span>PKR {{ number_format($invoice->amount_due, 2) }}</span></div>
      @endif
    </div>
  </div>

  <!-- Notes / Terms -->
  @if($invoice->notes || $invoice->terms)
  <div class="notes-section">
    @if($invoice->notes)
    <div class="note-box">
      <h4>Notes</h4>
      <p>{{ $invoice->notes }}</p>
    </div>
    @endif
    @if($invoice->terms)
    <div class="note-box">
      <h4>Terms & Conditions</h4>
      <p>{{ $invoice->terms }}</p>
    </div>
    @endif
  </div>
  @endif

  <div class="footer">
    <p>Thank you for your business! — Generated by InvoicePro</p>
  </div>
</div>
</body>
</html>
