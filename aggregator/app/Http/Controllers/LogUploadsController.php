<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use \Log;
use \Storage;

class LogUploadsController extends Controller
{
    protected $request;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function uploadSubmit()
    {
        @ini_set('upload_max_size','300M');
        @ini_set('post_max_size', '302M');
        $files = $this->request->file('file');
        $fullRequest = print_r($this->request->input(),true);
        //Log::info("Full Request:\n".$fullRequest);
        $logRequest = print_r($this->request->all(),true);
        //Log::info($logRequest);
        $filesRequest = print_r($files, true);
        $requestData = $this->request->input('data');
        //Log::info($filesRequest);
        
        if (array_key_exists('file',$this->request->all())) {
            if (is_array($files)) {
                foreach($files as $file) {
                    $this->LogAndStorePlayerUpload($file,$requestData);
                }
            } else {
                $file = $files;
                $this->LogAndStorePlayerUpload($file,$requestData);
            }
        } else {
            Log::info("File Array Key did not Exist");
        }
        return 'Upload successful!';
    }
    
    public function listMagicInfo()
    {
        $destinationPath = public_path().'/magicinfo/*';
        $fileList = glob($destinationPath);
        foreach ($fileList as $file) {
            $fileName = basename($file);
            echo '<a target="_blank" href="/aggregator/magicinfo/'.$fileName.'">'.$fileName.'</a><br />';
        }
    }
    
    public function uploadMagicInfo()
    {
        @ini_set('upload_max_size','300M');
        @ini_set('post_max_size', '302M');
        $files = $this->request->file('file');
        $destinationPath = public_path().'/magicinfo/';
        if (array_key_exists('file',$this->request->all())) {
            if (is_array($files)) {
                foreach($files as $file) {
                    $file->move($destinationPath,$file->getClientOriginalName());
                }
            } else {
                $file = $files;
                $file->move($destinationPath,$file->getClientOriginalName());
            }
        } else {
            Log::info("File Array Key did not Exist");
        }
        return 'Upload successful!';
    }
    
    public function listASC()
    {
        $destinationPath = public_path().'/asc/*';
        $fileList = glob($destinationPath);
        foreach ($fileList as $file) {
            $fileName = basename($file);
            echo '<a target="_blank" href="/aggregator/asc/'.$fileName.'">'.$fileName.'</a><br />';
        }
    }
    
    public function uploadASC()
    {
        $files = $this->request->file('file');
        $destinationPath = public_path().'/asc/';
        if (array_key_exists('file',$this->request->all())) {
            if (is_array($files)) {
                foreach($files as $file) {
                    $file->move($destinationPath,$file->getClientOriginalName());
                }
            } else {
                $file = $files;
                $file->move($destinationPath,$file->getClientOriginalName());
            }
        } else {
            Log::info("File Array Key did not Exist");
        }
        return 'Upload successful!';
    }
    
    private function LogAndStorePlayerUpload($file, $requestData) {
        $destinationPath = public_path().'/playerLogUploads/';
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = strtolower(preg_replace("/[^a-zA-Z0-9\s\-]/", "", basename($file->getClientOriginalName(),".".$fileExtension)));
        $fileName = preg_replace('/\s+/', ' ',$fileName);
        $fileName = str_replace(" ","_",$fileName);
        $fileName = $fileName.".".$fileExtension;
        $workPlayerName = explode('---',$fileName);
        Log::info("Player BLARS Log Upload - Player Name: ".$workPlayerName[0]." - File Name: ".$fileName." - Timestamp: ".date('l jS \of F Y h:i:s A'));
        Log::info("Request Data:\n".print_r(json_decode($requestData,true),true));
        $file->move($destinationPath, $fileName);
    }
}
