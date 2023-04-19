<?php

namespace Rikj000\Tracing\Drivers\Null;

use Rikj000\Tracing\Contracts\SpanContext;

class NullSpanContext implements SpanContext
{
    /**
     * Returns underlying (original) span context.
     *
     * @return mixed
     */
    public function getRawContext()
    {
        return null;
    }
}
