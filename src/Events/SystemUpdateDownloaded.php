<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class SystemUpdateDownloaded
{
    use Dispatchable;

    public function __construct(public readonly string $path) {}
}
