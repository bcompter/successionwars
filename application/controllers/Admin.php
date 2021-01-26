<?php
/*
 * Admin page
 * 
 * 
 */

class Admin extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        $this->load->model('adminmodel');
        $this->page['admin'] = $this->adminmodel->get_by_id(1);
    }    
    
    /**
     * Admin dashboard
     * 
     */
    function index()
    {
        $page = $this->page;
        
        // Load stats
        $games = $this->db->query('select count(game_id) as num_games from games where 1 limit 1')->row();
        $page['num_games'] = $games->num_games;
        
        $today = new DateTime();
        $today->modify('-30 days');
        
        $inactive_games = $this->db->query('select * from games join (select * from gamemsg order by timestamp desc) as gamemsg on gamemsg.game_id=games.game_id where 1 group by games.game_id')->result();
        $num_inactive_games = 0;
        foreach($inactive_games as $game)
        {
            if ($game->timestamp < $today->format('Y-m-d H:i:s'))
                $num_inactive_games++;
        }
        
        $page['num_inactive_games'] = $num_inactive_games;

        $users = $this->db->query('select count(id) as num_users from users where 1 limit 1')->row();
        $page['num_users'] = $users->num_users;
        
        $chats = $this->db->query('select count(chat_id) as num_chats from chat where 1 limit 1')->row();
        $page['num_chats'] = $chats->num_chats;

        $today = new DateTime();
        $today->modify('-1 day');
        
        $chats_today = $this->db->query('select count(chat_id) as num_chats_today from chat where time_stamp > "'.$today->format('Y-m-d H:i:s').'"')->row();
        $page['chats_today'] = $chats_today->num_chats_today;
        
        $gamemsgs = $this->db->query('select count(msg_id) as num_msgs from gamemsg where timestamp > "'.$today->format('Y-m-d H:i:s').'"')->row();
        $page['gamemsgs'] = $gamemsgs->num_msgs;
        
        $today = new DateTime();
        $today->modify('-7 days');
        
        $active_users = $this->db->query('select count(id) as num_active_users from users where last_login > "'.$today->format('U').'" limit 1')->row();
        $page['active_users'] = $active_users->num_active_users;
        
        $this->load->model('gamehelpmodel');
        $page['games'] = $this->gamehelpmodel->get_active();
        
        $page['content'] = 'viewadmin';
        $this->load->view('template', $page);        
    }
    
    function enter_maintenance()
    {
        // Enter maintenance mode!
        $obj['maintenance_mode'] = 1;
        $this->adminmodel->update(1, $obj);
        
        // Back to the admin page
        redirect('admin','refresh');
    }
    
    function exit_maintenance()
    {
        // Exit maintenance mode!
        $obj['maintenance_mode'] = 0;
        $this->adminmodel->update(1, $obj);
        
        // Back to the admin page
        redirect('admin','refresh');
    }
    
    function enable_registration()
    {
        // Enable registration!
        $obj['allow_register'] = 1;
        $this->adminmodel->update(1, $obj);
        
        // Back to the admin page
        redirect('admin','refresh');
    }
    
    function disable_registration()
    {
        // Disable registration!
        $obj['allow_register'] = 0;
        $this->adminmodel->update(1, $obj);
        
        // Back to the admin page
        redirect('admin','refresh');
    }
 
    /**
     * Update the message displayed on the dashboard
     */
    function dashboard_message()
    {
        $page = $this->page;
        
        $this->load->library('form_validation');
        
        // Validate form input
        $this->form_validation->set_rules('message', 'Message', 'required|max_length[500]');
        
        if ($this->form_validation->run() == true)
        { 
            // Update message
            $page['admin']->dashboard_message = $this->input->post('message');
            $this->adminmodel->update(1, $page['admin']);
            
            // Redirect
            $page['content'] = 'dashboard_update';
            $this->load->view('template', $page);
        }
        else
        {
            $page['content'] = 'dashboard_update';
            $this->load->view('template', $page);
        }
    }  // end dashboard_message
    
    /**
     * Detect and display 
     */
    function cross_check()
    {
        $page = $this->page;
        
        $page['units'] = $this->db->query('select * from combatunits
            join players on players.player_id=combatunits.owner_id
            join territories on territories.territory_id=combatunits.location_id
            where players.game_id<>combatunits.game_id
            and combatunits.owner_id<>0')->result();
        
        $page['content'] = 'admin_cross_check';
        $this->load->view('template', $page);
    }  // end cross check
    
    /**
     * Delete a unit from the server
     */
    function delete_unit($id=0)
    {
        $page = $this->page;
        
        if ($id != 0)
        {
            $this->db->query('DELETE FROM combatunits WHERE combatunit_id='.$id);
        }
        
        redirect('admin/cross_check', 'refresh');
        
    }  // end delete unit
    
}  // end Admin
