@extends('layouts.app')

@section('content')
<div class="py-12">
    
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Editar Carga Completa: <span class="font-mono">{{ $load->numero }}</span>
                    <span class="ml-2 px-2 py-0.5 rounded text-white text-lg" style="background-color: {{ $load->company->color ?? '#6366f1' }}">
                        {{ $load->company->name }}
                    </span>
                </h2>
                <a href="{{ route('loads.index') }}" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    &larr; Volver
                </a>
            </div>

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('loads.update', $load) }}" method="POST" id="load-form" class="space-y-4">
                @csrf
                @method('PUT')
                @include('loads._form')
            </form>
        </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@vite('resources/js/pages/loads/form.js')
@endsection
