<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Injectors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\AMQP;
use Zipkin\Propagation\Setter;

class AMQPInjector extends ZipkinInjector
{
    /**
     * @return AMQP
     */
    protected function getSetter(): Setter
    {
        return new AMQP();
    }
}
