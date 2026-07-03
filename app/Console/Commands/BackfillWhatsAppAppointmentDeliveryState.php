<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\AppointmentDeliveryStatusSyncer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('whatsapp:backfill-appointment-delivery-state {--client=}')]
#[Description('Backfill appointment WhatsApp state from stored messages.')]
class BackfillWhatsAppAppointmentDeliveryState extends Command
{
    public function handle(AppointmentDeliveryStatusSyncer $deliveryStatusSyncer): int
    {
        $clientId = $this->option('client');
        $clientId = is_numeric($clientId) ? (int) $clientId : null;

        $updated = $deliveryStatusSyncer->backfillFromStoredMessages($clientId);

        $this->info(sprintf('Backfilled %d appointment(s).', $updated));

        return self::SUCCESS;
    }
}
