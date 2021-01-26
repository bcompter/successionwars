<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{	
    
    var $page = null;
    
    function __construct()
    {
        parent::__construct();
        
        $this->benchmark->mark('code_start2');
        
        /* Fixed timezone bug in latest bluehost update */
        date_default_timezone_set('UTC');
        
        $this->load->model('adminmodel');
        $admin = $this->adminmodel->get_by_id(1);
            
        // Are we in maintenance mode?
        if ( $this->uri->segment(1) != 'admin' )
        {
            if ( $admin->maintenance_mode )
            {
                // Go home
                $this->session->set_flashdata('notice', 'Succession Wars is undergoing maintenance.  We will be back soon!');
                
                if ($this->uri->segment(1) != '')
                    redirect($this->config->item('base_url'), 'refresh');
            }
        }
        $this->page['admin'] = $admin;
        
        // Get user if logged in
        if ($this->ion_auth->logged_in())
        {
            $this->page['user'] = $this->ion_auth->get_user();
        }
        
        $this->benchmark->mark('code_end2');
        
        //log_message('error', 'Benchmark for core '.$this->benchmark->elapsed_time('code_start2', 'code_end2'));
        
        //
        //   DEBUG setting
        //
        $this->debug=2;
        // =0 : none
        // =1 (>0) : Game data (a more explicit game log for us to track if there is something requested in a game)
        // =2 (>1) : basic debugging; function calls
        // =3 (>2) : Full (lots of junk to help identify exactly where/what loops/MySQL calles crashed)
        // Example:
        // if ($this->debug>2) log_message('error', 'assign targets target '.$target_id.' for unit '.$combatunit_id);
        
        // Enable profiling 
        //$this->output->enable_profiler(TRUE);    
    }
}