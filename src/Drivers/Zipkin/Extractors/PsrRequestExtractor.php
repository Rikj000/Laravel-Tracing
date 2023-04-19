<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Extractors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\PsrRequest;
use Zipkin\Propagation\Getter;

class PsrRequestExtractor extends ZipkinExtractor
{
    /**
     * @return PsrRequest
     */
    protected function getGetter(): Getter
    {
        return new PsrRequest();
    }
}
