<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Commands;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorService;
use Simtabi\Laranail\Product\Updater\Doctor\ConfigurationCheck;
use Simtabi\Laranail\Product\Updater\Doctor\ExtensionsCheck;
use Simtabi\Laranail\Product\Updater\Doctor\LicenseCheck;
use Simtabi\Laranail\Product\Updater\Doctor\WritablePathsCheck;

final class DoctorCommand extends Command
{
    protected $signature = 'laranail::product-updater.doctor {--json}';

    protected $description = 'Diagnose the product updater environment and configuration';

    /** @var list<string> */
    protected array $commandAliases = ['product:update-doctor'];

    /**
     * The canonical updater health checks — reused by the service provider to
     * register them into the unified package-tools doctor.
     *
     * @var list<class-string<DoctorCheck>>
     */
    public const array CHECKS = [
        ConfigurationCheck::class,
        ExtensionsCheck::class,
        WritablePathsCheck::class,
        LicenseCheck::class,
    ];

    public function handle(): int
    {
        $service = new DoctorService;

        foreach (self::CHECKS as $check) {
            $service->register($check);
        }

        $report = $service->run();
        $summary = $service->summarise($report);
        $failed = $summary['fail'] > 0;

        if ($this->wantsJson()) {
            $this->line((string) json_encode([
                'status' => $failed ? 'degraded' : 'ok',
                'summary' => $summary,
                'checks' => array_map(static fn (array $row): array => [
                    'name' => $row['check']->name(),
                    'status' => $row['result']->status->value,
                    'message' => $row['result']->message,
                    'detail' => $row['result']->detail,
                ], $report),
            ], JSON_PRETTY_PRINT));

            return $failed ? self::FAILURE : self::SUCCESS;
        }

        $this->table(['', 'Check', 'Result'], array_map(static fn (array $row): array => [
            $row['result']->status->symbol(),
            $row['check']->name(),
            $row['result']->message,
        ], $report));

        $this->line(__('product-updater::product-updater.doctor.summary', [
            'pass' => $summary['pass'],
            'warn' => $summary['warn'],
            'fail' => $summary['fail'],
            'skip' => $summary['skip'],
        ]));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
