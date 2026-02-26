<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    protected $fillable = [
        'shipment_id',
        'tipo_paquete',
        'cantidad',
        'numero_remito',
        'peso',
        'volumen',
        'monto_valor_declarado',
        'monto_seguro_item',
        'referencia_recepcion',
        'referencia_orden_carga'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}