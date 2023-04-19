<?php

namespace Rikj000\Tracing;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Manager;
use Rikj000\Tracing\Contracts\Tracer;
use Rikj000\Tracing\Drivers\Null\NullTracer;
use Rikj000\Tracing\Drivers\Zipkin\ZipkinTracer;

class TracingDriverManager extends Manager
{
    /**
     * @var Repository
     */
    protected $config;

    /**
     * EngineManager constructor.
     * @param $app
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->config = $app->make('config');
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        if (is_null($this->config->get('tracing.driver'))) {
            return 'null';
        }

        return $this->config->get('tracing.driver');
    }

    /**
     * Create an instance of Zipkin tracing engine
     *
     * @return ZipkinTracer|Tracer
     */
    public function createZipkinDriver()
    {
        $tracer = new ZipkinTracer(
            $this->config->get('tracing.service_name'),
            $this->config->get('tracing.zipkin.host'),
            $this->config->get('tracing.zipkin.port'),
            $this->config->get('tracing.zipkin.options.128bit'),
            $this->config->get('tracing.zipkin.options.request_timeout', 5)
        );

        ZipkinTracer::setMaxTagLen(
            $this->config->get('tracing.zipkin.options.max_tag_len', ZipkinTracer::getMaxTagLen())
        );

        return $tracer->init();
    }

    public function createNullDriver()
    {
        return new NullTracer();
    }
}
