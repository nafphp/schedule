<?php

declare(strict_types=1);

use Naf\Core\Container;
use Naf\Queue\Core\Queue;
use Naf\Schedule\Commands\ScheduleListCommand;
use Naf\Schedule\Commands\ScheduleTickerCommand;
use Naf\Schedule\Core\JobRepository;
use Naf\Schedule\Core\Scheduler;
use Naf\Schedule\Support\CronParser;

use function Naf\app;
use function Naf\CLI\command;

app()->container()->set(CronParser::class, static fn() => new CronParser());

app()->container()->set(JobRepository::class, static fn() => new JobRepository());

app()->container()->set(Scheduler::class, function (Container $container) {
    $queue          = $container->get(Queue::class);
    $taskRepository = $container->get(JobRepository::class);
    $cronParser     = $container->get(CronParser::class);

    return new Scheduler($queue, $taskRepository, $cronParser);
});

command()->add(ScheduleTickerCommand::class);
command()->add(ScheduleListCommand::class);
