@extends('layouts.app')
@section('title','Team Members')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900">{{ __('pages.team_members') }}</h1>
      <p class="text-sm text-slate-500">{{ $users->count() }} of {{ $limit === PHP_INT_MAX ? '∞' : $limit }} users used</p>
    </div>
    @if($users->count() < $limit)
    <a href="{{ route('tenant.users.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="plus" class="w-4 h-4"></i>Add User
    </a>
    @else
    <span class="inline-flex items-center gap-2 bg-slate-100 text-slate-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed" title="Upgrade plan to add more users">
      <i data-lucide="lock" class="w-4 h-4"></i>Limit Reached
    </span>
    @endif
  </div>

  @if($limit !== PHP_INT_MAX)
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-medium text-slate-700">{{ __('pages.user_slots') }}</span>
      <span class="text-sm text-slate-500">{{ $users->count() }}/{{ $limit }}</span>
    </div>
    <div class="w-full bg-slate-100 rounded-full h-2">
      <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ min(100, ($users->count()/$limit)*100) }}%"></div>
    </div>
    @if($users->count() >= $limit)
    <p class="text-xs text-amber-600 mt-2">{{ __('pages.upgrade_slots') }}</p>
    @endif
  </div>
  @endif

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($users->count())
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('common.name') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('pages.email') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('pages.role') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('common.actions') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($users as $user)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-sm">
                {{ strtoupper(substr($user->name,0,1)) }}
              </div>
              <span class="text-sm font-medium text-slate-900">{{ $user->name }}</span>
              @if($user->id === auth()->id())<span class="ml-1 text-xs text-slate-400">(you)</span>@endif
            </div>
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $user->email }}</td>
          <td class="px-4 py-3">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold uppercase
              {{ $user->role === 'owner' ? 'bg-purple-100 text-purple-700' : ($user->role === 'manager' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
              {{ $user->role }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <div x-data="{ open: false }" class="relative inline-block text-left">
              <button @click="open=!open" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
              </button>
              <div x-show="open" @click.outside="open=false" x-cloak
                   class="absolute right-0 mt-1 w-44 bg-white rounded-xl border border-slate-200 shadow-lg z-10 py-1">
                <button @click="open=false; $dispatch('open-reset-{{ $user->id }}')"
                        class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                  <i data-lucide="key" class="w-3.5 h-3.5"></i>Reset Password
                </button>
                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('tenant.users.destroy', $user) }}"
                      data-confirm-title="Remove User?" data-confirm-message="Remove {{ $user->name }} from this team?" data-confirm-danger="true">
                  @csrf @method('DELETE')
                  <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                    <i data-lucide="user-x" class="w-3.5 h-3.5"></i>Remove
                  </button>
                </form>
                @endif
              </div>
            </div>
          </td>
        </tr>

        {{-- Reset Password Modal --}}
        <tr x-data="{ show: false }" @open-reset-{{ $user->id }}.window="show=true" x-cloak>
          <td colspan="4" class="p-0">
            <div x-show="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
              <div @click.outside="show=false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <h3 class="font-bold text-slate-900 mb-1">{{ __('pages.reset_password') }}</h3>
                <p class="text-sm text-slate-500 mb-4">{{ $user->name }}</p>
                <form method="POST" action="{{ route('tenant.users.reset-password', $user) }}">
                  @csrf
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('pages.new_password') }}</label>
                      <input type="password" name="password" required minlength="6" placeholder="{{ __('pages.min6') }}"
                             class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('pages.confirm_password') }}</label>
                      <input type="password" name="password_confirmation" required
                             class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                    </div>
                  </div>
                  <div class="flex gap-2 mt-4">
                    <button type="button" @click="show=false"
                            class="flex-1 px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-700">{{ __('common.cancel') }}</button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">{{ __('common.update') }}</button>
                  </div>
                </form>
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="px-4 py-12 text-center">
      <i data-lucide="users" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-400">{{ __('pages.no_team') }}</p>
    </div>
    @endif
  </div>
</div>
@endsection
