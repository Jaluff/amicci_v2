<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\Setting;
use App\Mail\ShipmentStatusNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendShipmentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public EmailLog $emailLog;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailLog $emailLog)
    {
        $this->emailLog = $emailLog;
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
            $this->emailLog->update([
                'status' => 'failed',
                'error_message' => 'Envío omitido: Las notificaciones por correo están deshabilitadas globalmente.',
            ]);
            return;
        }

        // Update status to sending
        $this->emailLog->update([
            'status' => 'sending',
            'error_message' => null,
        ]);

        try {
            $shipment = $this->emailLog->shipment;
            if (!$shipment) {
                throw new Exception('Guía no encontrada para el log de correo ID: ' . $this->emailLog->id);
            }

            // We use sendNow to execute synchronously inside the queue worker,
            // so we can catch exceptions and log status precisely.
            Mail::to($this->emailLog->recipient)->sendNow(
                new ShipmentStatusNotificationMail($shipment, $this->emailLog->stage)
            );

            $this->emailLog->update([
                'status' => 'sent',
                'error_message' => null,
            ]);
        } catch (Exception $e) {
            $this->emailLog->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);
            throw $e;
        }
    }
}
