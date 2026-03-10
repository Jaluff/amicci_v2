<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateAddressesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amicci:migrate-addresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Migrando direcciones de Companies...');
        \App\Models\Company::all()->each(function ($company) {
            if ($company->address_line1 || $company->phone || $company->email) {
                $company->addresses()->create([
                    'type' => 'Principal',
                    'address_line1' => $company->address_line1 ?? 'Sin dirección',
                    'address_line2' => $company->address_line2,
                    'phone' => $company->phone,
                    'email' => $company->email,
                    'is_primary' => true,
                ]);
            }
        });

        $this->info('Migrando direcciones de Parties...');
        \App\Models\Party::all()->each(function ($party) {
            if ($party->address || $party->phone || $party->email) {
                // locality/province to state?
                $party->addresses()->create([
                    'type' => 'Principal',
                    'address_line1' => $party->address ?? 'Sin dirección',
                    'city' => $party->city ?? $party->locality,
                    'state' => $party->province,
                    'zip_code' => $party->postal_code,
                    'phone' => $party->phone . ($party->phone_secondary ? ' / ' . $party->phone_secondary : ''),
                    'email' => $party->email,
                    'is_primary' => true,
                ]);
            }
        });

        $this->info('Migrando direcciones de Drivers...');
        \App\Models\Driver::all()->each(function ($driver) {
            if ($driver->address || $driver->phone) {
                $driver->addresses()->create([
                    'type' => 'Principal',
                    'address_line1' => $driver->address ?? 'Sin dirección',
                    'phone' => $driver->phone,
                    'is_primary' => true,
                ]);
            }
        });

        $this->info('Migrando direcciones de Deliverers...');
        \App\Models\Deliverer::all()->each(function ($deliverer) {
            if ($deliverer->address || $deliverer->phone || $deliverer->email) {
                $deliverer->addresses()->create([
                    'type' => 'Principal',
                    'address_line1' => $deliverer->address ?? 'Sin dirección',
                    'phone' => $deliverer->phone,
                    'email' => $deliverer->email,
                    'is_primary' => true,
                ]);
            }
        });

        $this->info('¡Migración de direcciones completada!');
    }
}