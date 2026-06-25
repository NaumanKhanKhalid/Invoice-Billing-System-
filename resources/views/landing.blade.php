<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShopSaas — Smart Billing for Every Shop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { brand: { 50:'#f0fdf4',100:'#dcfce7',500:'#22c55e',600:'#16a34a',700:'#15803d' } } } } }</script>
</head>
<body class="bg-white text-slate-800 font-sans">

{{-- Nav --}}
<nav class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur border-b border-slate-100">
  <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">S</div>
      <span class="font-bold text-slate-900 text-lg">ShopSaas</span>
    </div>
    <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
      <a href="#features" class="hover:text-green-600">Features</a>
      <a href="#plans" class="hover:text-green-600">Plans</a>
      <a href="#contact" class="hover:text-green-600">Contact</a>
    </div>
    <a href="{{ route('login') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Login</a>
  </div>
</nav>

{{-- Hero --}}
<section class="pt-32 pb-20 px-4 text-center bg-gradient-to-b from-green-50 to-white">
  <div class="max-w-3xl mx-auto">
    <span class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full mb-6 uppercase tracking-wide">Pakistan's #1 Shop Billing System</span>
    <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight mb-6">
      Apni Dukan ka Hisaab<br><span class="text-green-600">Digital Karo</span>
    </h1>
    <p class="text-lg text-slate-500 mb-8 max-w-xl mx-auto">Complete billing software for chicken shops, hardware stores, mobile accessory shops, bike spare parts — sab ke liye ek system.</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="#contact" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition-colors">Free Demo Chahiye? →</a>
      <a href="#plans" class="border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-3 rounded-xl font-semibold text-sm transition-colors">Plans Dekhein</a>
    </div>
  </div>
</section>

{{-- Shop types --}}
<section class="py-12 px-4 bg-white">
  <div class="max-w-4xl mx-auto">
    <p class="text-center text-sm font-semibold text-slate-400 uppercase tracking-wider mb-8">Yeh sab dukanon ke liye</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      @foreach([['🍗','Chicken Shop'],['🔧','Hardware Shop'],['📱','Mobile Shop'],['🏍️','Bike Parts']] as [$icon,$label])
      <div class="bg-slate-50 rounded-2xl p-5 text-center border border-slate-100 hover:border-green-200 hover:bg-green-50 transition-colors">
        <div class="text-3xl mb-2">{{ $icon }}</div>
        <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Features --}}
<section id="features" class="py-20 px-4 bg-slate-50">
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-slate-900 mb-3">Poora System — Ek Jagah</h2>
      <p class="text-slate-500">Manually register karne ki zaroorat nahi — sab kuch automatic.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      @foreach([
        ['📦','Purchases & Inventory','Supplier se purchases track karo, stock dekhte raho'],
        ['🧾','Supply Orders','Customer orders banao, invoice print karo PDF me'],
        ['💸','Udhar Book','Udhaar diya, kab wapas hoga — sab record me'],
        ['👥','Staff & Salaries','Employee attendance, salary, bonus sab manage karo'],
        ['📊','Daily Reports','Din ka hisaab ek click me — Day End report'],
        ['☁️','Cloud Backup','Google Drive pe backup — data safe rehega hamesha'],
      ] as [$icon,$title,$desc])
      <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
        <div class="text-2xl mb-3">{{ $icon }}</div>
        <h3 class="font-bold text-slate-900 mb-1">{{ $title }}</h3>
        <p class="text-sm text-slate-500">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Plans --}}
<section id="plans" class="py-20 px-4 bg-white">
  <div class="max-w-4xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-slate-900 mb-3">Simple Plans</h2>
      <p class="text-slate-500">No hidden charges. Monthly ya yearly — aap ki marzi.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      @foreach(config('plans') as $key => $plan)
      <div class="rounded-2xl border {{ $key === 'pro' ? 'border-green-400 shadow-xl ring-2 ring-green-200 relative' : 'border-slate-200 shadow-sm' }} p-6">
        @if($key === 'pro')
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">MOST POPULAR</div>
        @endif
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ $plan['name'] }}</p>
        <div class="flex items-end gap-1 mb-4">
          <span class="text-3xl font-extrabold text-slate-900">PKR {{ number_format($plan['price']) }}</span>
          <span class="text-slate-400 text-sm mb-1">/month</span>
        </div>
        <ul class="space-y-2 text-sm text-slate-600 mb-6">
          <li class="flex items-center gap-2"><span class="text-green-500">✓</span> {{ $plan['max_users'] === PHP_INT_MAX ? 'Unlimited' : $plan['max_users'] }} User{{ $plan['max_users'] > 1 ? 's' : '' }}</li>
          <li class="flex items-center gap-2"><span class="text-green-500">✓</span> All Core Modules</li>
          @if($plan['staff_module'])<li class="flex items-center gap-2"><span class="text-green-500">✓</span> Staff Management</li>@else<li class="flex items-center gap-2 text-slate-300"><span>✗</span> Staff Management</li>@endif
          @if($plan['google_backup'])<li class="flex items-center gap-2"><span class="text-green-500">✓</span> Google Drive Backup</li>@else<li class="flex items-center gap-2 text-slate-300"><span>✗</span> Google Drive Backup</li>@endif
        </ul>
        <a href="#contact" class="block text-center {{ $key === 'pro' ? 'bg-green-600 hover:bg-green-700 text-white' : 'border border-slate-200 hover:bg-slate-50 text-slate-700' }} py-2.5 rounded-xl text-sm font-semibold transition-colors">Get Started</a>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Contact --}}
<section id="contact" class="py-20 px-4 bg-green-600">
  <div class="max-w-xl mx-auto text-center text-white">
    <h2 class="text-3xl font-bold mb-3">Demo Chahiye?</h2>
    <p class="text-green-100 mb-8">Humse WhatsApp pe rabta karo. Aapki dukan ke hisaab se setup karenge.</p>
    <a href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum!%20ShopSaas%20ke%20baare%20mein%20maloomat%20chahiye."
       target="_blank"
       class="inline-flex items-center gap-3 bg-white text-green-700 hover:bg-green-50 px-6 py-4 rounded-xl font-bold text-sm transition-colors shadow-lg">
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      WhatsApp pe Rabta Karo
    </a>
    <p class="text-green-200 text-xs mt-4">Ya email karein: info@shopsaas.pk</p>
  </div>
</section>

{{-- Footer --}}
<footer class="bg-slate-900 text-slate-400 text-center py-6 text-sm">
  © {{ date('Y') }} ShopSaas. Made with ❤️ in Pakistan.
</footer>

</body>
</html>
