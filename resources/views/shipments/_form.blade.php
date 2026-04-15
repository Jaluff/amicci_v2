@php
    $isEdit = isset($shipment) && $shipment->exists;
    $items = $isEdit ? $shipment->items : [
        (object) [
            'tipo_paquete' => 'bultos',
            'cantidad' => 1,
            'numero_remito' => '',
            'peso' => 0,
            'volumen' => 0,
            'monto_valor_declarado' => 0,
            'monto_seguro_item' => 0,
            'referencia_recepcion' => '',
            'referencia_orden_carga' => ''
        ]
    ];
@endphp

<form id="shipment-form" method="POST" data-is-edit="{{ $isEdit ? 'true' : 'false' }}"
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
    </style>
    <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="font-bold text-gray-800 dark:text-white mb-3">📋 INFORMACIÓN GENERAL</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-3">
            @if(!$isEdit)
                <div>
                    <label class="font-medium text-gray-700 dark:text-yellow-300 block">N° Guía *</label>
                    <x-text-input id="numero" name="numero" type="text" value="{{ old('numero', '') }}"
                        class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700" placeholder="GU-001"
                        readonly />
                </div>
                @php
                    $userBranch = $branches->first();
                @endphp
                <div>
                    <label class="font-medium text-gray-700 dark:text-gray-300 block">Sucursal</label>
                    @if($branches->count() > 1)
                        <select name="branch_id" id="branch_id"
                            class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                            required>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" data-ubicacion="{{ $b->ubicacion_id }}"
                                    @selected(old('branch_id') == $b->id)>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-200">
                            {{ $userBranch?->name ?? '—' }}
                        </p>
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ old('branch_id', $userBranch?->id) }}">
                    @endif
                </div>
            @else
                <div>
                    <label class="font-medium text-gray-700 dark:text-gray-300 block">Sucursal</label>
                    <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        {{ $shipment->branch?->name ?? '—' }}
                    </p>
                    <input type="hidden" name="branch_id" id="branch_id" value="{{ $shipment->branch_id }}">
                </div>
            @endif
            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300 block">Fecha</label>
                <x-text-input id="fecha" name="fecha" type="date"
                    value="{{ old('fecha', $isEdit ? ($shipment->fecha ? $shipment->fecha->format('Y-m-d') : '') : date('Y-m-d')) }}"
                    class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700" />
            </div>
            <input type="hidden" name="ubicacion_actual"
                value="{{ $isEdit ? ($shipment->ubicacion_actual ?? 'Dto origen') : 'Dto origen' }}">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3">
            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300">Origen *</label>
                @php
                    $defaultOrigenId = $isEdit ? ($shipment->origen_id ?? $shipment->origin_id) : ($userBranch?->ubicacion_id);
                @endphp
                <select name="origen_id" id="origen_id"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    @foreach($ubicaciones as $u)
                        <option value="{{ $u->id }}" @selected($defaultOrigenId == $u->id)>{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class=" font-medium text-gray-700 dark:text-gray-300">Destino *</label>
                <select name="destino_id" id="destino_id"
                    class="w-full py-2 px-2  mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    <option value="">Seleccionar Destino...</option>
                    @foreach($ubicaciones as $u)
                        <option value="{{ $u->id }}" @selected(
                            $isEdit && ($shipment->destino_id ??
                                $shipment->destination_id) == $u->id
                        )>{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <label class="font-medium text-gray-700 dark:text-gray-300">Remitente *</label>
                <button type="button"
                    class="btn-quick-party absolute right-0 top-0 font-bold text-2xl text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 leading-none"
                    data-target="#remitente_id" title="Nuevo Remitente" style="margin-top: -2px;">+</button>
                <select name="remitente_id" id="remitente_id"
                    class="w-full py-2 px-2 mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    @foreach($parties as $p)
                        <option value="{{ $p->id }}" @selected($isEdit && ($shipment->remitente_id ?? $shipment->sender_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <label class="font-medium text-gray-700 dark:text-gray-300">Destinatario *</label>
                <button type="button"
                    class="btn-quick-party absolute right-0 top-0 font-bold text-2xl text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 leading-none"
                    data-target="#destinatario_id" title="Nuevo Destinatario" style="margin-top: -2px;">+</button>
                <select name="destinatario_id" id="destinatario_id"
                    class="w-full py-2 px-2 mt-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                    required>
                    @foreach($parties as $p)
                        <option value="{{ $p->id }}" @selected($isEdit && ($shipment->destinatario_id ?? $shipment->recipient_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: ESTADO & ENTREGA -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class=" font-bold text-gray-800 dark:text-white mb-3">📍 ESTADO & ENTREGA</h3>

        <!-- Fila 1: Contra-reembolso, Cobrada, Flete a pagar en + Info: N° Factura y Fecha Entrega -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 items-start">
            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300">¿Contra-reembolso?</label>
                <div class="flex gap-2 mt-1 items-center">
                    <label class="flex items-center gap-1">
                        <input type="radio" name="contra_reembolso" value="1" @checked(
                            old('contra_reembolso', $isEdit ?
                                $shipment->contra_reembolso : false)
                        ) class="w-3 h-3" />
                        <span class="text-gray-800 dark:text-gray-200 font-medium">Sí</span>
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="radio" name="contra_reembolso" value="0" @checked(
                            !old('contra_reembolso', $isEdit
                                ? $shipment->contra_reembolso : false)
                        ) class="w-3 h-3" />
                        <span class="text-gray-600 dark:text-gray-400">No</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300">¿Cobrada?</label>
                <div class="flex gap-2 mt-1 items-center">
                    <label class="flex items-center gap-1">
                        <input type="radio" name="cobrada" value="1" @checked(
                            old('cobrada', $isEdit ?
                                $shipment->cobrada : false)
                        ) class="w-3 h-3" />
                        <span class="text-gray-800 dark:text-gray-200 font-medium">Sí</span>
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="radio" name="cobrada" value="0" @checked(
                            !old('cobrada', $isEdit ?
                                $shipment->cobrada : false)
                        ) class="w-3 h-3" />
                        <span class="text-gray-600 dark:text-gray-400">No</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300">Flete a pagar en</label>
                <div class="flex gap-2 mt-1 items-center">
                    <label class="flex items-center gap-1">
                        <input type="radio" name="flete_a_pagar_en" value="origen" @checked(
                            strtolower(old('flete_a_pagar_en', $isEdit ? ($shipment->flete_a_pagar_en ?? 'origen') : 'origen')) === 'origen'
                        )
                            class="w-3 h-3" />
                        <span class="text-gray-800 dark:text-gray-200 font-medium">Origen</span>
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="radio" name="flete_a_pagar_en" value="destino" @checked(
                            strtolower(old('flete_a_pagar_en', $isEdit ? ($shipment->flete_a_pagar_en ?? 'origen') : 'origen')) === 'destino'
                        ) class="w-3 h-3" />
                        <span class="text-gray-600 dark:text-gray-400">Destino</span>
                    </label>
                </div>
            </div>

            {{-- Info: N° Factura --}}
            <div class="text-center">
                <p class="text-xs font-semibold text-indigo-500 dark:text-indigo-400 uppercase tracking-wider mb-1">N°
                    Factura</p>
                <p class="text-lg font-bold text-indigo-700 dark:text-indigo-300">
                    {{ $isEdit && $shipment->numero_factura ? $shipment->numero_factura : '—' }}
                </p>
            </div>

            {{-- Info: Fecha Entrega --}}
            <div class="text-center">
                <p class="text-xs font-semibold text-emerald-500 dark:text-emerald-400 uppercase tracking-wider mb-1">
                    Fecha Entrega</p>
                <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                    {{ $isEdit && $shipment->fecha_entrega ? $shipment->fecha_entrega->format('d/m/Y') : '—' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Notas (movida al final, antes de guardar) - placeholder removed here; will be inserted before buttons -->


    <!-- BANNER: Tarifa activa del remitente -->
    <div id="tariff-banner" style="display:none"
        class="flex items-center gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-600 rounded-lg px-4 py-2 text-sm text-amber-800 dark:text-amber-300">
        <svg class="w-5 h-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>
            Tarifa especial: <strong id="tariff-mode-label" class="font-semibold"></strong>.
            El flete se calcula automáticamente según los ítems.
        </span>
    </div>


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

                <div class="item-row p-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-9 gap-2 items-end">
                        <div>
                            <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Tipo</label>
                            <select name="items[{{ $index }}][tipo_paquete]"
                                class="w-full py-1.5 px-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm package-type"
                                required>
                                <option value="bultos" @selected(($item->tipo_paquete ?? 'bultos') === 'bultos')>Bultos
                                </option>
                                <option value="palets" @selected(($item->tipo_paquete ?? '') === 'palets')>Palets</option>
                                <option value="sobres" @selected(($item->tipo_paquete ?? '') === 'sobres')>Sobres</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Cantidad</label>
                            <x-text-input type="number" name="items[{{ $index }}][cantidad]"
                                value="{{ $item->cantidad ?? 1 }}" min="1" class="w-full py-1.5 px-1 text-sm" required />
                        </div>
                        <div>
                            <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Remito</label>
                            <x-text-input type="text" name="items[{{ $index }}][numero_remito]"
                                value="{{ $item->numero_remito ?? '' }}" class="w-full py-1.5 px-1 text-sm" />
                        </div>
                        <div>
                            <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Peso</label>
                            <x-text-input type="number" step="1" name="items[{{ $index }}][peso]"
                                value="{{ $item->peso ?? 0 }}" class="w-full py-1.5 px-1 text-sm" />
                        </div>
                        <div>
                            <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Volumen</label>
                            <x-text-input type="number" step="0.01" name="items[{{ $index }}][volumen]"
                                value="{{ $item->volumen ?? 0 }}" class="w-full py-1.5 px-1 text-sm" />
                        </div>
                        <div>
                            <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Orden
                                carga</label>
                            <x-text-input type="text" name="items[{{ $index }}][referencia_orden_carga]"
                                value="{{ $item->referencia_orden_carga ?? '' }}" class="w-full py-1.5 px-1 text-sm" />
                        </div>
                        <div>
                            <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">P.
                                Recepción</label>
                            <x-text-input type="text" name="items[{{ $index }}][referencia_recepcion]"
                                value="{{ $item->referencia_recepcion ?? '' }}" class="w-full py-1.5 px-1 text-sm" />
                        </div>
                        <div>
                            <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Valor
                                declarado</label>
                            <x-text-input type="number" step="0.01" name="items[{{ $index }}][monto_valor_declarado]"
                                value="{{ $item->monto_valor_declarado ?? 0 }}" class="w-full py-1.5 px-1 text-sm" />
                        </div>
                        <div class="flex items-end">
                            <button type="button"
                                class="remove-item bg-red-600 hover:bg-red-700 text-white px-2 py-1.5 rounded font-medium whitespace-nowrap w-full text-sm">✕</button>
                        </div>
                    </div>
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
                <x-text-input id="iva_percent" name="iva_percent" type="number" step="0.1"
                    value="{{ old('iva_percent', $isEdit ? ($shipment->iva_percent ?? 21) : 21) }}" class="w-full py-1 px-1  mt-1" min="0" />
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

    <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('shipments.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Cancelar
        </a>
        <button type="submit" name="action" value="save"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            @if($isEdit)
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Actualizar
            @else
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                    </path>
                </svg>
                Guardar
            @endif
        </button>
        <button type="submit" name="action" value="save_and_print" id="btn_save_and_print"
            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                </path>
            </svg>
            @if($isEdit)
                Actualizar e imprimir
            @else
                Guardar e imprimir
            @endif
        </button>
    </div>
</form>

@include('shipments._party_modal')

<script>
    window.GlobalContraPct = {{ (float) (\App\Models\Company::find(session('company_id'))?->contra_reembolso_percent ?? 0) }};

    document.addEventListener('DOMContentLoaded', function () {
        const btnPrint = document.getElementById('btn_save_and_print');
        const btnSave = document.querySelector('button[value="save"]');
        const form = document.getElementById('shipment-form');

        if (btnPrint && form) {
            btnPrint.addEventListener('click', function (e) {
                // Obviamos el bloqueador abriendo nosotros mismos una ventana vacía pero con nombre
                // Esto nos asegura mantener viva la referencia (window.opener) a esta página original
                window.open('', 'PrintWindow');
                form.setAttribute('target', 'PrintWindow');
            });
        }

        if (btnSave && form) {
            btnSave.addEventListener('click', function () {
                form.removeAttribute('target');
            });
        }

        // Usar jQuery para manejar los eventos compatibles con Select2
        $(document).ready(function () {
            var $branchSelect = $('#branch_id');
            var $origenSelect = $('#origen_id');
            var $destinoSelect = $('#destino_id');

            // Validación Origen vs Destino removed to allow same locations

            // Auto-seleccionar Origen al cambiar de Sucursal
            if ($branchSelect.length) {
                $branchSelect.on('change', function () {
                    // Si es un select nativo o Select2, buscamos el atributo data-ubicacion de la opción seleccionada
                    var $selected = $branchSelect.find('option:selected');
                    var ubicacionId = $selected.data('ubicacion');

                    if (ubicacionId && $origenSelect.length) {
                        // Sincronizamos y disparamos el cambio para Select2
                        $origenSelect.val(ubicacionId).trigger('change');
                    }
                });

                // Disparar inicialmente para cargar la sucursal por defecto
                $branchSelect.trigger('change');
            }
        });
    });
</script>

<template id="item-row-template">
    <div class="item-row p-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-9 gap-2 items-end">
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Tipo</label>
                <select name="items[__INDEX__][tipo_paquete]"
                    class="w-full py-1.5 px-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm package-type"
                    required>
                    <option value="bultos">Bultos</option>
                    <option value="palets">Palets</option>
                    <option value="sobres">Sobres</option>
                </select>
            </div>
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Cant. *</label>
                <x-text-input type="number" name="items[__INDEX__][cantidad]" value="1" min="1"
                    class="w-full py-1.5 px-1 text-sm" required />
            </div>
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Remito</label>
                <x-text-input type="text" name="items[__INDEX__][numero_remito]" class="w-full py-1.5 px-1 text-sm" />
            </div>
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Peso</label>
                <x-text-input type="number" step="1" name="items[__INDEX__][peso]" value="0"
                    class="w-full py-1.5 px-1 text-sm" />
            </div>
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Vol</label>
                <x-text-input type="number" step="1" name="items[__INDEX__][volumen]" value="0"
                    class="w-full py-1.5 px-1 text-sm" />
            </div>
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Orden carga</label>
                <x-text-input type="text" name="items[__INDEX__][referencia_orden_carga]"
                    class="w-full py-1.5 px-1 text-sm" />
            </div>
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">P. Recepción</label>
                <x-text-input type="text" name="items[__INDEX__][referencia_recepcion]"
                    class="w-full py-1.5 px-1 text-sm" />
            </div>
            <div>
                <label class="font-medium text-gray-600 dark:text-gray-400 block mb-0.5 text-xs">Valor</label>
                <x-text-input type="number" step="0.01" name="items[__INDEX__][monto_valor_declarado]" value="0"
                    class="w-full py-1.5 px-1 text-sm" />
            </div>
            <div class="flex items-end">
                <button type="button"
                    class="remove-item bg-red-600 hover:bg-red-700 text-white px-2 py-1.5 rounded font-medium whitespace-nowrap w-full text-sm">✕</button>
            </div>
        </div>
    </div>
</template>