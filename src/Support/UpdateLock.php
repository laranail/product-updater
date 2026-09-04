<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Support;

use Throwable;
use Illuminate\Support\Facades\Cache;
use Simtabi\Laranail\Product\Updater\Exceptions\UpdaterException;

/**
 * Serialises the apply phase so two concurrent updates cannot interleave.
 */
final readonly class UpdateLock
{
    /**
     * Run the callback while holding the update lock; throws if another update
     * is already in progress.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function run(callable $callback): mixed
    {
        $lock = Cache::lock('product-updater:apply', (int) config('product-updater.lock_ttl', 600));

        if (! $lock->get()) {
            throw UpdaterException::updateInProgress();
        }

        try {
            return $callback();
        } finally {
            try {
                $lock->release();
            } catch (Throwable) {
                // Lock auto-expires via its TTL; a release failure is non-fatal.
            }
        }
    }
}
