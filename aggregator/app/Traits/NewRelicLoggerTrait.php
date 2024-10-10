<?php

namespace App\Traits;

use Monolog\Logger;
use NewRelic\Monolog\Enricher\{Handler, Processor};

trait NewRelicLoggerTrait
{
    protected function setupNewRelicLogger()
    {
        $newrelic_log = new Logger('log');
        $newrelic_log->pushProcessor(new Processor);

        $handler = new Handler;
        $handler->setLicenseKey('ee2d839bc858a1b5feef951fb834dbdeFFFFNRAL');
        $newrelic_log->pushHandler($handler);

        return $newrelic_log;
    }
}

