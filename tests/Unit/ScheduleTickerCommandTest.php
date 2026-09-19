<?php

declare(strict_types=1);

namespace Tests\Unit;

use FilesystemIterator;
use Naf\CLI\Core\Input;
use Naf\CLI\Core\Output;
use Naf\Core\App;
use Naf\Schedule\Commands\ScheduleTickerCommand;
use Naf\Schedule\Core\Scheduler;
use Naf\Support\AppHolder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function Naf\app;

final class ScheduleTickerCommandTest extends TestCase
{
    public function testWorkerReceivesNumericCliLimitsAndSupportsPsrLogger(): void
    {
        $original  = app();
        $container = $original->container();
        $logger    = $container->get(LoggerInterface::class);
        $root      = sys_get_temp_dir() . '/naf-ticker-' . bin2hex(random_bytes(8));
        mkdir($root . '/vendor/bin', 0700, true);
        $marker = $root . '/worker.json';
        file_put_contents($root . '/vendor/bin/naf', '<?php file_put_contents(' . var_export($marker, true) . ', json_encode($argv));');
        $host = $this->createStub(App::class);
        $host->method('getBasePath')->willReturn($root);
        $host->method('container')->willReturn($container);
        AppHolder::set($host);
        $container->set(LoggerInterface::class, new NullLogger());
        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects($this->once())->method('tick')->willReturnCallback(function () use ($marker): int {
            $deadline = microtime(true) + 3;
            while (!is_file($marker) && microtime(true) < $deadline) {
                usleep(10000);
            }
            $this->assertFileExists($marker);
            $this->assertSame(['vendor/bin/naf', 'queue:consume', '--max-jobs=1', '--max-runtime=2'], json_decode(file_get_contents($marker), true));

            return 0;
        });

        try {
            $command = new ScheduleTickerCommand($scheduler);
            $input   = new Input(['--once', '--workers=1', '--max-jobs=1', '--max-runtime=2'], $command->getDefinition());
            $this->assertSame(0, $command->run($input, $this->createStub(Output::class)));
        } finally {
            AppHolder::set($original);
            $container->set(LoggerInterface::class, $logger);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($root);
        }
    }
}
