<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Setting;
use App\Models\Shipment;
use App\Jobs\SendGroupedShipmentsEmailJob;

class GroupedNotificationService
{
    /**
     * Consolidate and send grouped notifications for a collection of shipments at a given status stage.
     */
    public function sendGroupedNotifications(iterable $shipments, string $stageKey, string $status): void
    {
        // Check if global email sending is enabled
        $isEnabled = Setting::get('email_notifications_enabled', '1');
        if ($isEnabled !== '1' && $isEnabled !== true && $isEnabled !== 1) {
            return;
        }

        $groups = [];

        foreach ($shipments as $shipment) {
            // Ensure relations are loaded
            $shipment->loadMissing(['sender', 'recipient', 'origin', 'destination']);

            // 1. Process Sender
            if ($shipment->sender && $shipment->sender->wantsNotificationFor($stageKey)) {
                $senderEmail = strtolower(trim($shipment->sender->email));
                if (!empty($senderEmail)) {
                    if (!isset($groups[$senderEmail])) {
                        $groups[$senderEmail] = [
                            'party' => $shipment->sender,
                            'shipments' => [],
                        ];
                    }
                    $groups[$senderEmail]['shipments'][$shipment->id] = $shipment;
                }
            }

            // 2. Process Recipient
            if ($shipment->recipient && $shipment->recipient->wantsNotificationFor($stageKey)) {
                $recipientEmail = strtolower(trim($shipment->recipient->email));
                if (!empty($recipientEmail)) {
                    if (!isset($groups[$recipientEmail])) {
                        $groups[$recipientEmail] = [
                            'party' => $shipment->recipient,
                            'shipments' => [],
                        ];
                    }
                    $groups[$recipientEmail]['shipments'][$shipment->id] = $shipment;
                }
            }
        }

        if (empty($groups)) {
            return;
        }

        foreach ($groups as $email => $groupData) {
            $emailLogIds = [];

            foreach ($groupData['shipments'] as $shipment) {
                $emailLog = EmailLog::create([
                    'shipment_id' => $shipment->id,
                    'company_id' => $shipment->company_id,
                    'party_id' => $groupData['party']->id,
                    'recipient' => $email,
                    'stage' => $status,
                    'status' => 'pending',
                ]);
                $emailLogIds[] = $emailLog->id;
            }

            if (!empty($emailLogIds)) {
                SendGroupedShipmentsEmailJob::dispatch($emailLogIds);
            }
        }
    }
}
