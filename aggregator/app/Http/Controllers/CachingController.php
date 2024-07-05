<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use \AWS;
use \Config;
use \Cache;
use \Image;
use App\cacheAccount;
use App\cacheScroller;
use App\cacheTheme;
use App\badWords;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

use Monolog\Logger;
use NewRelic\Monolog\Enricher\{Handler, Processor};

class CachingController extends Controller
{
    public function __construct()
    {
        $this->cacheExpireMinutes = 10;
    }

    public function fillAccounts()
    {
        $accountJSON = $this->fillFeeds('allaccounts', 'FLSW9100201DTHCL');

        $accountsToUpdate = json_decode($accountJSON);

        $updatedAccounts = array();
        foreach ($accountsToUpdate as $account) {
            if (preg_match('/[0-9]{5}/', trim($account->accountID))) {
                $updatedAccounts[] = cacheAccount::updateOrCreate(['accountID' => $account->accountID], (array) $account);
            }
        }

        echo "<pre>";
        echo count($updatedAccounts) . PHP_EOL;
        echo print_r($updatedAccounts);
    }

    public function testAccounts()
    {
        $accountJSON = $this->fillFeeds('allaccounts', 'FLSW9100201DTHCL');

        $accountsToUpdate = json_decode($accountJSON);

        echo "<pre>";
        echo print_r($accountsToUpdate, true);
        echo "</pre>";
        exit;

        $updatedAccounts = array();
        foreach ($accountsToUpdate as $account) {
            if (preg_match('/[0-9]{5}/', trim($account->accountID))) {
                $updatedAccounts[] = cacheAccount::updateOrCreate(['accountID' => $account->accountID], (array) $account);
            }
        }

        echo "<pre>";
        echo count($updatedAccounts) . PHP_EOL;
        echo print_r($updatedAccounts);
    }

    public function fillScrollers()
    {
        $scrollerJSON = $this->fillFeeds('allscrollers', 'FLSW9100201DTHCL');

        $scrollersToUpdate = json_decode($scrollerJSON);

        $updatedScrollers = array();
        foreach ($scrollersToUpdate as $scroller) {
            $updatedScrollers[] = cacheScroller::updateOrCreate(['scrollerID' => $scroller->scrollerID], (array) $scroller);
        }

        echo "<pre>";
        echo count($updatedScrollers) . PHP_EOL;
        echo print_r($updatedScrollers);
    }

