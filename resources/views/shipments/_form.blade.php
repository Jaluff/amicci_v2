@php
$isEdit = isset($shipment) && $shipment->exists;
$items = $isEdit ? $shipment->items : [ (object)[ 'tipo_paquete' => 'bultos', 'cantidad' => 1, 'numero_remito' => '',
'peso' => 0, 'volumen' => 0, 'monto_valor_declarado' => 0, 'monto_seguro_item' => 0, 'referencia_recepcion' => '',
'referencia_orden_carga' => '' ] ];
@endphp

<form id="shipment-form" method="POST"
    action="{{ $isEdit ? route('shipments.update', $shipment) : route('shipments.store') }}" class="space-y-4">
    @csrf
    <!-- Local styles to normalize select and Select2 appearance inside this form -->
    <style>
        /* native selects in form: ensure readable color in light/dark */
        #shipment-form select {
            color: #0f172a;
        }

        .dark #shipment-form select {
            color: #f8fafc;
        }

        /* Size and padding for Select2 single selection to match native selects */
        #shipment-form .select2-container--default .select2-selection--single {
            height: 2.5rem !important;
            padding: 0.375rem 0.5rem !important;
            border-radius: 0.375rem !important;
            border: 1px solid #d1d5db !important;
            background: #ffffff !important;
            color: #0f172a !important;
            font-size: 1rem !important;
            line-height: 1.5rem !important;
        }

        .dark #shipment-form .select2-container--default .select2-selection--single {
            border-color: #374151 !important;
            background: #0f172a !important;
            color: #f8fafc !important;
        }

        /* Ensure rendered text uses same color */
        #shipment-form .select2-selection__rendered {
            color: inherit !important;
        }
    </style>
    <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class=" font-bold text-gray-800 dark:text-white">📋 INFORMACIÓN GENERAL</h3>
            <div class="flex items-center gap-3">
                <div class="w-48">
                    <label class=" font-medium text-gray-700 dark:text-yellow-300 block">N° Guía *</label>
                    @if($isEdit)
                    <x-text-input id="numero" name="numero" type="text" value="{{ $shipment->numero }}"
                        class="w-full py-2 px-2  mt-0.5 rounded border-gray-300 dark:border-gray-700"
                        placeholder="GU-001" readonly />
                    @else
                    <x-text-input id="numero" name="numero" type="text" value="{{ old('numero', '') }}"
                        class="w-full py-2 px-2  mt-0.5 rounded border-gray-300 dark:border-gray-700"
                        placeholder="GU-001" readonly />
                    @endif
                </div>
                <div class="w-48        ">
                    <label class=" font-medium text-gray-700 dark:text-gray-300 block">Fecha</label>
                    <x-text-input id="fecha" name="fecha" type="date"
                        value="{{ old('fecha', $isEdit ? ($shipment->fecha ? $shipment->fecha->format('Y-m-d') : '') : date('Y-m-d')) }}"
                        class="w-full py-2 px-2  mt-0.5 rounded border-gray-300 dark:border-gray-700" />
                </div>
                <div class="w-48">
                    <label class="font-medium text-gray-700 dark:text-gray-300 block">Ubicación</label>
                    <select id="ubicacion_actual" name="ubicacion_actual"
                        class="w-full py-2 px-2  mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                        required>
                        <option value="Dto origen" @selected(old('ubicacion_actual', $isEdit ? ($shipment->
                            ubicacion_actual ?? '') : '') === 'Dto origen')>Dto origen</option>
                        <option value="En tránsito" @selected(old('ubicacion_actual', $isEdit ? ($shipment->
                            ubicacion_actual ?? '') : '') === 'En tránsito')>En tránsito</option>
                        <option value="Dto destino" @selected(old('ubicacion_actual', $isEdit ? ($shipment->
                            ubicacion_actual ?? '') : '') === 'Dto destino')>Dto destino</option>
                        <option value="En reparto" @selected(old('ubicacion_actual', $isEdit ? ($shipment->
                            ubicacion_actual ?? '') : '') === 'En reparto')>En reparto</option>
                        <option value="Entregado" @selected(old('ubicacion_actual', $isEdit ? ($shipment->
                            ubicacion_actual ?? '') : '') === 'Entregado')>Entregado</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3">
            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300">Origen *</label>
                <select name="origen_id" id="origen_id"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    @foreach($ubicaciones as $u)
                    <option value="{{ $u->id }}" @selected($isEdit && ($shipment->origen_id ?? $shipment->origin_id) ==
                        $u->id)>{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">Destino *</label>
                <select name="destino_id" id="destino_id"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    @foreach($ubicaciones as $u)
                    <option value="{{ $u->id }}" @selected($isEdit && ($shipment->destino_id ??
                        $shipment->destination_id) == $u->id)>{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">Remitente *</label>
                <select name="remitente_id" id="remitente_id"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    @foreach($parties as $p)
                    <option value="{{ $p->id }}" @selected($isEdit && ($shipment->remitente_id ?? $shipment->sender_id)
                        == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">Destinatario *</label>
                <select name="destinatario_id" id="destinatario_id"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    @foreach($parties as $p)
                    <option value="{{ $p->id }}" @selected($isEdit && ($shipment->destinatario_id ??
                        $shipment->recipient_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: ESTADO & ENTREGA -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class=" font-bold text-gray-800 dark:text-white mb-3">📍 ESTADO & ENTREGA</h3>

        <!-- Fila 1: N° Factura, Estado Facturación, Fecha Entrega, Flete a pagar (cada uno en una columna) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">N° Factura</label>
                <x-text-input id="numero_factura" name="numero_factura" type="text"
                    value="{{ old('numero_factura', $isEdit ? ($shipment->numero_factura ?? '') : '') }}"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700" placeholder="FAC-001" />
            </div>
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">Estado Fact.</label>
                <select id="estado_facturacion" name="estado_facturacion"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                    @foreach(['No facturada','Facturada','Rendida','Anulada'] as $s)
                    <option value="{{ $s }}" @selected(old('estado_facturacion', $isEdit ? ($shipment->
                        estado_facturacion ?? 'No facturada') : 'No facturada') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">Fecha Entrega</label>
                <x-text-input id="fecha_entrega" name="fecha_entrega" type="date"
                    value="{{ old('fecha_entrega', $isEdit && isset($shipment->fecha_entrega) ? $shipment->fecha_entrega->format('Y-m-d') : '') }}"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700" />
            </div>
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">Flete a pagar</label>
                <select id="flete_a_pagar_en" name="flete_a_pagar_en"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                    <option value="destino" @selected(old('flete_a_pagar_en', $isEdit ? ($shipment->flete_a_pagar_en ??
                        'destino') : 'destino') === 'destino')>Destino</option>
                    <option value="origen" @selected(old('flete_a_pagar_en', $isEdit ? ($shipment->flete_a_pagar_en ??
                        '') : '') === 'origen')>Origen</option>
                </select>
            </div>
        </div>

        <!-- Fila 2: Radios (Contra, Rendida, Cobrada) en una sola línea -->
        <div class="grid grid-cols-1 gap-2 mb-3">
            <div class="grid grid-cols-3 gap-3 items-center justify-center">
                <div>
                    <label class=" font-medium text-gray-700 dark:text-gray-300">¿Contra Entrega?</label>
                    <div class="flex gap-2 mt-1 items-center">
                        <label class="flex items-center gap-1 ">
                            <input type="radio" name="contra_reembolso" value="1" @checked(old('contra_reembolso',
                                $isEdit ? $shipment->contra_reembolso : false)) class="w-3 h-3" />
                            <span class="text-gray-800 dark:text-gray-200 font-medium">Sí</span>
                        </label>
                        <label class="flex items-center gap-1 ">
                            <input type="radio" name="contra_reembolso" value="0" @checked(! old('contra_reembolso',
                                $isEdit ? $shipment->contra_reembolso : false)) class="w-3 h-3" />
                            <span class="text-gray-600 dark:text-gray-400">No</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class=" font-medium text-gray-700 dark:text-gray-300">¿Rendida?</label>
                    <div class="flex gap-2 mt-1 items-center">
                        <label class="flex items-center gap-1 ">
                            <input type="radio" name="rendida" value="1" @checked(old('rendida', $isEdit ?
                                $shipment->rendida : false)) class="w-3 h-3" />
                            <span class="text-gray-800 dark:text-gray-200 font-medium">Sí</span>
                        </label>
                        <label class="flex items-center gap-1 ">
                            <input type="radio" name="rendida" value="0" @checked(! old('rendida', $isEdit ?
                                $shipment->rendida : false)) class="w-3 h-3" />
                            <span class="text-gray-600 dark:text-gray-400">No</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class=" font-medium text-gray-700 dark:text-gray-300">¿Cobrada?</label>
                    <div class="flex gap-2 mt-1 items-center">
                        <label class="flex items-center gap-1 ">
                            <input type="radio" name="cobrada" value="1" @checked(old('cobrada', $isEdit ?
                                $shipment->cobrada : false)) class="w-3 h-3" />
                            <span class="text-gray-800 dark:text-gray-200 font-medium">Sí</span>
                        </label>
                        <label class="flex items-center gap-1 ">
                            <input type="radio" name="cobrada" value="0" @checked(! old('cobrada', $isEdit ?
                                $shipment->cobrada : false)) class="w-3 h-3" />
                            <span class="text-gray-600 dark:text-gray-400">No</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notas (movida al final, antes de guardar) - placeholder removed here; will be inserted before buttons -->


    <!-- SECCIÓN 3: CARGA (ITEMS COMPACTA) -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex justify-between items-center mb-2">
            <h3 class=" font-bold text-gray-800 dark:text-white">📦 CARGA</h3>
            <button type="button" id="add-item"
                class=" px-2 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-medium">+ Añadir</button>
        </div>

        <div id="items-container" class="space-y-2">
            @foreach(old('items', $items) as $index => $rawItem)
            @php $item = is_array($rawItem) ? (object) $rawItem : $rawItem; @endphp

            <div
                class="item-row p-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded  flex gap-2 items-end">
                <div class="flex-1">
                    <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Tipo</label>
                    <select name="items[{{ $index }}][tipo_paquete]"
                        class="w-full py-1.5 px-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900  package-type"
                        required>
                        <option value="bultos" @selected(($item->tipo_paquete ?? 'bultos') === 'bultos')>Bultos</option>
                        <option value="palets" @selected(($item->tipo_paquete ?? '') === 'palets')>Palets</option>
                        <option value="sobres" @selected(($item->tipo_paquete ?? '') === 'sobres')>Sobres</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Cant. *</label>
                    <x-text-input type="number" name="items[{{ $index }}][cantidad]" value="{{ $item->cantidad ?? 1 }}"
                        min="1" class="w-full py-1.5 px-1 " required />
                </div>
                <div class="flex-1">
                    <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Remito</label>
                    <x-text-input type="text" name="items[{{ $index }}][numero_remito]"
                        value="{{ $item->numero_remito ?? '' }}" class="w-full py-1.5 px-1 " />
                </div>
                <div class="flex-1">
                    <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Peso</label>
                    <x-text-input type="number" step="1" name="items[{{ $index }}][peso]" value="{{ $item->peso ?? 0 }}"
                        class="w-full py-1.5 px-1 " />
                </div>
                <div class="flex-1">
                    <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Vol</label>
                    <x-text-input type="number" step="1" name="items[{{ $index }}][volumen]"
                        value="{{ $item->volumen ?? 0 }}" class="w-full py-1.5 px-1 " />
                </div>
                <div class="flex-1">
                    <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Valor</label>
                    <x-text-input type="number" step="0.01" name="items[{{ $index }}][monto_valor_declarado]"
                        value="{{ $item->monto_valor_declarado ?? 0 }}" class="w-full py-1.5 px-1 " />
                </div>
                <button type="button"
                    class="remove-item bg-red-600 hover:bg-red-700 text-white px-2 py-1.5 rounded font-medium  whitespace-nowrap">✕</button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- SECCIÓN 4: IMPORTES & FACTURACIÓN -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class=" font-bold text-gray-800 dark:text-white mb-3">💰 IMPORTES & FACTURACIÓN</h3>

        <!-- Cargos -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
            <div>
                <label class=" text-gray-600 dark:text-gray-400">Flete ($)</label>
                <x-text-input id="flete" type="number" name="flete" step="0.01"
                    value="{{ old('flete', $isEdit ? ($shipment->flete ?? $shipment->flete) : 0) }}"
                    class="w-full py-1.5 px-2  mt-1" min="0" />
            </div>
            <div>
                <label class=" text-gray-600 dark:text-gray-400">Seguro ($)</label>
                <x-text-input id="seguro" type="number" name="seguro" step="0.01"
                    value="{{ old('seguro', $isEdit ? ($shipment->seguro ?? $shipment->seguro) : 0) }}"
                    class="w-full py-1.5 px-2  mt-1" min="0" />
            </div>
            <div>
                <label class=" text-gray-600 dark:text-gray-400">Com. Contrareembolso ($)</label>
                <x-text-input id="monto_contra_reembolso" type="number" name="monto_contra_reembolso" step="0.01"
                    value="{{ old('monto_contra_reembolso', $isEdit ? ($shipment->monto_contra_reembolso ?? $shipment->monto_contra_reembolso) : 0) }}"
                    class="w-full py-1.5 px-2  mt-1" min="0" />
            </div>
            <div>
                <label class=" text-gray-600 dark:text-gray-400">Ret. mercaderia ($)</label>
                <x-text-input id="retencion_mercaderia" type="number" name="retencion_mercaderia" step="0.01"
                    value="{{ old('retencion_mercaderia', $isEdit ? ($shipment->retencion_mercaderia ?? $shipment->retention_mercaderia ?? 0) : 0) }}"
                    class="w-full py-1.5 px-2  mt-1" min="0" />
            </div>
            <div>
                <label class=" text-gray-600 dark:text-gray-400">Otros ($)</label>
                <x-text-input id="otros_cargos" type="number" name="otros_cargos" step="0.01"
                    value="{{ old('otros_cargos', $isEdit ? ($shipment->otros_cargos ?? $shipment->otros_cargos) : 0) }}"
                    class="w-full py-1.5 px-2  mt-1" min="0" />
            </div>
        </div>

        <!-- Resumen y totales -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded">
                <p class=" text-gray-600 dark:text-gray-400 font-semibold">SUBTOTAL ($)</p>
                <x-text-input id="subtotal" name="subtotal" type="text"
                    value="{{ old('subtotal', $isEdit ? ($shipment->subtotal ?? 0) : 0) }}"
                    class="w-full bg-gray-100 dark:bg-gray-700 font-bold  mt-1 py-1 px-1 border-0" readonly />
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded">
                <p class=" text-gray-600 dark:text-gray-400 font-semibold">IVA %</p>
                <x-text-input id="iva_percent" name="iva_percent" type="number" step="0.01"
                    value="{{ old('iva_percent', 21) }}" class="w-full py-1 px-1  mt-1" min="0" />
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded">
                <p class=" text-gray-600 dark:text-gray-400 font-semibold">IVA $</p>
                <x-text-input id="iva_monto" name="iva_monto" type="number" step="0.01"
                    value="{{ old('iva_monto', $isEdit ? ($shipment->iva_monto ?? 0) : 0) }}"
                    class="w-full bg-gray-100 dark:bg-gray-700 font-bold  mt-1 py-1 px-1 border-0" readonly />
            </div>
            <div
                class="bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 p-2 rounded border border-green-300 dark:border-green-700">
                <p class=" text-green-700 dark:text-green-300 font-bold">TOTAL 💰</p>
                <x-text-input id="total" name="total" type="text"
                    value="{{ old('total', $isEdit ? ($shipment->total ?? 0) : 0) }}"
                    class="w-full bg-white dark:bg-gray-800 font-bold  text-green-700 dark:text-green-400 mt-1 py-1 px-1 border-green-300"
                    readonly />
            </div>
        </div>
    </div>

    <!-- BOTONES ACCIÓN -->
    <!-- NOTAS: colocadas antes de los botones de acción -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <label class=" font-medium text-gray-700 dark:text-gray-300">Notas</label>
        <textarea name="notas" id="notas" rows="4"
            class="w-full mt-2  px-2 py-2 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('notas', $isEdit ? ($shipment->notas ?? $shipment->notes ?? '') : '') }}</textarea>
    </div>

    <div
        class="flex justify-between gap-2 sticky bottom-0 bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-lg">
        <a href="{{ route('shipments.index') }}"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded font-medium  hover:bg-gray-300 dark:hover:bg-gray-600">
            ← Volver
        </a>
        <x-primary-button type="submit" class="px-6 py-2  font-medium">
            @if($isEdit)
            ✅ Actualizar
            @else
            💾 Guardar
            @endif
        </x-primary-button>
    </div>
</form>

<template id="item-row-template">
    <div
        class="item-row p-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded  flex gap-2 items-end">
        <div class="flex-1">
            <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Tipo</label>
            <select name="items[__INDEX__][tipo_paquete]"
                class="w-full py-1.5 px-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900  package-type"
                required>
                <option value="bultos">Bultos</option>
                <option value="palets">Palets</option>
                <option value="sobres">Sobres</option>
            </select>
        </div>
        <div class="flex-1">
            <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Cant. *</label>
            <x-text-input type="number" name="items[__INDEX__][cantidad]" value="1" min="1" class="w-full py-1.5 px-1 "
                required />
        </div>
        <div class="flex-1">
            <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Remito</label>
            <x-text-input type="text" name="items[__INDEX__][numero_remito]" class="w-full py-1.5 px-1 " />
        </div>
        <div class="flex-1">
            <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Peso</label>
            <x-text-input type="number" step="1" name="items[__INDEX__][peso]" value="0" class="w-full py-1.5 px-1 " />
        </div>
        <div class="flex-1">
            <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Vol</label>
            <x-text-input type="number" step="1" name="items[__INDEX__][volumen]" value="0"
                class="w-full py-1.5 px-1 " />
        </div>
        <div class="flex-1">
            <label class=" font-medium text-gray-600 dark:text-gray-400 block mb-0.5">Valor</label>
            <x-text-input type="number" step="0.01" name="items[__INDEX__][monto_valor_declarado]" value="0"
                class="w-full py-1.5 px-1 " />
        </div>
        <button type="button"
            class="remove-item bg-red-600 hover:bg-red-700 text-white px-2 py-1.5 rounded font-medium  whitespace-nowrap">✕</button>
    </div>
</template>