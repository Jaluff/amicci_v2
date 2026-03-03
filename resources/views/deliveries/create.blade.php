@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">Crear Nuevo Reparto</h2>

            <form action="{{ route('deliveries.store') }}" method="POST" id="delivery-form">
                @csrf
                @include('deliveries._form', ['delivery' => new \App\Models\Delivery()])
            </form>
        </div>
    </div>
</div>

{{-- Modal para seleccionar Guías --}}
@include('deliveries._modal_shipments')
@include('partials._modal_shipment_problems')
@endsection

@section('scripts')
@vite('resources/js/pages/deliveries/deliveries.js')
@endsection