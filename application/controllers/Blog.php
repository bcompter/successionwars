<?php
/*
 * Developer Blog
 */
class Blog extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('blogmodel');

    }    
    
    /**
     * Blog homepage
     * List latest published posts
     */
    function index()
    {
        $page = $this->page;
    
	$page['posts'] = $this->blogmodel->get_last();
        
        $page['content'] = 'blog_index';
        $this->load->view('template', $page);
    }
    
    /**
     * Preview a post
     * @param type $post_id 
     */
    function preview($post_id=0)
    {
        $page = $this->page;
        
        // Valid input
        if ($post_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Away we go
        $this->load->model('blogmodel');
        $page['content'] = 'blog_index';
        $page['posts'] = $this->blogmodel->get_by_id($post_id);
        
        $this->load->view('template', $page);
        
    }  // end preview
    
    /**
     * View a post, including an add comment field
     * @param type $post_id 
     */
    function view_post($post_id=0)
    {
        $page = $this->page;
        
        // Valid input
        if ($post_id == 0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Fetch the post
        $post = $this->blogmodel->get_by_id($post_id);
        if (!isset($post->post_id))
        {
            $this->page['error'] = 'No such post.';
            $this->index();
            return;
        }
        
        // Away we go
        $this->load->model('blogmodel');
        $page['content'] = 'blog_index';
        $page['posts'] = $this->blogmodel->get_by_id_result($post_id);
        
        $this->load->view('template', $page);
        
    }  // end view_post
    
    /**
     * Add a comment to a post
     * @param type $post_id 
     */
    function add_comment($post_id=0)
    {
        $page = $this->page;
        
        // Valid input
        if ($post_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
    }
    
    /**
     * Admin dashboard
     */
    function dashboard()
    {
        $page = $this->page;
        
        // Signed in?
        if (!isset($page['user']->id))
        {
            redirect('auth/login', 'refresh');
        }
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Away we go
        $this->load->model('blogmodel');
        $page['drafts'] = $this->blogmodel->get_drafts();
        
        $page['content'] = 'blog_dashboard';
        $this->load->view('template', $page);
    }
    
    /**
     * Create a new draft post
     */
    function create_post()
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // away we go
        $post = new stdClass();
        $post->author_id = $page['user']->id;
        $date = new DateTime();
        $post->last_edit = $date->format('Y-m-d H:i:s');
        $this->blogmodel->create($post);
        
        // Redirect to edit 
        $insert_id = $this->db->insert_id();
        redirect('blog/edit_post/'.$insert_id, 'refresh');
    }
    
    /**
     * Edit a post
     * @param type $post_id 
     */
    function edit_post($post_id=0)
    {
        $page = $this->page;
        $this->load->helper('form');
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Valid input
        if ($post_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Fetch the post
        $post = $this->blogmodel->get_by_id($post_id);
        if (!isset($post->post_id))
        {
            $this->page['error'] = 'No such post.';
            $this->index();
            return;
        }
        
        // Load the view
        $page['post'] = $post;
        $page['content'] = 'blog_edit';
        $this->load->view('template', $page);
    }
    
    /**
     * Save changes to a post
     * @param type $post_id 
     */
    function save_post($post_id=0)
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Valid input
        if ($post_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Fetch the post
        $post = $this->blogmodel->get_by_id($post_id);
        if (!isset($post->post_id))
        {
            $this->page['error'] = 'No such post.';
            $this->index();
            return;
        }
        
        $text = $this->input->post('content');
        $title = $this->input->post('title');

        // Save changes
        $post_update = new stdClass();
        $post_update->text = $text;
        $post_update->title = $title;
        
        $titleurl = str_replace(' ', '_', $title);
        $titleurl = str_replace('\'', '', $titleurl);
        $post_update->title_url = strtolower($titleurl);
        
        $date = new DateTime();
        $post_update->last_edit = $date->format('Y-m-d H:i:s');
        $this->blogmodel->update($post_id, $post_update);
        
        // Back to edit
        redirect('blog/edit_post/'.$post_id);
    }
    
    /**
     * Change post state to published
     * @param type $post_id 
     */
    function publish_post($post_id=0)
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Valid input
        if ($post_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Fetch the post
        $post = $this->blogmodel->get_by_id($post_id);
        if (!isset($post->post_id))
        {
            $this->page['error'] = 'No such post.';
            $this->dashboard();
            return;
        }
        if ($post->status != 'Draft')
        {
            $this->page['error'] = 'Can\'t publish a non-draft post.';
            $this->dashboard();
            return;
        }
        
        // Update status
        $post_update = new stdClass();
        $post_update->status = 'Published';
        $date = new DateTime();
        $post_update->published_on = $date->format('Y-m-d H:i:s');
        $this->blogmodel->update($post->post_id, $post_update);
        
        // redirect to dashboard
        $this->page['notice'] = 'Post Published!';
        $this->dashboard();
    }
    
    /**
     * Delete a post
     * @param type $post_id 
     */
    function delete_post($post_id=0)
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Valid input
        if ($post_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Fetch the post
        $post = $this->blogmodel->get_by_id($post_id);
        if (!isset($post->post_id))
        {
            $this->page['error'] = 'No such post.';
            $this->index();
            return;
        }
        
        // Delete!
        $this->blogmodel->delete($post_id);
        
        // redirect to dashboard
        $this->page['notice'] = 'Post Deleted!';
        $this->dashboard();
    }
    
    /**
     * View all posts
     * Admin only
     */
    function view_posts()
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        $page['posts'] = $this->blogmodel->get_all();
        $page['content'] = 'blog_view_posts';
        $page['title'] = 'All Posts';
        
        $this->load->view('template', $page);
        
    }
    
    /**
     * View draft posts
     * Admin only
     */
    function view_drafts()
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        $page['posts'] = $this->blogmodel->get_drafts();
        $page['content'] = 'blog_view_posts';
        $page['title'] = 'Drafts';
        
        $this->load->view('template', $page);
    }
  
    /**
     * View a list of all comments requiring moderation
     */
    function moderation()
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
    }
    
    /**
     * Approve a comment
     * @param type $comment_id 
     */
    function approve_comment($comment_id=0)
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Valid input
        if ($comment_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Fetch the comment
        $comment = $this->blogmodel->get_comment_by_id($comment_id);
        if (!isset($comment->comment_id))
        {
            $this->page['error'] = 'No such comment.';
            $this->index();
            return;
        }
        
        // Must be in moderation
    }
    
    /**
     * Delete a comment
     * @param type $comment_id 
     */
    function delete_comment($comment_id=0)
    {
        $page = $this->page;
        
        // Admin only
        if (!$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Admin only.';
            $this->index();
            return;
        }
        
        // Valid input
        if ($comment_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Fetch the comment
        $comment = $this->blogmodel->get_comment_by_id($comment_id);
        if (!isset($comment->comment_id))
        {
            $this->page['error'] = 'No such comment.';
            $this->index();
            return;
        }
    }
    
} // end blog controller