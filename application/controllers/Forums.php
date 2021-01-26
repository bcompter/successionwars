<?php

/**
 * Forums Controller 
 */

class Forums extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('forummodel');
        
    }    

    /**
     * Forum home
     */
    function index()
    {
        $page = $this->page;
        
        // Fetch all sections
        $page['sections'] = $this->forummodel->get_sections(isset($page['user']->id)? $page['user']->id : NULL);
        
        // Away we go
        $page['content'] = 'forums/home';
        $this->load->view('template', $page);
    }
    
    /**
     * View a section and all of its topics
     */
    function view_section($section_id=0, $offset=0)
    {
        $page = $this->page;
        log_message('error', 'fetch page '.$this->db->last_query());
        
        // Validate input
        if ($section_id == 0)
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }        

        $section = $this->forummodel->get_section($section_id);
        log_message('error', 'fetch section '.$this->db->last_query());
        if (!isset($section->section_id))
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }
        
        $num_topics = $this->forummodel->num_topics($section_id);
        log_message('error', 'Num topics '.$this->db->last_query());

        $offset = (int)$offset;
        if ($offset < 0 || $offset > $num_topics)
        {
            $page['error'] = 'Invalid topic offset input!';
            $this->load->view('template', $page);
            return;
        }

        // Determine number of posts displayed per page
        $this->load->model('usermodel');
        if (isset($page['user']->id))   // if logged in, get user's preference
        {
            $posts_per_page = $this->usermodel->posts_per_page($page['user']->id)->forum_posts_per_page;
            log_message('error', 'fetch preferences '.$this->db->last_query());
        }
        else                            // else use default if not logged in
            $posts_per_page = 30;
            
        $page['posts_per_page'] = $posts_per_page;
        
        // Fetch all topics
        $page['topics'] = $this->forummodel->get_topics($section_id, $offset, isset($page['user']->id) ? $page['user']->id : NULL);
        
        log_message('error', 'fetch topics '.$this->db->last_query());
        
        $page['num_topics'] = $num_topics;
        $page['section'] = $section;
        $page['offset'] = $offset;
        
        // Away we go
        $page['content'] = 'forums/view_section';
        $this->load->view('template', $page);
    }
    
    /**
     * View a topic and all of its posts
     */
    function view_topic($topic_id=0, $offset=0)
    {
        $page = $this->page;
        
        // Validate input
        if ($topic_id == 0)
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }        

        $topic = $this->forummodel->get_topic($topic_id);
        if (!isset($topic->topic_id))
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }
        
        $section = $this->forummodel->get_section($topic->section_id);
        
        $num_posts = $this->forummodel->num_posts($topic_id);
        
        $offset = (int)$offset;
        if ($offset < 0 || $offset > $num_posts)
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }
        
        // Set number of posts per page according to the user's preference
        $this->load->model('usermodel');
        if (isset($page['user']->id))
            $posts_per_page = $this->usermodel->posts_per_page($page['user']->id)->forum_posts_per_page;
        else
            $posts_per_page = 30;
        if ($posts_per_page == 0)   // If user preference is to view all posts of a thread on one page
            $posts_per_page = $num_posts;
        
        // Get the posts of this topic for this page
        $posts = $this->forummodel->get_posts($topic_id, $offset, $posts_per_page);
        
        // Set last post's date on the page to the user's last viewed timestamp for this topic
        if (isset($page['user']->id))
        {
            $last_post_shown = end($posts);
            $last_read_data = $this->forummodel->get_last_read($topic_id, $page['user']->id);
            if (!isset($last_read_data->last_read) || $last_read_data->last_read < max($last_post_shown->created_on,$last_post_shown->modified_on))
            {
                $last_post_in_thread = $this->forummodel->get_last_post_in_topic($topic_id);
                
                $last_read_update = new stdClass();            
                $last_read_update->topic_id = $topic_id;
                $last_read_update->user_id = $page['user']->id;
                if ($last_post_shown->created_on == $last_post_in_thread->created_on)
                    $last_read_update->last_read = date("Y-m-d H:i:s");
                else
                    $last_read_update->last_read = max($last_post_shown->created_on,$last_post_shown->modified_on);
                $this->forummodel->update_unread($last_read_update,(isset($last_read_data->id) ? $last_read_data->id : NULL));
            }
        }
        
        // Increment topic num_views
        $t = new stdClass();
        $t->num_views = $topic->num_views + 1;
        $t->topic_id = $topic_id;
        $this->forummodel->update_topic($topic_id, $t);
        
        // Determine if we should show edit and delete links
        $page['show_links'] = false;
        if ($this->ion_auth->is_admin())
        {
            $page['show_links'] = true;
        }
        foreach($posts as $post)
        //  TODO Change this to only allow regular users to edit the first and last post if they are the author
        {
            if (isset($page['user']->id) && $page['user']->id == $post->author_id)
                $post->show_links = true;
            else
                $post->show_links = false;
        }
        
        $page['posts_per_page'] = $posts_per_page;
        
        // Away we go
        if (isset($last_read_data->last_read))
            $page['last_read'] = $last_read_data->last_read;
        $page['content'] = 'forums/view_topic';
        $page['topic'] = $topic;
        $page['section'] = $section;
        $page['posts'] = $posts;
        $page['num_posts'] = $num_posts;
        $page['posts_per_page'] = $posts_per_page;
        $page['offset'] = $offset;
        $this->load->view('template', $page);
        
    }  // end forums
    
    /**
     * View a specific post within a topic
     * Automatically select the correct offset required
     */
    function view_post($post_id=0)
    {
        $page = $this->page;
    }
    
    /**
     * Create a new topic in a section
     */
    function create_topic($section_id=0)
    {
        $page = $this->page;
        
        $this->load->library('form_validation');
        
        // Validate input
        if ($section_id == 0)
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }        

        $this->load->model('forummodel');
        $section = $this->forummodel->get_section($section_id);
        if (!isset($section->section_id))
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }
        
        // Requires logged in
        if (!isset($page['user']->id))
        {
            $page['error'] = 'Must be logged in!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be playing in at least one game
        $is_playing = $this->db->query('select * from users join players on players.user_id=users.id where id='.$page['user']->id.' limit 1')->result();
        if (count($is_playing) == 0)
        {
            $page['error'] = 'To prevent spam, we require that you play in a at least one game before posting to the forums.';
            $this->load->view('template', $page);
            return;
        }
        
        // Validate form input
        $this->form_validation->set_rules('title', 'Title', 'required|max_length[500]|min_length[3]');
        $this->form_validation->set_rules('content', 'Post', 'required|min_length[1]');

        if ($this->form_validation->run() == true)
        {
            // Add topic
            $topic = new stdClass();
            $topic->title = $this->input->post('title', true);
            $topic->section_id = $section_id;
            $topic->creator_id = $page['user']->id;
            $this->forummodel->create_topic($topic);
            
            // Add post
            $post = new stdClass();
            $post->topic_id = $this->db->insert_id();
            
            $post->author_id = $page['user']->id;
            $post->text = $this->input->post('content', true);
            $post->author_ip = $_SERVER['REMOTE_ADDR'];
            $this->forummodel->create_post($post);
            
            // View section
            $this->session->set_flashdata('notice', 'Topic Created');
            redirect('forums/view_section/'.$section_id, 'refresh');
        }
        else
        {
            // Show the form
            $page['section'] = $section;
            $page['content'] = 'forums/form_topic';
            $this->load->view('template', $page);
        }
        
    }  // end create_topic
    
    /**
     * Add a reply to an existing topic
     */
    function create_post($topic_id=0)
    {
        $page = $this->page;
        
        $this->load->library('form_validation');
        
        // Validate input
        if ($topic_id == 0)
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }        

        $this->load->model('forummodel');
        $topic = $this->forummodel->get_topic($topic_id);
        if (!isset($topic->topic_id))
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }
        
        // Requires logged in
        if (!isset($page['user']->id))
        {
            $page['error'] = 'Must be logged in!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be playing in at least one game
        $is_playing = $this->db->query('select * from users join players on players.user_id=users.id where id='.$page['user']->id.' limit 1')->result();
        if (count($is_playing) == 0)
        {
            $page['error'] = 'To prevent spam, we require that you play in a at least one game before posting to the forums.';
            $this->load->view('template', $page);
            return;
        }
        
        // Validate form input
        $this->form_validation->set_rules('content', 'Post', 'required|min_length[1]');

        if ($this->form_validation->run() == true)
        {            
            // Add post
            $post = new stdClass();
            $post->topic_id = $topic_id;
            $post->author_id = $page['user']->id;
            $post->text = $this->input->post('content', true);
            $post->author_ip = $_SERVER['REMOTE_ADDR'];
            $this->forummodel->create_post($post);
            
            // View section
            $this->session->set_flashdata('notice', 'Post Created');
            redirect('forums/view_topic/'.$topic_id, 'refresh');
        }
        else
        {
            // Show the form
            $page['topic'] = $topic;
            $page['content'] = 'forums/form_post';
            $this->load->view('template', $page);
        }
        
    }  // end create_post
    
    /**
     * Delete a post.
     * Delete all child posts if this is a main topic
     * Available to all post authors and admin
     */
    function delete_post($post_id)
    {
        $page = $this->page;
        
        $this->load->library('form_validation');
        
        // Validate input
        if ($post_id == 0)
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }        

        $this->load->model('forummodel');
        $post = $this->forummodel->get_post($post_id);
        if (!isset($post->post_id))
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }

        // Requires logged in
        if (!isset($page['user']->id))
        {
            $page['error'] = 'Must be logged in!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be the post owner or an admin
        if ($page['user']->id != $post->author_id && !$this->ion_auth->is_admin())
        {
            $page['error'] = 'Not Allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch the topic
        $topic = $this->forummodel->get_topic($post->topic_id);
        
        // Away we go!!!
        $this->forummodel->delete_post($post_id);
        $this->session->set_flashdata('notice', 'Post Deleted!');
        redirect('forums/view_section/'.$topic->section_id, 'refresh');        
        
    }  // end delete_post
    
    /**
     * Edit a post
     */
    function edit_post($post_id)
    {
        $page = $this->page;
        
        $this->load->library('form_validation');
        
        // Validate input
        if ($post_id == 0)
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }        

        $this->load->model('forummodel');
        $post = $this->forummodel->get_post($post_id);
        if (!isset($post->post_id))
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }
        
        $topic = $this->forummodel->get_topic($post->topic_id);
        if (!isset($topic->topic_id))
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('template', $page);
            return;
        }

        // Requires logged in
        if (!isset($page['user']->id))
        {
            $page['error'] = 'Must be logged in!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be the post owner or an admin
        if ($page['user']->id != $post->author_id && !$this->ion_auth->is_admin())
        {
            $page['error'] = 'Not Allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Validate form input
        $this->form_validation->set_rules('content', 'Post', 'required|min_length[1]');

        if ($this->form_validation->run() == true)
        {            
            // Update post
            $postupdate                 = new stdClass();
            $postupdate->text           = $this->input->post('content', true);
            $postupdate->modified_on    = NULL;
            $this->forummodel->update_post($post_id, $postupdate);
            
            // View section
            $this->session->set_flashdata('notice', 'Post updated');
            redirect('forums/view_topic/'.$topic->topic_id, 'refresh');
        }
        else
        {
            // Show the form
            $page['topic']      = $topic;
            $page['post']       = $post;
            $page['content']    = 'forums/form_post_edit';
            $this->load->view('template', $page);
        }
        
    }  // end edit_post
        
}  // end Forums