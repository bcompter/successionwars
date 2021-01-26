<?php

class User extends MY_Controller {
    
    function __construct()
    {
        parent::__construct();
        
    }
    
    function change_preferences()
    {
        $page = $this->page;
        
        // Must be logged in
        if (!isset($page['user']->id))
        {
            redirect('auth/login', 'refresh');
        }
        
        // Validate form input
        $send_email = $this->input->post('send_email');
        $send_email = ($send_email == '1' ? 1 : 0);
        
        $email_on_private_message = $this->input->post('email_on_private_message');
        $email_on_private_message = ($email_on_private_message == '1' ? 1 : 0);
        
        $auto_kill_all = $this->input->post('auto_kill_all');
        if ($auto_kill_all != '1' && $auto_kill_all != '2')
            $auto_kill_all = 0;
        //$auto_kill_all = ($auto_kill_all == '1' || $auto_kill_all == '2' ? $auto_kill_all : 0);

        $forum_posts_per_page = $this->input->post('forum_posts_per_page');

        if ($send_email !== false)
        {
            $user = new stdClass();
            $user->id = $page['user']->id;
            $user->send_me_email = $send_email;
            $user->email_on_private_message = $email_on_private_message;
            $user->auto_kill_all = $auto_kill_all;
            $user->forum_posts_per_page = $forum_posts_per_page;
            $this->db->where('id', $page['user']->id);
            $this->db->update('users', $user);
            
            $this->session->set_flashdata('notice', 'Preferences Updated! '. $this->input->post('auto_kill_all'));
        }
        else 
        {
            $this->session->set_flashdata('error', 'An error occurred!');
        }
        
        redirect('user/preferences','refresh');
        
    }
    
    function preferences()
    {
        $page = $this->page;
        
        // Must be logged in
        if (!isset($page['user']->id))
        {
            redirect('auth/login', 'refresh');
        }
        
        // Show the form!
        $this->load->helper('form');
        $page['content'] = 'user_preferences';
        $this->load->view('template', $page);
        
    }  // update_preferences
    
    
    /**
     * View all games belonging to this user
     */
    function view_games($user_id = 0)
    {
        $page = $this->page;
        
        // Must be logged in
        if (!isset($page['user']->id))
        {
            // TODO Should give an error about needing to be logged in
            redirect('auth/login', 'refresh');
        }

        // Make sure an id is provided
        if ($user_id == 0)
        {
            $page['content'] = 'auth';
            $page['error'] = 'ERROR! A user id must be provided.';
            $this->load->view('template', $page);
        }
        
        // Fetch number of trackers you have not voted on
        $this->load->model('bugmodel');
        $page['trackerstovote'] = $this->bugmodel->get_not_voted($page['user']->id);
        
        // Fetch all user bugs
        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $this->db->where('status !=', 'Completed');
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
        
        $user_being_viewed = $this->ion_auth->get_user($user_id);
        $page['user_being_viewed'] = $user_being_viewed;
        
        $this->load->model('gamemodel');
        $page['games'] = $this->gamemodel->get_by_user($user_id);
        
        $page['content'] = 'user_view_games';
        $this->load->view('template', $page);
        
    }  // end view_games
}


?>
