<?php

namespace Rikj000\Tracing\Contracts;

interface SpanContext
{
    /**
     * Returns underlying (original) span context.
     *
     * @return mixed
     */
    public function getRawContext();
}
