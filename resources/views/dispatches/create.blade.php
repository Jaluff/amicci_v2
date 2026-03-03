@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Crear Nuevo Despacho</h2>
                <a href="{{ route('dispatches.index') }}"
                    class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
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

            <form action="{{ route('dispatches.store') }}" method="POST" id="dispatch-form">
                @csrf
                @include('dispatches._form', ['dispatch' => new \App\Models\Dispatch()])
            </form>
        </div>
    </div>
</div>

@include('dispatches._modal_routes')
@endsection

@section('scripts')
@vite('resources/js/pages/dispatches/form.js')
@endsection