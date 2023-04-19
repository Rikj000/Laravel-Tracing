<?php

namespace Rikj000\Tracing\Integration\Concerns;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Lucid\Foundation\Events\FeatureStarted as LegacyFeatureStarted;
use Lucid\Foundation\Events\JobStarted as LegacyJobStarted;
use Lucid\Foundation\Events\OperationStarted as LegacyOperationStarted;
use Rikj000\Tracing\Facades\Trace;
use Lucid\Events\FeatureStarted;
use Lucid\Events\OperationStarted;
use Lucid\Events\JobStarted;

/**
 * Enables tracing for projects based on a Lucid Architecture
 *
 * https://github.com/lucidarch/lucid
 * https://github.com/lucid-architecture/laravel-microservice
 */
trait TracesLucidArchitecture
{
    protected function traceLucidArchitecture()
    {
        $this->resolveLucidUnits();

        $this->renameSpanAfterFeature();
        $this->annotateRunningOperations();
        $this->annotateRunningJobs();
    }

    protected function resolveLucidUnits()
    {
        if (!class_exists(FeatureStarted::class)) {
            App::bind(FeatureStarted::class, function () {
                return LegacyFeatureStarted::class;
            });
        }

        if (!class_exists(OperationStarted::class)) {
            App::bind(OperationStarted::class, function () {
                return LegacyOperationStarted::class;
            });
        }

        if (!class_exists(JobStarted::class)) {
            App::bind(JobStarted::class, function () {
                return LegacyJobStarted::class;
            });
        }
    }

    protected function renameSpanAfterFeature()
    {
        Event::listen(FeatureStarted::class, function (FeatureStarted $event) {
            if (in_array(ShouldQueue::class, class_implements($event->name))) {
                /*
                 * If feature is queued using a "sync" driver, FeatureStarted event will be triggered twice
                 * within a single request lifecycle: first time on dispatch and another time during execution.
                 * We want to skip renaming the span if it's the former and rename queue span if it's the latter.
                 */
                if (App::has('tracing.queue.span')) {
                    App::get('tracing.queue.span')->setName($event->name);
                }
            } else {
                optional(Trace::getRootSpan())->setName($event->name);
            }
        });
    }

    protected function annotateRunningOperations()
    {
        Event::listen(OperationStarted::class, function (OperationStarted $event) {
            $this->annotateEvent($event->name);
        });
    }

    protected function annotateRunningJobs()
    {
        Event::listen(JobStarted::class, function (JobStarted $event) {
            $this->annotateEvent($event->name);
        });
    }

    /**
     * @param  string  $eventName
     */
    private function annotateEvent(string $eventName)
    {
        if (App::has('tracing.queue.span')) {
            App::get('tracing.queue.span')->annotate($eventName);
        } else {
            optional(Trace::getRootSpan())->annotate($eventName);
        }
    }
}
