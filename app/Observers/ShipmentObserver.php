<?php

namespace App\Observers;

use App\Jobs\SendShipmentEmailJob;
use App\Models\EmailLog;
use App\Models\Setting;
use App\Models\Shipment;

class ShipmentObserver
{
    protected array $statusMap = [
        'Dto origen' => 'created',
        'Entregado' => 'entregado',
    ];

    /**
     * Handle the Shipment "created" event.
     */
    public function created(Shipment $shipment): void
    {
        $status = $shipment->ubicacion_actual;

        if (isset($this->statusMap[$status])) {
            $this->checkAndSendNotifications($shipment, $this->statusMap[$status], $status);
        }
    }

    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(Shipment $shipment): void
    {
        if ($shipment->wasChanged('ubicacion_actual')) {
            $status = $shipment->ubicacion_actual;

            if (isset($this->statusMap[$status])) {
                $this->checkAndSendNotifications($shipment, $this->statusMap[$status], $status);
            }
        }
    }

    /**
     * Check if sender or recipient wants notifications and queue the mail.
     */
    protected function checkAndSendNotifications(Shipment $shipment, string $key, string $status): void
    {
        // Check if global email sending is enabled
        $isEnabled = Setting::get('email_notifications_enabled', '1');
        if ($isEnabled !== '1' && $isEnabled !== true && $isEnabled !== 1) {
            return;
        }

        // 1. Cargar relaciones si no están cargadas
        $shipment->loadMissing(['sender', 'recipient', 'origin', 'destination']);

        $sentEmails = [];

        // 2. Verificar y enviar al Remitente
        if ($shipment->sender && $shipment->sender->wantsNotificationFor($key)) {
            $senderEmail = strtolower(trim($shipment->sender->email));
            if (!empty($senderEmail)) {
                $emailLog = EmailLog::create([
                    'shipment_id' => $shipment->id,
                    'company_id' => $shipment->company_id,
                    'party_id' => $shipment->sender->id,
                    'recipient' => $shipment->sender->email,
                    'stage' => $status,
                    'status' => 'pending',
                ]);
                SendShipmentEmailJob::dispatch($emailLog);
                $sentEmails[] = $senderEmail;
            }
        }

        // 3. Verificar y enviar al Destinatario
        if ($shipment->recipient && $shipment->recipient->wantsNotificationFor($key)) {
            $recipientEmail = strtolower(trim($shipment->recipient->email));
            if (!empty($recipientEmail) && !in_array($recipientEmail, $sentEmails)) {
                $emailLog = EmailLog::create([
                    'shipment_id' => $shipment->id,
                    'company_id' => $shipment->company_id,
                    'party_id' => $shipment->recipient->id,
                    'recipient' => $shipment->recipient->email,
                    'stage' => $status,
                    'status' => 'pending',
                ]);
                SendShipmentEmailJob::dispatch($emailLog);
            }
        }
    }
}
