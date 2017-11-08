<?php

class Api extends Controller {
	
	public function display($f3) {
		extract($f3->get('PARAMS'));
		extract($f3->get('GET'));

		//Check for authentication token and fail without
		if(!isset($token) || $token != $f3->get('site.apikey')) {
			echo json_encode(array('error' => '403')); die();
		}

		//Provide API access
		if(!isset($id)) {
			$results = array();
			$result = $this->Model->$model->fetchAll();
			foreach($result as $r) {
				$results[] = $r->cast();
			}
		} else {
			$result = $this->Model->$model->fetch($id);
			$results = $result->cast();
		}

		//File not found
		if(empty($results)) { 
			echo json_encode(array('error' => '404')); die();
		}
		
		echo json_encode($results);
		exit();
	}

}

?>