    public function buildScrollers()
    {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;

        $workBadWords = badWords::all()->toArray();
        $badWords = array();
        foreach ($workBadWords as $word) {
            $badWords[] = strtolower($word['word']);
        }
        $bannedCategories = array('war', 'disaster', 'religion', 'political');
        $rssIcons = array('topnews', 'arts', 'business', 'education', 'entertainment', 'health', 'humaninterest', 'international', 'labor', 'lifestyle', 'mlb', 'nba', 'nfl', 'nhl', 'politics', 'quirky', 'religion', 'science', 'social', 'sports', 'usnews', 'weather');
        $rssDefaultIcon = 'default';

        $data = array();
        $data['stories'] = array();
        $data['banned'] = array();

        // Load News Stories and Process
        $xml = simplexml_load_file(getenv('FEEDS_DIRECTORY_PATH') . '/digicache/AllStories.xml');

        if (property_exists($xml, 'news')) {
            $bannedCount = 0;
            foreach ($xml->news as $story) {
                $banned = false;
                $storyCategory = strtolower(str_replace(" ", "", (string)$story->category));
                if (!in_array($storyCategory, $bannedCategories)) {
                    $storyText = preg_replace('/\s+/', ' ', (string)$story->item);
                    $storyWords = explode(" ", $storyText);
                    $storyLength = strlen($storyText);
                    $punctuation = array(',', ':', '-', '!', '?', '.', ';', '\'');
                    $checkWords = array_unique($storyWords);
                    foreach ($checkWords as $checkTarget) {
                        if (in_array(str_replace($punctuation, '', strtolower($checkTarget)), $badWords)) {
                            $banned = true;
                            $bannedWord = $checkTarget;
                            $bannedCount++;
                            break;
                        }
                    }
                    if ($storyLength < 210 && $storyLength > 30 && !$banned) {
                        $data['stories'][] = array('text' => $storyText, 'category' => $storyCategory);
                    } else {
                        if ($storyLength >= 210 || $storyLength <= 30) {
                            $bannedReason = 'Story Length';
                        } else {
                            $bannedReason = $bannedWord;
                        }
                        $data['banned'][] = array('text' => $storyText, 'category' => $storyCategory, 'reason' => $bannedReason);
                    }
                }
            }
        }

        $xml = simplexml_load_file(getenv('FEEDS_DIRECTORY_PATH') .'/digicache/ThisDate.xml');

        if (property_exists($xml, 'item')) {
            $bannedCount = 0;
            foreach ($xml->item as $story) {
                $banned = false;
                $storyText = 'This Date in History: ' . preg_replace('/\s+/', ' ', (string)$story->description);
                $storyCategory = 'default';
                $storyWords = explode(" ", $storyText);
                $storyLength = strlen($storyText);
                $punctuation = array(',', ':', '-', '!', '?', '.', ';', '\'');
                $checkWords = array_unique($storyWords);
                foreach ($checkWords as $checkTarget) {
                    if (in_array(str_replace($punctuation, '', strtolower($checkTarget)), $badWords)) {
                        $banned = true;
                        $bannedWord = $checkTarget;
                        $bannedCount++;
                        break;
                    }
                }
                if ($storyLength < 210 && $storyLength > 30 && !$banned) {
                    $data['stories'][] = array('text' => $storyText, 'category' => $storyCategory);
                } else {
                    if ($storyLength >= 210 || $storyLength <= 30) {
                        $bannedReason = 'Story Length';
                    } else {
                        $bannedReason = $bannedWord;
                    }
                    $data['banned'][] = array('text' => $storyText, 'category' => $storyCategory, 'reason' => $bannedReason);
                }
            }
        }

        $xml = simplexml_load_file(getenv('FEEDS_DIRECTORY_PATH') .'/digicache/BornDate.xml');

        if (property_exists($xml, 'item')) {
            $bannedCount = 0;
            foreach ($xml->item as $story) {
                $banned = false;
                $storyText = 'Born on this Date: ' . preg_replace('/\s+/', ' ', (string)$story->description);
                $storyCategory = 'default';
                $storyWords = explode(" ", $storyText);
                $storyLength = strlen($storyText);
                $punctuation = array(',', ':', '-', '!', '?', '.', ';', '\'');
                $checkWords = array_unique($storyWords);
                foreach ($checkWords as $checkTarget) {
                    if (in_array(str_replace($punctuation, '', strtolower($checkTarget)), $badWords)) {
                        $banned = true;
                        $bannedWord = $checkTarget;
                        $bannedCount++;
                        break;
                    }
                }
                if ($storyLength < 210 && $storyLength > 30 && !$banned) {
                    $data['stories'][] = array('text' => $storyText, 'category' => $storyCategory);
                } else {
                    if ($storyLength >= 210 || $storyLength <= 30) {
                        $bannedReason = 'Story Length';
                    } else {
                        $bannedReason = $bannedWord;
                    }
                    $data['banned'][] = array('text' => $storyText, 'category' => $storyCategory, 'reason' => $bannedReason);
                }
            }
        }

        $stories = $data['stories'];
        shuffle($stories);

        $countStories = 0;
        $customerInserts = array();

        foreach ($stories as $storyKey => $storyArray) {
            if ($countStories >= 6) {
                $customerInserts[] = array('text' => 'Customer Scroller Inserts', 'category' => 'customer');
                $countStories = 0;
            }
            $customerInserts[] = $storyArray;
            $countStories++;
        }

        $data['stories'] = $customerInserts;


        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);
        $data['execution_time'] = $total_time;

        $xml_data = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><scroller></scroller>');
        self::array_to_xml($data, $xml_data);
        $result = $xml_data->asXML();

