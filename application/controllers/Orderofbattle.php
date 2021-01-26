<?php

/**
 * Order of battle
 */
class Orderofbattle extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }    
    
    /**
     * View all orders of battle
     */
    function index()
    {
        $page = $this->page;
        $this->load->model('orderofbattlemodel');
        $oob = $this->orderofbattlemodel->get_all();
        
        $page['orderofbattles'] = $oob;
        $page['content'] = 'orderofbattles';
        $this->load->view('template', $page);
    }
    
    /**
     * Edit an existing order of battle
     */
    function edit_oob($oob_id)
    {
        // Make sure user is signed in
        $page = $this->page;
        
        if (!isset($page['user']->id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        $this->load->model('orderofbattlemodel');
        $page['oob'] = $this->orderofbattlemodel->get_by_id($oob_id);
        
        // Must be a draft
        if (!$page['oob']->draft)
        {
            $page['error'] = 'Can\'t change a released Order of Battle';
            $this->load->view('template', $page);
            return;
        }
        
        // Permission
        if ($page['user']->id != $page['oob']->user_id && $page['user']->group_id != 1)
        {
            $page['error'] = 'Only Admins and OOB creators are allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go

        // Validate form input
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|max_length[40]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[200]');
        $this->form_validation->set_rules('year', 'Year', 'required|integer|greater_than[2000]|less_than[4000]');

        if ($this->form_validation->run() == true)
        { 
            // edit the order of battle
            $oob                        = $page['oob'];
            $oob->name                  = $this->input->post('name');
            $oob->description           = $this->input->post('description');
            $oob->year                  = $this->input->post('year');
            $oob->destroy_jumpships     = $this->input->post('destroy_jumpships');
            $oob->auto_factory_dmg_mod  = $this->input->post('auto_factory_dmg_mod');
            $oob->use_merc_phase        = $this->input->post('use_merc_phase');
            $oob->capitals_to_win       = $this->input->post('capitals_to_win');
            $oob->use_comstar           = $this->input->post('use_comstar');
            $oob->use_terra_interdict   = $this->input->post('use_terra_interdict');
            $oob->use_terra_loot        = $this->input->post('use_terra_loot');
            $oob->user_id               = $page['user']->id;
            
            $this->load->model('orderofbattlemodel');
            $this->orderofbattlemodel->update($oob_id, $oob);
            
            // View the order of battle you just edited
            $this->session->set_flashdata('notice','Order of Battle Updated!');
            redirect('orderofbattle/view/'.$oob_id,'refresh');
        }
        else
        {   
            $page['label'] = 'Edit';
            $page['content'] = 'orderofbattleform';
            $this->load->view('template', $page);
        }
    }  // end edit_oob
    
    /**
     * Create a new order of battle
     */
    function create()
    {
        // Make sure user is signed in
        $page = $this->page;
        
        if (!isset($page['user']->id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // For now, only admins can create order of battles
        if ($page['user']->group_id != 1 && $page['user']->group_id != 3)
        {
            $page['error'] = 'Not allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go
        
        // Validate form input
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|max_length[40]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[200]');
        $this->form_validation->set_rules('year', 'Year', 'required|integer|greater_than[2000]|less_than[4000]');

        if ($this->form_validation->run() == true)
        { 
            // create the order of battle
            $oob                        = new stdClass();
            $oob->name                  = $this->input->post('name');
            $oob->description           = $this->input->post('description');
            $oob->year                  = $this->input->post('year');
            $oob->destroy_jumpships     = $this->input->post('destroy_jumpships');
            $oob->auto_factory_dmg_mod  = $this->input->post('auto_factory_dmg_mod');
            $oob->use_merc_phase        = $this->input->post('use_merc_phase');
            $oob->capitals_to_win       = $this->input->post('capitals_to_win');
            $oob->use_comstar           = $this->input->post('use_comstar');
            $oob->use_terra_interdict   = $this->input->post('use_terra_interdict');
            $oob->use_terra_loot        = $this->input->post('use_terra_loot');
            $oob->user_id               = $page['user']->id;
            
            $this->load->model('orderofbattlemodel');
            $this->orderofbattlemodel->create($oob);
            
            // View the order of battle you just created
            redirect('orderofbattle/','refresh');
        }
        else
        {   
            $oob                        = new stdClass();
            $oob->capitals_to_win       = 4;
            $oob->destroy_jumpships     = false;
            $oob->auto_factory_dmg_mod  = false;
            $oob->use_merc_phase        = false;
            $oob->use_comstar           = true;
            $oob->use_terra_interdict   = true;
            $oob->use_terra_loot        = true;
            $oob->year                  = 3025;
            $page['oob']                = $oob;
            $page['label']              = 'Create';
            $page['content']            = 'orderofbattleform';
            $this->load->view('template', $page);
        }
    }  // end create
    
    function view($id=0)
    {
        // Make sure user is signed in
        $page = $this->page;
        if (!isset($page['user']->id))
        {
            $page['error'] = 'Please sign in!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must provide an ID
        if ($id == 0)
        {
            $page['error'] = 'No id!';
            $this->load->view('template', $page);
            return;
        }    
        
        // Away we go
        $this->load->model('orderofbattlemodel');
        $page['orderofbattle'] = $this->orderofbattlemodel->get_by_id($id);
        
        // Fetch all components
        $page['data'] = $this->orderofbattlemodel->get_data($id);
        $page['cards'] = $this->db->query('select * from card_types')->result();
        $page['maps'] = $this->orderofbattlemodel->get_regions($id);
        $page['mercs'] = $this->db->query('select * from orderofbattledata where type=3 and arg4data=1 and oob_id='.$id)->result();
        
        // Fetch the available world ids
        $this->load->model('worldmodel');
        $page['worlds'] = $this->worldmodel->get_all();
        
        // Fetch the creator
        $page['creator'] = $this->ion_auth_model->get_user($page['orderofbattle']->user_id)->row();
        
        $page['content'] = 'vieworderofbattle';
        $this->load->view('template', $page);
    }

    function delete($oobid=0, $data_id=0)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($oobid == 0 || $data_id == 0)
        {
            $page['error'] = 'Bad data!';
            $this->load->view('template', $page);
            return;
        }
        
        // OOB must exist
        $this->load->model('orderofbattlemodel');
        $page['orderofbattle'] = $this->orderofbattlemodel->get_by_id($oobid);
        if (!isset($page['orderofbattle']->orderofbattle_id ))
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // Permission
        if ($page['user']->id != $page['orderofbattle']->user_id && $page['user']->group_id != 1)
        {
            $page['error'] = 'Only Admins and OOB creators are allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be a draft
        if (!$page['orderofbattle']->draft)
        {
            $page['error'] = 'Can\'t change a released Order of Battle';
            $this->load->view('template', $page);
            return;
        }
        
        $this->db->query('delete from orderofbattledata where data_id = '.$data_id);
        $this->page['notice'] = 'Data Deleted!';
        $this->view($oobid);
    }
    
    function copy($oobid=0, $data_id=0)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($oobid == 0 || $data_id == 0)
        {
            $page['error'] = 'Bad data!';
            $this->load->view('template', $page);
            return;
        }
        
        // OOB must exist
        $this->load->model('orderofbattlemodel');
        $page['orderofbattle'] = $this->orderofbattlemodel->get_by_id($oobid);
        if (!isset($page['orderofbattle']->orderofbattle_id ))
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be a draft
        if (!$page['orderofbattle']->draft)
        {
            $page['error'] = 'Can\'t change a released Order of Battle';
            $this->load->view('template', $page);
            return;
        }
        
        // Permission
        if ($page['user']->id != $page['orderofbattle']->user_id && $page['user']->group != 1)
        {
            $page['error'] = 'Only Admins and OOB creators are allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go...
        // Fetch data object
        $data = $this->db->query('select * from orderofbattledata where data_id = '.$data_id)->row();
        
        // Create new object
        $this->load->model('orderofbattlemodel');
        $this->orderofbattlemodel->create_data($data);
        
        $this->session->set_flashdata('notice', 'Data copied.');
        redirect('orderofbattle/view/'.$oobid, 'refresh');
    }
    
    function edit($data_id=0)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($data_id == 0)
        {
            $page['error'] = 'Bad data selection: $data_id='.$data_id.' !';
            $this->load->view('template', $page);
            return;
        }
        
        // OOB must exist
        $this->load->model('orderofbattlemodel');
        $data = $this->db->query('select * from orderofbattledata where data_id='.$data_id.' limit 1')->row();
        $page['orderofbattle'] = $this->orderofbattlemodel->get_by_id($data->oob_id);
        if (!isset($page['orderofbattle']->orderofbattle_id ))
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // Permission
        if ($page['user']->id != $page['orderofbattle']->user_id && $page['user']->group_id != 1)
        {
            $page['error'] = 'Only Admins and OOB creators are allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be a draft
        if (!$page['orderofbattle']->draft)
        {
            $page['error'] = 'Can\'t change a released Order of Battle';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go...
            
        //Check for POST data
        $showform = true;
        if ($this->input->post('data_id', true) != NULL)
            $showform = false;                
        
        //If no POST data, display the form        
        if ($showform)
        {
            // Get data to populate choices
            //$page['factions'] = $this->db->query('select arg0data as faction from orderofbattledata where type = 0')->result();
            $page['maps'] = $this->orderofbattlemodel->get_regions($data->oob_id);
            $page['regions'] = $this->db->query('select arg0data as region, arg1data as map_id from orderofbattledata where type = 2 order by arg0data asc')->result();
            $page['cards'] = $this->db->query('select * from card_types')->result();
            
            // Show edit view
            $page['data'] = $this->db->query('select * from orderofbattledata where data_id = '.$data_id.' limit 1')->row();        
            $page['type'] = $page['data']->type;
            
            $page['mercs'] = $this->db->query('select * from orderofbattledata where type=3 and arg4data=1 and oob_id='.$page['data']->oob_id.' order by arg0data asc')->result();

            $page['factions'] = $this->db->query('select arg0data as faction, arg1data as map_id from orderofbattledata where type = 0 and oob_id='.$page['data']->oob_id)->result();

            if ($page['type']==2)
            {
                
                $page['region'] = $this->orderofbattlemodel->get_region($page['data']->arg1data)->name;
                
            }
            else if ($page['type'] == 3)
            {
                if ($page['data']->arg3data != 'None')
                {
                    $page['region'] = $this->orderofbattlemodel->get_region($page['data']->arg3data);
                    if (isset($page['region']->name))
                    {
                        $page['region'] = $page['region']->name;
                    }
                    else
                    {
                        $page['region'] = 'Free Set-up';
                    }
                }
                else
                    $page['region'] = 'Not Initially Available';
            }
            else if ($page['type']==4)
            {
                $t = $this->orderofbattlemodel->get_region($page['data']->arg2data);
                if (isset($t->name))
                    $page['region'] = $t->name;
            }
            else if ($page['type']==5)
            {
                $page['region'] = $this->orderofbattlemodel->get_region($page['data']->arg0data)->name;
            }            
            
            $this->load->helper('form');
            $page['content'] = 'orderofbattleedit';
            $this->load->view('template', $page);  
        }
        // If POST data, update the database
         else
        {
            // Edit order of battle
             $type = $this->input->post('type', true);
             $data->oob_id = $this->input->post('oob_id', true);
             $data->data_id = $this->input->post('data_id', true);
            
            if ($type == 0) // PLAYERS
            {
                //$data->arg0column = 'faction';
                $data->arg0data = $this->input->post('faction', true);
                //$data->arg1column = 'turn_order';
                $data->arg1data = $this->input->post('turnorder', true);
                //$data->arg2column = 'money';
                $data->arg2data = $this->input->post('money', true);
                //$data->arg3column = 'tech_level';
                $data->arg3data = $this->input->post('tech', true);
                //$data->arg4column = 'color';
                $data->arg4data = $this->input->post('color', true);
                //$data->arg5column = 'text_color';
                $data->arg5data = $this->input->post('textcolor', true);
                $data->arg6column = 'free_bribes';
                $data->arg6data = $this->input->post('free_bribes', true);
                $data->arg7column = 'setup_order';
                $data->arg7data = $this->input->post('setuporder', true);
                $elementals = $this->input->post('elementals', true);
                $data->arg8column = 'may_build_elementals';
                if ($elementals != '')
                {
                    $data->arg8data = $elementals;
                }
            }
            else if ($type == 1) // CARDS
            {
                $data->arg0data = $this->input->post('card', true);
            }
            else if ($type == 2) // TERRITORIES
            {
                //$data->arg0column = 'faction';
                $data->arg0data = $this->input->post('faction', true);
                //$data->arg1column = 'map_id';
                $data->arg1data = $this->input->post('mapid', true);
                
                $data->arg2column = 'is_periphery';
                if ($data->arg0data == 'Neutral')
                    $data->arg2data = 1;
                else
                    $data->arg2data = 0;
                
                $data->arg3column = 'garrison_name';
                $data->arg3data = $this->input->post('garrison_name', true);
                
                $data->arg4column = 'resource';
                $data->arg4data = $this->input->post('resource', true);
                
                $data->arg5column = 'is_regional';
                $data->arg5data = $this->input->post('is_regional', true);
                
                $data->arg6column = 'is_capital';
                $data->arg6data = $this->input->post('is_capital', true);
            }
            else if ($type == 3)  // COMBAT UNITS
            {
                //$data->arg0column = 'name';
                $data->arg0data = $this->input->post('name', true);
                //$data->arg1column = 'faction';
                $data->arg1data = $this->input->post('faction', true);
                //$data->arg2column = 'prewar_strength';
                $data->arg2data = $this->input->post('strength', true);
                //$data->arg3column = 'location_id';
                $data->arg3data = $this->input->post('location', true);
                $data->arg4column = 'is_merc';
                if ( $this->input->post('is_merc', true) == 'on' )
                    $data->arg4data = 1;
                else
                    $data->arg4data = 0;
                
                if ( $this->input->post('can_rebuild', true) == 'on' )
                    $data->arg5data = 1;
                else
                    $data->arg5data = 0;                
            }
            else if ($type == 4)  // LEADERS
            {
                //$data->arg0column = 'name';
                    $data->arg0data = $this->input->post('name', true);
                //$data->arg1column = 'faction';
                $data->arg1data = $this->input->post('faction', true);  
                if ($data->arg1data == 'Neutral')
                    $data->arg1data = null;
                //$data->arg2column = 'location_id';
                $data->arg2data = $this->input->post('location', true);
                //$data->arg3column = 'military';
                $data->arg3data = $this->input->post('military', true);
                //$data->arg4column = 'combat';
                $data->arg4data = $this->input->post('combat', true);
                //$data->arg5column = 'admin';
                $data->arg5data = $this->input->post('admin', true);
                //$data->arg6column = 'loyalty';
                $data->arg6data = $this->input->post('loyalty', true);
                
                // Associated units!
                unset ($data->arg7data);
            }
            else if ($type == 5)  // FACTORIES
            {
                //$data->arg0column = 'location_id';
                $data->arg0data = $this->input->post('location', true);
            }
            else if ($type == 6)  // JUMPSHIPS
            {
                //$data->arg0column = 'faction';
                $data->arg0data = $this->input->post('faction', true);                
                //$data->arg1column = 'location_id';
                $data->arg1data = $this->input->post('location', true);
                //$data->arg2column = 'capacity';
                $data->arg2data = $this->input->post('capacity', true);
            }
            
            $this->orderofbattlemodel->edit_data($data);
            
            $this->session->set_flashdata('notice', 'Item updated.');
            redirect('orderofbattle/view/'.$data->oob_id, 'refresh');
        }
    }
    
    function add($id=0, $type=-1, $arg=0)
    {
        $page = $this->page;
        $this->load->helper('form');
        
        // Make sure ids are provided
        if ($id == 0 || $type == -1)
        {
            $page['error'] = 'Please sign in!';
            $this->load->view('template', $page);
            return;
        }
        
        // OOB must exist
        $this->load->model('orderofbattlemodel');
        $page['orderofbattle'] = $this->orderofbattlemodel->get_by_id($id);
        if (!isset($page['orderofbattle']->orderofbattle_id ))
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be a draft
        if (!$page['orderofbattle']->draft)
        {
            $page['error'] = 'Can\'t change a released Order of Battle';
            $this->load->view('template', $page);
            return;
        }
        
        // Permission
        if ($page['user']->id != $page['orderofbattle']->user_id && $page['user']->group_id != 1)
        {
            $page['error'] = 'Only Admins and OOB creators are allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Check for post data
        $showform = true;
        if ($type == 0 && $this->input->post('faction', true) != false)
            $showform = false;
        else if ($type == 1 && $arg != 0)
            $showform = false;
        else if ($type == 2 && $this->input->post('faction', true) != false)
            $showform = false;
        else if ($type == 3 && $this->input->post('strength', true) != false)
            $showform = false;
        else if ($type == 4 && $this->input->post('military', true) != false)
            $showform = false;
        else if ($type == 5 && $this->input->post('location', true) != false)
            $showform = false;
        else if ($type == 6 && $this->input->post('faction', true) != false)
            $showform = false;
        
        // Setup $page for errors
        $pageform['orderofbattle'] = $page['orderofbattle'];
        $pageform['cards'] = $this->db->query('select * from card_types')->result();
        $pageform['factions'] = $this->db->query('select arg0data as faction from orderofbattledata where type = 0 and oob_id='.$id)->result();
        $pageform['maps'] = $this->orderofbattlemodel->get_regions_by_world($page['orderofbattle']->world_id);
        $pageform['type'] = $type;            
        $pageform['mercs'] = $this->db->query('select * from orderofbattledata where type=3 and arg4data=1 and oob_id='.$id.' order by arg0data asc')->result();

        if ($showform)
        {
            // Show add view
            $pageform['content'] = 'orderofbattleadd';
            $this->load->view('template', $pageform);
        }
        else
        {
            // Add to order of battle
            $data = new stdClass();
            $data->oob_id = $id;
            $data->type = $type;
            
            if ($type == 0) // PLAYERS
            {
                $data->arg0column = 'faction';
                $data->arg0data = $this->input->post('faction', true);
                $data->arg1column = 'turn_order';
                $data->arg1data = $this->input->post('turnorder', true);
                $data->arg7column = 'setup_order';
                $data->arg7data = $this->input->post('setuporder', true);
                $data->arg2column = 'money';
                $data->arg2data = $this->input->post('money', true);
                $data->arg3column = 'tech_level';
                $data->arg3data = $this->input->post('tech', true);
                $data->arg4column = 'color';
                $data->arg4data = $this->input->post('color', true);
                $data->arg5column = 'text_color';
                $data->arg5data = $this->input->post('textcolor', true);
                $data->arg6column = 'free_bribes';
                $data->arg6data = $this->input->post('free_bribes', true);
                $data->arg8column = 'may_build_elementals';
                $data->arg8data = $this->input->post('elementals', true);
                if ($data->arg8data == '')
                {
                    unset($data->arg8data);
                }
            }
            else if ($type == 1) // CARDS
            {
                if ($arg != 0)
                {
                    $data->arg0column = 'type_id';
                    $data->arg0data = $arg;
                }
                else
                {
                    $this->page['error'] = 'Bad input.';
                    $this->view($id);
                } 
            }
            else if ($type == 2) // TERRITORIES
            {
                $data->arg0column = 'faction';
                $data->arg0data = $this->input->post('faction', true);
                    
                $data->arg1column = 'map_id';
                $data->arg1data = $this->input->post('mapid', true);
                
                $data->arg2column = 'is_periphery';
                if ($data->arg0data == 'Neutral')
                    $data->arg2data = 1;
                else
                    $data->arg2data = 0;
                
                $data->arg3column = 'garrison_name';
                $data->arg3data = $this->input->post('garrison_name', true);
                
                $data->arg4column = 'resource';
                $data->arg4data = $this->input->post('resource', true);
                
                $data->arg5column = 'is_regional';
                $data->arg5data = $this->input->post('is_regional', true);
                
                $data->arg6column = 'is_capital';
                $data->arg6data = $this->input->post('is_capital', true);
            }
            else if ($type == 3)  // COMBAT UNITS
            {
                // Check the type of unit (Mech, Conventional, or Elemental)
                $combat_unit_type = $this->input->post('type', true);
                
                $data->arg0column = 'name';
                $data->arg1column = 'faction';
                $data->arg2column = 'prewar_strength';
                $data->arg3column = 'location_id';
                $data->arg4column = 'is_merc';
                $data->arg5column = 'can_rebuild';
                
                $data->arg1data = $this->input->post('faction', true);                
                $data->arg3data = $this->input->post('location', true);
                
                if ($combat_unit_type == 'Mech')
                {
                    $data->arg0data = $this->input->post('name', true);
                    $data->arg2data = $this->input->post('strength', true);
                    if ( $this->input->post('is_merc', true) == 'on' )
                        $data->arg4data = 1;
                    else
                        $data->arg4data = 0;
                    
                    if ( $this->input->post('can_rebuild', true) == 'on' )
                        $data->arg5data = 1;
                    else
                        $data->arg5data = 0; 
                }
                else if ($combat_unit_type == 'Conventional')
                {
                    $data->arg0data = 'Conventional';
                    $data->arg2data = 3;
                    $data->arg4data = 0;
                    $data->arg5data = 0; 
                    $data->arg6column = 'is_conventional';
                    $data->arg6data = 1;
                }
                else if ($combat_unit_type == 'Elemental')
                {
                    // Faction must be able to build Elementals
                    $players = $this->orderofbattlemodel->get_players($id);
                    foreach($players as $p)
                    {
                        if ($p->arg0data == $data->arg1data)
                        {
                            if (isset($p->arg8data) && $p->arg8data != '')
                            {
                                $data->arg0data = $p->arg8data;
                                $data->arg2data = 2;
                                $data->arg4data = 0;
                                $data->arg5data = 0;
                                $data->arg6column = 'is_elemental';
                                $data->arg6data = 1;
                            }
                            else
                            {
                                // This faction is not allowed to make elementals, fail
                                $pageform['content'] = 'orderofbattleadd';
                                $this->load->view('template', $pageform);
                            }
                        }
                    }
                    
                    // Make sure we arrived at a match or fail out
                    if (!isset($data->arg0data))
                    {
                        // This faction is not allowed to make elementals, fail
                        $pageform['content'] = 'orderofbattleadd';
                        $this->load->view('template', $pageform);
                    }
                    
                }
                else
                {
                    // Fail, unknown combat unit type...
                    $pageform['content'] = 'orderofbattleadd';
                    $this->load->view('template', $pageform);
                }
            }
            else if ($type == 4)  // LEADERS
            {
                $data->arg0column = 'name';
                $data->arg0data = $this->input->post('name', true);
                $data->arg1column = 'faction';
                
                $data->arg1data = $this->input->post('faction', true);               
                
                $data->arg2column = 'location_id';
                $data->arg2data = $this->input->post('location', true);
                $data->arg3column = 'military';
                $data->arg3data = $this->input->post('military', true);
                $data->arg4column = 'combat';
                $data->arg4data = $this->input->post('combat', true);
                $data->arg5column = 'admin';
                $data->arg5data = $this->input->post('admin', true);
                $data->arg6column = 'loyalty';
                $data->arg6data = $this->input->post('loyalty', true);
                
                $data->arg7column = 'associated_units';
                $associated_units = $this->input->post('associated_units', true);

                if ( $associated_units == '0' || $associated_units == 'null' )
                {
                    unset($data->arg7data);
                }
                else
                {
                    $data->arg7data = $associated_units;
                }
            }
            else if ($type == 5)  // FACTORIES
            {
                $data->arg0column = 'location_id';
                $data->arg0data = $this->input->post('location', true);
            }
            else if ($type == 6)  // JUMPSHIPS
            {
                $data->arg0column = 'faction';
                $data->arg0data = $this->input->post('faction', true);                
                $data->arg1column = 'location_id';
                $location = $this->input->post('location', true);
                $data->arg1data = ( $location == 'Free' ? null : $location );
                $data->arg2column = 'capacity';
                $data->arg2data = $this->input->post('capacity', true);
            }
            
            $this->orderofbattlemodel->create_data($data);

            $this->session->set_flashdata('notice', 'New item added.');
            redirect('orderofbattle/view/'.$id, 'refresh');
        }

    }
    
    /**
     * Add one of every map to an order of battle to speed up 
     * 
     */
    function generate_map($id=0, $world_id=0)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($id == 0)
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // Default is the standard map
        if ($world_id == 0)
            $world_id = 1;
        
        // OOB must exist
        $this->load->model('orderofbattlemodel');
        $page['orderofbattle'] = $this->orderofbattlemodel->get_by_id($id);
        if (!isset($page['orderofbattle']->orderofbattle_id ))
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // OOB must be a draft
        if (!$page['orderofbattle']->draft)
        {
            $page['error'] = 'Can\'t change a released Order of Battle';
            $this->load->view('template', $page);
            return;
        }
        
        // Permission
        if ($page['user']->id != $page['orderofbattle']->user_id && $page['user']->group != 1)
        {
            $page['error'] = 'Only Admins and OOB creators are allowed!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go!
        $this->load->model('mapmodel');
        $this->load->model('orderofbattlemodel');
        $maps = $this->mapmodel->get_all_by_world($world_id);
        
        // Must be a valid map
        if (count($maps) == 0)
        {
            $page['error'] = 'No such map!';
            $this->load->view('template', $page);
            return;
        }    
        
        $o              = new stdClass();
        $o->arg0column  = 'faction';
        $o->arg0data    = 'Comstar';
        $o->arg1column  = 'map_id';
        $o->arg2column  = 'is_periphery';
        $o->arg2data    = '0';
        $o->type        = 2;
        $o->oob_id      = $id;
        foreach($maps as $m)
        {
           $o->arg1data = $m->map_id;
           $this->orderofbattlemodel->create_data($o);
        }
        
        //set orderofbattle.world_id to $world_id
        $oob_update = new stdClass();
        $oob_update->world_id = $world_id;
        $this->orderofbattlemodel->update($id, $oob_update);
        
        $this->page['notice'] = 'Map Generated!';
        $this->view($id);
    }
    
    /**
     * Set this order of battle to draft status
     */
    function set_to_draft($oob_id=0)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($oob_id == 0)
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // OOB must exist
        $this->load->model('orderofbattlemodel');
        $orderofbattle = $this->orderofbattlemodel->get_by_id($oob_id);
        if (!isset($orderofbattle->orderofbattle_id ))
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        $orderofbattleupdate = new stdClass();
        $orderofbattleupdate->orderofbattle_id = $orderofbattle->orderofbattle_id;
        $orderofbattleupdate->draft = 1;
        $this->orderofbattlemodel->update($orderofbattleupdate->orderofbattle_id, $orderofbattleupdate);
        
        redirect('orderofbattle/view/'.$oob_id,'refresh');
        
    }  // end function set_to_draft()
    
    /**
     * Set this order of battle to released status
     */
    function set_to_released($oob_id=0)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($oob_id == 0)
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // OOB must exist
        $this->load->model('orderofbattlemodel');
        $orderofbattle = $this->orderofbattlemodel->get_by_id($oob_id);
        if (!isset($orderofbattle->orderofbattle_id ))
        {
            $page['error'] = 'Error!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        $orderofbattleupdate = new stdClass();
        $orderofbattleupdate->orderofbattle_id = $orderofbattle->orderofbattle_id;
        $orderofbattleupdate->draft = 0;
        $this->orderofbattlemodel->update($orderofbattleupdate->orderofbattle_id, $orderofbattleupdate);
        
        redirect('orderofbattle/view/'.$oob_id,'refresh');
        
    }  // end function set_to_released()
    
}  // end orderofbattle
