<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Injectors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\Rikj000Http;
use Zipkin\Propagation\Setter;

class Rikj000HttpInjector extends ZipkinInjector
{
    /**
     * @return Rikj000Http
     */
    protected function getSetter(): Setter
    {
        return new Rikj000Http();
    }
}
