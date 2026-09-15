<?php

declare(strict_types=1);

namespace Naf\Schedule\Core;

use Naf\Queue\Core\QueueJobInterface;

interface ScheduledJobInterface extends QueueJobInterface
{
    /**
     * Return a regular cron expression
     *
     * @return string
     */
    public function getCronExpression(): string;
}
