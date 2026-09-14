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

The ticker starts the actual queue:consume command, supports --once, and terminates its managed children on exit. Tick state uses a file lock, reload and atomic replacement; failed enqueue does not mark a minute complete. Use durable queue job IDs for consumer deduplication across enqueue/state crash boundaries. Optional schedule:heartbeat_file records ticker polling activity.
