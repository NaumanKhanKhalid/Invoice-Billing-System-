{{-- In-page receipt preview slide-over (opened via 'open-receipt' event) --}}
<div x-data="feeReceiptPreview()" @open-receipt.window="show($event.detail)" x-cloak>
  {{-- overlay --}}
  <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-black/50" @click="close()"></div>

  {{-- panel --}}
  <div x-show="open"
       x-transition:enter="transition transform duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition transform duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
       class="fixed right-0 inset-y-0 z-50 w-full max-w-2xl bg-slate-100 shadow-2xl flex flex-col">
    {{-- header --}}
    <div class="flex items-center justify-between gap-2 px-4 py-3 bg-white border-b border-slate-200 shrink-0">
      <div class="flex items-center gap-2 min-w-0">
        <button @click="close()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">✕</button>
        <h3 class="font-bold text-slate-900">Fee Receipt</h3>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <template x-if="wa">
          <a :href="wa" target="_blank" class="inline-flex items-center gap-1.5 bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-lg text-sm font-medium">
            <i data-lucide="message-circle" class="w-4 h-4"></i><span class="hidden sm:inline">WhatsApp</span>
          </a>
        </template>
        <button @click="saveImage()" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium">
          <i data-lucide="image" class="w-4 h-4"></i><span class="hidden sm:inline">Save</span>
        </button>
        <button @click="printIt()" class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-lg text-sm font-medium">
          <i data-lucide="printer" class="w-4 h-4"></i>Print
        </button>
      </div>
    </div>
    {{-- body --}}
    <div class="flex-1 overflow-y-auto p-4 sm:p-6" x-ref="body">
      <div x-show="loading" class="flex items-center justify-center py-20 text-slate-400 text-sm">
        <svg class="animate-spin w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".2"/><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        Loading…
      </div>
      <div x-show="!loading" x-html="html"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
function feeReceiptPreview() {
  return {
    open: false, loading: false, html: '', wa: '', full: '',
    async show(detail) {
      this.wa = detail.wa || ''; this.full = detail.full || '';
      this.open = true; this.loading = true; this.html = '';
      document.body.style.overflow = 'hidden';
      try {
        const res = await fetch(detail.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        this.html = await res.text();
      } catch (e) {
        this.html = '<p class="text-center text-red-500 py-10">Receipt load nahi hua.</p>';
      }
      this.loading = false;
      this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },
    close() { this.open = false; document.body.style.overflow = ''; },
    printIt() { window.print(); },
    async saveImage() {
      const el = this.$refs.body.querySelector('#receipt');
      if (!el || typeof html2canvas === 'undefined') { window.print(); return; }
      const canvas = await html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
      const link = document.createElement('a');
      link.download = 'fee-receipt.png';
      link.href = canvas.toDataURL('image/png');
      link.click();
    },
  };
}
</script>
