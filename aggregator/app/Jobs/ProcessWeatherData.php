<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;

use Monolog\Logger;
use NewRelic\Monolog\Enricher\{Handler, Processor};

class ProcessWeatherData extends Job
{
    protected $accounts;

    public function __construct($accounts)
    {
        $this->accounts = $accounts;
    }

    public function handle()
    {
        // Monolog Logger
        $newrelic_log = new Logger('log');
        $newrelic_log->pushProcessor(new Processor);

        $handler = new Handler;
        $handler->setLicenseKey('ee2d839bc858a1b5feef951fb834dbdeFFFFNRAL');

        $client = new Client();
        $promises = [];

        $counter = 0;
        $failCounter = 0;

        foreach ($this->accounts as $account) {
            if (!empty($account['accountZip']) && preg_match('/[0-9]{5}/', trim($account['accountZip']))) {
                $url = 'https://wxapi.digichief.com/api/weather/GetWeather/04a3f908-d85f-489e-a90e-f90f4011e314?zipcode=' . $account['accountZip'] . '&format=xml';
                $promises[] = $client->getAsync($url)->then(
                    function ($response) use ($account, &$counter, $newrelic_log) {
                        $result = $response->getBody()->getContents();
                        $xmlFile = simplexml_load_string($result);
                        if ($xmlFile !== false) {
                            $savePath = base_path('feeds/digicache/') . $account['accountZip'] . '.XML';
                            $xmlFile->asXML($savePath);

                            $newrelic_log->pushHandler(new Handler(Logger::INFO));
                            $newrelic_log->info('XML file is saved for account ' . $account['accountZip'], array('platform' => 'Feeds Aggregator', 'type' => 'info', 'message' => 'File saved for account ' . $account['accountZip']  . " Count: ". $counter));

                            ++$counter; // Completed processing for this account
                            Log::info('Processing account with Zipcode: '. $account['accountZip'] . " Processed count: " . $counter);
                        } else {
                            throw new \Exception('Error parsing XML for Zipcode ' . $account['accountZip']);
                        }
                    },
                    function ($exception) use ($account, &$failCounter, $newrelic_log) {
                        ++$failCounter;
                        $newrelic_log->pushHandler(new Handler(Logger::CRITICAL));
                        $newrelic_log->critical('Error processing account ' . $account['accountZip'] . ' with zip code ' . $account['accountZip'], array('platform' => 'Feeds Aggregator', 'type' => 'error', 'message' => $exception->getMessage()));

                        if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                            newrelic_notice_error($exception);
                        }
                    }
                );
            }
        }

        // Using Promise to make asynchronous
        Promise\settle($promises)->wait();

        Log::info("Failed accounts count: ". $failCounter);
        Log::info("Processed accounts count on this batch: " . $counter);
        Log::info("---------------------------------------------------");
    }
}