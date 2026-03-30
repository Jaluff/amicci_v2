@extends('layouts.app')

@section('content')
<div class="py-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2
                class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
                Editar Cliente: {{ $party->name }}
            </h2>

            <!-- Errors -->
            @if ($errors->any())
            <div
                class="mb-4 bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 p-4 rounded-md">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @include('parties._form', ['party' => $party])

        </div>
</div>
@endsection