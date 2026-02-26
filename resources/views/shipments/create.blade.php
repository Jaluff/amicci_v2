@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="w-full sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200">Nueva Guía</h2>
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 text-red-700 dark:text-red-300 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @include('shipments._form')
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/shipments/form.js')
@endsection
