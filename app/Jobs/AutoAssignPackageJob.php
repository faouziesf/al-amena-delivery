<?php

namespace App\Jobs;

use App\Models\Package;
use App\Services\PackageAutoAssignmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AutoAssignPackageJob implements ShouldQueue
{
    use Queueable;

    public $packageId;

    /**
     * Create a new job instance.
     */
    public function __construct($packageId)
    {
        $this->packageId = $packageId;
    }

    /**
     * Execute the job.
     */
    public function handle(PackageAutoAssignmentService $autoAssignmentService): void
    {
        $package = Package::find($this->packageId);

        if (!$package) {
            Log::warning("Colis ID {$this->packageId} introuvable pour auto-assignation");
            return;
        }

        $autoAssignmentService->autoAssignPickedUpPackage($package);
    }
}