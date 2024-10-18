<?php

namespace App\Jobs;

use App\Traits\NewRelicLoggerTrait;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Log;

class ProcessWeatherData extends Job
{
    use NewRelicLoggerTrait;

    protected $accounts;

    public $failedAccounts = 0;
    public $processedAccounts = 0;

    public function __construct($accounts)
    {
        $this->accounts = $accounts;
    }

    public function handle()
    {
        $newrelic_log = $this->setupNewRelicLogger();

        $client = new Client();
        $promises = [];

        $counter = 0;
        $failCounter = 0;

        foreach ($this->accounts as $account) {
            if (!empty($account['accountZip']) && preg_match('/[0-9]{5}/', trim($account['accountZip']))) {
                $url = 'https://wxapi.digichief.com/api/weather/GetWeather/04a3f908-d85f-489e-a90e-f90f4011e314?zipcode=' . $account['accountZip'] . '&format=xml';
                $promises[] = $client->getAsync($url)->then(
                    function ($response) use ($account, &$counter) {
                        $result = $response->getBody()->getContents();
                        $xmlFile = simplexml_load_string($result);
                        if ($xmlFile !== false) {
                            $savePath = base_path('../feeds/digicache/') . $account['accountZip'] . '.XML';
                            try{
                                $xmlFile->asXML($savePath);
                                ++$this->processedAccounts; // Completed processing for this account
                                Log::info('Processing account with Zipcode: '. $account['accountZip'] . " Processed count: " . $counter);
                            }catch(Exception $e){
                                Log::error('Error Processing xml for '. $account['accountZip'] . " on Error: ". $e->getMessage());
                            }
                        } else {
                            throw new Exception('Error parsing XML for Zipcode ' . $account['accountZip']);
                        }
                    },
                    function ($exception) use ($account, &$failCounter, $newrelic_log) {
                        ++$this->failedAccounts; // Failed processing for this account;

                        Log::error('Error Processing account with Zipcode: '. $account['accountZip'] . " Processed count: " . $failCounter . " Exception: " . $exception->getMessage());

                        if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                            $newrelic_log->critical('Error processing account with zip code ' . $account['accountZip'], array('platform' => 'Feeds Aggregator', 'type' => 'error', 'message' => $exception->getMessage()));
                            newrelic_notice_error($exception);
                        }
                    }
                );
            }
        }

        // Using Promise to make asynchronous
        Utils::settle($promises)->wait();

        Log::info("Failed accounts count: ". $failCounter);
        Log::info("Processed accounts count on this batch: " . $counter);
        Log::info("---------------------------------------------------");
    }
}
