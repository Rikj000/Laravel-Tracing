<?php

namespace Rikj000\Tracing\Drivers\Zipkin\Extractors;

use Rikj000\Tracing\Drivers\Zipkin\Propagation\GooglePubSub;
use Zipkin\Propagation\Getter;

class GooglePubSubExtractor extends ZipkinExtractor
{
    /**
     * @return GooglePubSub
     */
    protected function getGetter(): Getter
    {
        return new GooglePubSub();
    }
}
