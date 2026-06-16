<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\Setting;
use App\Mail\GroupedShipmentsStatusNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendGroupedShipmentsEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $emailLogIds;

    /**
     * Create a new job instance.
     *
     * @param array $emailLogIds
     */
    public function __construct(array $emailLogIds)
    {
        $this->emailLogIds = $emailLogIds;
        $this->afterCommit = true;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if global email sending is enabled
        $isEnabled = Setting::get('email_notifications_enabled', '1');
        if ($isEnabled !== '1' && $isEnabled !== true && $isEnabled !== 1) {
            EmailLog::whereIn('id', $this->emailLogIds)->update([
                'status' => 'failed',
                'error_message' => 'Envío omitido: Las notificaciones por correo están deshabilitadas globalmente.',
            ]);
            return;
        }

        // Update status of all logs to sending
        EmailLog::whereIn('id', $this->emailLogIds)->update([
            'status' => 'sending',
            'error_message' => null,
        ]);

        try {
            $emailLogs = EmailLog::whereIn('id', $this->emailLogIds)
                ->with(['shipment.sender', 'shipment.recipient', 'shipment.origin', 'shipment.destination', 'shipment.items'])
                ->get();

            if ($emailLogs->isEmpty()) {
                throw new Exception('No se encontraron registros de logs para los IDs proporcionados.');
            }

            // Extract unique shipments and general metadata
            $shipments = $emailLogs->map(fn($log) => $log->shipment)->filter()->unique('id');
            $recipient = $emailLogs->first()->recipient;
            $stage = $emailLogs->first()->stage;

            if ($shipments->isEmpty()) {
                throw new Exception('No hay guías válidas asociadas a estos registros de log.');
            }

            // Send grouped email using sendNow to catch exceptions synchronously in the worker
            Mail::to($recipient)->sendNow(
                new GroupedShipmentsStatusNotificationMail($shipments, $stage, $recipient)
            );

            // Mark all logs as sent
            EmailLog::whereIn('id', $this->emailLogIds)->update([
                'status' => 'sent',
                'error_message' => null,
            ]);
        } catch (Exception $e) {
            EmailLog::whereIn('id', $this->emailLogIds)->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);
            throw $e;
        }
    }
}
