<?php

declare(strict_types=1);

namespace Naf\Schedule;

use Naf\Schedule\Core\Scheduler;

use function Naf\app;

/**
 * @return Scheduler
 */
function scheduler(): Scheduler
{
    return app()->container()->get(Scheduler::class);
}
