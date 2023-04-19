<?php

namespace Rikj000\Tracing\Tests\Zipkin;

use Rikj000\Tracing\Drivers\Zipkin\ZipkinTracer;
use Zipkin\Recording\Span;
use Zipkin\Reporter;

trait InteractsWithZipkin
{
    /**
     * @param  Reporter  $reporter
     * @param  string|null  $serviceName
     * @param  string  $host
     * @param  int  $port
     * @param  int  $requestTimeout
     * @param  bool  $usesTraceId128bits
     * @return ZipkinTracer
     */
    protected function createTracer(
        Reporter $reporter,
        string $serviceName = 'example',
        string $host = 'localhost',
        int $port = 9411,
        int $requestTimeout = 5,
        bool $usesTraceId128bits = false
    ): ZipkinTracer
    {
        $tracer = new ZipkinTracer($serviceName, $host, $port, $usesTraceId128bits, $requestTimeout, $reporter);
        $tracer->init();

        return $tracer;
    }

    /**
     * @param  array  $spans
     */
    protected function shiftSpan(array &$spans): Span
    {
        /** @var Span $span */
        $span = array_shift($spans);
        return $span;
    }
}
