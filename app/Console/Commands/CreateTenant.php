<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Stancl\Tenancy\Database\Models\Domain;
use Throwable;

class CreateTenant extends Command
{
    protected $signature = 'create:tenant';

    protected $description = 'Create a new tenant with domain interactively';

    public function handle(): int
    {
        $this->info('🚀 Tenant Creation Started');

        $id = trim((string) $this->ask('🔤 Enter Tenant ID (e.g. nobat1)'));

        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/'],
        ]);

        if ($validator->fails()) {
            $this->error('⛔ '.$validator->errors()->first('id'));

            return self::FAILURE;
        }

        if (Tenant::find($id)) {
            $this->error("⛔ Tenant with ID '$id' already exists.");

            return self::FAILURE;
        }

        $domain = strtolower(rtrim(trim((string) $this->ask('🌐 Enter Domain (e.g. nobat1.test)')), '.'));

        $validator = Validator::make(['domain' => $domain], [
            'domain' => ['required', 'string', 'max:255', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/'],
        ]);

        if ($validator->fails()) {
            $this->error('⛔ Enter a valid hostname without a scheme or path (for example, nobat1.test).');

            return self::FAILURE;
        }

        if (in_array($domain, config('tenancy.central_domains', []), true)) {
            $this->error("⛔ '$domain' is configured as a central domain.");

            return self::FAILURE;
        }

        if (Domain::query()->where('domain', $domain)->exists()) {
            $this->error("⛔ Domain '$domain' is already assigned to a tenant.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('<fg=yellow>⏳ Creating tenant and running migrations. Please wait...</>');

        $tenant = null;

        try {
            // Creating the tenant synchronously provisions and seeds its database.
            $tenant = Tenant::create(['id' => $id]);
            $tenant->domains()->create(['domain' => $domain]);
        } catch (Throwable $exception) {
            $this->error('⛔ Tenant creation failed: '.$exception->getMessage());

            // Model events can fail after the central record has been inserted,
            // before Tenant::create() returns and assigns $tenant.
            $tenant ??= Tenant::find($id);

            if ($tenant?->exists) {
                try {
                    // TenantDeleted also removes a database that was already provisioned.
                    $tenant->delete();
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                    $this->warn("⚠ Automatic cleanup failed. Check tenant '$id' and its database manually.");
                }
            }

            report($exception);

            return self::FAILURE;
        }

        $this->info("✅ Tenant '$id' with domain '$domain' created successfully!");

        return self::SUCCESS;
    }
}
