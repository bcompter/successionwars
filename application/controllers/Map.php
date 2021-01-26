<?php

class Map extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }  
 
    /**
     * Load a map to play the game
     * Loads all territories
     * Determines number of and combined strength of all comabt units
     * Determines number of and combined space of all jumpships
     * Determines number of leaders
     */
    function load($game_id=0)
    {
        // Check input
        if ($game_id == 0)
        {
            return;
        }
        
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            return;
        }
        $page['game'] = $game;
        
        $this->benchmark->mark('start');
        
        $this->load->model('mapmodel');
        $page['map'] = $this->mapmodel->get_by_game($game_id);
        
        $page['strength'] = $this->mapmodel->get_sum_strength($game_id);
        $page['capacity'] = $this->mapmodel->get_sum_capacity($game_id);
        
        // Fetch correct arrows
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        $page['game'] = $game;
        
        $this->load->model('orderofbattlemodel');
        $oob = $this->orderofbattlemodel->get_by_id($game->orderofbattle);
        $arrow_row = $this->db->query('select * from arrows where world_id='.$oob->world_id)->row();
        $page['arrows'] = $arrow_row->divs;
        
        $this->benchmark->mark('end');
        
        //log_message('error', 'Map for game_id '.$game_id.' Loaded in '.$this->benchmark->elapsed_time('start', 'end'));
        
        // Away we go!
        $this->load->view('map', $page);
        
    }  // end load
    
    function demo()
    {
        $game_id = 5;
        
        $this->load->model('mapmodel');
        $page['map'] = $this->mapmodel->get_by_game($game_id);
        $page['strength'] = $this->mapmodel->get_sum_strength($game_id);
        $page['capacity'] = $this->mapmodel->get_sum_capacity($game_id);
        
        $this->load->view('mapdemo', $page);
    }  // end demo

    /**
     * View all available maps
     */
    function view_maps()
    {
        $page = $this->page;
        
        $this->load->model('worldmodel');
        $page['maps'] = $this->worldmodel->get_all();
        
        $page['content'] = 'map_view_all';
        $this->load->view('template', $page);
        
    }  // end view_maps    
    
    /**
     * Create a new map
     */
    function create()
    {
        $page = $this->page;
        
        // Must be an admin or super user
        if ( !$this->ion_auth->is_admin() && $page['user']->group_id != 3)
        {
            $this->session->set_flashdata('error', 'You are not allowed to do that, sorry.');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // Validate form input
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Map Name', 'required|max_length[100]');
        
        if ($this->form_validation->run() == true)
        { 
            // Create the new map
            $newmap = new stdClass();
            $newmap->name = $this->input->post('name', true);
            $newmap->user_id = $page['user']->id;
            $this->load->model('worldmodel');
            $this->worldmodel->create($newmap);
            
            $this->page['notice'] = 'Map created successfully.';
            $this->view_maps();
        }
        else
        {
            // Show the form            
            $page['name'] = array('name' => 'name',
                        'id' => 'name',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('name'),
            );
            
            $page['content'] = 'map_create';
            $this->load->view('template', $page);
        }
        
    }  // end create
    
    /**
     * Select what map to copy into an existing map
     */
    function copy_select($world_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($world_id == 0)
        {
            $this->session->set_flashdata('error', 'No input...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // World must exist
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($world_id);
        if (!isset($world->world_id))
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }

        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // Away we go...
        $page['world'] = $world;
        $page['otherworlds'] = $this->worldmodel->get_all();
        $page['content'] = 'map_copy_select';
        
        $this->load->view('template', $page);
        
    }  // end copy_select
    
    /**
     * Copy the contents of an existing world into this one
     */
    function copy($world_id=0, $copy_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($world_id == 0 || $copy_id == 0)
        {
            $this->session->set_flashdata('error', 'No input...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // World must exist
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($world_id);
        if (!isset($world->world_id))
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }

        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // Copy target must exist
        $copy = $this->worldmodel->get_by_id($copy_id);
        if (!isset($copy->world_id))
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // Target World must be empty
        $this->load->model('mapmodel');
        $maps = $this->mapmodel->get_all_by_world($world_id);
        if (count($maps) > 0)
        {
            $this->session->set_flashdata('error', 'Error...');
            redirect('map/view_maps', 'refresh');
            return;
        }
        
        // Away we go...
        unset($maps);
        $maps = $this->mapmodel->get_all_by_world($copy_id);
        foreach($maps as $map)
        {
            // Make a copy
            unset($newmap);
            $newmap = new stdClass();
            $newmap->world_id               = $world_id;
            $newmap->name                   = $map->name;
            $newmap->default_resource       = $map->default_resource;
            $newmap->top                    = $map->top;
            $newmap->left                   = $map->left;
            $newmap->height                 = $map->height;
            $newmap->width                  = $map->width;
            $newmap->default_is_regional    = $map->default_is_regional;
            $newmap->default_is_capital     = $map->default_is_capital;                   
                    
            $this->mapmodel->create($newmap);
        }
        
        $this->session->set_flashdata('notice', 'Copy operation completed!');
        redirect('map/view/'.$world_id, 'refresh');
        
    }  // end copy_existing
    
    /**
     * View a map
     */
    function view($world_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($world_id == 0)
        {
            $page['error'] = 'Cannot a view a map without providing a world id.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must exist
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot a view a map that is not found.';
            $this->load->view('template', $page);
            return;
        }
        
        // If we are either an admin or the designer we can edit this map
        if (($this->ion_auth->is_admin() || $page['user']->id == $world->user_id) && $world->is_draft)
            $page['can_edit'] = true;
        else
            $page['can_edit'] = false;
        
        if ($this->ion_auth->is_admin())
            $page['is_admin'] = true;
        else
            $page['is_admin'] = false;
        
        // Fetch all maps that go with this world
        $this->load->model('mapmodel');
        $page['maps'] = $this->mapmodel->get_all_by_world($world->world_id);
        
        // Away we go
        $page['world'] = $world;
        $page['content'] = 'map_view';
        $this->load->view('template', $page);
        
    }  // end view
    
    /**
     * Edit a map
     */
    function edit($world_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($world_id == 0)
        {
            $page['error'] = 'Cannot edit a map without providing a world id.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must exist
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot edit a map when it is not found.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $page['error'] = 'Cannot edit a map that is not yours.';
            $this->load->view('template', $page);
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $page['error'] = 'Cannot edit a map that is not in draft state.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go
        $this->load->model('mapmodel');
        $page['maps'] = $this->mapmodel->get_all_by_world($world->world_id);
        
        $page['world'] = $world;
        $page['content'] = 'map_edit';
        $this->load->view('template', $page);
        
    }  // end edit
    
    /**
     * Add a new region to this map
     */
    function add_territory($world_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($world_id == 0)
        {
            $page['error'] = 'Cannot add a territory without providing a world id.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be either the creator or an admin
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($world_id);
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $page['error'] = 'Cannot add a territory to a map that is not yours.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must exist        
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot add a territory to a map that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $page['error'] = 'Cannot add a territory to a map that is not in draft state.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Validate form input
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('name', 'Region Name', 'required|max_length[100]');
        $this->form_validation->set_rules('resource', 'Resources', 'required|is_natural');
        $this->form_validation->set_rules('height', 'Height', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('width', 'Width', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('type', 'Type', 'required');
        
        if ($this->form_validation->run() == true)
        { 
            // Create the new map
            $newmap = new stdClass();
            $newmap->name               = $this->input->post('name', true);
            $newmap->default_resource   = $this->input->post('resource', true);
            $newmap->top                = 0;
            $newmap->left               = 0;
            $newmap->height             = $this->input->post('height', true);
            $newmap->width              = $this->input->post('width', true);
            $newmap->world_id           = $world_id;
            $type = $this->input->post('type', true);
            if ($type == 'regional')
            {
                $newmap->default_is_regional = true;
            }
            else if ($type == 'capital')
            {
                $newmap->default_is_capital = true;
            }

            $this->db->trans_start();
            $this->load->model('mapmodel');
            $this->mapmodel->create($newmap);
            $newmap->map_id = $this->db->insert_id();
            $this->db->query('UPDATE worlds SET modified_on=now() WHERE world_id='.$world->world_id);
            $this->db->trans_complete();
            
            $page['map'] = $newmap;
            $this->load->view('map_add_success', $page);
        }
        else
        {
            // Show the form
            $page['world'] = $world;
            $page['name'] = array('name' => 'name',
                        'id' => 'name',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('name'),
            );
            
            $page['content'] = 'map_add';
            $this->load->view('templatexml', $page);
        }
        
    }  // end add_region
    
    /**
     * View a single territory
     */
    function view_territory($map_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($map_id == 0)
        {
            $page['error'] = 'Cannot view a territory without providing a map id.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must exist
        $this->load->model('mapmodel');
        $map = $this->mapmodel->get_by_id($map_id);
        if (!isset($map->map_id))
        {
            $page['error'] = 'Cannot view a territory from a map that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $page['map'] = $map;
        $page['content'] = 'map_view_territory';
        $this->load->view('templatexml', $page);
        
    }  // end view_territory
    
    /**
     * Edit a territory
     */
    function edit_territory($map_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($map_id == 0)
        {
            $page['error'] = 'Cannot edit a territory without providing a map id.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must exist
        $this->load->model('mapmodel');
        $map = $this->mapmodel->get_by_id($map_id);
        if (!isset($map->map_id))
        {
            $page['error'] = 'Cannot edit a territory to a map that does not exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the world it belongs to
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($map->world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot edit a territory of a world that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }

        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $page['error'] = 'Cannot edit a territory if it is not your map.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $page['error'] = 'Cannot edit a territory of a world that is not in draft state.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Validate form input
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('name', 'Region Name', 'required|max_length[100]');
        $this->form_validation->set_rules('resource', 'Resources', 'required|is_natural');
        $this->form_validation->set_rules('type', 'Type', 'required');
        
        if ($this->form_validation->run() == true)
        {
            // Update the map
            $newmap = new stdClass();
            $newmap->name                   = $this->input->post('name', true);
            $newmap->default_resource       = $this->input->post('resource', true);
            $type                           = $this->input->post('type', true);
            $newmap->default_is_capital     = false;
            $newmap->default_is_regional    = false;
            if ($type == 'regional')
            {
                $newmap->default_is_regional = true;
            }
            else if ($type == 'capital')
            {
                $newmap->default_is_capital = true;
            }
            $this->db->trans_start();
            $this->mapmodel->update($map_id, $newmap);
            $this->db->query('UPDATE worlds SET modified_on=now() WHERE world_id='.$world->world_id);
            $this->db->trans_complete();
            
            $map = $this->mapmodel->get_by_id($map_id);
            $page['map'] = $map;
            $this->load->view('map_edit_success', $page);
        }
        else
        {
            // Show the form
            $page['world'] = $world;
            $page['map'] = $map;
            
            $page['content'] = 'map_edit_territory';
            $this->load->view('templatexml', $page);
        }

    }  // end edit_territory

    /**
     * Delete a territory
     */
    function delete_territory($map_id=0)
    {
        $page = $this->page;
        
        // Check input
        if ($map_id == 0)
        {
            $page['error'] = 'Cannot delete a territory without providing a map id.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must exist
        $this->load->model('mapmodel');
        $map = $this->mapmodel->get_by_id($map_id);
        if (!isset($map->map_id))
        {
            $page['error'] = 'Cannot delete a territory from a map that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the world it belongs to
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($map->world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot delete a territory from a world that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }

        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $page['error'] = 'Cannot delete a territory from a map that is not yours.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $page['error'] = 'Cannot delete a territory from a world that is not in draft state.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Confirm
        if ($this->session->flashdata('confirm') != 'YES')
        {
            $this->page['warning'] = 'Delete this Territory, Are you sure?';
            $this->session->set_flashdata('confirm', 'YES');
            $this->view_territory($map_id);
            return;
        }
        
        // Away we go
        $this->db->trans_start();
        $this->mapmodel->delete($map_id);
        $this->db->query('UPDATE worlds SET modified_on=now() WHERE world_id='.$world->world_id);
        $this->db->trans_complete();
        $this->load->view('map_delete_success');
        
    }  // end delete_territory
    
    /**
     * Move a territory
     */
    function edit_position($map_id=0, $top=-1, $left=-1)
    {
        $page = $this->page;
        
        // Check input
        if ($map_id == 0 || $top == -1 || $left == -1)
        {
            $page['error'] = 'Cannot move a territory position without a map id nor direction.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must exist
        $this->load->model('mapmodel');
        $map = $this->mapmodel->get_by_id($map_id);
        if (!isset($map->map_id))
        {
            $page['error'] = 'Cannot move a territory for a map that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the world it belongs to
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($map->world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot move a territory for a world that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }

        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $page['error'] = 'Cannot move a territory of a map that is not yours.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $page['error'] = 'Cannot move a territory of a world that is in not in draft state.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Both top and left need to be evenly divisable by 50
        if ($top % 50 != 0 || $left % 50 != 0)
        {
            $page['error'] = 'Cannot move a territory that has an invalid size.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go!
        $mapupdate = new stdClass();
        $mapupdate->top = $top;
        $mapupdate->left = $left;
        
        $this->db->trans_start();
        $this->mapmodel->update($map_id, $mapupdate);
        $this->db->query('UPDATE worlds SET modified_on=now() WHERE world_id='.$world->world_id);
        $this->db->trans_complete();
        
        $this->load->view('map_update_success', $page);
        
    }  // end edit_location
    
    /**
     * Change a territories size
     */
    function edit_size($map_id=0, $height=0, $width=0)
    {
        $page = $this->page;
        
        // Check input
        if ($map_id == 0 || $height == -1 || $width == -1)
        {
            $page['error'] = 'Cannot edit the size of a territory without proper input.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must exist
        $this->load->model('mapmodel');
        $map = $this->mapmodel->get_by_id($map_id);
        if (!isset($map->map_id))
        {
            $page['error'] = 'Cannot edit the size of a territory of a map not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the world it belongs to
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($map->world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot edit the size of a territory of a map that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }

        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $page['error'] = 'Cannot edit the size of a territory of a map you do not own.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // World must be in a draft state
        if (!$world->is_draft)
        {
            $page['error'] = 'Cannot edit the size of a territory of a map not in draft state.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Both height and width need to be evenly divisable by 50
        if ($height % 50 != 0 || $width % 50 != 0)
        {
            $page['error'] = 'Cannot edit the size of a territory with invalid parameters.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be greater than or equal to 50
        if ($height < 50 || $width < 50)
        {
            $page['error'] = 'Cannot edit the size of a territory with parameters too small.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go!
        $mapupdate = new stdClass();
        $mapupdate->height = $height;
        $mapupdate->width = $width;
        
        $this->db->trans_start();
        $this->mapmodel->update($map_id, $mapupdate);
        $this->db->query('UPDATE worlds SET modified_on=now() WHERE world_id='.$world->world_id);
        $this->db->trans_complete();
        
        $this->load->view('map_update_success', $page);
        
    }  // end edit_size
    
    /**
     * Update the status of a world to either draft or release
     */
    function update_status($world_id=0, $status=0)
    {
        $page = $this->page;
        
        // Check input
        if ($world_id == 0 || $status === 0)
        {
            $page['error'] = 'Cannot update a world status without proper input.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must exist
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot update a world status of a world not found.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be an admin
        if (!$this->ion_auth->is_admin())
        {
            $page['error'] = 'Cannot update a world status unless you are an Admin.';
            $this->load->view('template', $page);
            return;
        }
        
        // Input must be eitherdraft or released
        if ($status !== 'draft' && $status !== 'released')
        {
            $page['error'] = 'Cannot update a world status with an improper status.';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go
        $worldupdate = new stdClass();
        if ($status == 'draft')
        {
            $worldupdate->is_draft = true;
            $this->session->set_flashdata('notice', $world->name.' updated to DRAFT status.');
        }
        else
        {
            $worldupdate->is_draft = false;
            $this->session->set_flashdata('notice', $world->name.' updated to RELEASED status.');
        }
        $this->db->trans_start();
        $this->worldmodel->update($world_id, $worldupdate);
        $this->db->trans_complete();
        
        redirect('map/view/'.$world_id, 'refresh');
        
    }  // end update_status
    
    /**
     * An admin tool used to connect together territories on a map
     */
    function pathtool( $world=0, $a=0, $b=0 )
    {
        // assume game 1
        
        // Restrict to admin
        if ( !$this->ion_auth->is_admin() )
        {
            log_message('error', 'unathorized access attempt to path tool.');
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        $this->load->model('mapmodel');
        if( !isset($world) || $world==0 )
            $page['allterritories'] = $this->mapmodel->get_all();
        else
            $page['allterritories'] = $this->mapmodel->get_all_by_world($world);
        
        if( $a!=0 )
        {
            $page['thisterritory'] = $this->mapmodel->get_by_id($a);
            $page['connected'] = $this->mapmodel->get_adjacent_paths($page['thisterritory']->map_id);
        }
        
        if ( $b!=0 )
        {
            // fetch
            $ta = $this->mapmodel->get_by_id($a);
            $tb = $this->mapmodel->get_by_id($b);
            
            // connect them up
            $this->load->model('pathmodel');
            $path = new stdClass();
            $path->origin_id = $ta->map_id;
            $path->destination_id = $tb->map_id;
            $this->pathmodel->create($path);
            $path->origin_id = $tb->map_id;
            $path->destination_id = $ta->map_id;
            $this->pathmodel->create($path);
            $page['message'] = 'New path created!';
            unset($page['connected']);
            unset($page['thisterritory']);
        }

        $this->load->view('pathtool', $page);
    }  // end pathtool
    
    /**
     * An admin tool used to connect together territories on a map
     * This function does NOT create the back links
     */
    function pathtool2( $world=0, $a=0, $b=0 )
    {
        // assume game 1
        
        // Restrict to admin
        if ( !$this->ion_auth->is_admin() )
        {
            log_message('error', 'unathorized access attempt to path tool.');
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        $this->load->model('mapmodel');
        if( !isset($world) || $world==0 )
            $page['allterritories'] = $this->mapmodel->get_all();
        else
            $page['allterritories'] = $this->mapmodel->get_all_by_world($world);
        
        if( $a!=0 )
        {
            $page['thisterritory'] = $this->mapmodel->get_by_id($a);
            $page['connected'] = $this->mapmodel->get_adjacent_paths($page['thisterritory']->map_id);
        }
        
        if ( $b!=0 )
        {
            // fetch
            $ta = $this->mapmodel->get_by_id($a);
            $tb = $this->mapmodel->get_by_id($b);
            
            // connect them up
            $this->load->model('pathmodel');
            $path = new stdClass();
            $path->origin_id = $ta->map_id;
            $path->destination_id = $tb->map_id;
            $this->pathmodel->create($path);
            $page['message'] = 'New single path created!';
            unset($page['connected']);
            unset($page['thisterritory']);
        }

        $this->load->view('pathtool2', $page);
    }  // end pathtool2
    
    
    /**
     * Delete a path from a map
     */
    function delete_path( $world=0, $thisterritory_id=0, $path_id=0 )
    {
        // Restrict to admin
        if ( !$this->ion_auth->is_admin() )
        {
            log_message('error', 'unathorized access attempt to path tool.');
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        $this->load->model('pathmodel');
        //  Find & Delete the reverse path
        $opposing_path = $this->pathmodel->get_opposing_path_by_id($path_id);
        $this->pathmodel->delete($opposing_path->path_id);
        $this->pathmodel->delete($path_id);
        
        $this->load->model('mapmodel');
        if( !isset($world) || $world==0 || !isset($thisterritory_id) || $thisterritory_id==0)
            $page['allterritories'] = $this->mapmodel->get_all();
        else
        {
            $page['allterritories'] = $this->mapmodel->get_all_by_world($world);
            $page['thisterritory'] = $this->mapmodel->get_by_id($thisterritory_id);
            $page['connected'] = $this->mapmodel->get_adjacent_paths($page['thisterritory']->map_id);
        }
        
        $page['message'] = 'Path deleted in both directions!';
        $this->load->view('pathtool', $page);
        
    }  // end delete_path
    
    /**
     * Delete a path from a map
     * Returns you to pathtool2 so as not to start creating reverse links as well by accident
     */
    function delete_path2( $world=0, $thisterritory_id=0, $path_id=0 )
    {
        // Restrict to admin
        if ( !$this->ion_auth->is_admin() )
        {
            log_message('error', 'unathorized access attempt to path tool.');
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        $this->load->model('pathmodel');
        $this->pathmodel->delete($path_id);
        
        $this->load->model('mapmodel');
        if( !isset($world) || $world==0 || !isset($thisterritory_id) || $thisterritory_id==0)
            $page['allterritories'] = $this->mapmodel->get_all();
        else
        {
            $page['allterritories'] = $this->mapmodel->get_all_by_world($world);
            $page['thisterritory'] = $this->mapmodel->get_by_id($thisterritory_id);
            $page['connected'] = $this->mapmodel->get_adjacent_paths($page['thisterritory']->map_id);
        }
        
        $page['message'] = 'Path Deleted!';
        $this->load->view('pathtool2', $page);
        
    }  // end delete_path
    
    function CreateJumpsForWorld($world_id=0)
    {
log_message('error', 'function CreateJumpsForWorld('.$world_id.') by user_id '.$page['user']->id.', '.(is_admin() == TRUE ? '': 'NOT').' as Admin.');
        $page = $this->page;

        // Check input
        if ($world_id == 0)
        {
            $page['error'] = 'Cannot create jumps for a map without providing a world id.';
            $this->load->view('template', $page);
            return;
        }

        // Must exist
        $this->load->model('worldmodel');
        $world = $this->worldmodel->get_by_id($world_id);
        if (!isset($world->world_id))
        {
            $page['error'] = 'Cannot create jumps for a map when it is not found.';
            $this->load->view('template', $page);
            return;
        }

        // Must be either the creator or an admin
        if (!$this->ion_auth->is_admin() && $page['user']->id != $world->user_id)
        {
            $page['error'] = 'Cannot create jumps for a map that is not yours.';
            $this->load->view('template', $page);
            return;
        }

        // Away we go
        $this->load->model('mapmodel');
        // RCs = array of regions from worldID, each with “ID, TLX, TLY, BRX, BRY”	
        $RCs = $this->mapmodel->get_all_by_world($world_id);

        // sleight of hand to make the math easier to understand and prevent convoluted code further below
        $TotalNumRCs = count($RCs);
//log_message('error', '$TotalNumRCs is '.$TotalNumRCs);
        for ($N=0; $N < $TotalNumRCs; $N++)
        {
//log_message('error', 'N1: '.$N);
            $RCs[$N]->TLX = $RCs[$N]->left;
            $RCs[$N]->TLY = $RCs[$N]->top;
            $RCs[$N]->BRX = $RCs[$N]->TLX + $RCs[$N]->width;
            $RCs[$N]->BRY = $RCs[$N]->TLY + $RCs[$N]->height;
        }

        $this->load->helper('map');
        
        // Cycle through entire list of regions to check coordinates against all the others (except the last)
        for ($N=0; $N < $TotalNumRCs-1; $N++)
        {
            // Cycle through list of regions starting with the one *after* the current one (i.e. N+1)
            for ($M=$N+1; $M < $TotalNumRCs; $M++)
            {
                if (($RCs[$N]->BRY == $RCs[$M]->TLY &&	// The bottom edge of the “current N” region is in line with the top of the “temporary M”
                    ($RCs[$M]->TLX < $RCs[$N]->BRX  &&  $RCs[$M]->BRX > $RCs[$N]->TLX))
                OR ($RCs[$N]->TLY == $RCs[$M]->BRY &&   // The top edge of the “current N” region is in line with the bottom of the “temporary M”
                    ($RCs[$M]->TLX < $RCs[$N]->BRX  &&  $RCs[$M]->BRX > $RCs[$N]->TLX))
                OR ($RCs[$N]->BRX == $RCs[$M]->TLX &&   // The right edge of the “current N” region is in line with the left of the “temporary M”
                    ($RCs[$M]->TLY < $RCs[$N]->BRY  &&  $RCs[$M]->BRY > $RCs[$N]->TLY))
                OR ($RCs[$N]->TLX == $RCs[$M]->BRX &&   // The left edge of the “current N” region is in line with the right of the “temporary M”
                    ($RCs[$M]->TLY < $RCs[$N]->BRY  &&  $RCs[$M]->BRY > $RCs[$N]->TLY)))
                {
//log_message('error', 'Check jumpExists('.$RCs[$N]->map_id.','.$RCs[$M]->map_id.')');
                    if(!jumpExists($RCs[$N]->map_id,$RCs[$M]->map_id))
                        createjump($RCs[$N]->map_id, $RCs[$M]->map_id);
                    if(!jumpExists($RCs[$M]->map_id,$RCs[$N]->map_id))
                        createjump($RCs[$M]->map_id, $RCs[$N]->map_id);
                }
            }
        }
        $this->session->set_flashdata('Jumps created for $world_id ', $world_id.'.');
        redirect('map/view/'.$world_id, 'refresh');
    }
       
}  //end map