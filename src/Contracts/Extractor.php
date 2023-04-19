<?php

namespace Rikj000\Tracing\Contracts;

interface Extractor
{
    /**
     * Extract span context from given carrier
     *
     * @param mixed $carrier
     * @return SpanContext|null
     */
    public function extract($carrier): ?SpanContext;
}
