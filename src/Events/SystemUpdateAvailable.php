<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Simtabi\Laranail\Product\Updater\ValueObjects\ProductRelease;

final readonly class SystemUpdateAvailable
{
    use Dispatchable;

    public function __construct(public ProductRelease $release) {}
}
