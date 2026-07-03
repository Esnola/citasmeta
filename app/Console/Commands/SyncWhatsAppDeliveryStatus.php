<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('whatsapp:sync-delivery-status')]
#[Description('Check delivered WhatsApp logs and mark appointments as delivered.')]
class SyncWhatsAppDeliveryStatus extends Command
{
    public function handle(AppointmentDeliveryStatusSyncer $deliveryStatusSyncer): int
    {
        $updated = $deliveryStatusSyncer->syncAll();

        $this->info(sprintf('Synced %d delivered appointment(s).', $updated));

        return self::SUCCESS;
    }
}
