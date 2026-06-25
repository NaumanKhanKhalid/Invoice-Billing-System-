<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Expired</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-lg p-10 max-w-md w-full text-center">
    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
      <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Subscription Expired</h1>
    <p class="text-slate-500 mb-1">
      <strong>{{ $tenant->shop_name }}</strong>
    </p>
    <p class="text-slate-400 text-sm mb-6">
      Your subscription expired on
      <strong class="text-red-500">{{ $tenant->plan_expires_at->format('d M Y') }}</strong>.
      Please contact support to renew your plan.
    </p>
    <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600">
      <p class="font-semibold mb-1">Contact to Renew:</p>
      <p>📞 Call / WhatsApp your service provider</p>
      <p class="mt-1 text-xs text-slate-400">Once renewed, access will be restored immediately.</p>
    </div>
  </div>
</body>
</html>
