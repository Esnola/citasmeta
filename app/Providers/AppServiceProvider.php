<?php

namespace App\Providers;

use App\Livewire\AppointmentReminderSettings;
use App\Livewire\WhatsAppConnectionTest;
use App\Livewire\WhatsAppTemplateManager;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('whatsapp-connection-test', WhatsAppConnectionTest::class);
        Livewire::component('whatsapp-template-manager', WhatsAppTemplateManager::class);
        Livewire::component('appointment-reminder-settings', AppointmentReminderSettings::class);
    }
}
