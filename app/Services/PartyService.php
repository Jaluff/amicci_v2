<?php

namespace App\Services;

use App\Models\Party;
use App\Models\PartyTariffSetting;
use Illuminate\Support\Facades\DB;

class PartyService
{
    public function createParty(array $data): Party
    {
        return DB::transaction(function () use ($data) {
            $party = Party::create([
                'name' => $data['name'],
                'document' => $data['document'] ?? null,
                'document_type' => $data['document_type'] ?? null,
                'tax_status' => $data['tax_status'] ?? null,
                'iva_percent' => $data['iva_percent'] ?? 0,
                'has_insurance' => filter_var($data['has_insurance'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'insurance_percent' => $data['insurance_percent'] ?? null,
                'phone' => $data['phone'] ?? null,
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            if (!empty($data['addresses'])) {
                $this->syncAddresses($party, $data['addresses']);
            }

            if (!empty($data['tariff']['billing_mode'])) {
                $this->saveTariffSetting($party, $data['tariff']);
            }

            return $party;
        });
    }

    public function updateParty(Party $party, array $data): Party
    {
        return DB::transaction(function () use ($party, $data) {
            $party->update([
                'name' => $data['name'],
                'document' => $data['document'] ?? null,
                'document_type' => $data['document_type'] ?? null,
                'tax_status' => $data['tax_status'] ?? null,
                'iva_percent' => $data['iva_percent'] ?? 0,
                'has_insurance' => filter_var($data['has_insurance'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'insurance_percent' => $data['insurance_percent'] ?? null,
                'phone' => $data['phone'] ?? null,
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            if (isset($data['addresses'])) {
                $this->syncAddresses($party, $data['addresses']);
            } else {
                // If it was skipped completely, might mean "clear all" or just no addresses supplied.
                // Keeping existing behavior: if 'addresses' is totally missing, it won't delete them.
            }

            // Actualizar configuración tarifaria
            if (!empty($data['tariff']['billing_mode'])) {
                $this->saveTariffSetting($party, $data['tariff']);
            } elseif (isset($data['tariff'])) {
                // Si viene el array tariff pero sin billing_mode, es porque se desmarcó el checkbox
                $party->tariffSettings()->delete();
            }

            return $party;
        });
    }

    public function ajaxCreateParty(array $data): Party
    {
        return DB::transaction(function () use ($data) {
            $party = Party::create([
                'name' => $data['name'],
                'document_type' => $data['document_type'] ?? null,
                'document' => $data['document'] ?? null,
                'tax_status' => $data['tax_status'] ?? null,
                'iva_percent' => $data['iva_percent'] ?? 0,
                'has_insurance' => filter_var($data['has_insurance'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'insurance_percent' => $data['insurance_percent'] ?? null,
                // Assigning phone and email directly to the model instead of creating a dummy address.
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            // No se guarda la dirección cuando se da de alta por el modal.
            return $party;
        });
    }

    private function syncAddresses(Party $party, array $addressesData): void
    {
        $hasPrimary = collect($addressesData)->contains('is_primary', true);
        $existingAddressesIds = [];

        foreach ($addressesData as $index => $addrData) {
            // Ignorar direcciones vacías que el frontend puede enviar por error
            if (empty($addrData['address_line1']) && empty($addrData['city'])) {
                continue;
            }

            $isPrimary = $hasPrimary ? !empty($addrData['is_primary']) : ($index === 0);

            $address = $party->addresses()->updateOrCreate(
                ['id' => $addrData['id'] ?? null],
                [
                    'type' => $addrData['type'] ?? 'Sucursal',
                    'address_line1' => $addrData['address_line1'] ?? null,
                    'city' => $addrData['city'] ?? null,
                    'state' => $addrData['state'] ?? null,
                    'zip_code' => $addrData['zip_code'] ?? null,
                    'phone' => $addrData['phone'] ?? null,
                    'email' => $addrData['email'] ?? null,
                    'is_primary' => $isPrimary,
                ]
            );

            $existingAddressesIds[] = $address->id;
        }

        // Eliminar las ausentes que pertenecían antes a este cliente
        $party->addresses()->whereNotIn('id', $existingAddressesIds)->delete();
    }

    private function saveTariffSetting(Party $party, array $tariffData): void
    {
        $mode = $tariffData['billing_mode'] ?? '';
        $finalMode = ($mode === 'bultos_pallets') ? 'bultos_pallets' : $mode;

        PartyTariffSetting::updateOrCreate(
            ['party_id' => $party->id],
            [
                'tariff_table_id' => null,
                'billing_mode' => $finalMode,
                'minimum_charge' => ($tariffData['minimum_charge'] ?? null) ?: null,
                'rate_per_ton_custom' => ($tariffData['rate_per_ton_custom'] ?? null) ?: null,
                'rate_per_m3_custom' => ($tariffData['rate_per_m3_custom'] ?? null) ?: null,
                'rate_per_bulto' => ($tariffData['rate_per_bulto'] ?? null) ?: null,
                'minimum_per_bulto' => ($tariffData['minimum_per_bulto'] ?? null) ?: null,
                'rate_per_pallet' => ($tariffData['rate_per_pallet'] ?? null) ?: null,
                'minimum_per_pallet' => ($tariffData['minimum_per_pallet'] ?? null) ?: null,
                'declared_value_pct' => ($tariffData['declared_value_pct'] ?? null) ?: null,
                'valid_from' => $tariffData['valid_from'] ?? null,
                'valid_until' => ($tariffData['valid_until'] ?? null) ?: null,
                'notes' => ($tariffData['notes'] ?? null) ?: null,
            ]
        );
    }
}
