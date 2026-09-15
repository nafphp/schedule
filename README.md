<div align="center" style="text-align: center;">

![NAF](assets/naf-logo-small-square.png)

[![NAF Schedule Plugin](https://github.com/nafphp/schedule/actions/workflows/php.yml/badge.svg)](https://github.com/nafphp/schedule/actions/workflows/php.yml)

</div>

[← Back to NAF](https://github.com/nafphp/framework)

---

# naf/schedule

> **Minimalistic scheduling for NAF – cron-based, predictable, and queue-aware.**

This plugin provides a lightweight scheduler for recurring tasks using cron expressions.
It is designed to work seamlessly with the NAF Queue plugin, without introducing hidden magic or unnecessary complexity.

> 🧩 Part of the official NAF plugin collection.  
> Use it when you want to run recurring jobs reliably – without relying on system cron files or external schedulers.

## Documentation

**[Scheduled jobs →](https://nafphp.github.io/docs/scheduling/)**

Everything about this package — what it does, how it is configured and what it needs — lives
in the [NAF documentation](https://nafphp.github.io/docs/). Not sure which packages you need?
[Start here](https://nafphp.github.io/docs/choosing-packages/).

## Install

```bash
composer require naf/schedule
```

## License

MIT. Part of [NAF](https://github.com/nafphp/framework).


## Unreleased Nafinity integration candidate

Target branch: `v0.2.3-rc`. This behavior is not a published release yet.

`schedule:ticker --workers=1 --max-jobs=10 --max-runtime=60` starts the real queue
consumer with validated integer limits. Embedded workers write to the host's
`logs/queue/` directory and work with any PSR-3 logger. The ticker closes its child
processes when it exits or fails. `--once` runs one scheduling pass.

The ticker starts the actual queue:consume command, supports --once, and terminates its managed children on exit. Tick state uses a file lock, reload and atomic replacement; failed enqueue does not mark a minute complete. Use durable queue job IDs for consumer deduplication across enqueue/state crash boundaries. Optional schedule:heartbeat_file records ticker polling activity.

## PHP code style

Source, tests and PHP templates follow the shared [NAF code style](https://github.com/nafphp/docs/blob/main/CODE_STYLE.md)
(PER Coding Style 3.0 with the Nafinity readability rules). After `composer install`, run
`composer style:check` to verify formatting or `composer style:fix` to apply it. The formatter
is a development dependency. Review template output and run the package checks after changes.
