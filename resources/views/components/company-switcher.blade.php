@php
    $user = Auth::user();
    $currentCompany = $user ? $user->company : null;
    $companies = collect(); // Empty collection by default

    if ($user) {
        if ($user->hasRole('Super Admin')) {
            $companies = App\Models\Company::where('is_active', true)->get();
        } elseif ($user->company_id) {
            $companies = App\Models\Company::where('id', $user->company_id)->get();
        }
    }
@endphp

@if($companies->count() > 0)
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out px-3 py-2 border border-transparent rounded-md">
        <svg class="h-5 w-5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <span>{{ $currentCompany->name ?? 'Select Company' }}</span>
        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
        <div class="py-1">
            @foreach($companies as $company)
                <form method="POST" action="{{ route('company.switch', $company) }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $currentCompany && $currentCompany->id == $company->id ? 'bg-gray-100 font-semibold' : '' }}">
                        {{ $company->name }}
                        @if($currentCompany && $currentCompany->id == $company->id)
                            <span class="float-right text-green-500">✓</span>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
@endif