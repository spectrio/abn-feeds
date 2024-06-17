<?php

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Pttv extends CI_Controller {
	
	public function __construct() {
		
		parent::__construct ();
		
		$this->load->model('feeds_model');
	}

	public function set($location = null, $start = null, $end = null) {
		if (!is_null($location) && !is_null($start) && !is_null($end)) {
			if (strtolower($start) == "stop" || strtolower($end) == "stop") {
				$data = array(
						'location_id' => $location,
						'start' => null,
						'end' => null,
						'requested' => 0
				);				
			} else {
			    $processedStart = strtotime(str_replace('-', '/', $start)) - (60 * 60);
			    $processedEnd = strtotime(str_replace('-', '/', $end));
			    $data = array(
			        'location_id' => $location,
			        'start' => date('Y-m-d H:i:s', $processedStart),
			        'end' => date('Y-m-d H:i:s', $processedEnd),
			        'requested' => 0
			    );
			}
			/*$votedToday = ($lastDate->format('Y-m-d') == date('Y-m-d'));
			if ($)*/
			if ($this->feeds_model->set_pttv($data)) {
				$data["result"] = true;
			} else {
				$data["result"] = false;
			}
			echo json_encode($data);
			/*echo "<pre>";
			print_r($data);
			echo "</pre>";*/
			//echo $location." - ".gmdate("H:i:s A",strtotime($start))." - ".gmdate("H:i:s A", strtotime($end));
		}
	}
}
