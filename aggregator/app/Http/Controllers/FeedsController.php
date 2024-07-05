<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\cacheDevice;
use \Config;
use \Cache;
use \Response;

class FeedsController extends Controller
{
    public function __construct()
    {
        $this->cacheExpireMinutes = 10;
    }
    
    public function digichiefFeeds($feedType = 'news')
    {
        if (!is_null($feedType)) {
            $feedType = strtolower($feedType);
            switch ($feedType) {
                case 'news':
                    $url = Config::get('digichief.xmlAllNewsEndpoint');
                    break;
                case 'borndate':
                    $this->cacheExpireMinutes = 60;
                    $url = Config::get('digichief.xmlBornDateEndpoint');
                    break;
                case 'thisdate':
                    $this->cacheExpireMinutes = 60;
                    $url = Config::get('digichief.xmlThisDateHistoryEndpoint');
                    break;
                default:
                    $url = Config::get('digichief.xmlAllNewsEndpoint');
                    break;
            }
            
            $rememberKey = sha1($url);
            
            return Cache::remember($rememberKey, $this->cacheExpireMinutes, function () use ($url) {
                $data = file_get_contents($url);
                return Response::make($data, '200')->header('Content-Type', 'application/xml');
            });
        }
        return Response::make("", '200')->header('Content-Type', 'application/xml');
    }
    
    public function portalFeeds($feedType = null, $channel = null)
    {
        if (!is_null($channel)) {
            $feedType = strtolower($feedType);
            $accountID = substr($channel,4,5);
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
                default:
                    $url = '/source/get_scroller/';
                    break;
            }
            
            $workURL = $baseDomain.$url;
            if ($feedType = 'account') {
                $workURL.= $accountID."/abn-scala-request-".strrev($accountID);
            } else {
                $workURL.= $channel."/abn-scala-request-".strrev($channel);
            }
            
            $url = $workURL;
            
            $rememberKey = sha1($url);
            
            return Cache::remember($rememberKey, $this->cacheExpireMinutes, function () use ($url) {
                $data = file_get_contents($url);
                return Response::make($data, '200')->header('Content-Type', 'application/json');
            });
        }
        return Response::make("", '200')->header('Content-Type', 'application/json');
    }
    
    public function deviceConfiguration($name = null, Request $request)
    {
        $headerKey = $request->header('ABN-Authorization');
        if ($headerKey == '4OnkuS8rM7xdjNu9hd33t5j9rKY6cH4U' || 1 == 1) {
            $deviceConfig = cacheDevice::where('name', strtoupper($name))->get()->toArray();
            if (count($deviceConfig) > 0) {
                $returnConfig = $deviceConfig[0];
                //unset($returnConfig['dealerCode']);
                unset($returnConfig['mac']);

                $channel = $returnConfig['locationID'];
                $url = getenv('HTTPS') . getenv('REMOTE_BETA_ENDPOINT') . "/source/get_dtv/";
                $accountNumber = substr($channel,4,5);
                $json = file_get_contents($url.$channel."/abn-scala-request-".strrev($channel));
                $obj = json_decode($json);

                $workData = $obj[0];
                $pathParts = pathinfo($workData->dtv_chrome_logo);
                $basePath = $pathParts['dirname'];
                $returnConfig['lightLogo'] = str_replace("/content/",getenv('HTTPS') . getenv('ABNCDN'),$basePath."/".$accountNumber."_ABN_LIVE_LOGO_WHITE.png");
                $returnConfig['darkLogo'] = str_replace("/content/",getenv('HTTPS') . getenv('ABNCDN'),$basePath."/".$accountNumber."_ABN_LIVE_LOGO.png");

                $returnConfig['dealerCodeJSON'] = $returnConfig['dealerCode'];
                $returnConfig['dealerCode'] = json_decode($returnConfig['dealerCode'], true);
                
                $boolArray = array('false' => false, 'true' => true);
                $returnConfig['notification'] = $boolArray[$returnConfig['notification']];
                $returnConfig['pttv'] = $boolArray[$returnConfig['pttv']];
                $returnConfig['hdpttv'] = $boolArray[$returnConfig['hdpttv']];
                $returnConfig['abnLive'] = $boolArray[$returnConfig['abnLive']];
                $returnConfig['scriptDebugging'] = $boolArray[$returnConfig['scriptDebugging']];
                $returnConfig['clearLocalStorage'] = $boolArray[$returnConfig['clearLocalStorage']];
                return response()->json($returnConfig);
            } else {
                return response()->json(['error' => 'Cannot find a device with the name of: '.$name]);
            }
        }
        return Response::make("", '200')->header('Content-Type', 'application/json');
    }
}
