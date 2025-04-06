<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;


class CreateTenant extends Command
{
    protected $signature = 'create:tenant';
    protected $description = 'Create a new tenant with domain interactively';

    public function handle(): void
    {
        $this->info('🚀 Tenant Creation Started');

        $id = $this->ask('🔤 Enter Tenant ID (e.g. nobat1)');
        if (Tenant::find($id)) {
            $this->error("⛔ Tenant with ID '$id' already exists.");
            return;
        }

        $domain = $this->ask('🌐 Enter Domain (e.g. nobat1.test)');

        $this->newLine();
        $this->line('<fg=yellow>⏳ Creating tenant and running migrations. Please wait...</>');

        $tenant = Tenant::create(['id' => $id]);
        $tenant->domains()->create(['domain' => $domain]);

        $this->info("✅ Tenant '$id' with domain '$domain' created successfully!");
    }
}
