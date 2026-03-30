@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('tariff-tables.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Nuevo Cuadro Tarifario</h2>
        </div>
        @include('tariffTables._form')
    </div>
</div>
@endsection
