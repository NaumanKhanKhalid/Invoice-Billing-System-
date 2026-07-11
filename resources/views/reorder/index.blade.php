@extends('layouts.app')
@section('title','Reorder List')
@section('content')
<div class="space-y-6" x-data="reorderList()">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900">Reorder List</h1>
      <p class="text-sm text-slate-500">{{ $products->count() }} low stock products</p>
    </div>
    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>Back to Products
    </a>
  </div>

  @if($products->count())
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left w-10">
            <input type="checkbox" checked @change="items.forEach(i => i.checked = $event.target.checked)"
                   class="rounded border-slate-300 text-green-600 focus:ring-green-300">
          </th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Product</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Current Stock</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Alert Level</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Suggested Qty</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <template x-for="(item, idx) in items" :key="item.id">
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
              <input type="checkbox" x-model="item.checked" class="rounded border-slate-300 text-green-600 focus:ring-green-300">
            </td>
            <td class="px-4 py-3">
              <p class="font-medium text-slate-900" x-text="item.name"></p>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700" x-text="item.stock + ' ' + item.unit"></span>
            </td>
            <td class="px-4 py-3 text-center text-sm text-slate-600" x-text="item.alert"></td>
            <td class="px-4 py-3 text-center">
              <input type="number" min="1" x-model.number="item.qty"
                     class="w-24 px-2 py-1.5 text-sm text-center border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3 flex flex-wrap items-center gap-3">
    <div class="relative">
      <i data-lucide="truck" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
      <select x-model="supplier" class="appearance-none pl-8 pr-7 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
        <option value="">Select Supplier</option>
        @foreach($suppliers as $sup)
        <option value="{{ wa_number($sup->phone) }}">{{ $sup->name }} ({{ $sup->phone }})</option>
        @endforeach
      </select>
      <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
    </div>
    <button type="button" @click="sendWhatsApp()" :disabled="!supplier || !checkedItems().length"
            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="message-circle" class="w-4 h-4"></i>Send WhatsApp Order
    </button>
    <button type="button" @click="copyList()" :disabled="!checkedItems().length"
            class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="copy" class="w-4 h-4"></i><span x-text="copied ? 'Copied!' : 'Copy List'"></span>
    </button>
    <div class="ml-auto text-sm text-slate-500"><span x-text="checkedItems().length"></span> items selected</div>
  </div>
  @else
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-12 text-center">
    <i data-lucide="package-check" class="w-10 h-10 text-green-300 mx-auto mb-3"></i>
    <p class="text-slate-500 font-medium">Sab stock theek hai 👍</p>
  </div>
  @endif
</div>

<script>
function reorderList() {
  return {
    supplier: '',
    copied: false,
    items: @json($products->map(fn($p) => [
      'id' => $p->id,
      'name' => $p->name,
      'unit' => $p->unit,
      'stock' => $p->stock_qty,
      'alert' => $p->low_stock_alert,
      'qty' => $p->suggested_qty,
      'checked' => true,
    ])->values()),
    checkedItems() {
      return this.items.filter(i => i.checked);
    },
    buildMessage() {
      const lines = this.checkedItems().map((i, idx) => `${idx + 1}. ${i.name} — ${i.qty} ${i.unit}`);
      return 'Assalam o Alaikum! Order:\n' + lines.join('\n') + '\nJald bhej dein. Shukriya.';
    },
    sendWhatsApp() {
      if (!this.supplier || !this.checkedItems().length) return;
      window.open('https://wa.me/' + this.supplier + '?text=' + encodeURIComponent(this.buildMessage()), '_blank');
    },
    copyList() {
      if (!this.checkedItems().length) return;
      navigator.clipboard.writeText(this.buildMessage()).then(() => {
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
      });
    },
  };
}
</script>
@endsection
