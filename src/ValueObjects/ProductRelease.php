<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\ValueObjects;

use Carbon\CarbonInterface;

/**
 * An available product release, shaped to match Botble's CoreProduct payload.
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
        public ?string $checksum = null,    // expected SHA-256 of the archive (hex)
        public ?string $signature = null,   // base64 detached signature of the archive
        public ?string $minPhp = null,      // minimum PHP version this release requires
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
            'update_id'   => $this->updateId,
            'version'     => $this->version,
            'released_at' => $this->releasedAt?->toIso8601String(),
            'summary'     => $this->summary,
            'changelog'   => $this->changelog,
            'has_sql'     => $this->hasSql,
            'checksum'    => $this->checksum,
            'min_php'     => $this->minPhp,
        ];
    }
}
