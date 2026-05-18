<?php

namespace App\Http\Controllers;

use App\Actions\Invoice\AssignShipmentsToInvoiceAction;
use App\Actions\Invoice\DetachShipmentsFromInvoiceAction;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Shipment;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly AssignShipmentsToInvoiceAction $assignShipments,
        private readonly DetachShipmentsFromInvoiceAction $detachShipments,
    ) {}

    // -------------------------------------------------------------------------
    // Listado (pivotado sobre Guias)
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $companies = Company::active()->orderBy('name')->get();

        return view('billing.index', compact('companies'));
    }

    /**
     * DataTable del indice de facturacion.
     *
     * Pivotamos sobre Shipment con LEFT JOIN a invoices para que aparezcan
     * TODAS las guias (facturadas o no) y se pueda filtrar por cliente
     * (remitente O destinatario) sin depender de que exista una invoice.
     */
    public function datatable(Request $request): mixed
    {
        $query = Shipment::withoutGlobalScopes()
            ->leftJoin('invoices', function ($join) {
                $join->on('invoices.id', '=', 'shipments.invoice_id')
                    ->whereNull('invoices.deleted_at');
            })
            ->with(['sender', 'recipient'])
            ->select(
                'shipments.*',
                'invoices.numero        as invoice_numero',
                'invoices.fecha_factura as invoice_fecha',
            );

        if ($request->filled('start_date')) {
            $query->whereDate('shipments.fecha', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('shipments.fecha', '<=', $request->end_date);
        }
        if ($request->filled('company_id')) {
            $query->where('shipments.company_id', $request->company_id);
        }
        if ($request->filled('party_id')) {
            // El cliente puede ser remitente O destinatario de la guia
            $partyId = (int) $request->party_id;
            $query->where(function ($q) use ($partyId) {
                $q->where('shipments.remitente_id', $partyId)
                    ->orWhere('shipments.destinatario_id', $partyId);
            });
        }
        if ($request->filled('numero')) {
            $query->where('shipments.numero', 'like', '%'.$request->numero.'%');
        }
        // Filtro: numero de factura asociada
        if ($request->filled('invoice_numero')) {
            $query->where('invoices.numero', 'like', '%'.$request->invoice_numero.'%');
        }
        // Filtro: estado de facturacion (1=con factura, 0=sin factura)
        if ($request->filled('facturada') && in_array($request->facturada, ['1', '0'])) {
            if ($request->facturada === '1') {
                $query->whereNotNull('shipments.invoice_id');
            } else {
                $query->whereNull('shipments.invoice_id');
            }
        }
        // Filtro: cobrada
        if ($request->filled('cobrada') && in_array($request->cobrada, ['1', '0'])) {
            $query->where('shipments.cobrada', $request->cobrada === '1');
        }

        return DataTables::of($query->orderByDesc('shipments.fecha'))
            ->editColumn('fecha', fn ($row) => $row->fecha?->format('d/m/Y') ?? '-')
            ->addColumn('sender_name', fn ($row) => $row->sender?->name ?? '-')
            ->addColumn('recipient_name', fn ($row) => $row->recipient?->name ?? '-')
            ->addColumn('invoice_badge', function (Shipment $row): string {
                if ($row->invoice_id) {
                    return '<a href="'.route('billing.show', $row->invoice_id).'"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200">'
                        .($row->invoice_numero ?? '#').'</a>';
                }

                return '<span class="text-gray-400 text-xs italic">Sin factura</span>';
            })
            ->editColumn('cobrada', fn ($row) => $row->cobrada
                ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Cobrada</span>'
                : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pendiente</span>'
            )
            ->editColumn('total', fn ($row) => '$ '.number_format($row->total, 2, ',', '.'))
            ->addColumn('selection', function (Shipment $row): string {
                // If it already has an invoice_id, we disable the checkbox
                $disabled = $row->invoice_id ? 'disabled' : '';

                return '<input type="checkbox" class="row-select w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                    value="'.$row->id.'" data-total="'.$row->total.'" '.$disabled.'>';
            })
            ->addColumn('actions', function (Shipment $row): string {
                $html = '<div class="flex items-center gap-2">';
                if ($row->invoice_id) {
                    $html .= '<a href="'.route('billing.show', $row->invoice_id).'" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">Ver Factura</a>';
                }
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['selection', 'invoice_badge', 'cobrada', 'actions'])
            ->make(true);
    }

    // -------------------------------------------------------------------------
    // Listado de Facturas (Agrupadas)
    // -------------------------------------------------------------------------

    public function invoicesIndex(): View
    {
        $companies = Company::active()->orderBy('name')->get();

        return view('billing.invoices', compact('companies'));
    }

    public function invoicesDatatable(Request $request): mixed
    {
        $query = Invoice::withoutGlobalScopes()
            ->with(['party'])
            ->selectRaw('
                invoices.*,
                (SELECT COUNT(*) FROM shipments
                    WHERE shipments.invoice_id = invoices.id
                    AND shipments.deleted_at IS NULL) as shipments_count
            ');

        if ($request->filled('start_date')) {
            $query->whereDate('invoices.fecha_factura', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoices.fecha_factura', '<=', $request->end_date);
        }
        if ($request->filled('company_id')) {
            $query->where('invoices.company_id', $request->company_id);
        }
        if ($request->filled('party_id')) {
            $query->where('invoices.party_id', $request->party_id);
        }
        if ($request->filled('numero')) {
            $query->where('invoices.numero', 'like', '%'.$request->numero.'%');
        }
        // Filtro: cobrada
        if ($request->filled('cobrada') && in_array($request->cobrada, ['1', '0'])) {
            $query->where('invoices.cobrada', $request->cobrada === '1');
        }

        return DataTables::of($query->orderByDesc('invoices.fecha_factura'))
            ->editColumn('fecha_factura', fn ($row) => $row->fecha_factura?->format('d/m/Y') ?? '-')
            ->addColumn('party_name', fn ($row) => $row->party?->name ?? '-')
            ->addColumn('shipments_count', fn ($row) => $row->shipments_count ?? 0)
            ->editColumn('cobrada', fn ($row) => $row->cobrada
                ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Cobrada</span>'
                : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pendiente</span>'
            )
            ->editColumn('total', fn ($row) => '$ '.number_format($row->total, 2, ',', '.'))
            ->addColumn('actions', function (Invoice $row): string {
                $html = '<div class="flex items-center gap-2">';
                $html .= '<a href="'.route('billing.show', $row->id).'" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">Ver Factura</a>';
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['cobrada', 'actions'])
            ->make(true);
    }

    // -------------------------------------------------------------------------
    // Creacion de facturas
    // -------------------------------------------------------------------------

    public function create(): View
    {
        $companies = Company::active()->orderBy('name')->get();

        return view('billing.create', compact('companies'));
    }

    /**
     * DataTable AJAX de guias disponibles para facturar.
     * Muestra TODAS las guias (incluso ya facturadas -- el admin puede re-facturar).
     */
    public function availableShipments(Request $request): mixed
    {
        $query = Shipment::withoutGlobalScopes()
            ->with(['sender', 'recipient', 'invoice'])
            ->select('shipments.*');

        // Filtros del panel de seleccion
        if ($request->filled('start_date')) {
            $query->whereDate('fecha', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('fecha', '<=', $request->end_date);
        }
        if ($request->filled('company_id')) {
            $query->where('shipments.company_id', $request->company_id);
        }
        if ($request->filled('party_id')) {
            $partyIds = is_array($request->party_id) ? $request->party_id : [$request->party_id];
            $query->where(function ($q) use ($partyIds) {
                $q->whereIn('shipments.remitente_id', $partyIds)
                    ->orWhereIn('shipments.destinatario_id', $partyIds);
            });
        }
        if ($request->filled('numero')) {
            $query->where('shipments.numero', 'like', '%'.$request->numero.'%');
        }

        $isAdmin = auth()->user()?->hasRole('admin');

        return DataTables::of($query->orderByDesc('fecha'))
            ->addColumn('selection', function (Shipment $row) use ($isAdmin): string {
                $disabled = (! $isAdmin && $row->invoice_id) ? 'disabled' : '';
                $title = (! $isAdmin && $row->invoice_id)
                    ? 'title="Guia ya facturada. Solo admin puede re-facturar."'
                    : '';

                return '<input type="checkbox" class="row-select w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                    value="'.$row->id.'" data-total="'.$row->total.'" '.$disabled.' '.$title.'>';
            })
            ->editColumn('fecha', fn ($row) => $row->fecha?->format('d/m/Y') ?? '-')
            ->addColumn('sender_name', fn ($row) => $row->sender?->name ?? '-')
            ->addColumn('recipient_name', fn ($row) => $row->recipient?->name ?? '-')
            ->addColumn('invoice_badge', function (Shipment $row): string {
                if ($row->invoice_id) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">'
                        .$row->invoice?->numero.'</span>';
                }

                return '<span class="text-gray-400 text-xs">-</span>';
            })
            ->editColumn('flete', fn ($row) => '$ '.number_format($row->flete, 2, ',', '.'))
            ->editColumn('seguro', fn ($row) => '$ '.number_format($row->seguro, 2, ',', '.'))
            ->editColumn('monto_contra_reembolso', fn ($row) => '$ '.number_format($row->monto_contra_reembolso, 2, ',', '.'))
            ->editColumn('retencion_mercaderia', fn ($row) => '$ '.number_format($row->retencion_mercaderia, 2, ',', '.'))
            ->editColumn('total', fn ($row) => '$ '.number_format($row->total, 2, ',', '.'))
            ->editColumn('ubicacion_actual', fn ($row) => $row->ubicacion_actual ?? '-')
            ->editColumn('cobrada', fn ($row) => $row->cobrada ? 'Si' : 'No')
            ->rawColumns(['selection', 'invoice_badge'])
            ->make(true);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        try {
            $invoice = $this->invoiceService->generateInvoice(
                shipmentIds: $request->validated('shipment_ids'),
                data: $request->safe()->except(['shipment_ids', 'party_id', 'company_id']),
                partyId: (int) $request->validated('party_id'),
                companyId: (int) $request->validated('company_id'),
                isAdmin: auth()->user()?->hasRole('admin') ?? false,
            );
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['shipment_ids' => $e->getMessage()]);
        }

        return redirect()
            ->route('billing.show', $invoice)
            ->with('success', "Factura #{$invoice->numero} generada con exito.");
    }

    // -------------------------------------------------------------------------
    // Detalle
    // -------------------------------------------------------------------------

    public function show(Invoice $invoice): View
    {
        $invoice->load(['party', 'company', 'shipments.sender', 'shipments.recipient']);

        return view('billing.show', compact('invoice'));
    }

    // -------------------------------------------------------------------------
    // Edicion (solo admin)
    // -------------------------------------------------------------------------

    public function edit(Invoice $invoice): View
    {
        abort_if($invoice->cobrada, 403, 'No se puede editar una factura ya cobrada.');

        $invoice->load(['party', 'shipments']);
        $companies = Company::active()->orderBy('name')->get();

        return view('billing.edit', compact('invoice', 'companies'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->cobrada, 403, 'No se puede editar una factura ya cobrada.');

        try {
            $invoice->update($request->safe()->except(['shipment_ids']));

            if ($request->has('shipment_ids')) {
                $this->assignShipments->execute(
                    $invoice,
                    $request->validated('shipment_ids'),
                    isAdmin: true,
                );
            }
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['shipment_ids' => $e->getMessage()]);
        }

        return redirect()
            ->route('billing.show', $invoice)
            ->with('success', "Factura #{$invoice->numero} actualizada.");
    }

    // -------------------------------------------------------------------------
    // Cobro
    // -------------------------------------------------------------------------

    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->markAsPaid($invoice);

        return redirect()->route('billing.index')->with('success', "Factura #{$invoice->numero} marcada como cobrada. Todas las guias asociadas fueron actualizadas.");
    }
}
