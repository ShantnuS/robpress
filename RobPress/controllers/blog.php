<?php
class Blog extends Controller {
	
	public function index($f3) {	
		if ($f3->exists('PARAMS.3')) {
			$categoryid = $f3->get('PARAMS.3');
			$category = $this->Model->Categories->fetch($categoryid);
			$postlist = array_values($this->Model->Post_Categories->fetchList(array('id','post_id'),array('category_id' => $categoryid)));
			$posts = $this->Model->Posts->fetchAll(array('id' => $postlist, 'published' => 'IS NOT NULL'),array('order' => 'published DESC'));
			$f3->set('category',$category);
		} else {
			$posts = $this->Model->Posts->fetchPublished();
		}

		$blogs = $this->Model->map($posts,'user_id','Users');
		$blogs = $this->Model->map($posts,array('post_id','Post_Categories','category_id'),'Categories',false,$blogs);
		$f3->set('blogs',$blogs);
	}

	public function view($f3) {
		$id = $f3->get('PARAMS.3');
		if(empty($id)) {
			return $f3->reroute('/');
		}
		$post = $this->Model->Posts->fetch($id);
		if(empty($post)) {
			return $f3->route('/');
		}
		
		$blog = $this->Model->map($post,'user_id','Users');
		$blog = $this->Model->map($post,array('post_id','Post_Categories','category_id'),'Categories',false,$blog);

		$comments = $this->Model->Comments->fetchAll(array('blog_id' => $id));
		$allcomments = $this->Model->map($comments,'user_id','Users');

		$f3->set('comments',$allcomments);
		$f3->set('blog',$blog);		
	}

	public function reset($f3) {
		$allposts = $this->Model->Posts->fetchAll();
		$allcategories = $this->Model->Categories->fetchAll();
		$allcomments = $this->Model->Comments->fetchAll();
		$allmaps = $this->Model->Post_Categories->fetchAll();
		foreach($allposts as $post) $post->erase();
		foreach($allcategories as $cat) $cat->erase();
		foreach($allcomments as $com) $com->erase();
		foreach($allmaps as $map) $map->erase();
		StatusMessage::add('Blog has been reset');
		return $f3->reroute('/');
	}

	public function comment($f3) {
		$id = $f3->get('PARAMS.3');
		$post = $this->Model->Posts->fetch($id);
		if($this->request->is('post')) {
			$comment = $this->Model->Comments;
			$comment->copyfrom('POST');
			$comment->blog_id = $id;
			$comment->created = mydate();

			//Moderation of comments
			if (!empty($this->Settings['moderate']) && $this->Auth->user('level') < 2) {
				$comment->moderated = 0;
			} else {
				$comment->moderated = 1;
			}

			//Default subject
			if(empty($this->request->data['subject'])) {
				$comment->subject = 'RE: ' . $post->title;
			}

			$comment->save();

			//Redirect
			if($comment->moderated == 0) {
				StatusMessage::add('Your comment has been submitted for moderation and will appear once it has been approved','success');
			} else {
				StatusMessage::add('Your comment has been posted','success');
			}
			return $f3->reroute('/blog/view/' . $id);
		}
	}

	public function moderate($f3) {
		list($id,$option) = explode("/",$f3->get('PARAMS.3'));
		$comments = $this->Model->Comments;
		$comment = $comments->fetch($id);

		$post_id = $comment->blog_id;
		//Approve
		if ($option == 1) {
			$comment->moderated = 1;
			$comment->save();
		} else {
		//Deny
			$comment->erase();
		}
		StatusMessage::add('The comment has been moderated');
		$f3->reroute('/blog/view/' . $comment->blog_id);
	}

	public function search($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			$f3->set('search',$search);

			//Get search results
			$search = str_replace("*","%",$search); //Allow * as wildcard
			$ids = $this->db->connection->exec("SELECT id FROM `posts` WHERE `title` LIKE \"%$search%\" OR `content` LIKE '%$search%'");
			$ids = Hash::extract($ids,'{n}.id');
			if(empty($ids)) {
				StatusMessage::add('No search results found for ' . $search); 
				return $f3->reroute('/blog/search');
			}

			//Load associated data
			$posts = $this->Model->Posts->fetchAll(array('id' => $ids));
			$blogs = $this->Model->map($posts,'user_id','Users');
			$blogs = $this->Model->map($posts,array('post_id','Post_Categories','category_id'),'Categories',false,$blogs);

			$f3->set('blogs',$blogs);
			$this->action = 'results';	
		}
	}
}
?>
