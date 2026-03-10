@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200">Nuevo Repartidor</h2>
            @include('deliverers._form', ['isEdit' => false, 'deliverer' => new \App\Models\Deliverer()])
        </div>
    </div>
</div>
@endsection
