<?php

namespace Rikj000\Tracing;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Rikj000\Tracing\Contracts\Tracer;
use Rikj000\Tracing\Facades\Trace;
use Rikj000\Tracing\Listeners\TraceCommand;

class TracingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole() && function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/tracing.php' => config_path('tracing.php'),
            ]);
        }

        $this->app['events']->listen(CommandStarting::class, TraceCommand::class);

        $this->app['events']->listen(
            JobProcessing::class,
            'Rikj000\Tracing\Listeners\QueueJobSubscriber@onJobProcessing'
        );
        $this->app['events']->listen(
            JobProcessed::class,
            'Rikj000\Tracing\Listeners\QueueJobSubscriber@onJobProcessed'
        );
        $this->app['events']->listen(
            JobFailed::class,
            'Rikj000\Tracing\Listeners\QueueJobSubscriber@onJobFailed'
        );

        if ($this->app['config']['tracing.errors']) {
            $this->app['events']->listen(MessageLogged::class, function (MessageLogged $event) {
                if ($event->level == 'error') {
                    optional(Trace::getRootSpan())->tag('error', 'true');
                    optional(Trace::getRootSpan())->tag('error_message', $event->message);
                }
            });
        }

        if (method_exists($this->app, 'terminating')) {
            $this->app->terminating(function () {
                optional(Trace::getRootSpan())->finish();
                Trace::flush();
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( dirname(__DIR__).'/config/tracing.php', 'tracing');

        $this->app->singleton(TracingDriverManager::class, function ($app) {
            return new TracingDriverManager($app);
        });

        $this->app->singleton(Tracer::class, function ($app) {
            return $app->make(TracingDriverManager::class)->driver($this->app['config']['tracing.driver']);
        });
    }
}
