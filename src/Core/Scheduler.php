<?php

declare(strict_types=1);

namespace Naf\Schedule\Core;

use DateTimeImmutable;
use Naf\CLI\Core\Output;
use Naf\Queue\Core\Queue;
use Naf\Schedule\Support\CronParser;
use RuntimeException;

use function Naf\app;
use function Naf\config;

class Scheduler
{
    private string $stateFile;
    private array $lastRun  = [];
    private int $jobsQueued = 0;

    public function __construct(
        private readonly Queue $queue,
        private readonly JobRepository $jobs,
        private readonly CronParser $cronParser,
        ?string $stateFile = null,
    ) {
        $this->stateFile = $stateFile ?? sys_get_temp_dir() . '/naf-schedule-state.json';
        $this->loadState();
    }

    public function addScheduledJob(string $scheduledJob, array $payload = []): void
    {
        $this->jobs->add($scheduledJob, $payload);
    }

    public function tick(Output $output): int
    {
        $lock = fopen($this->stateFile . '.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            throw new RuntimeException('Cannot lock scheduler state.');
        }

        try {
            $this->loadState();
            $now = new DateTimeImmutable();

            foreach ($this->jobs->all() as $jobClass => $payload) {
                $jobInstance = app()->container()->make($jobClass, $payload);

                if (!($jobInstance instanceof ScheduledJobInterface)) {
                    continue;
                }

                $expression = $jobInstance->getCronExpression();

                if (!$this->cronParser->isDue($expression, $now)) {
                    continue;
                }

                $currentMinute = $now->format('Y-m-d H:i');
                $jobKey        = $jobClass . ':' . $expression;

                if (isset($this->lastRun[$jobKey]) && $this->lastRun[$jobKey] === $currentMinute) {
                    continue;
                }

                $output->writeLine("Scheduler: Pushing job: {$jobClass} at {$currentMinute}");

                $jobPayload = $payload;
                $coalesce   = config('schedule:queue:coalesce', true);

                if ($coalesce) {
                    $jobPayload['_job_id'] = sha1('schedule:' . $jobKey);
                }

                $jobClassName = get_class($jobInstance);

                $this->queue->push($jobClassName, $jobPayload);
                $this->lastRun[$jobKey] = $currentMinute;
                $this->saveState();

                $this->jobsQueued++;
            }

            return $this->jobsQueued;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function loadState(): void
    {
        if (file_exists($this->stateFile)) {
            $data          = file_get_contents($this->stateFile);
            $this->lastRun = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($this->lastRun)) {
                throw new RuntimeException('Invalid scheduler state.');
            }
        }
    }

    private function saveState(): void
    {
        $temporary = $this->stateFile . '.' . bin2hex(random_bytes(8)) . '.tmp';

        try {
            $data = json_encode($this->lastRun, JSON_THROW_ON_ERROR);
            if (
                file_put_contents($temporary, $data, LOCK_EX) !== strlen($data)
                || !rename($temporary, $this->stateFile)
            ) {
                throw new RuntimeException('Cannot persist scheduler state.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