        /*echo "<pre>";
    	echo print_r($data, true);
    	echo print_r($result,true);
    	echo "</pre>";*/
        return Response::make($result, '200')->header('Content-Type', 'text/xml');
    }

    public function testBuildScrollers()
    {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;

        $badWords = badWords::all()->toArray();
        echo "<pre>";
        echo print_r($badWords, true);

        $bannedCategories = array('war', 'disaster', 'religion', 'political');
        $rssIcons = array('topnews', 'arts', 'business', 'education', 'entertainment', 'health', 'humaninterest', 'international', 'labor', 'lifestyle', 'mlb', 'nba', 'nfl', 'nhl', 'politics', 'quirky', 'religion', 'science', 'social', 'sports', 'usnews', 'weather');
        $rssDefaultIcon = 'default';

        $data = array();
        $data['stories'] = array();
        $data['banned'] = array();

        // Load News Stories and Process
        $xml = simplexml_load_file(getenv('FEEDS_DIRECTORY_PATH') . '/digicache/AllStories.xml');

        if (property_exists($xml, 'news')) {
            $bannedCount = 0;
            foreach ($xml->news as $story) {
                $banned = false;
                $storyCategory = strtolower(str_replace(" ", "", (string)$story->category));
                if (!in_array($storyCategory, $bannedCategories)) {
                    $storyText = preg_replace('/\s+/', ' ', (string)$story->item);
                    $storyWords = explode(" ", $storyText);
                    $storyLength = strlen($storyText);
                    $punctuation = array(',', ':', '-', '!', '?', '.', ';', '\'');
                    $checkWords = array_unique($storyWords);
                    foreach ($checkWords as $checkTarget) {
                        if (in_array(str_replace($punctuation, '', strtolower($checkTarget)), $badWords)) {
                            $banned = true;
                            $bannedWord = $checkTarget;
                            $bannedCount++;
                            break;
                        }
                    }
                    if ($storyLength < 210 && $storyLength > 30 && !$banned) {
                        $data['stories'][] = array('text' => $storyText, 'category' => $storyCategory);
                    } else {
                        if ($storyLength >= 210 || $storyLength <= 30) {
                            $bannedReason = 'Story Length';
                        } else {
                            $bannedReason = $bannedWord;
                        }
                        $data['banned'][] = array('text' => $storyText, 'category' => $storyCategory, 'reason' => $bannedReason);
                    }
                }
            }
        }

        $xml = simplexml_load_file(getenv('FEEDS_DIRECTORY_PATH') .'/digicache/ThisDate.xml');

        if (property_exists($xml, 'item')) {
            $bannedCount = 0;
            foreach ($xml->item as $story) {
                $banned = false;
                $storyText = 'This Date in History: ' . preg_replace('/\s+/', ' ', (string)$story->description);
                $storyCategory = 'default';
                $storyWords = explode(" ", $storyText);
                $storyLength = strlen($storyText);
                $punctuation = array(',', ':', '-', '!', '?', '.', ';', '\'');
                $checkWords = array_unique($storyWords);
                foreach ($checkWords as $checkTarget) {
                    if (in_array(str_replace($punctuation, '', strtolower($checkTarget)), $badWords)) {
                        $banned = true;
                        $bannedWord = $checkTarget;
                        $bannedCount++;
                        break;
                    }
                }
                if ($storyLength < 210 && $storyLength > 30 && !$banned) {
                    $data['stories'][] = array('text' => $storyText, 'category' => $storyCategory);
                } else {
                    if ($storyLength >= 210 || $storyLength <= 30) {
                        $bannedReason = 'Story Length';
                    } else {
                        $bannedReason = $bannedWord;
                    }
                    $data['banned'][] = array('text' => $storyText, 'category' => $storyCategory, 'reason' => $bannedReason);
                }
            }
        }

        $xml = simplexml_load_file(getenv('FEEDS_DIRECTORY_PATH') .'/digicache/BornDate.xml');

        if (property_exists($xml, 'item')) {
            $bannedCount = 0;
            foreach ($xml->item as $story) {
                $banned = false;
                $storyText = 'Born on this Date: ' . preg_replace('/\s+/', ' ', (string)$story->description);
                $storyCategory = 'default';
                $storyWords = explode(" ", $storyText);
                $storyLength = strlen($storyText);
                $punctuation = array(',', ':', '-', '!', '?', '.', ';', '\'');
                $checkWords = array_unique($storyWords);
                foreach ($checkWords as $checkTarget) {
                    if (in_array(str_replace($punctuation, '', strtolower($checkTarget)), $badWords)) {
                        $banned = true;
                        $bannedWord = $checkTarget;
                        $bannedCount++;
                        break;
                    }
                }
                if ($storyLength < 210 && $storyLength > 30 && !$banned) {
                    $data['stories'][] = array('text' => $storyText, 'category' => $storyCategory);
                } else {
                    if ($storyLength >= 210 || $storyLength <= 30) {
                        $bannedReason = 'Story Length';
                    } else {
                        $bannedReason = $bannedWord;
                    }
                    $data['banned'][] = array('text' => $storyText, 'category' => $storyCategory, 'reason' => $bannedReason);
                }
            }
        }

        $stories = $data['stories'];
        shuffle($stories);

        $countStories = 0;
        $customerInserts = array();

        foreach ($stories as $storyKey => $storyArray) {
            if ($countStories >= 6) {
                $customerInserts[] = array('text' => 'Customer Scroller Inserts', 'category' => 'customer');
                $countStories = 0;
            }
            $customerInserts[] = $storyArray;
            $countStories++;
        }

        $data['stories'] = $customerInserts;


        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);
        $data['execution_time'] = $total_time;

        $xml_data = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><scroller></scroller>');
        self::array_to_xml($data, $xml_data);
        $result = $xml_data->asXML();

        /*echo "<pre>";
         echo print_r($data, true);
         echo print_r($result,true);
         echo "</pre>";*/
        return Response::make($result, '200')->header('Content-Type', 'text/xml');
    }

    private function array_to_xml($data, &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                self::array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }


    public function fillThemes()
    {
        $themeJSON = $this->fillFeeds('allthemes', 'FLSW9100201DTHCL');

        $themesToUpdate = json_decode($themeJSON);

        $updatedThemes = array();
        foreach ($themesToUpdate as $theme) {
            if (preg_match('/[a-zA-z]{4}[0-9]{7}[a-zA-z]{5}/', $theme->channelName) && strlen($theme->channelName) == 16) {
                $updatedThemes[] = cacheTheme::updateOrCreate(['channelName' => $theme->channelName], (array) $theme);
            }
        }

        echo "<pre>";
        echo count($updatedThemes) . PHP_EOL;
        echo print_r($updatedThemes);
    }

    public function fillFeeds($feedType = null, $channel = null)
    {
        if (!is_null($channel)) {
            $feedType = strtolower($feedType);
            $accountID = substr($channel, 4, 5);
            if ($accountID == '91002' || $accountID == '12345') {
                $baseDomain = getenv('HTTPS') . getenv('GM_ENDPOINT');
            } else {
                $baseDomain = getenv('HTTPS'). getenv('REMOTE_BETA_ENDPOINT');
            }
            $this->cacheExpireMinutes = 5;
            switch ($feedType) {
                case 'scroller':
                    $url = '/source/get_scroller/';
                    break;
                case 'menuboard':
                    $url = '/source/get_menuboard/';
                    break;
                case 'dtv':
                    $url = '/source/get_dtv/';
                    break;
                case 'theme':
                    $url = '/source/get_theme/';
                    break;
                case 'account':
                    $url = '/source/get_account_details/';
                    $this->cacheExpireMinutes = 60;
                    break;
                case 'allaccounts':
                    $url = '/source/get_all_account_details/';
                    break;
                case 'allscrollers':
                    $url = '/source/get_all_scroller_details/';
                    break;
                case 'allthemes':
                    $url = '/source/get_all_theme_details/';
                    break;
                default:
                    $url = '/source/get_scroller/';
                    break;
            }

            $workURL = $baseDomain . $url;
            if ($feedType == 'account') {
                $workURL .= $accountID . "/abn-scala-request-" . strrev($accountID);
            } elseif ($feedType == 'allaccounts' || $feedType == 'allscrollers' || $feedType == 'allthemes') {
                $workURL .= "XX12345XX4752ZZ";
            } else {
                $workURL .= $channel . "/abn-scala-request-" . strrev($channel);
            }

            $url = $workURL;

            $rememberKey = sha1($url);

            $data = file_get_contents($url);
            return $data;

            return Cache::remember($rememberKey, $this->cacheExpireMinutes, function () use ($url) {
                $data = file_get_contents($url);
                return $data;
                return Response::make($data, '200')->header('Content-Type', 'application/json');
            });
        }
        return Response::make("", '200')->header('Content-Type', 'application/json');
    }

    public function cacheLogos($sliceSize = 5)
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        if ($sliceSize && $sliceSize >= 1 && $sliceSize < 99999) {
            Log::info("============================================================");
            Log::info("Beginning Logo Cache Process with Slice Size of: " . $sliceSize);
            Log::info("============================================================");
            $url = getenv('HTTPS') . getenv('GM_ENDPOINT').'/source/get_all_account_numbers/XX12345XX4752ZZ';
            $data = json_decode(file_get_contents($url), true);
            $accountList = array();
            foreach ($data as $row) {
                if (isset($row['accountNumber'])) {
                    $accountList[] = $row['accountNumber'];
                }
            }
            $accountList = array_slice($accountList, 0, $sliceSize);
            $s3Upload = AWS::createClient('s3');
            $workData = array();
            foreach ($accountList as $accountNumber) {
                $priLogoURL = 'https://app.abn.live/api/accounts/' . $accountNumber . '/logo';
                $tmpPriLogo = @file_get_contents($priLogoURL);
                if ($tmpPriLogo === FALSE) {
                    Log::error('PRIMARY Logo URL failed: ' . $accountNumber);
                    continue;
                }
                $altLogoURL = 'https://app.abn.live/api/accounts/' . $accountNumber . '/alternate_logo';
                $tmpAltLogo = @file_get_contents($altLogoURL);
                if ($tmpAltLogo === FALSE) {
                    Log::error('ALTERNATE Logo URL failed: ' . $accountNumber);
                    continue;
                }
                $workData[] = array(
                    'accountNumber' => $accountNumber,
                    'priLogoURL' => $priLogoURL,
                    'altLogoURL' => $altLogoURL,
                    'primaryLogo' => $tmpPriLogo,
                    'alternateLogo' => $tmpAltLogo,
                    'awsKey' => 'logos/' . $accountNumber . '/'
                );
            }

            foreach ($workData as $workRow) {
                $logoName = $workRow['accountNumber'] . '_ABN_LIVE_LOGO.png';
                $tmpImage = @file_get_contents($workRow['primaryLogo']);
                if ($tmpImage === FALSE) {
                    Log::error('PRIMARY Logo download failed for account: ' . $workRow['accountNumber']);
                } else {
                    $imgWork = Image::make($tmpImage)->trim()->encode('png');
                    try {
                        $workRow['primaryResult'] = $s3Upload->putObject([
                            'Bucket' => 'abnlogos',
                            'Key'    => $workRow['awsKey'] . $logoName,
                            'Body'   => (string) $imgWork,
                            'ACL'    => 'public-read',
                            'ContentType' => 'image/png',
                        ]);
                    } catch (Aws\S3\Exception\S3Exception $e) {
                        Log::info("Amazon Logo Upload Error: " . $e);
                        Log::info(print_r($workRow, true));
                    }
                }

                $logoName = $workRow['accountNumber'] . '_ABN_LIVE_LOGO_WHITE.png';
                $tmpImage = @file_get_contents($workRow['alternateLogo']);
                if ($tmpImage === FALSE) {
                    Log::error('ALTERNATE Logo download failed for account: ' . $workRow['accountNumber']);
                } else {
                    $imgWork = Image::make($tmpImage)->trim()->encode('png');
                    try {
                        $workRow['alternateResult'] = $s3Upload->putObject([
                            'Bucket' => 'abnlogos',
                            'Key'    => $workRow['awsKey'] . $logoName,
                            'Body'   => (string) $imgWork,
                            'ACL'    => 'public-read',
                            'ContentType' => 'image/png',
                        ]);
                    } catch (Aws\S3\Exception\S3Exception $e) {
                        Log::info("Amazon Logo Upload Error: " . $e);
                        Log::info(print_r($workRow, true));
                    }
                }
            }
            Log::info("=========================================================");
            Log::info("Ending Logo Cache Process with Slice Size of: " . $sliceSize);
            Log::info("=========================================================");
            return Response::make($workData, '200')->header('Content-Type', 'application/json');
        }
        return Response::make(array('error' => 'Incorrect Slice Size!'), '500')->header('Content-Type', 'application/json');
    }

    public function fillWeather()
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        libxml_use_internal_errors(true);

        // Monolog Logger
        $newrelic_log = new Logger('log');
        $newrelic_log->pushProcessor(new Processor);

        $handler = new Handler;
        $handler->setLicenseKey('ee2d839bc858a1b5feef951fb834dbdeFFFFNRAL');

        $context = stream_context_create(['http' => ['ignore_errors' => true]]);
        $accountsToUpdate = cacheAccount::all();
        foreach ($accountsToUpdate as $account) {
			try {
				if (preg_match('/[0-9]{5}/', trim($account->accountID))) {
					if (preg_match('/[0-9]{5}/', trim($account->accountZip))) {
						$url = 'https://wxapi.digichief.com/api/weather/GetWeather/04a3f908-d85f-489e-a90e-f90f4011e314?zipcode=' . $account->accountZip . '&format=xml';
						$result = file_get_contents($url, false, $context);

						if ($result == false) {
							throw new Exception('Could not fetch content from URL: ' . $url);
						}

						$xmlFile = simplexml_load_string($result);
						Log::info('XML is saved for account ' . $account->accountID);

						if ($xmlFile === false) {
							throw new Exception('Error parsing XML from URL: ' . $url);
						}

						// $savePath = '/var/www/scala-weather-feed/aggregator/feeds/digicache/' . $account->accountZip . '.XML';
						$savePath = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/' . $account->accountZip . '.XML';
						$newrelic_log->pushHandler(new Handler(Logger::INFO));
						$newrelic_log->info('XML file is saved for account ' . $account->accountID, array('platform' => 'Feeds Aggregator', 'type' => 'info', 'message' => 'File saved for account ' . $account->accountID));

						if ($xmlFile->asXML($savePath) === false) {
							throw new Exception('Could not retrieve weather for account ' . $account->accountID . ' with zip code ' . $account->accountZip . ' at URL ' . $url);
						}
					}
				}
			} catch (Exception $e) {
				Log::error($e->getMessage());
				Log::error('Error processing account ' . $account->accountID . ' with zip code ' . $account->accountZip . ': ');

				$newrelic_log->pushHandler(new Handler(Logger::CRITICAL));
				$newrelic_log->critical('Error processing account ' . $account->accountID . ' with zip code ' . $account->accountZip, array('platform' => 'Feeds Aggregator', 'type' => 'error', 'message' => $e->getMessage()));

				if (extension_loaded('newrelic')) { // Ensure PHP agent is available
					newrelic_notice_error($e);
				}
			}
        }

        $newrelic_log->pushHandler(new Handler(Logger::INFO));
		$newrelic_log->info('All accounts are updated');

        // No error
        if (extension_loaded('newrelic')) { // Ensure PHP agent is available
            newrelic_record_custom_event("All accounts are updated", []);
        }

        return Response::make($accountsToUpdate, '200')->header('Content-Type', 'application/json');
    }
}