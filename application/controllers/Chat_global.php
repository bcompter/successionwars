<?php

/**
 * Gloabl chat room feature
 */
class Chat_global extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
    }   
    
    /**
     * View the chat room
     * 
     */
    function index()
    {
        $page = $this->page;
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        // Away we go
        $page['content'] = 'global_chat';
        $this->load->view('template', $page);
        
    }  // end index
    
    /**
     * Send a chat message
     */
    function chat()
    {
        $page = $this->page;
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect($this->config->item('base_url'), 'refresh');
        }

        // Extract data from post
        $page['msg'] = $this->input->post('msg');
        if ($page['msg'] == "")
            return;
        
        // Strip html and other bad stuff
        $page['msg'] = strip_tags($page['msg']);
        
        // Upload Data
        $chat['message'] = $page['msg'];
        $chat['user_id'] = $page['user']->id;
        $chat['color'] = 'red'; 
        $this->load->model('chatglobalmodel');
        $this->chatglobalmodel->create($chat);  
        
    }  // end chat
    
    /**
     * Load recent chats
     */
    function load_chat()
    {
        $page = $this->page;
 
        $this->load->model('chatglobalmodel');
        $page['chats'] = $this->chatglobalmodel->get_last();
        $page['users'] = $this->chatglobalmodel->get_users();
        
        $this->load->view('global_chat_load', $page);
    }  // end load_chat
    
    /**
     * Fetch new chat messages to be displayed on the 
     */
    function update()
    {
        $page = $this->page;
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect($this->config->item('base_url'), 'refresh');
        }

        $page['time'] = $this->input->post('chattime');
        
        $this->load->model('chatglobalmodel');
        $page['messages'] = $this->chatglobalmodel->get_new($page['time']);
        $page['users'] = $this->chatglobalmodel->get_users();
        
        $this->load->view('global_chat_update', $page);
        
    }  // end update

    
}  // end chat_global