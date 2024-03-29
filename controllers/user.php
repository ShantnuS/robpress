<?php
class User extends Controller {

	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($userid);

		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			$check = $this->Model->Users->fetch(array('username' => $username));

			if (!empty($check)) {
				StatusMessage::add('User already exists','danger');
			} else if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
			} else {
				$user = $this->Model->Users;
				// $user->copyfrom('POST'); // This needs to be changed
				$user->username = htmlspecialchars($username);
				$user->displayname = htmlspecialchars($displayname);
				$user->email = htmlspecialchars($email);
				$user->created = mydate();
				$user->bio = '';
				$user->level = 1;
				$user->setPassword($password);
				if(empty($displayname)) {
					$user->displayname = $user->username;
				}

				$user->salt = bin2hex(openssl_random_pseudo_bytes(32));
				$user->cookie = bin2hex(openssl_random_pseudo_bytes(32));

				//Set the users password
				$user->setPassword(hash_hmac("sha512", $user->password, $user->salt));

				$user->save();
				StatusMessage::add('Registration complete','success');
				return $f3->reroute('/user/login');
			}
		}
	}

	public function login($f3) {
		/** YOU MAY NOT CHANGE THIS FUNCTION - Make any changes in Auth->checkLogin, Auth->login and afterLogin() */
		if ($this->request->is('post')) {

			//Check for debug mode
			$settings = $this->Model->Settings;
			$debug = $settings->getSetting('debug');

			//Either allow log in with checked and approved login, or debug mode login
			list($username,$password) = array($this->request->data['username'],$this->request->data['password']);
			if (
				($this->Auth->checkLogin($username,$password,$this->request,$debug) && ($this->Auth->login($username,$password))) ||
				($debug && $this->Auth->debugLogin($username))) {

					$this->afterLogin($f3);

			} else {
				StatusMessage::add('Invalid username or password','danger');
			}
		}
	}

	/* Handle after logging in */
	private function afterLogin($f3) {
				StatusMessage::add('Logged in succesfully','success');

				//Redirect to where they came from
				// Open redirect - removing it completely and redirecting to home page
				// if(isset($_GET['from'])) {
				// 	$f3->reroute($_GET['from']);
				// } else {
				// 	$f3->reroute('/');
				// }
				$f3->reroute('/');
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');
	}


	public function profile($f3) {
		$id = $this->Auth->user('id');
		extract($this->request->data);
		$u = $this->Model->Users->fetch($id);
		$oldpass = $u->password;
		if($this->request->is('post')) {
			$u->copyfrom('POST');
			if(empty($u->password)) { $u->password = $oldpass; }

			/*
			//Handle avatar upload --- OLD ---
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
				$url = File::Upload($_FILES['avatar']);
				$u->avatar = $url;
			} else if(isset($reset)) {
				$u->avatar = '';
			}
			*/

			//Handle avatar upload
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
				//Check if file is a gif png or jpg
				$imgExts =  array('gif','png' ,'jpg');
				$img = $_FILES['avatar']['name'];
				$ext = pathinfo($img, PATHINFO_EXTENSION);
				if(!in_array($ext,$imgExts) ) {
					\StatusMessage::add('This is not a valid image file','danger');
					return $f3->reroute('/user/profile');
				}
				else{
					//Check the mime types of the image
					//Only jpg png or gif is allowed
					$imgmimes =  array('image/gif', 'image/png' ,'image/jpeg');
					$mimeType = $_FILES['avatar']['type'];
					if(!in_array($mimeType,$imgmimes)){
						\StatusMessage::add('The MIME types do not match','danger');
						return $f3->reroute('/user/profile');
					}
					else{
						$url = File::Upload($_FILES['avatar']);
						$u->avatar = $url;
					}
				}
			} else if(isset($reset)) {
				$u->avatar = '';
			}

			$u->save();
			\StatusMessage::add('Profile updated succesfully','success');
			return $f3->reroute('/user/profile');
		}
		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function promote($f3) {
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetch($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}

}
?>
