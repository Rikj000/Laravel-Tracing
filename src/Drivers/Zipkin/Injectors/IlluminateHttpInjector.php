<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Injectors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\IlluminateHttp;
use Zipkin\Propagation\Setter;

class IlluminateHttpInjector extends ZipkinInjector
{
    /**
     * @return IlluminateHttp
     */
    protected function getSetter(): Setter
    {
        return new IlluminateHttp();
    }
}
