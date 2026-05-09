<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PartyTariffSetting;
use App\Models\Shipment;
use App\Models\TariffBracket;
use App\Models\TariffTable;

/**
 * GuiaImporteService
 *
 * Calcula el importe del flete de una guía (Shipment) en base al
 * cuadro tarifario activo y a la configuración particular del cliente.
 *
 * REGLA FUNDAMENTAL:
 *   Si el cliente (remitente o destinatario) NO tiene una configuración
 *   tarifaria activa en party_tariff_settings → NO se calcula nada.
 *   El importe del flete queda en 0 y debe completarse manualmente.
 *
 * MODOS DE FACTURACIÓN:
 *   'kg'              → busca el tramo de peso en tariff_brackets
 *   'tonelada'        → rate_per_ton_custom * (peso_total / 1000)
 *   'volumen'         → rate_per_m3_custom * volumen_total_m3
 *   'bultos'          → rate_per_bulto * total_bultos
 *   'pallets'         → rate_per_pallet * total_pallets
 *   'valor_declarado' → rate por mil ( / 1000 ) del valor_declarado_total
 *
 * En todos los modos se aplica el minimum_charge si el resultado es menor.
 */
class GuiaImporteService
{
    /**
     * Calcula el importe de flete para la guía dada.
     *
     * Retorna un array con el desglose del cálculo o null si no hay
     * configuración tarifaria activa para el cliente.
     *
     * @param  Shipment  $shipment  La guía con sus items cargados
     * @param  int  $tariffTableId  ID del cuadro tarifario a usar (según la ruta)
     * @return array|null Array con el desglose, o null si no hay tarifa aplicable
     */
    public function calcular(Shipment $shipment, int $tariffTableId): ?array
    {
        // ── 1. Buscar la configuración tarifaria del cliente ──────────────
        //
        // Se busca por el remitente. Si el remitente no tiene configuración,
        // se intenta con el destinatario.
        // Si ninguno tiene configuración → retorna null (sin cálculo).
        $setting = $this->resolverSetting($shipment, $tariffTableId);

        if ($setting === null) {
            // El cliente no tiene configuración tarifaria activa
            // El flete debe ingresarse manualmente en la guía
            return null;
        }

        // ── 2. Cargar el cuadro tarifario base ───────────────────────────
        $tariffTable = TariffTable::find($tariffTableId);

        if ($tariffTable === null || ! $tariffTable->is_active) {
            return null;
        }

        // ── 3. Calcular totales de los ítems de la guía ──────────────────
        $shipment->loadMissing('items');

        $totalPesoKg = $shipment->items->sum('peso');         // Kg totales
        $totalVolumenM3 = $shipment->items->sum('volumen');       // M3 totales
        $totalBultos = $shipment->items
            ->where('tipo_paquete', 'bultos')
            ->sum('cantidad');
        $totalPallets = $shipment->items
            ->where('tipo_paquete', 'palets')
            ->sum('cantidad');
        $totalValorDeclarado = $shipment->items->sum('monto_valor_declarado');

        // ── 4. Calcular el flete según el modo de facturación ────────────
        $importeCalculado = match ($setting->billing_mode) {

            // POR KG: busca el tramo correspondiente al peso total
            'kg' => $this->calcularPorKg($tariffTable->id, $totalPesoKg),

            // POR TONELADA: precio por tonelada * (peso / 1000)
            'tonelada' => $this->calcularPorTonelada(
                $setting->rate_per_ton_custom ?? $tariffTable->rate_per_ton,
                $totalPesoKg
            ),

            // POR VOLUMEN: precio por M3 * volumen total
            'volumen' => $this->calcularPorVolumen(
                $setting->rate_per_m3_custom ?? $tariffTable->rate_per_m3,
                $totalVolumenM3
            ),

            // POR BULTOS: precio por bulto * cantidad de bultos
            'bultos' => $this->calcularPorUnidad(
                (float) ($setting->rate_per_bulto ?? 0),
                $totalBultos
            ),

            // POR PALLETS: precio por pallet * cantidad de pallets
            'pallets' => $this->calcularPorUnidad(
                (float) ($setting->rate_per_pallet ?? 0),
                $totalPallets
            ),

            // POR BULTOS + PALLETS: suma ambos (únicos modos combinables)
            // Cada uno aplica su propio mínimo antes de sumar.
            'bultos_pallets' => (function () use ($setting, $totalBultos, $totalPallets): float {
                // Importe por bultos con su mínimo propio
                $bultoImporte = $this->calcularPorUnidad((float) ($setting->rate_per_bulto ?? 0), $totalBultos);
                $minBulto = (float) ($setting->minimum_per_bulto ?? 0);
                $bultoFinal = ($minBulto > 0 && $bultoImporte < $minBulto) ? $minBulto : $bultoImporte;

                // Importe por pallets con su mínimo propio
                $palletImporte = $this->calcularPorUnidad((float) ($setting->rate_per_pallet ?? 0), $totalPallets);
                $minPallet = (float) ($setting->minimum_per_pallet ?? 0);
                $palletFinal = ($minPallet > 0 && $palletImporte < $minPallet) ? $minPallet : $palletImporte;

                return $bultoFinal + $palletFinal;
            })(),

            // POR VALOR DECLARADO: cálculo por mil del valor declarado total
            'valor_declarado' => $this->calcularPorValorDeclarado(
                (float) ($setting->declared_value_pct ?? 0),
                $totalValorDeclarado
            ),

            default => 0.0,
        };

        // ── 5. Aplicar importe mínimo si corresponde ─────────────────────
        $importeFinal = $importeCalculado;

        if ($setting->minimum_charge !== null && $importeCalculado < (float) $setting->minimum_charge) {
            $importeFinal = (float) $setting->minimum_charge;
        }

        // ── 6. Retornar desglose completo del cálculo ────────────────────
        return [
            // Datos del cliente y modo
            'party_id' => $setting->party_id,
            'billing_mode' => $setting->billing_mode,
            'billing_mode_label' => $setting->billing_mode_label,

            // Datos usados en el cálculo
            'total_peso_kg' => $totalPesoKg,
            'total_volumen_m3' => $totalVolumenM3,
            'total_bultos' => $totalBultos,
            'total_pallets' => $totalPallets,
            'total_valor_declarado' => $totalValorDeclarado,

            // Resultado
            'importe_calculado' => round($importeCalculado, 2),
            'minimum_charge' => $setting->minimum_charge ? (float) $setting->minimum_charge : null,
            'importe_final' => round($importeFinal, 2),

            // Cuadro tarifario usado
            'tariff_table_id' => $tariffTable->id,
            'tariff_table_name' => $tariffTable->name,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Métodos privados de cálculo
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Busca la configuración tarifaria activa del remitente o destinatario.
     * Prioridad: remitente → destinatario.
     * NO filtra por tariff_table_id: el cuadro se determina en el paso siguiente
     * usando el origen/destino de la guía.
     */
    private function resolverSetting(Shipment $shipment, int $tariffTableId): ?PartyTariffSetting
    {
        $payerId = null;

        if ($shipment->flete_a_pagar_en === 'destino') {
            $payerId = $shipment->destinatario_id;
        } else {
            // Default to origen
            $payerId = $shipment->remitente_id;
        }

        if ($payerId) {
            return PartyTariffSetting::where('party_id', $payerId)
                ->active()
                ->first();
        }

        return null;
    }

    /**
     * Calcula el flete buscando el tramo de peso correspondiente.
     * Si el peso >= 1000 kg, usa la tarifa por tonelada del cuadro general.
     *
     * @param  int  $tariffTableId  ID del cuadro tarifario
     * @param  float  $pesoKg  Peso total de la guía en kg
     */
    private function calcularPorKg(int $tariffTableId, float $pesoKg): float
    {
        // Para pesos >= 1000 kg se usa la tarifa por tonelada del cuadro
        if ($pesoKg >= 1000) {
            $tariffTable = TariffTable::find($tariffTableId);

            return $this->calcularPorTonelada((float) $tariffTable->rate_per_ton, $pesoKg);
        }

        // Buscar el tramo de la escala que contenga el peso
        $bracket = TariffBracket::where('tariff_table_id', $tariffTableId)
            ->where('weight_from', '<=', (int) ceil($pesoKg))
            ->where('weight_to', '>=', (int) ceil($pesoKg))
            ->first();

        if ($bracket === null) {
            // No se encontró tramo → retornar 0 (flete manual)
            return 0.0;
        }

        return (float) $bracket->rate;
    }

    /**
     * Calcula el flete por tonelada.
     * Fórmula: rate_per_ton * (peso_kg / 1000)
     */
    private function calcularPorTonelada(float $ratePerTon, float $pesoKg): float
    {
        if ($pesoKg <= 0 || $ratePerTon <= 0) {
            return 0.0;
        }

        return $ratePerTon * ($pesoKg / 1000);
    }

    /**
     * Calcula el flete por volumen (M3).
     * Fórmula: rate_per_m3 * volumen_m3
     */
    private function calcularPorVolumen(float $ratePerM3, float $volumenM3): float
    {
        if ($volumenM3 <= 0 || $ratePerM3 <= 0) {
            return 0.0;
        }

        return $ratePerM3 * $volumenM3;
    }

    /**
     * Calcula el flete por unidad (bultos o pallets).
     * Fórmula: rate_per_unidad * cantidad
     */
    private function calcularPorUnidad(float $ratePerUnit, int|float $cantidad): float
    {
        if ($cantidad <= 0 || $ratePerUnit <= 0) {
            return 0.0;
        }

        return $ratePerUnit * $cantidad;
    }

    /**
     * Calcula el flete sobre el valor declarado (cálculo por ciento).
     * Fórmula: valor_declarado * (tasa / 100)
     *
     * @param  float  $tasa  Tasa por ciento (ej: 1 = 1% del valor)
     */
    private function calcularPorValorDeclarado(float $tasa, float $valorDeclarado): float
    {
        if ($valorDeclarado <= 0 || $tasa <= 0) {
            return 0.0;
        }

        return $valorDeclarado * ($tasa / 100);
    }
}
