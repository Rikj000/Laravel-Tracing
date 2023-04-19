<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Injectors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\PsrRequest;
use Zipkin\Propagation\Setter;

class PsrRequestInjector extends ZipkinInjector
{
    /**
     * @return PsrRequest
     */
    protected function getSetter(): Setter
    {
        return new PsrRequest();
    }
}
