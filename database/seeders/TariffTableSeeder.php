<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TariffTableSeeder extends Seeder
{
    /**
     * Carga los 4 cuadros tarifarios reales con sus tramos de peso.
     *
     * Cuadros incluidos:
     *   1. Buenos Aires → Mendoza Este
     *   2. Buenos Aires → Mendoza Sur
     *   3. Buenos Aires (Cap. Fed.) → Mendoza  ← cuadro distinto a Zona Este
     *   4. Mendoza → Buenos Aires (Cap. Fed.)
     *
     * Los precios de los tramos están en pesos argentinos tal como figuran
     * en el cuadro tarifario oficial proporcionado.
     *
     * AVISO: Este seeder usa truncate antes de insertar para evitar duplicados
     * si se ejecuta más de una vez. No borrar si hay datos de producción.
     */
    public function run(): void
    {
        // Limpia primero para evitar duplicados en re-ejecuciones
        // Deshabilitamos FK checks para poder truncar en orden sin violar constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('tariff_brackets')->truncate();
        DB::table('tariff_tables')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Fecha de vigencia de los cuadros — sin fecha de vencimiento (null)
        $validFrom = Carbon::create(2026, 1, 1)->toDateString();

        // ════════════════════════════════════════════════════════════════════
        // CUADRO 1: Buenos Aires → Mendoza Este
        // Tarifa por tonelada (+1000 kg): $145.031 / Ton
        // Aforo por M3: $60.540 / M3
        // ════════════════════════════════════════════════════════════════════
        $tablaBAMzaEste = DB::table('tariff_tables')->insertGetId([
            'name'         => 'Buenos Aires → Mendoza Este',
            'origin'       => 'Buenos Aires',
            'destination'  => 'Mendoza Este',
            'rate_per_ton' => 145031.00,  // $145.031 por tonelada
            'rate_per_m3'  => 60540.00,   // $60.540 por M3
            'valid_from'   => $validFrom,
            'valid_until'  => null,
            'is_active'    => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->insertBrackets($tablaBAMzaEste, [
            [1,   20,  16649.00],
            [21,  30,  18442.00],
            [31,  40,  20461.00],
            [41,  50,  22750.00],
            [51,  60,  25246.00],
            [61,  70,  28015.00],
            [71,  80,  31382.00],
            [81,  90,  35471.00],
            [91,  100, 39987.00],
            [101, 150, 48782.00],
            [151, 180, 55839.00],
            [181, 200, 66940.00],
            [201, 250, 72964.00],
            [251, 300, 85959.00],
            [301, 400, 95192.00],
            [401, 500, 107118.00],
            [501, 600, 118704.00],
            [601, 750, 125783.00],
            [751, 999, 133382.00],
        ]);

        // ════════════════════════════════════════════════════════════════════
        // CUADRO 2: Buenos Aires → Mendoza Sur
        // Tarifa por tonelada (+1000 kg): $178.328 / Ton
        // Aforo por M3: $77.850 / M3
        // ════════════════════════════════════════════════════════════════════
        $tablaBAMzaSur = DB::table('tariff_tables')->insertGetId([
            'name'         => 'Buenos Aires → Mendoza Sur',
            'origin'       => 'Buenos Aires',
            'destination'  => 'Mendoza Sur',
            'rate_per_ton' => 178328.00,  // $178.328 por tonelada
            'rate_per_m3'  => 77850.00,   // $77.850 por M3
            'valid_from'   => $validFrom,
            'valid_until'  => null,
            'is_active'    => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->insertBrackets($tablaBAMzaSur, [
            [1,   20,  21416.00],
            [21,  30,  23721.00],
            [31,  40,  26318.00],
            [41,  50,  29262.00],
            [51,  60,  32473.00],
            [61,  70,  36035.00],
            [71,  80,  40367.00],
            [81,  90,  45627.00],
            [91,  100, 51434.00],
            [101, 150, 62751.00],
            [151, 180, 71754.00],
            [181, 200, 86104.00],
            [201, 250, 93542.00],
            [251, 300, 108875.00],
            [301, 400, 122444.00],
            [401, 500, 137787.00],
            [501, 600, 152690.00],
            [601, 750, 161794.00],
            [751, 999, 171568.00],
        ]);

        // ════════════════════════════════════════════════════════════════════
        // CUADRO 3: Buenos Aires (Cap. Fed.) → Mendoza
        // Tarifa diferente a "Zona Este" — servicio desde Capital Federal
        // Tarifa por tonelada (+1000 kg): $120.617 / Ton
        // Aforo por M3: $52.630 / M3
        // ════════════════════════════════════════════════════════════════════
        $tablaBACapFedMza = DB::table('tariff_tables')->insertGetId([
            'name'         => 'Buenos Aires (Cap. Fed.) → Mendoza',
            'origin'       => 'Buenos Aires (Cap. Fed.)',
            'destination'  => 'Mendoza',
            'rate_per_ton' => 120617.00,  // $120.617 por tonelada
            'rate_per_m3'  => 52630.00,   // $52.630 por M3
            'valid_from'   => $validFrom,
            'valid_until'  => null,
            'is_active'    => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->insertBrackets($tablaBACapFedMza, [
            [1,   20,  14465.00],
            [21,  30,  16056.00],
            [31,  40,  17823.00],
            [41,  50,  19783.00],
            [51,  60,  21960.00],
            [61,  70,  24376.00],
            [71,  80,  27300.00],
            [81,  90,  30559.00],
            [91,  100, 34772.00],
            [101, 150, 42421.00],
            [151, 180, 48509.00],
            [181, 200, 58211.00],
            [201, 250, 63450.00],
            [251, 300, 73602.00],
            [301, 400, 82783.00],
            [401, 500, 93188.00],
            [501, 600, 103246.00],
            [601, 750, 109441.00],
            [751, 999, 115978.00],
        ]);

        // ════════════════════════════════════════════════════════════════════
        // CUADRO 4: Mendoza → Buenos Aires (Cap. Fed.)
        // Tarifa por tonelada (+1000 kg): $150.690 / Ton
        // Aforo por M3: $65.785 / M3
        // ════════════════════════════════════════════════════════════════════
        $tablaMzaBA = DB::table('tariff_tables')->insertGetId([
            'name'         => 'Mendoza → Buenos Aires',
            'origin'       => 'Mendoza',
            'destination'  => 'Buenos Aires',
            'rate_per_ton' => 150690.00,  // $150.690 por tonelada
            'rate_per_m3'  => 65785.00,   // $65.785 por M3
            'valid_from'   => $validFrom,
            'valid_until'  => null,
            'is_active'    => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->insertBrackets($tablaMzaBA, [
            [1,   20,  18097.00],
            [21,  30,  20045.00],
            [31,  40,  22241.00],
            [41,  50,  24727.00],
            [51,  60,  27441.00],
            [61,  70,  30451.00],
            [71,  80,  34113.00],
            [81,  90,  38553.00],
            [91,  100, 43463.00],
            [101, 150, 51526.00],
            [151, 180, 60634.00],
            [181, 200, 72761.00],
            [201, 250, 79308.00],
            [251, 300, 92004.00],
            [301, 400, 103470.00],
            [401, 500, 116435.00],
            [501, 600, 129026.00],
            [601, 750, 136720.00],
            [751, 999, 144979.00],
        ]);

        $this->command->info('✅ TariffTableSeeder: 4 cuadros tarifarios y 76 tramos cargados correctamente.');
    }

    /**
     * Inserta los tramos de peso (brackets) para un cuadro tarifario.
     *
     * @param  int    $tariffTableId  ID del cuadro tarifario padre
     * @param  array  $brackets       Array de [weight_from, weight_to, rate]
     */
    private function insertBrackets(int $tariffTableId, array $brackets): void
    {
        $rows = array_map(fn($b) => [
            'tariff_table_id' => $tariffTableId,
            'weight_from'     => $b[0],
            'weight_to'       => $b[1],
            'rate'            => $b[2],
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $brackets);

        DB::table('tariff_brackets')->insert($rows);
    }
}
