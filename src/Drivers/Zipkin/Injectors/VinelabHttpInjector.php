<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Injectors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\VinelabHttp;
use Zipkin\Propagation\Setter;

class VinelabHttpInjector extends ZipkinInjector
{
    /**
     * @return VinelabHttp
     */
    protected function getSetter(): Setter
    {
        return new VinelabHttp();
    }
}
