<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched when the system requirements for an update are not met
 * (the update aborts before any file is written).
 */
final readonly class RequirementsFailed
{
    use Dispatchable;

    public function __construct(public string $reason) {}
}
