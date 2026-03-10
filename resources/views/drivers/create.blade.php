@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200">Nuevo Conductor</h2>
            @include('drivers._form', ['isEdit' => false, 'driver' => new \App\Models\Driver()])
        </div>
    </div>
</div>
@endsection
