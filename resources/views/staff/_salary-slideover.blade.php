{{-- In-page salary slip preview slide-over (opened via 'open-salary' event) --}}
<div x-data="salarySlipPreview()" @open-salary.window="show($event.detail)" x-cloak>
  {{-- overlay --}}
  <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-black/50 print:hidden" @click="close()"></div>

  {{-- panel --}}
  <div x-show="open"
       x-transition:enter="transition transform duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition transform duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
       class="fixed right-0 inset-y-0 z-50 w-full max-w-2xl bg-slate-100 shadow-2xl flex flex-col">
    {{-- header --}}
    <div class="flex items-center justify-between gap-2 px-4 py-3 bg-white border-b border-slate-200 shrink-0 print:hidden">
      <div class="flex items-center gap-2 min-w-0">
        <button @click="close()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">✕</button>
        <h3 class="font-bold text-slate-900">Salary Slip</h3>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button @click="downloadPdf()" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium">
          <i data-lucide="file-text" class="w-4 h-4"></i><span class="hidden sm:inline">PDF</span>
        </button>
        <button @click="saveImage()" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium">
          <i data-lucide="image" class="w-4 h-4"></i><span class="hidden sm:inline">Image</span>
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

<style>
@media print {
  /* Hide overlay + header (they'd otherwise leave a blank first page). */
  [x-data="salarySlipPreview()"] .print\:hidden { display: none !important; }
  /* Neutralise the fixed slide-over so #slip prints in normal flow (not clipped). */
  [x-data="salarySlipPreview()"], [x-data="salarySlipPreview()"] .flex-1,
  [x-data="salarySlipPreview()"] > div {
    position: static !important; overflow: visible !important;
    inset: auto !important; width: auto !important; max-width: none !important;
    padding: 0 !important; margin: 0 !important;
    background: transparent !important; box-shadow: none !important;
    transform: none !important; display: block !important;
  }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
function salarySlipPreview() {
  return {
    open: false, loading: false, html: '', full: '',
    async show(detail) {
      this.full = detail.full || '';
      this.open = true; this.loading = true; this.html = '';
      document.body.style.overflow = 'hidden';
      try {
        const res = await fetch(detail.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        this.html = await res.text();
      } catch (e) {
        this.html = '<p class="text-center text-red-500 py-10">Slip load nahi hua.</p>';
      }
      this.loading = false;
      this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },
    close() { this.open = false; document.body.style.overflow = ''; },
    printIt() {
      // The slide-over lives on a tall page (staff info + salary table), and
      // hidden-but-present content leaves a blank first page when printing in
      // place. So print the standalone slip page (?print=1) which contains only
      // the document — it auto-prints and closes itself (just a quick flash).
      if (this.full) { window.open(this.full + (this.full.includes('?') ? '&' : '?') + 'print=1', '_blank'); }
      else { window.print(); }
    },
    async saveImage() {
      const el = this.$refs.body.querySelector('#slip');
      if (!el || typeof html2canvas === 'undefined') { window.print(); return; }
      const canvas = await html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
      const link = document.createElement('a');
      link.download = 'salary-slip.png';
      link.href = canvas.toDataURL('image/png');
      link.click();
    },
    async downloadPdf() {
      const el = this.$refs.body.querySelector('#slip');
      if (!el || typeof html2canvas === 'undefined' || !window.jspdf) { window.print(); return; }
      const canvas = await html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
      const img = canvas.toDataURL('image/png');
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
      const pw = pdf.internal.pageSize.getWidth();
      const ph = pdf.internal.pageSize.getHeight();
      const iw = pw - 40;
      const ih = canvas.height * iw / canvas.width;
      pdf.addImage(img, 'PNG', 20, 20, iw, Math.min(ih, ph - 40));
      pdf.save('salary-slip.pdf');
    },
  };
}
</script>
