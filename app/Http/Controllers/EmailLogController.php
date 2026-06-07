<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SendShipmentEmailJob;
use App\Models\EmailLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    public function index(): View
    {
        $userCompanies = auth()->user()->companies;
        $emailsEnabled = Setting::get('email_notifications_enabled', '1') === '1';

        return view('email_logs.index', compact('userCompanies', 'emailsEnabled'));
    }

    public function stats(Request $request): JsonResponse
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : null;

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : null;

        $company_ids = auth()->user()->companies->pluck('id')->toArray();
        $selected_company_id = $request->input('company_id');

        $query = EmailLog::query();

        if ($selected_company_id && in_array((int)$selected_company_id, $company_ids)) {
            $query->where('company_id', $selected_company_id);
        } else {
            $query->whereIn('company_id', $company_ids);
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $selected_status = $request->input('status');
        if ($selected_status) {
            $query->where('status', $selected_status);
        }

        // KPIs
        $total = (clone $query)->count();
        $sent = (clone $query)->where('status', 'sent')->count();
        $failed = (clone $query)->where('status', 'failed')->count();
        $pending = (clone $query)->whereIn('status', ['pending', 'sending'])->count();

        // Chart Status Breakdown
        $statusBreakdown = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusBreakdown = array_merge([
            'sent' => 0,
            'failed' => 0,
            'pending' => 0,
            'sending' => 0,
        ], $statusBreakdown);

        // Chart Stage Breakdown (Horizontal Bar)
        $stageBreakdown = (clone $query)
            ->select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->orderByDesc('total')
            ->get()
            ->map(fn($log) => [
                'stage' => $log->stage,
                'total' => $log->total,
            ])
            ->toArray();

        // Chart Daily Email Volume over the range
        $dailyFrom = $from ?? now()->subDays(29)->startOfDay();
        $dailyTo = $to ?? now()->endOfDay();

        $dailyVolume = (clone $query)
            ->whereBetween('created_at', [$dailyFrom, $dailyTo])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $dailyChartLabels = [];
        $dailyChartData = [];
        $currentDate = $dailyFrom->copy();
        while ($currentDate->lte($dailyTo)) {
            $dayStr = $currentDate->format('Y-m-d');
            $dailyChartLabels[] = $currentDate->format('d/m');
            $dailyChartData[] = $dailyVolume[$dayStr] ?? 0;
            $currentDate->addDay();
        }

        return response()->json([
            'kpi' => [
                'total' => $total,
                'sent' => $sent,
                'failed' => $failed,
                'pending' => $pending,
            ],
            'chart_status' => $statusBreakdown,
            'chart_stages' => $stageBreakdown,
            'chart_daily' => [
                'labels' => $dailyChartLabels,
                'data' => $dailyChartData,
            ],
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : null;

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : null;

        $company_ids = auth()->user()->companies->pluck('id')->toArray();
        $selected_company_id = $request->input('company_id');

        $query = EmailLog::query()
            ->with(['shipment', 'company', 'party']);

        if ($selected_company_id && in_array((int)$selected_company_id, $company_ids)) {
            $query->where('company_id', $selected_company_id);
        } else {
            $query->whereIn('company_id', $company_ids);
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $selected_status = $request->input('status');
        if ($selected_status) {
            $query->where('status', $selected_status);
        }

        $logs = $query->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($logs);
    }

    public function resend(EmailLog $emailLog): JsonResponse
    {
        $company_ids = auth()->user()->companies->pluck('id')->toArray();
        if (!in_array($emailLog->company_id, $company_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado.',
            ], 403);
        }

        $emailLog->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        SendShipmentEmailJob::dispatch($emailLog);

        return response()->json([
            'success' => true,
            'message' => 'Correo encolado para reenvío.',
        ]);
    }

    public function toggleEmails(Request $request): JsonResponse
    {
        $enabled = $request->input('enabled') ? '1' : '0';
        Setting::set('email_notifications_enabled', $enabled);

        $msg = $enabled === '1' 
            ? 'Envío de correos habilitado globalmente.' 
            : 'Envío de correos deshabilitado globalmente.';

        return response()->json([
            'success' => true,
            'message' => $msg,
            'enabled' => $enabled === '1',
        ]);
    }
}
