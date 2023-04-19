<?php

namespace Rikj000\Tracing\Contracts;

interface Injector
{
    /**
     * Serialize span into given carrier
     *
     * @param  SpanContext  $spanContext
     * @param  mixed  $carrier
     */
    public function inject(SpanContext $spanContext, &$carrier): void;
}
