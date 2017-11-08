<?php

namespace Admin;

class Comment extends AdminController {

	public function index($f3) {
		//Unmoderated comments
		$un = $this->Model->Comments->fetchAll(array('moderated' => 0));
		$unmoderated = $this->Model->map($un,'user_id','Users');
		$unmoderated = $this->Model->map($un,'blog_id','Posts',true,$unmoderated);

		//Moderated comments
		$mod = $this->Model->Comments->fetchAll(array('moderated' => 1));
		$moderated = $this->Model->map($mod ,'user_id','Users');
		$moderated = $this->Model->map($mod ,'blog_id','Posts',true,$moderated);

		$f3->set('unmoderated',$unmoderated);
		$f3->set('moderated',$moderated);
	}

	public function edit($f3) {
		$id = $f3->get('PARAMS.3');
		$comment = $this->Model->Comments->fetch($id);
		if($this->request->is('post')) {
			$comment->copyfrom('POST');
			$comment->save();
			\StatusMessage::add('Comment updated succesfully','success');
			return $f3->reroute('/admin/comment');
		} 
		$_POST = $comment;
		$f3->set('comment',$comment);
	}

}

?>
