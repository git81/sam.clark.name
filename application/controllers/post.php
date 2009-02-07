<?php defined('SYSPATH') OR die('No direct access allowed.');

class Post_Controller extends ScribeController implements Crud
{
	public function index()
	{
		
	}

	public function create()
	{
		$post_form = Morf::factory(array('post' => TRUE))
							->input('title')
							->input('slug')
							->textarea('contents')
							->input('tags')
							->checkbox('display_visible', 'display_visible')
							->checkbox('comment_status', 'comment_status');

		if ($new_post = $this->input->post())
		{
			$success = ORM::factory('Post')->create($new_post);
			if ($success)
				url::redirect('post/index');
			else
				$post_form->add_error($new_post->errors());
		}

		echo $post_form;
	}

	public function read($id = NULL, $page = NULL)
	{
		
	}

	public function update($id = NULL)
	{
		
	}

	public function delete($id = NULL)
	{
		
	}
} // End