<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Extractors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\IlluminateHttp;
use Zipkin\Propagation\Getter;

class IlluminateHttpExtractor extends ZipkinExtractor
{
    /**
     * @return IlluminateHttp
     */
    protected function getGetter(): Getter
    {
        return new IlluminateHttp();
    }
}
