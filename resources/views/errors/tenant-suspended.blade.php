<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Suspended</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-lg p-10 max-w-md w-full text-center">
    <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-5">
      <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Account Suspended</h1>
    <p class="text-slate-500 mb-1">
      <strong>{{ $tenant->shop_name }}</strong>
    </p>
    <p class="text-slate-400 text-sm mb-6">
      Your account has been temporarily suspended. Please contact support to resolve this.
    </p>
    <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600">
      <p class="font-semibold mb-1">Contact Support:</p>
      <p>📞 Call / WhatsApp your service provider</p>
    </div>
  </div>
</body>
</html>
