<?php

class Jumpship extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }  

    /**
     * Name a jumpship
     */
    function name($jumpship_id=0)
    {
        $page = $this->page;
        
        // Valid input
        if ($jumpship_id==0)
        {
            $page['error'] = 'Error! Cannot name a jumpship without an ID.';
            $this->load->view('templatexml', $error);
            return;
        }
        
        // Must own the jumpship in question
        $this->load->model('jumpshipmodel');
        $jumpship = $this->jumpshipmodel->get_by_id($jumpship_id);
        
        $this->load->model('playermodel');
        $owner = $this->playermodel->get_by_id($jumpship->owner_id);
        
        if ($owner->user_id != $page['user']->id)
        {
            $this->session->set_flashdata('error', 'Error! Cannot name a jumpship you do not own.');
            redirect('sw/jumpship/'.$jumpship_id, 'refresh');
        }
        
        // Away we go
        $this->load->library('form_validation');
        
        // Validate form input
        $name = $this->input->post('input');
        if (count_chars($name) > 40 && $name != false)
        {
            $page['error'] = 'Error! Cannot name a jumpship with a name longer than 40 characters.';
            $name == false;
        }
        
        if ($name != false)
        { 
            // Update the jumpship
            $jumpshipupdate = new stdClass();
            $jumpshipupdate->name = $name;
            $jumpshipupdate->jumpship_id = $jumpship_id;
            
            $this->jumpshipmodel->update($jumpship_id, $jumpshipupdate);
            
            // Redirect to jumpship view            
            $this->page['notice'] = 'Jumpship Name Updated!';
            $this->view($jumpship_id);
        }
        else
        {
            $page['jumpship'] = $jumpship;
            $page['content'] = 'jumpship_name';
            $this->load->view('templatexml', $page);
        }
        
    }  // end name
    
    /**
     * Jump from one territory to another along a path
     */
    function move($jumpship_id=0,$territory_id=0)
    {
        $page = $this->page;
        $this->load->model('combatunitmodel');
        $this->load->helper('movement');
        
        // Make sure ids are provided
        if ( $jumpship_id == 0 || $territory_id == 0 )
        {
            $page['error'] = 'Error! Cannot move jumpship without providing jumpship ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect('auth/login', 'refresh');
        }
        
        // Fetch the jumpship
        $this->load->model('jumpshipmodel');
        $jumpship = $this->jumpshipmodel->get_by_jumpshipid($jumpship_id);
        
        // Make sure the jumpship exists
        if ( !isset($jumpship) ) 
        {
            $page['error'] = 'Error! Cannot move a jumpship that doesn\'t exist!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the owner
        $this->load->model('playermodel');
        $owner = $this->playermodel->get_by_id($jumpship->owner_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($owner->game_id);
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game->game_id);
        $is_playing = false;
        foreach( $players as $player )
        {
            if ( $player->user_id == $page['user']->id )
            {
                $is_playing = true;
                $this_player = $player;
            }
        }
        if ( !$is_playing )
        {
            $page['error'] = 'Error! Cannot move a jumpship in a game you aren\'t playing in.';
            $this->load->view('templatexml', $page);
            return;
        }
        $player = $this_player;
        
        // Must own the jumpship
        if ( $jumpship->owner_id != $player->player_id )
        {
            $page['error'] = 'Error! Cannot move a jumpship you don\'t own.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Jumpship can't be under construction
        if ( $jumpship->being_built )
        {
            $this->page['error'] = 'Jumpship is under construction!';
            $this->view($jumpship_id);
            return;
        }
        
        // If the game is on hold
        $error = check_game_hold($game->game_id);
        if ( $error !== false )
        {
            $page['error'] = 'Error! Cannot move jumpship because: '.$error;
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch paths out of this location
        $this->load->model('territorymodel');
        $adjacent = $this->territorymodel->get_adjacent($jumpship->location_id, $game->game_id);
        
        // Make sure the destination is adjacent to the jumpships current location
        $is_adjacent = false;
        foreach($adjacent as $location)
        {
            if ( $location->territory_id == $territory_id )
            {
                $is_adjacent = true;
                $destination = $location;
            }
        }
        if (!$is_adjacent)
        {
            $this->page['error'] = 'Destination is not adjacent!';
            $this->view($jumpship_id);
            return;
        }
        
        // Check for periphery        
        if ($destination->is_periphery)
        {
            $this->page['error'] = 'You cannot move to a neutral Periphery nation!  Open up bidding if you wish to gain control of the territory and forces.';
            $this->view($jumpship_id);
            return;
        }
        
        // Fetch the origin
        $origin = $this->territorymodel->get_by_id($jumpship->location_id);
        
        // Check phase
        // Movement is normal
        // Combat is Retreat
        // Anything else is an error
        if ($game->phase == 'Movement')
        {
            $retreat = false;
            // Make sure it is the player's turn
            if ( $game->player_id_playing != $player->player_id)
            {
                $this->page['error'] = 'It is not your turn!';
                $this->view($jumpship_id);
                return;
            }
            
            // Make sure the jumpship has the moves available
            if ($player->tech_level > 14)
                    $moves = 4;
            else if ($player->tech_level < -6)
                    $moves = 2;
            else
                $moves = 3;

            if ( $jumpship->moves_this_turn >= $moves )
            {
                $this->page['error'] = 'This jumpship is out of moves for the turn!';
                $this->view($jumpship_id);
                return;
            }
            
            // If the current location is not friendly, the origin and destination
            // must not have any enemy units in it
            $destination_occupied = false;
            $origin_occupied = false;
            
            $this->db->where('owner_id <>', $jumpship->owner_id);
            $units = $this->combatunitmodel->get_by_location($destination->territory_id);

            if (count($units) > 0)
                $destination_occupied = true;
            
            $this->db->where('owner_id <>', $jumpship->owner_id);
            $units = $this->combatunitmodel->get_by_location($origin->territory_id);

            if (count($units) > 0)
                $origin_occupied = true;

            if ($origin_occupied  && $origin->player_id != $jumpship->owner_id)
            {
                if ($destination->player_id != $jumpship->owner_id)
                {
                    $this->page['error'] = 'You can only move this jumpship back to a friendly territory!';
                    $this->view($jumpship_id);
                    return;
                }
            }

            // If you have units loaded, the origin must not contain enemy units
            $loadedunits = $this->combatunitmodel->get_by_jumpship($jumpship_id);
            if (count($loadedunits) > 0 && $origin_occupied && $origin->player_id != $jumpship->owner_id)
            {
                $this->page['error'] = 'You can\'t leave an occupied enemy territory with loaded combat units!';
                $this->view($jumpship_id);
                return;
            }
            
        }
        else if ($game->phase == 'Combat')
        {
            $retreat = true;
            if ($game->combat_rnd == 0)
            {
                $this->page['error'] = 'Retreats are not allowed!';
                $this->view($jumpship_id);
                return;
            }
            
            // If retreat, the destination must be friendly, the origin must be contested
            if ($destination->player_id != $jumpship->owner_id)
            {
                $this->page['error'] = 'Destination is not friendly!';
                $this->view($jumpship_id);
                return;
            }
            if (!$origin->is_contested)
            {
                $this->page['error'] = 'You cannot retreat from here!';
                $this->view($jumpship_id);
                return;
            }
            
            // If retreat, the origin MUST have friendly combat units remaining and
            // have all casualties assigned...
            // Plus, there must be combat units alive on the oppossing side
            $this->load->model('combatlogmodel');
            $combatlog = $this->combatlogmodel->get_by_player_territory($player->player_id, $origin->territory_id);
            if (!$combatlog->is_retreat_allowed)
            {
                $this->page['error'] = 'You are not allowed to retreat! You have either been wiped out or have wiped out your opponent!';
                $this->view($jumpship_id);
                return;
            }        
        }
        else
        {
            $this->page['error'] = 'Wrong phase!';
            $this->view($jumpship_id);
            return;
        }
        
        // Away we go!
        // Fire up the KF Drive!!!
        $jumpshipupdate = new stdClass();
        $jumpshipupdate->location_id = $territory_id;
        
        // Determine next movement order
        $this->load->model('movementlogmodel');
        $next_order = $this->movementlogmodel->get_movement_order($game->game_id) + 1;
        
        // Don't decrement movement if we are retreating (ie. the Combat Phase)
        if ($game->phase != 'Combat')
        {
            $jumpshipupdate->moves_this_turn = $jumpship->moves_this_turn + 1;
        }
        
        create_movement_log('jumpship', $jumpship_id, $game->game_id, $jumpship->location_id, $next_order);
        $jumpshipupdate->location_id = $territory_id;
        $this->jumpshipmodel->update($jumpship_id, $jumpshipupdate);
        
        // Move loaded units
        $this->load->model('combatunitmodel');
        $loadedunits = $this->combatunitmodel->get_by_jumpship($jumpship_id);
        foreach($loadedunits as $unit)
        {
            $unit->location_id = $territory_id;
            if($game->phase == 'Movement')
            {
                $unit->was_loaded = $jumpship_id;
            }
            if ($game->phase == 'Combat')
            {
                // Automatically drop all units
                $unit->was_loaded = 0;
                $unit->loaded_in_id = null;
            }
            $this->combatunitmodel->update($unit->combatunit_id, $unit);
            create_movement_log('combatunit', $unit->combatunit_id, $game->game_id, $jumpship->location_id, $next_order);
        }
        
        // Move loaded leaders
        $this->load->model('leadermodel');
        $loadedleaders = $this->leadermodel->get_by_jumpship($jumpship_id);
        foreach($loadedleaders as $leader)
        {
            $leaderupdate = new stdClass();
            $leaderupdate->leader_id = $leader->leader_id;
            $leaderupdate->location_id = $territory_id;
            if($game->phase == 'Movement')
            {
                $leaderupdate->was_loaded = $jumpship_id;
            }
            if ($game->phase == 'Combat')
            {
                // Automatically drop all units
                $leader->was_loaded = 0;
                $leader->loaded_in_id = null;
            }
            $this->leadermodel->update($leader->leader_id, $leaderupdate);
            create_movement_log('leader', $leader->leader_id, $game->game_id, $jumpship->location_id, $next_order);
            
            if ( $retreat && $player->tech_level != 25 && $leader->combat_used)
            {
                // remove combat bonus
                $this->db->query('delete from combatbonus where source_type=1 and source_id='.$leader->leader_id);
            }
        }
        
        // Check for contested area and mark the territory accordingly
        if ( $this->territorymodel->is_contested($territory_id) )
        {
            if ($destination->is_contested != true)
            {
                $territoryupdate = new stdClass();
                $territoryupdate->is_contested = true;
                $territoryupdate->territory_id = $territory_id;
                $this->territorymodel->update($territory_id, $territoryupdate);
            }
        }
        $this->page['notice'] = 'Jumped to '.$destination->name.'.';
        
        game_message($game->game_id, 
                $player->faction.($retreat ? ' retreated' : ' moved').
                ' Jumpship '.$jumpship->capacity.($jumpship->jumpship_name != "" ? ' ('.$jumpship->jumpship_name.')' : '').
                ' from '.$origin->name.' to '.$destination->name.'.');
        
        // Update origin and destination
        update_territory($destination->territory_id);
        update_territory($origin->territory_id);
        
        // Load the jumpship view
        $this->view($jumpship_id);

    }  // end move
    
     /**
     * Fetch all of the jumpships in this game belonging to the current player
     * @param type $game_id The game being played
     */
    function jumpships($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'Error! Cannot view jumpships in a game that doesn\t exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect('auth/login', 'refresh');
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'Error! Cannot view jumpships when there is a problem finding the correct game.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
        }
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'Error! Cannot view jumpships in a game you are not playing in.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch locations in the game...
        $this->load->model('jumpshipmodel');
        $page['jumpships'] = $this->jumpshipmodel->get_by_player($page['player']->player_id);
        $this->load->view('jumpships', $page);

    }
    
     /**
     * Fetch a single jumpship
     * @param type $jumpship_id The jumpship id
     */
    function view($jumpship_id=0)
    {
        $page = $this->page;
        $page['content'] = 'jumpship';
        
        // Make sure an id is provided
        if ( $jumpship_id == 0 )
        {
            $page['error'] = 'Error! Cannot view a jumpship without an ID';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the jumpship
        $this->load->model('jumpshipmodel');
        $temp['jumpship'] = $this->jumpshipmodel->get_by_jumpshipid($jumpship_id);
        
        // Make sure the jumpship exists
        if ( !isset($temp['jumpship']->jumpship_id) ) 
        {
            $page['error'] = 'Error! Cannot view a jumpship that doesn\'t exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the jumpship
        $page['jumpship'] = $temp['jumpship'];
        
        // Fetch the owner
        $this->load->model('playermodel');
        $page['owner'] = $this->playermodel->get_by_id($page['jumpship']->owner_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['owner']->game_id);
                
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($page['game']->game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;                
            }
        }
        
        // Fetch units in this location for this jumpships owner_id
        $this->load->model('combatunitmodel');
        $page['units'] = $this->combatunitmodel->get_by_location_player($page['jumpship']->location_id, $page['jumpship']->owner_id);
        
        // Fetch leaders in this location for this jumpships owner_id
        $this->load->model('leadermodel');
        $page['leaders'] = $this->leadermodel->get_by_player_territory($page['jumpship']->owner_id, $page['jumpship']->location_id);
        
        // Fetch paths out of this location
        $this->load->model('territorymodel');
        $page['adjacent'] = $this->territorymodel->get_adjacent($page['jumpship']->location_id, $page['game']->game_id);
        
        // For retreats, fetch the combatlog if any
        $this->load->model('combatlogmodel');
        $log = $this->combatlogmodel->get_by_player_territory($page['jumpship']->owner_id, $page['jumpship']->location_id);
        if (isset($log->casualties_owed) && $log->casualties_owed > 0)
            $page['owes_casualties'] = 1;
        else
            $page['owes_casualties'] = 0;
        
        $this->load->view('templatexml', $page);
    }   
    
    function load_unit($jumpship_id=0, $unit_id=0)
    {
        // Make sure an id is provided
        if ( $jumpship_id == 0 || $unit_id == 0 )
            redirect($this->config->item('base_url'), 'refresh');
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            $this->page['error'] = 'Not signed in!';
            $this->view($jumpship_id);
            return;
        }
                
        // Make sure the jumpship and combat unit exist
        $this->load->model('jumpshipmodel');
        $this->load->model('combatunitmodel');
        $page['jumpship'] = $this->jumpshipmodel->get_by_id($jumpship_id);
        if ( !isset($page['jumpship']->jumpship_id) ) 
        {
            $this->page['error'] = 'No such jumpship!';
            $this->view($jumpship_id);
            return;
        }
        
        $page['combatunit'] = $this->combatunitmodel->get_by_id($unit_id);
        if ( !isset($page['combatunit']->combatunit_id) ) 
        {
            $this->page['error'] = 'No such combat unit!';
            $this->view($jumpship_id);
            return;
        }      

        // Player is owned by user associated with this request
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['jumpship']->owner_id);
        if ($page['player']->user_id != $this->ion_auth->get_user()->id)
        {
            $this->page['error'] = 'Bad player!';
            $this->view($jumpship_id);
            return;
        }

        // Jumpship and combat unit are owned by the same player
        if ($page['jumpship']->owner_id != $page['combatunit']->owner_id)
        {
            $this->page['error'] = 'Not your combat unit!';
            $this->view($jumpship_id);
            return;
        }

        if ($page['jumpship']->owner_id != $page['player']->player_id)
        {
            $this->page['error'] = 'Not your jumpship!';
            $this->view($jumpship_id);
            return;
        }

        // Are retreats allowed?
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        $this->load->model('territorymodel');
        $location = $this->territorymodel->get_by_id($page['jumpship']->location_id);
        if ($page['game']->combat_rnd > 0 && $location->is_contested == true && $page['game']->phase == 'Combat')
            $retreat = true;
        else
            $retreat = false;
        
        // Must be players turn, unless retreats are allowed
        if ($page['game']->player_id_playing != $page['player']->player_id && !$retreat)
        {
            $this->page['error'] = 'It is not your turn!';
            $this->view($jumpship_id);
            return;
        }
        
        // Must be the movement phase, unless retreats are allowed
        if ($page['game']->phase != 'Movement' && !$retreat)
        {
            $this->page['error'] = 'It is not the right phase!';
            $this->view($jumpship_id);
            return;
        }
        
        // Combat unit and jumpship can't be under construction
        if ( $page['jumpship']->being_built || $page['combatunit']->being_built )
        {
            $this->page['error'] = 'This jumpship is under construction!';
            $this->view($jumpship_id);
            return;
        }
        
        if ($retreat)
        {
            
            $this->load->model('territorymodel');
            $location = $this->territorymodel->get_by_id($page['jumpship']->location_id);
            if ($location->is_contested == false)
            {
                $this->page['error'] = 'Error! You cannot retreat from an uncontested region.';
                $this->view($jumpship_id);
                return;
            }
            else
            {
                // All casualties are assigned...
                $this->load->model('combatlogmodel');
                $playerlogs = $this->combatlogmodel->get_by_player_territory($page['player']->player_id, $page['jumpship']->location_id);
                foreach($playerlogs as $playerlog )
                {
                    if (isset($playerlog->casualties_owed))
                    {
                        if ($playerlog->casualties_owned != 0)
                        {
                            $this->page['error'] = 'Error! Cannot load units while you have unassigned casualties in this territory.';
                            $this->view($jumpship_id);
                            return;
                        }
                    }
                }
            }
        }
        
        // Jumpship must have room available
        $page['loadedunits'] = $this->combatunitmodel->get_by_jumpship($jumpship_id);
        $loadedsize = 0;
        foreach($page['loadedunits'] as $unit)
        {
            $loadedsize += $unit->size;
        }
        $loadedsize += $page['combatunit']->size;
        if ($loadedsize > $page['jumpship']->capacity)
        {
            $this->page['error'] = 'Error! Cannot load unit as jumpship does not have capacity.';
            $this->view($jumpship_id);
            return;
        }    
        
/*        // Unit must not be loaded already
        if ($page['combatunit']->loaded_in_id != null)
        {
            $this->page['error'] = 'Error! Cannot load a unit that has already been loaded.';
            $this->view($jumpship_id);
            return;
        } 
*/        
        // Cant have been loaded earlier this turn in a different jumpship, unless retreat is allowed
        if ( $page['combatunit']->was_loaded!=0 && $page['combatunit']->was_loaded!=$jumpship_id && !$retreat)
        {
            $this->page['error'] = 'Error! Cannot load unit into more than one jumpship in a turn unless retreating.';
            $this->view($jumpship_id);
            return;
        } 
        
        // All Aboard!!!
        $page['combatunit']->loaded_in_id = $jumpship_id;
        $this->combatunitmodel->update($unit_id, $page['combatunit']);
        
        // Load the jumpship view
        $this->page['notice'] = 'Loaded '.$page['combatunit']->name.'.';
        $this->view($jumpship_id);
    }
    
    function drop_unit($unit_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $unit_id == 0 )
        {
            $this->page['error'] = 'What jumpship?';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
            redirect('auth/login', 'refresh');
        
        // Make sure the unit exists
        $this->load->model('combatunitmodel');
        $page['combatunit'] = $this->combatunitmodel->get_by_id($unit_id);
        if ( !isset($page['combatunit']->combatunit_id) )
        {
            $this->page['error'] = 'No such unit.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // The unit must belong to a player associated with the user
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['combatunit']->owner_id);
        if ( $page['player']->user_id != $this->ion_auth->get_user()->id )
        {
            $this->page['error'] = 'You do not own this unit.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        // Drop 'em
        $jumpship_id = $page['combatunit']->loaded_in_id;
        $page['combatunit']->loaded_in_id = null;
        $this->combatunitmodel->update($unit_id, $page['combatunit']);
        
        // Load the jumpship view
        $this->page['notice'] = 'Unloaded '.$page['combatunit']->name.'.';
        $this->view($jumpship_id);     
    }
    
    /**
     * Drop all combatunits and leaders from this jumpship
     */
    function drop_all($jumpship_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $jumpship_id == 0 )
        {
            $this->page['error'] = 'What jumpship?';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
            redirect('auth/login', 'refresh');
        
        // Fetch the jumpship
        $this->load->model('jumpshipmodel');
        $temp['jumpship'] = $this->jumpshipmodel->get_by_jumpshipid($jumpship_id);
        
        // Make sure the jumpship exists
        if ( !isset($temp['jumpship']->jumpship_id) ) 
        {
            $page['error'] = 'Error! Cannot drop units from a jumpship that doesn\'t exist';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the jumpship
        $page['jumpship'] = $temp['jumpship'];
        
        $this->load->model('playermodel');
        $owner = $this->playermodel->get_by_id($page['jumpship']->owner_id);
        
        // Must own the jumpship in question
        if ($owner->user_id != $page['user']->id)
        {
            $this->session->set_flashdata('error', 'You do not own that jumpship!');
            redirect('sw/jumpship/'.$jumpship_id, 'refresh');
        }
        
        // Away we go...
        
        // Drop combat units and leaders
        $this->jumpshipmodel->drop_all($jumpship_id);

        $this->page['notice'] = 'All units dropped!';
        $this->view($jumpship_id);
        
    }  // end drop_all
    
    function load_leader($jumpship_id=0, $leader_id=0)
    {
        // Make sure an id is provided
        if ( $leader_id == 0 || $jumpship_id == 0 )
            redirect($this->config->item('base_url'), 'refresh');
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
            redirect($this->config->item('base_url'), 'refresh');
        
        // Make sure the jumpship and leader exist
        $this->load->model('leadermodel');
        $this->load->model('jumpshipmodel');
        $page['jumpship'] = $this->jumpshipmodel->get_by_id($jumpship_id);
        if ( count($page['jumpship']) != 1 ) 
        {
            $this->page['error'] = 'ERROR! Problem finding jumpship to load leader into.';
            $this->load->view('templatexml', $page);
            return;
        }    
        
        $page['leader'] = $this->leadermodel->get_by_id($leader_id);
        if ( count($page['leader']) != 1 ) 
        {
            $page['error'] = 'Error! Cannot load leader in a jumpship there was a problem finding.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Player is owned by user associated with this request
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['jumpship']->owner_id);
        if ($page['player']->user_id != $this->ion_auth->get_user()->id)
        {
            $page['error'] = 'Error! Cannot load another player\'s leader.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Jumpship and combat unit are owned by the same player
        if ($page['jumpship']->owner_id != $page['leader']->controlling_house_id)
        {
            $page['error'] = 'Error! Cannot load leader into a jumpship controlled by a different player.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        if ($page['jumpship']->owner_id != $page['player']->player_id)
        {
            $page['error'] = 'Error! Cannot load leader into a different player\'s jumpship.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Are retreats allowed?
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        $this->load->model('territorymodel');
        $location = $this->territorymodel->get_by_id($page['jumpship']->location_id);
        if ($page['game']->combat_rnd > 0 && $location->is_contested && $page['game']->phase == 'Combat')
            $retreat = true;
        else
            $retreat = false;
        
        // Must be players turn, unless retreats are allowed
        if ($page['game']->player_id_playing != $page['player']->player_id && !$retreat)
        {
            $page['error'] = 'Error! Cannot load leader if it\'s not your turn unless you are retreating.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        // Must be the movement phase, unless retreats are allowed
        if ($page['game']->phase != 'Movement' && !$retreat)
        {
            $page['error'] = 'Error! Cannot load leader if it is not the movement phase unless you are retreating.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        // Jumpship can't be under construction
        if ( $page['jumpship']->being_built )
        {
            $page['error'] = 'Error! Cannot load a leader into a jumpship being built.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        // Cant have been loaded earlier this turn in a different jumpship, unless retreats are allowed
        if ( $page['leader']->was_loaded!=0 && $page['leader']->was_loaded!=$jumpship_id && !$retreat)
        {
            $page['error'] = 'Error! Cannot load leader into more than one jumpship in a turn unless retreating.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        // cant have been just bribed
        if ($page['leader']->just_bribed != 0 || $page['leader']->allegiance_to_house_id == 0)
        {
            $page['error'] = 'Error! Cannot load leader who was just bribed.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must own and control, cant be captured or bribed
        if ( $page['leader']->allegiance_to_house_id != $page['leader']->controlling_house_id )
        {
            $this->page['error'] = 'Error! Cannot load leader that does not have allegiance to you.';
            $this->view($jumpship_id);
            return;
        }        

        // All Aboard!!!
        $leaderupdate = new stdClass();
        $leaderupdate->loaded_in_id = $jumpship_id;
        $leaderupdate->leader_id = $page['leader']->leader_id;
        $this->leadermodel->update($leader_id, $leaderupdate);
        
        // Load the jumpship view
        $this->page['notice'] = 'Loaded '.$page['leader']->name.'.';
        $this->view($jumpship_id);
    }
    
    function drop_leader($unit_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $unit_id == 0 )
        {
            $this->page['error'] = 'ERROR! Cannot drop leader without providing an ID.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        // Make sure the user is signed in
        if ( !isset($page['user']->id) )
            redirect('auth/login', 'refresh');
        
        // Make sure the unit exists
        $this->load->model('leadermodel');
        $page['leader'] = $this->leadermodel->get_by_id($unit_id);
        if ( count($page['leader']) != 1 )
        {
            $this->page['error'] = 'ERROR! Problem finding leader to drop.';
            $this->load->view('templatexml', $page);
            return;
        }    
        
        // The unit must belong to a player associated with the user
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['leader']->controlling_house_id);
        if ( $page['player']->user_id != $this->ion_auth->get_user()->id )
        {
            $this->page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }    
        
        // Drop 'em
        $jumpship_id = $page['leader']->loaded_in_id;
        $leaderupdate = new stdClass();
        $leaderupdate->loaded_in_id = null;
        $leaderupdate->leader_id = $page['leader']->leader_id;
        $this->leadermodel->update($unit_id, $leaderupdate);
        
        // Load the jumpship view
        $this->view($jumpship_id);     
    }    
    
    
    /**
     * Destroy a friendly jumpship so it can't be used against you if captured!
     * @param type $jumpship_id
     * @return type 
     */
    function scuttle($jumpship_id=0)
    {
        $page = $this->page;
        
        // Valid input
        if ($jumpship_id==0)
        {
            $page['error'] = 'Error! Cannot scuttle jumpship without ID.';
            $this->load->view('templatexml', $error);
            return;
        }        
        
        // Must own the jumpship in question
        $this->load->model('jumpshipmodel');
        $jumpship = $this->jumpshipmodel->get_by_jumpshipid($jumpship_id);
        
        $this->load->model('playermodel');
        $owner = $this->playermodel->get_by_id($jumpship->owner_id);
        
        if ($owner->user_id != $page['user']->id)
        {
            $this->session->set_flashdata('error', 'Error! Cannot scuttle a jumpship you do not own.');
            redirect('sw/jumpship/'.$jumpship_id, 'refresh');
        }
        
        // require friendly combat forces in the region
        // add to view to remove SCUTTLE link if there aren't any
        $this->load->model('combatunitmodel');
        if(!(count($this->combatunitmodel->get_by_location_player($jumpship->location_id,$jumpship->owner_id)) > 0))
        {
            $page['error'] = 'Error! Cannot scuttle a jumpship without friendly combat units present.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the player who owns the Jumpship
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($jumpship->owner_id);
        
        // May not scuttle if you are eliminated
        if ($player->turn_order == 0)
        {
            $page['error'] = 'Error! Cannot scuttle a jumpship when you are eliminated!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($player->game_id);
        
        // phase must not be combat after barrage 1
        // add to view to remove SCUTTLE link if in combat
        if($game->phase =="Combat" && $game->combat_rnd > 0)
        {
            $page['error'] = 'Error! Cannot scuttle a jumpship while your combat units are busy in combat.';
            $this->load->view('templatexml', $page);
            return;
        }        
        
        // Confirm scuttle
        if ($this->session->flashdata('confirm') != 'YES')
        {
            $this->page['warning'] = 'You are about to destroy your jumpship ('.$jumpship->capacity.')'.($jumpship->jumpship_name==NULL ? '' : ', '.$jumpship->jumpship_name.',').' in '.$jumpship->name.'!  Are you sure?';
            $this->session->set_flashdata('confirm', 'YES');
            $this->view($jumpship_id);
            return;
        }
        
        if ($this->jumpshipmodel->deletejumpship($jumpship_id))
        {
            $page['notice'] = 'Jumpship has been destroyed!';
            game_message($player->game_id, $player->faction.' has scuttled a jumpship('.$jumpship->capacity.')'.($jumpship->jumpship_name==NULL ? '' :', '.$jumpship->jumpship_name.',').' in '.$jumpship->name.'.');
            update_territory($jumpship->location_id);
        }
        else
        {
            $page['error'] = 'Error! Cannot scuttle a jumpship when destruction fails.';
        }
        
        $this->load->view('templatexml', $page);
    }  // end scuttle
    
    /**
     * Undo the last movement
     */
    function undo_movement($game_id=0)
    {
	$page = $this->page;

	// Validate input
	if ($game_id == 0)
	{
            $page['error'] = 'No such game.';
            $this->load->view('templatexml', $page);
            return;
	}

        // Must be logged in to continue
        if (!isset($page['user']))
        {
            $page['error'] = 'Must be logged in.';
            $this->load->view('templatexml', $page);
            return;
        }
        
	// Fetch and validate the game
	$this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
	if (!isset($game->game_id))
	{
            $page['error'] = 'No such game.';
            $this->load->view('templatexml', $page);
            return;
	}
	
	// Must be playing in the game
	$this->load->model('playermodel');
	$players = $this->playermodel->get_by_game($game_id);
	foreach ($players as $p)
	{
            if ($p->user_id == $page['user']->id)
            {
                $player = $p;
            }
	}
	if (!isset($player->player_id) || $player->player_id != $game->player_id_playing)
	{
            $page['error'] = 'No such player.';
            $this->load->view('templatexml', $page);
            return;
	}
	
	// Must be the movement phase
	if ($game->phase != 'Movement')
	{
            $page['error'] = 'Not the right phase.';
            $this->load->view('templatexml', $page);
            return;
	}
	
	// Fetch the current movement order
	$this->load->model('movementlogmodel');
	$order = $this->movementlogmodel->get_movement_order($game_id);
	if ($order <= 0)
	{
            $page['error'] = 'No movement to undo.';
            $this->load->view('templatexml', $page);
            return;
	}

	// Fetch all movement logs for this order
	$logs = $this->movementlogmodel->get_logs_by_order($game_id, $order);
	
	// Make sure the jumpship still exists, if scuttled then this will break the undo option...
        $this->load->model('jumpshipmodel');
	$jumpship = $this->movementlogmodel->get_jumpship_log_by_order($game_id, $order);
        $jumpship = $this->jumpshipmodel->get_by_id($jumpship->object_id);
        if (!isset($jumpship->jumpship_id))
        {
            $page['error'] = 'Jumpship has been scuttled or destroyed, cannot undo movement past this point.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Also check to see if units should be unloaded
        $unload = false;
        if ($jumpship->moves_this_turn == 1)
        {
            $unload = true;
        }

	// Away we go...
	$this->db->trans_start();
	$this->load->model('combatunitmodel');
	$this->load->model('leadermodel');
	foreach($logs as $log)
	{
            // Reverse movement and delete the log
            $new_obj = new stdClass();
            if ($log->object_type == 'combatunit')
            {
                $new_obj->combatunit_id 	= $log->object_id;
                $new_obj->location_id 		= $log->prev_location_id;
                if($unload)
                {
                    $new_obj->was_loaded        = 0;
                }
                $this->combatunitmodel->update($new_obj->combatunit_id, $new_obj);
            }
            else if ($log->object_type == 'leader')
            {
                $new_obj->leader_id 		= $log->object_id;
                $new_obj->location_id 		= $log->prev_location_id;
                if($unload)
                {
                    $new_obj->was_loaded        = 0;
                }
                $this->leadermodel->update($new_obj->leader_id, $new_obj);
            } 
            else if ($log->object_type == 'jumpship')
            {
                $old_location_id                = $jumpship->location_id;
                $new_location_id                = $log->prev_location_id;
                $new_obj->jumpship_id 		= $log->object_id;
                $new_obj->location_id 		= $log->prev_location_id;
                $new_obj->moves_this_turn	= $jumpship->moves_this_turn - 1;
                $this->jumpshipmodel->update($new_obj->jumpship_id, $new_obj);
            }
            
            // Delete the log
            $this->movementlogmodel->delete($log->log_id);
            
	}
	update_territory($old_location_id);
        update_territory($new_location_id);
        
	// All done...
	game_message($game_id, $player->faction.' has undone his last movement, #'.$order.'.');
        
        $page['notice'] = 'Last Movement, #'.$order.' has been reversed.';
        $this->db->trans_complete();
        
        // Load the jumpship view
        $this->load->view('templatexml', $page);
        
    }  // end undo_movement
    
}  // end jumpship controller