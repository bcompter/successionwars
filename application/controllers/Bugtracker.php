<?php
/*
 * Bug and Feature Tracker
 */

class Bugtracker extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        
        // Must be logged in
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        
        $this->load->model('adminmodel');
        $this->page['admin'] = $this->adminmodel->get_by_id(1);
        
        $this->load->model('bugmodel');
        
        // Stock info required for side bars in all views...
        $this->db->limit(10);
        $this->db->where('status <>', 'Completed');
        $this->page['top_bugs'] = $this->bugmodel->get_all_bugs();
        $this->db->limit(10);
        $this->db->where('status <>', 'Completed');
        $this->page['top_features'] = $this->bugmodel->get_all_features();
    }    
    
    /**
     * Bug tracker homepage
     */
    function index()
    {
        $page = $this->page;
        
        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
		
        $page['content'] = 'bug_home';
        $this->load->view('template', $page);
    }
    
    /**
     * Create a new bug
     */
    function create()
    {
        $page = $this->page;
        
        $this->load->library('form_validation');
        
        // Validate form input
        $this->form_validation->set_rules('title', 'Game Title', 'required|max_length[40]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[600]');
        
        if ($this->form_validation->run() == true)
        { 
            // Create the bug!
            $bug = new stdClass();
            $bug->title         = $this->input->post('title');
            $bug->description   = $this->input->post('description');
            $bug->status        = 'Pending Review';
            $bug->user_id       = $page['user']->id;
            $bug->created_on    = null;
            
            $is_bug = $this->input->post('is_bug');

            if ($is_bug == '1')
                $bug->is_bug = true;
            else
                $bug->is_bug = false;
            
            $this->bugmodel->create($bug);
            $insert_id = $this->db->insert_id();
            
            // Redirect to new bug view
            $this->session->set_flashdata('notice', 'Tracker has been created.');
            redirect('bugtracker/view/'.$insert_id, 'refresh');
            
            // Email all admins
            $admins  = $this->db->query('SELECT * FROM users WHERE group_id=1')->result();
            foreach($admins as $a)
            {
                email_user($a, 'A new tracker, <a href="http://www.scrapyardarmory.com/successionwars/index.php/bugtracker/view/'.$insert_id.'">'.$bug->title.'</a>, has been created.');
            }
        }
        else
        {
            $page['content'] = 'bug_create';
            $this->load->view('template', $page);
        }
        
    }  // end create
    
    /**
     * View all bugs
     */
    function view_all()
    {
        $page = $this->page;

        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }

        $page['bugs'] = $this->bugmodel->get_all();

        $page['title'] = 'All Trackers';
        $page['content'] = 'bug_view_all';
        $this->load->view('template', $page);	
    }
    
    /**
     * View all bugs created by this logged in user
     */
    function view_user()
    {
        $page = $this->page;

        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
		
        $page['bugs'] = $this->bugmodel->get_by_user($page['user']->id);
		
        $page['title'] = 'Your Bugs';
        $page['content'] = 'bug_view_all';
        $this->load->view('template', $page);
    }
    
    function view_completed()
    {
        $page = $this->page;

        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
        
        $this->db->where('status', 'Completed');
        $page['bugs'] = $this->bugmodel->get_all();
		
        $page['title'] = 'Completed Trackers';
        $page['content'] = 'bug_view_all';
        $this->load->view('template', $page);
    }
    
    function view_to_vote()
    {
        $page = $this->page;

        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
        
        $this->load->model('bugmodel');
        $page['bugs'] = $this->bugmodel->get_not_voted($page['user']->id);
		
        $page['title'] = 'Trackers You Have Not Voted On';
        $page['content'] = 'bug_view_all';
        $this->load->view('template', $page);
    }
    
    function view_inprogress()
    {
        $page = $this->page;

        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
        
        $this->db->where('status', 'In Progress');
        $page['bugs'] = $this->bugmodel->get_all();
		
        $page['title'] = 'In Progress Trackers';
        $page['content'] = 'bug_view_all';
        $this->load->view('template', $page);
    }
    
    function view_pending()
    {
        $page = $this->page;

        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
        
        $this->db->where('status', 'Pending Review');
        $page['bugs'] = $this->bugmodel->get_all();
		
        $page['title'] = 'Pending Trackers';
        $page['content'] = 'bug_view_all';
        $this->load->view('template', $page);
    }
    
    /**
     * View a bug and all of its comments
     */
    function view($bug_id=0)
    {
        $page = $this->page;

        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
        
        $this->load->helper('form');
        
        if ($bug_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
        
        // Load up information
        $page['bug'] = $this->bugmodel->get_by_id($bug_id);
        $page['bug_owner'] = $this->ion_auth_model->get_user($page['bug']->user_id)->row();
        $page['comments'] = $this->bugmodel->get_comments($bug_id);
        $page['karma'] = $this->bugmodel->get_karma($bug_id, $page['user']->id);
        
        // Away we go
        $page['content'] = 'bug_view';
        $this->load->view('template', $page);
        
    }
    
    /**
     * Delete a bug
     */
    function delete($bug_id=0)
    {
        $page = $this->page;
        
        // Valid input
        if ($bug_id==0)
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
        
        // Must be a valid bug
        $bug = $this->bugmodel->get_by_id($bug_id);
        if (!isset($bug->bug_id))
        {
            $this->page['error'] = 'No such bug.';
            $this->index();
            return;
        }
        
        // Delete Bug
        $this->bugmodel->delete($bug_id);
        
        // Delete comments
        $this->db->query('delete from bug_comments where bug_id='.$bug_id);
        
        // Delete karma
        $this->db->query('delete from bug_karma where bug_id='.$bug_id);
        
        // Redirect to index
        $this->session->set_flashdata('notice', 'Bug has been deleted.');
        redirect('bugtracker/view_all', 'refresh');
    }
    
    /**
     * Update a bugs status
     */
    function update_status($bug_id=0)
    {
        $page = $this->page;
		
        // Valid input
        if ($bug_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
		
        // Must be a valid bug
        $bug = $this->bugmodel->get_by_id($bug_id);
        if (!isset($bug->bug_id))
        {
            $this->page['error'] = 'No such bug.';
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
        
        $this->load->library('form_validation');
        
        // Validate form input
        $this->form_validation->set_rules('status', 'Bug Status', 'required|max_length[40]');
        
        if ($this->form_validation->run() == true)
        { 
            // Update the bug!
            $bugupdate = new stdClass();
            $bugupdate->status = $this->input->post('status');
            $bugupdate->bug_id = $bug_id;
            $bugupdate->modified_on = null;
            
            $this->bugmodel->update($bug_id, $bugupdate);
            
            // Record a log message
            bug_message($bug_id, $page['user']->id, 'Changed status from '.$bug->status.' to '.$bugupdate->status.'.');
            
            // Redirect to new bug view
            $this->session->set_flashdata('notice', 'Status Updated!');
            redirect('bugtracker/view/'.$bug_id, 'refresh');
        }
        else
        {
            $page['bug'] = $bug;
            $page['content'] = 'bug_update';
            $this->load->view('template', $page);
        }
        
    }  // end update_status
    
    /**
     * Up or Down vote a bug
     */
    function vote($bug_id=0, $value=0)
    {
        $page = $this->page;
		
        // Valid input
        if ($bug_id==0 || $value==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
		
	// Must be a valid bug
        $bug = $this->bugmodel->get_by_id($bug_id);
        if (!isset($bug->bug_id))
        {
            $this->page['error'] = 'No such bug.';
            $this->index();
            return;
        }
		
        // Must not of already voted
        $karma = $this->bugmodel->get_karma($bug_id, $page['user']->id);
        if (isset($karma->karma_id))
        {
            $this->page['error'] = 'No such bug.';
            $this->index();
            return;
        }
		
        // Double check value
        if ($value != 1 && $value != -1)
        {
            $this->page['error'] = 'Invalid value.';
            $this->index();
            return;
        }
		
        // Away we go
        unset($karma);
        $karma = new stdClass();
        $karma->bug_id = $bug_id;
        $karma->user_id = $page['user']->id;
        $karma->value = $value;
        $this->db->insert('bug_karma', $karma);

        // Redirect to bug view
        $this->session->set_flashdata('notice', 'Your vote has been submitted!');
        redirect('bugtracker/view/'.$bug_id, 'refresh');
    }
    
    /**
     * Add a new comment to a bug
     */
    function add_comment($bug_id=0)
    {
        $page = $this->page;
		
        // Valid input
        if ($bug_id==0)
        {
            $this->page['error'] = 'Invalid input.';
            $this->index();
            return;
        }
		
        // Must be a valid bug
        $bug = $this->bugmodel->get_by_id($bug_id);
        if (!isset($bug->bug_id))
        {
            $this->page['error'] = 'No such bug.';
            $this->index();
            return;
        }
		
        // Must be logged in
        if ( !isset($page['user']->id) )
        {
            $this->page['error'] = 'You are not allowed to comment if you are not logged in.';
            $this->index();
            return;
        }
		
        // Away we go
        $comment = new stdClass();
        $comment->text = $this->input->post('comment');
        $comment->user_id = $page['user']->id;
        $comment->bug_id = $bug_id;
        $this->db->insert('bug_comments', $comment);
        
        // Update the bug timestamp
        $bugupdate = new stdClass();
        $bugupdate->bug_id = $bug_id;
        $bugupdate->modified_on = null;
        $this->bugmodel->update($bug_id, $bugupdate);

        $this->session->set_flashdata('notice', 'Your comment has been sumbitted!');
        redirect('bugtracker/view/'.$bug_id, 'refresh');
        
    }
        
} // end bugtracker controller