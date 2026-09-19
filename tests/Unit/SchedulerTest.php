<?php

declare(strict_types=1);

namespace Tests\Unit;

use Naf\CLI\Core\Output;
use Naf\Queue\Core\Queue;
use Naf\Queue\Drivers\QueueDriverInterface;
use Naf\Schedule\Core\JobRepository;
use Naf\Schedule\Core\ScheduledJobInterface;
use Naf\Schedule\Core\Scheduler;
use Naf\Schedule\Support\CronParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EveryMinuteJob implements ScheduledJobInterface
{
    public function getCronExpression(): string
    {
        return '* * * * *';
    }

    public function execute(Output $output): void
    {
    }
}
final class SchedulerTest extends TestCase
{
    public function testFailedEnqueueDoesNotSuppressRetryAndFreshInstanceSeesState(): void
    {
        $state = sys_get_temp_dir() . '/naf-schedule-regression-' . bin2hex(random_bytes(6));
        $jobs  = new JobRepository();
        $jobs->add(EveryMinuteJob::class, []);
        $driver = $this->createMock(QueueDriverInterface::class);
        $calls  = 0;
        $driver
            ->expects(self::exactly(2))
            ->method('enqueue')
            ->willReturnCallback(function () use (&$calls) {
                if (++$calls === 1) {
                    throw new RuntimeException('queue unavailable');
                }
            });
        $output    = $this->createStub(Output::class);
        $scheduler = new Scheduler(new Queue($driver), $jobs, new CronParser(), $state);

        try {
            try {
                $scheduler->tick($output);
                self::fail('Expected queue failure');
            } catch (RuntimeException $e) {
                self::assertSame('queue unavailable', $e->getMessage());
            }
            self::assertFileDoesNotExist($state);
            self::assertSame(1, $scheduler->tick($output));
            $fresh = new Scheduler(new Queue($driver), $jobs, new CronParser(), $state);
            self::assertSame(0, $fresh->tick($output));
        } finally {
            foreach ([$state, $state . '.lock'] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
