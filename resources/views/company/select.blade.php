@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Seleccionar Empresa</h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Elegí con qué empresa querés trabajar</p>
        </div>

        <div class="space-y-3">
            @foreach ($companies as $company)
            <form method="POST" action="{{ route('company.switch') }}">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">
                <button type="submit"
                    class="w-full flex items-center justify-between p-4 bg-white dark:bg-gray-800 border-2 {{ session('company_id') == $company->id ? 'border-indigo-500' : 'border-gray-200 dark:border-gray-700' }} rounded-xl hover:border-indigo-400 hover:shadow-md transition-all duration-150 group">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-lg">
                            {{ strtoupper(substr($company->name, 0, 2)) }}
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $company->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Prefijo: {{ $company->prefix }}</div>
                        </div>
                    </div>
                    @if(session('company_id') == $company->id)
                    <span
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/50 px-2 py-1 rounded-full">Activa</span>
                    @else
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    @endif
                </button>
            </form>
            @endforeach
        </div>
    </div>
</div>
@endsection