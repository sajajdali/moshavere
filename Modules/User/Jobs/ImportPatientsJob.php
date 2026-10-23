<?php

namespace Modules\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\User\Entities\User;
use Modules\User\Support\PatientImporter;
use Throwable;

/**
 * پردازش فایل اکسل بیماران در پس زمینه تا درخواست صفحه به خاطر تعداد زیاد ردیف ها timeout نشود.
 * وضعیت و نتیجه در کش نگه داشته می شود و صفحه آن را می خواند.
 */
class ImportPatientsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public const STORAGE_DISK = 'local';

    public function __construct(
        public string $importId,
        public string $path,
        public int $adminId,
        public ?string $tenantId = null,
    ) {}

    /** @return array{status:string,processed:int,total:int,result:?array,error:?string}|null */
    public static function status(string $importId): ?array
    {
        return Cache::get(self::cacheKey($importId));
    }

    public static function markQueued(string $importId): void
    {
        self::put($importId, ['status' => 'queued', 'processed' => 0, 'total' => 0, 'result' => null, 'error' => null]);
    }

    private static function cacheKey(string $importId): string
    {
        return "patient-import:$importId";
    }

    private static function put(string $importId, array $state): void
    {
        Cache::put(self::cacheKey($importId), $state, now()->addDay());
    }

    public function handle(): void
    {
        if ($this->tenantId && ! tenancy()->initialized) {
            tenancy()->initialize($this->tenantId);
        }

        try {
            $admin = User::findOrFail($this->adminId);
            $disk = Storage::disk(self::STORAGE_DISK);

            self::put($this->importId, ['status' => 'running', 'processed' => 0, 'total' => 0, 'result' => null, 'error' => null]);

            $result = (new PatientImporter($admin))->import(
                $disk->path($this->path),
                fn (int $processed, int $total) => self::put($this->importId, [
                    'status' => 'running', 'processed' => $processed, 'total' => $total, 'result' => null, 'error' => null,
                ]),
            );

            self::put($this->importId, [
                'status' => 'done', 'processed' => $result['total'], 'total' => $result['total'], 'result' => $result, 'error' => null,
            ]);
        } catch (Throwable $e) {
            $this->failed($e);
        } finally {
            Storage::disk(self::STORAGE_DISK)->delete($this->path);
        }
    }

    public function failed(Throwable $e): void
    {
        report($e);
        self::put($this->importId, [
            'status' => 'failed', 'processed' => 0, 'total' => 0, 'result' => null, 'error' => $e->getMessage(),
        ]);
    }
}
