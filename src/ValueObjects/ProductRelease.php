<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\ValueObjects;

use Carbon\CarbonInterface;

/**
 * An available product release (ported from Botble's CoreProduct value object).
 */
final readonly class ProductRelease
{
    public function __construct(
        public string $updateId,
        public string $version,
        public ?CarbonInterface $releasedAt = null,
        public ?string $summary = null,
        public ?string $changelog = null,
        public bool $hasSql = false,
    ) {}

    /**
     * Whether this release is newer than the given current version.
     */
    public function isNewerThan(string $currentVersion): bool
    {
        return version_compare($this->version, $currentVersion, '>');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'update_id' => $this->updateId,
            'version' => $this->version,
            'released_at' => $this->releasedAt?->toIso8601String(),
            'summary' => $this->summary,
            'changelog' => $this->changelog,
            'has_sql' => $this->hasSql,
        ];
    }
}
