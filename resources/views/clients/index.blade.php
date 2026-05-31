@extends('layouts.app')
@section('title', 'Clients')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Clients</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $clients->total() }} total clients</p>
        </div>
        <a href="{{ route('clients.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Add Client
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name, email, company..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
                </div>
            </div>
            <select name="status"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit"
                    class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('status'))
            <a href="{{ route('clients.index') }}"
               class="px-4 py-2 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Company</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Phone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Invoices</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($clients as $client)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($client->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 leading-tight">{{ $client->name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $client->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 hidden md:table-cell">{{ $client->company_name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 hidden lg:table-cell">{{ $client->phone ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <x-badge :status="$client->is_active ? 'active' : 'inactive'" />
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 hidden md:table-cell">{{ $client->invoices_count }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('clients.show', $client) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50 transition-all">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span class="hidden sm:inline">View</span>
                                </a>
                                <a href="{{ route('clients.edit', $client) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50 transition-all">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span class="hidden sm:inline">Edit</span>
                                </a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                      onsubmit="return confirm('Delete this client?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-red-600 border border-red-100 bg-red-50 px-2.5 py-1.5 rounded-md hover:bg-red-100 transition-all">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-slate-400">
                            <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-slate-200"></i>
                            <p class="font-medium">No clients found</p>
                            <p class="text-sm mt-1">Add your first client to get started.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $clients->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
