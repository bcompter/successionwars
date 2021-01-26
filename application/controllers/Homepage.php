<?php
/*
 * This is the homepage which will describe the game, provide a link to the
 * login page/register page.
 * 
 * Not much dynamic content here.
 */

class Homepage extends MY_Controller {
    
    function __construct()
    {
        parent::__construct();
        
    }
       
    function index()
    {   
        $page = $this->page;
        
        $page['content'] = 'welcome_message';
        $this->load->view('template', $page);
    }
    
}


?>
