<?php

namespace Rikj000\Tracing\Tests\Zipkin;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rikj000\Tracing\Contracts\Tracer;
use Rikj000\Tracing\Listeners\QueueJobSubscriber;
use Rikj000\Tracing\Tests\Fixtures\ExampleJob;
use Rikj000\Tracing\Tests\Fixtures\NoopReporter;

class QueueJobSubscriberTest extends TestCase
{
    use InteractsWithZipkin;

    public function test_job_processed()
    {
        $container = new Container();

        $reporter = Mockery::spy(NoopReporter::class);
        $tracer = $this->createTracer($reporter);

        $container->instance(Tracer::class, $tracer);

        $jobInstance = new ExampleJob(
            "Example Payload",
            "",
            new Fluent(['name' => 'John Doe']),
            $tracer->startSpan("Example")->getContext()
        );
        $jobInstance->onConnection("sync");
        $jobInstance->onQueue("default");

        $job = new SyncJob($container, json_encode([
            'job' => ExampleJob::class,
            'displayName' => '',
            'data' => [
                'command' => serialize($jobInstance)
            ]
        ]), "sync", "default");

        $subscriber = new QueueJobSubscriber($container);

        $subscriber->onJobProcessing(new JobProcessing("sync", $job));
        $subscriber->onJobProcessed(new JobProcessed("sync", $job));

        $tracer->flush();

        $reporter->shouldHaveReceived('report')->with(Mockery::on(function ($spans) {
            $span = $this->shiftSpan($spans);

            $parentId = $span->getSpanId();

            $span = $this->shiftSpan($spans);

            $this->assertEquals($parentId, $span->getParentId());
            $this->assertEquals('ExampleJob', $span->getName());
            $this->assertEquals('sync', Arr::get($span->getTags(), 'connection_name'));
            $this->assertEquals('default', Arr::get($span->getTags(), 'queue_name'));
            $this->assertEquals([
                'primitive' => 'Example Payload',
                'fluent' => [
                    'name' => 'John Doe',
                ],
            ], json_decode(Arr::get($span->getTags(), 'job_input'), true));

            return true;
        }));
    }

    public function test_job_failed()
    {
        $container = new Container();

        $reporter = Mockery::spy(NoopReporter::class);
        $tracer = $this->createTracer($reporter);

        $container->instance(Tracer::class, $tracer);

        $jobInstance = new ExampleJob(
            "Example Payload",
            "",
            new Fluent(['name' => 'John Doe']),
            $tracer->startSpan("Example")->getContext()
        );
        $jobInstance->onConnection("sync");
        $jobInstance->onQueue("default");

        $job = new SyncJob($container, json_encode([
            'job' => ExampleJob::class,
            'displayName' => '',
            'data' => [
                'command' => serialize($jobInstance)
            ]
        ]), "sync", "default");

        $subscriber = new QueueJobSubscriber($container);

        $subscriber->onJobProcessing(new JobProcessing("sync", $job));
        $subscriber->onJobFailed(new JobFailed("sync", $job, new Exception("whatever")));

        $tracer->flush();

        $reporter->shouldHaveReceived('report')->with(Mockery::on(function ($spans) {
            $span = $this->shiftSpan($spans);

            $parentId = $span->getSpanId();

            $span = $this->shiftSpan($spans);

            $this->assertEquals($parentId, $span->getParentId());
            $this->assertEquals('ExampleJob', $span->getName());
            $this->assertEquals('sync', Arr::get($span->getTags(), 'connection_name'));
            $this->assertEquals('default', Arr::get($span->getTags(), 'queue_name'));
            $this->assertEquals([
                'primitive' => 'Example Payload',
                'fluent' => [
                    'name' => 'John Doe',
                ],
            ], json_decode(Arr::get($span->getTags(), 'job_input'), true));
            $this->assertEquals('true', Arr::get($span->getTags(), 'error'));
            $this->assertEquals('whatever', Arr::get($span->getTags(), 'error_message'));

            return true;
        }));
    }
}
