<?php

namespace Rikj000\Tracing\Listeners;

use Illuminate\Console\Command;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Arr;
use Rikj000\Tracing\Contracts\ShouldBeTraced;
use Rikj000\Tracing\Contracts\Tracer;

class TraceCommand
{
    /**
     * @var Tracer
     */
    protected $tracer;

    /**
     * @var Kernel
     */
    protected $artisan;

    /**
     * Create the event listener.
     *
     * @param  Tracer  $tracer
     * @param  Kernel  $artisan
     */
    public function __construct(Tracer $tracer, Kernel $artisan)
    {
        $this->tracer = $tracer;
        $this->artisan = $artisan;
    }

    /**
     * Handle the event.
     *
     * @param  CommandStarting  $event
     * @return void
     */
    public function handle(CommandStarting $event)
    {
        if ($event->command && $this->shouldTraceCommand($event->command)) {
            $span = $this->tracer->startSpan("artisan {$event->command}");

            $span->tag('type', 'cli');
            $span->tag('argv', implode(PHP_EOL, $_SERVER['argv']));
        }
    }

    /**
     * @param  string  $command
     * @return bool
     */
    protected function shouldTraceCommand(string $command): bool
    {
        /** @var Command $command */
        $command = Arr::get($this->artisan->all(), $command);

        if (!$command) {
            return false;
        }

        $interfaces = class_implements($command);

        return isset($interfaces[ShouldBeTraced::class]);
    }
}
