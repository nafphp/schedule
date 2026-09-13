# Working on naf/schedule

NAF is a small PHP framework with optional Composer plugins. Its core owns boot,
configuration, the service container, routing, events and PSR-7 responses. Prefer existing
NAF helpers, services and extension interfaces; keep application business rules in the host.
This package declares `type: naf-plugin` and is discovered after installation in a NAF host.
The plugin repository itself is not the application's web root.

Before changing code, read the [shared contribution workflow](https://github.com/nafphp/docs/blob/main/AGENT_WORKFLOW.md)
and [release procedure](https://github.com/nafphp/docs/blob/main/RELEASING.md).
In the multi-repository workspace, the same documents are in the sibling `docs/` checkout;
use the linked copies when working from a standalone clone. Preserve other contributors' work.
Review and update user documentation with every behavior change. Source fixes use an RC branch;
verified documentation-only changes can be merged and published by the agent.

## What this plugin does

`naf/schedule` evaluates cron expressions and enqueues due jobs through `naf/queue`. Install
with `composer require naf/schedule`. A ticker decides what is due; a queue worker executes
it. Running only the ticker does not execute your scheduled business work.

## Use it

Create a host class `app/Jobs/HeartbeatJob.php`:

```php
<?php
namespace App\Jobs;

use Naf\CLI\Core\Output;
use Naf\Schedule\Core\ScheduledJobInterface;

final class HeartbeatJob implements ScheduledJobInterface
{
    public function getCronExpression(): string { return '*/5 * * * *'; }
    public function execute(Output $output): void { $output->writeLine('heartbeat'); }
}
```

Register in the host bootstrap after the scheduler plugin has booted:

```php
<?php
use App\Jobs\HeartbeatJob;
use function Naf\Schedule\scheduler;

scheduler()->addScheduledJob(HeartbeatJob::class);
```

Inspect with `vendor/bin/naf schedule:list`; run `vendor/bin/naf schedule:ticker` and
`vendor/bin/naf queue:consume` as separate host processes. Constructor payloads can be supplied
as the second registration argument. Scheduling is not a substitute for idempotent jobs.

## Change it here

[Scheduler](src/Core/Scheduler.php), [CronParser](src/Support/CronParser.php),
[JobRepository](src/Core/JobRepository.php), [job contract](src/Core/ScheduledJobInterface.php)
and [commands](src/Commands/) own the implementation. Preserve cron semantics, minute
suppression and queue coalescing. The default state file is shared under the system temporary
directory; supply a dedicated state file when constructing a scheduler for isolated tests or
multiple installations. Do not invent a configuration key that bootstrap does not read.

## Verify

Run `composer test` and `composer validate --strict`. Use [tests](tests/) with isolated state
and queue storage. Verify boundary times, repeated ticks, payloads and actual worker execution.
No `analyse` script is declared.

User docs: [Scheduling](https://nafphp.github.io/docs/scheduling/).
