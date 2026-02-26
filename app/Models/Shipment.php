<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use SoftDeletes;

    protected $casts = [
        'fecha' => 'date',
        'fecha_entrega' => 'date',
        'cobrada' => 'boolean',
        'contra_reembolso' => 'boolean',
        'rendida' => 'boolean',
    ];

    protected $fillable = [
        // Spanish field names (primary)
        'numero', 'fecha', 'origen_id', 'destino_id', 'remitente_id', 'destinatario_id',
        'tipo_flete', 'cobrada', 'contra_reembolso', 'rendida',
        'numero_factura', 'flete_a_pagar_en', 'ubicacion_id', 'fecha_entrega', 'turno_entrega',
        'ubicacion_actual', 'estado_facturacion', 'route_sheet_id',
        'flete', 'seguro', 'monto_contra_reembolso', 'retencion_mercaderia', 'otros_cargos',
        'subtotal', 'iva_monto', 'total', 'notas',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            $model->company_id = session('company_id');
        });
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function origin()
    {
        return $this->belongsTo(Ubicacion::class, 'origen_id');
    }

    public function destination()
    {
        return $this->belongsTo(Ubicacion::class, 'destino_id');
    }

    public function sender()
    {
        return $this->belongsTo(Party::class, 'remitente_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Party::class, 'destinatario_id');
    }
}