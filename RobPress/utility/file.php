<?php

class File {

	public static function Upload($array,$local=false) {
		$f3 = Base::instance();
		extract($array);
		$directory = getcwd() . '/uploads';
		$destination = $directory . '/' . $name;
		$webdest = '/uploads/' . $name;

		//Local files get moved
		if($local) {
			if (copy($tmp_name,$destination)) {
				chmod($destination,0666);
				return $webdest;
			} else {
				return false;
			}
		//POSTed files are done with move_uploaded_file
		} else {
			if (move_uploaded_file($tmp_name,$destination)) {
				chmod($destination,0666);
				return $webdest;
			} else {
				return false;
			}
		}
	}

}

?>
