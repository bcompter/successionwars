<?php

/**
 * SW is the main game engine and handles most requests that modify the game state
 * like the game phases
 */

class Sw extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }   
    
    /**
     * Fetch all of the locations in this game
     * @param type $game_id The game being played
     */
    function locations($game_id=0)
    {
        // Validate input from user
        $page = $this->check( $game_id );
        
        // Fetch locations in the game...
        $this->load->model('territorymodel');
        $page['locations'] = $this->territorymodel->get_by_game($game_id);
        $this->load->view('territories', $page);
    }
    
    /**
     * Fetch a single location
     * @param type $game_id The location id
     */
    function location($territory_id=0)
    {
    	$this->benchmark->mark('code_start');
    	
        $page = $this->page;

        // Make sure an id is provided
        if ( $territory_id == 0 )
        {
            $page['error'] = 'No such territory!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the territory
        $this->load->model('territorymodel');
        
        $this->db->select('territories.*, players.faction, players.color, players.text_color, map.*');
        $this->db->join('players','players.player_id=territories.player_id', 'left');  
        $temp['territory'] = $this->territorymodel->get_by_id($territory_id);
        //log_message('error', 'fetch 000 '.$this->db->last_query());
        $game_id = $temp['territory']->game_id;
        
        // Make sure the territory exists
        if ( !isset($temp['territory']->game_id) ) 
        {
            $page['error'] = 'No such territory!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'No such game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the player
        
        $interdicted;   // tracking for house interdicted
        $houseinterdict;
        $has_units;
        
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        //log_message('error', 'fetch 00 '.$this->db->last_query());
        $page['is_playing'] = false;

        foreach( $page['players'] as $player )
        {
            if ( isset($page['user']->id) && $player->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
            if ($player->house_interdict > 0)
            {
                $houseinterdict[] = $player;
            }
        }
        
        // Fetch the territory
        $page['territory'] = $temp['territory'];
        
        // Fetch all units in the territory
        $this->load->model('combatunitmodel');
        $page['units'] = $this->combatunitmodel->get_by_location($territory_id);
        //log_message('error', 'fetch 0 '.$this->db->last_query());
        
        foreach($page['units'] as $u)
        {
            if (!isset($has_units[$u->owner_id]))
            {
                $has_units[$u->owner_id] = true;
            }
        }
        
        // Fetch all jumpships in the territory
        $this->load->model('jumpshipmodel');
        $page['jumpships'] = $this->jumpshipmodel->get_by_territory($territory_id);
        //log_message('error', 'fetch 1 '.$this->db->last_query());
        
        // Fetch all leaders in the territory
        $this->load->model('leadermodel');
        $page['leaders'] = $this->leadermodel->get_by_territory($territory_id);
        //log_message('error', 'fetch 2 '.$this->db->last_query());
        
        // Fetch factory if available
        $this->load->model('factorymodel');
        $page['factory'] = $this->factorymodel->get_by_location($territory_id);
        //log_message('error', 'fetch 3 '.$this->db->last_query());
        
        // Fetch all combat bonuses in the territory
        $this->load->model('combatbonusmodel');
        $page['bonuses'] = $this->combatbonusmodel->get_by_territory($territory_id);
        //log_message('error', 'fetch 4 '.$this->db->last_query());
                
        // Handle house interdict
        foreach($page['bonuses'] as $b)
        {
            if ($b->value == -2)
            {
                $interdicted[$b->player_id] = true;
            }
        }
        if (isset($houseinterdict) && count($houseinterdict) > 0)
        {
            foreach($houseinterdict as $hi)
            {
                if ( !isset($interdicted[$hi->player_id]) )
                {
                    if ( isset($has_units[$hi->player_id]) )
                    {
                        $new = new stdClass();
                        $new->value = -2;
                        $new->faction = $hi->faction;
                        $page['bonuses'][] = $new;
                    }
                    else if ( $page['territory']->player_id == $hi->player_id )
                    {
                        $new = new stdClass();
                        $new->value = -2;
                        $new->faction = $hi->faction;
                        $page['bonuses'][] = $new;
                    }

                }
            }
        }
        $page['content'] = 'territory';
        $this->load->view('templatexml', $page);
        
        $this->benchmark->mark('code_end');
        
        //log_message('error', 'Benchmark for sw location '.$this->benchmark->elapsed_time('code_start', 'code_end'));
    }    
    
    /**
     * In the event of multi-faction combat, we need a way to assign targets
     * for all combat units
     * @param type $territory_id 
     */
    function assign_targets($territory_id=0, $combatunit_id = 0, $target_id = 0)
    {
        $page = $this->page;

        // Make sure an id is provided
        if($territory_id == 0)
        {
            $page['error'] = 'No such target!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($territory_id);

        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($territory->game_id);

        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
                $player = $p;
        }

        if (!isset($player->player_id))
        {
            $page['error'] = 'You are not playing in that game!';
            $this->load->view('templatexml', $page);
            return;
        }
        $this->load->model('gamemodel');
        $game  = $this->gamemodel->get_by_id($player->game_id);

        if ($game->phase != 'Combat')
        {
            $page['error'] = 'Wrong phase!';
            $this->load->view('templatexml', $page);
            return;
        }

        if ($combatunit_id == 0)
        {   
            // Display target view
            $page['content'] = 'assigntargets';
            $page['territory'] = $territory;
            $page['players'] = $players;
            $page['player'] = $player;
            
            $this->load->model('combatunitmodel');
            $page['units'] = $this->combatunitmodel->get_by_location($territory_id);
            
            $this->load->view('templatexml', $page);
            return;
        }
        else
        {
            if ($this->debug > 3) log_message('error', 'assign targets target '.$target_id.' for unit '.$combatunit_id);
            
            // Fetch target
            $target = $this->playermodel->get_by_id($target_id);
            
            // Fetch unit
            $this->load->model('combatunitmodel');
            $unit = $this->combatunitmodel->get_by_id($combatunit_id);
            
            // Must exist
            if (!isset($target->player_id))
            {
                $this->page['error'] = 'No such player!';
                $this->assign_targets($territory_id);
                return;
            }
            if (!isset($unit->combatunit_id))
            {
                $this->page['error'] = 'No such target!';
                $this->assign_targets($territory_id);
                return;
            }
            
            // Must be in the same game
            if($unit->location_id != $territory_id)
            {
                $this->page['error'] = 'You are not playing in that game!';
                $this->assign_targets($territory_id);
                return;
            }
                                    
            // Must be a valid target
            $this->load->model('combatunitmodel');
            $units = $this->combatunitmodel->get_by_location($territory_id);
            $validtargets;
            foreach ($units as $unit)
            {
                if ($unit->owner_id != $player->player_id)
                    $validtargets[$unit->owner_id] = 1;
            }
            if (!isset($validtargets[$target_id]) && $target_id != 0 )
            {
                $this->page['error'] = 'Target is not valid!';
                $this->assign_targets($territory_id);
                return;
            }
            
            // Can't target yourself
            if($target_id == $player->player_id)
            {
                $this->page['error'] = 'Can\'t target yourself!';
                $this->assign_targets($territory_id);
                return;
            }
            
            // Away we go!
            $unitupdate = new stdClass();
            $unitupdate->combatunit_id = $combatunit_id;
            $unitupdate->target_id = $target_id;
            $this->combatunitmodel->update($combatunit_id, $unitupdate);
            
            $this->page['notice'] = 'Target assigned.';
            $this->assign_targets($territory_id);     
        }
        
    } // end assign_targets
    
    /**
     * View combat units from a single player
     * @param type $game_id 
     */
    function combat_units($game_id='0')
    {
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect('auth/login', 'refresh');
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
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the players combat units
        $this->load->model('combatunitmodel');
        $page['combatunits'] = $this->combatunitmodel->get_by_player($page['player']->player_id);
        
        
        // Away we go
        $this->load->view('military', $page);
    }
     
    function update($game_id=0)
    {        
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $this->page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }  

        // Make sure $time is a valid sql timestamp
        //...

        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( !isset($page['game']->game_id) )
        {
            $this->page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }  

        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( isset($page['user']->id) && $player->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
            if ( $player->player_id == $page['game']->player_id_playing )
            {
                $page['current_player'] = $player;
            }
        }
        
        // Send info concerning the undo movement link
        if (isset($page['player']) && isset($page['current_player']->player_id)
                && $page['player']->player_id == $page['current_player']->player_id 
                && $page['game']->phase == 'Movement')
        {
            $page['enableundo'] = true;
        }
        else
        {
            $page['enableundo'] = false;
        }

        if (!isset($page['current_player']))
            $page['current_player'] = 'None';
        
        $page['chattime'] = $this->input->post('chattime');
        $page['msgtime'] = $this->input->post('msgtime');
        $page['maptime'] = $this->input->post('maptime');
        
        //log_message('error', 'Chat time is '.$this->input->post('chattime'));
        
        $this->load->model('chatmodel');
        $page['chats'] = $this->chatmodel->get_new($game_id, $page['chattime']);
        //log_message('error', 'query 0 '.$this->db->last_query());
        $this->load->model('gamemsgmodel');
        $page['gamemsgs'] = $this->gamemsgmodel->get_new($game_id, $page['msgtime']);
        //log_message('error', 'query 1 '.$this->db->last_query());
        $this->load->model('mapmodel');
        $page['maps'] = $this->mapmodel->get_by_game_time($game_id, $page['maptime']);
        //log_message('error', 'query 3 '.$this->db->last_query());

        // Are we waiting on mercenaries?
        $this->load->model('combatunitmodel');
        $mercs = $this->combatunitmodel->mercs_for_hire($game_id);

        if (count($mercs) > 0)
        {
            $page['mercs'] = true;

            $page['merc_bids'] = $this->db->query('select players.faction, mercoffers.offer from players
                left join mercoffers on mercoffers.player_id=players.player_id
                where players.game_id='.$game_id.'
                group by players.player_id
                ')->result();            
        }
        else
        {
            $page['mercs'] = false;
        }

        // Mercenary placement
        $page['mercs_to_place'] = $this->combatunitmodel->mercs_to_place($game_id);
        //log_message('error', 'query 4 '.$this->db->last_query());
        
        // Are we waiting on periphery?
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($game_id);
        if (count($bids) > 0)
        {
            $page['periphery'] = true;
            $page['bids'] = $bids;
        }
        else
            $page['periphery'] = false;

        $this->db->where('last_update >', $page['maptime']);
        $page['strength'] = $this->mapmodel->get_sum_strength($game_id);
        //log_message('error', 'query 5 '.$this->db->last_query());

        $this->db->where('last_update >', $page['maptime']);
        $page['capacity'] = $this->mapmodel->get_sum_capacity($game_id);
        //log_message('error', 'query 6 '.$this->db->last_query());

        // Timer function
        // Subtract the current time from the last action time
        $timer = new DateTime($page['game']->last_action, new DateTimeZone('mst'));
        $now = new DateTime(null, new DateTimeZone('mst'));
        $delta = date_diff($timer, $now);

        if ($delta->days == 0 && $delta->h == 0)
        {
            // Show the minutes
            $page['timer'] = $delta->format('%i Minutes');
        }
        else if ($delta->days == 0)
        {
            // Show the hours and minutes
            if ($delta->h == 1)
                $page['timer'] = $delta->format('%h Hour, %i Minutes');
            else
                $page['timer'] = $delta->format('%h Hours, %i Minutes');
        }
        else
        {
            // Show everything
            $page['timer'] = $delta->format('%d Days, %h Hours, %i Minutes');
        }
        
        // Dont disturb the flashdata
        $this->session->keep_flashdata('confirm');

        $this->load->view('update', $page);
    }
    
    /**
     *  Done
     *      Movement
     *      Production
     *      or Combat
     * 
     * @param type $game_id The game
     * @param type $player_id Optional id of the player to be forced to done, game owner only
     * @return type 
     */
    function done($game_id=0, $player_id=0)
    {
        if ($this->debug>2) log_message('error', 'done($game_id='.$game_id.', $player_id='.$player_id.')');
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $this->page['error'] = 'No Game id provided!';
            $this->load->view('templatexml', $page);
            return;
        }  
        
        // Make sure the user is signed in
        if ( !isset($page['user']->id) )
            redirect('auth/login', 'refresh');
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'No such game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($game_id);
        if(isset($bids) && count($bids) > 0)
        {
            $page['error'] = 'Can\'t click DONE while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }

        // confirm!
        if ($this->session->flashdata('confirm') != 'YES')
        {
            if ($page['game']->phase == "Player Setup")
                $page['warning'] = 'Are you sure you are done with '.$page['game']->phase.'?<br>Check that your borders are garrisoned!';
            else if ($page['game']->phase == "Combat" && $page['game']->combat_rnd > 0)
                $page['warning'] = 'Are you sure you are done with '.$page['game']->phase.' and retreating?';
            else if ($page['game']->phase == "Draw")
                $page['warning'] = 'Are you sure you are ready to draw cards?';
            else
                $page['warning'] = 'Are you sure you are done with '.$page['game']->phase.'?';            
            
            $this->session->set_flashdata('confirm', 'YES');
            
            $this->load->view('templatexml', $page);
            return;
        }

        // Make sure the user is playing in the game
        // Under special circumstances, the game owner may force a player done
        // That is what $player_id is for...  If NOT 0 then the current user MUST own this game
        if ($player_id != 0)
        {
            // Make sure the user owns this game or is an admin
            if (( $page['game']->creator_id != $page['user']->id ) || (!$this->ion_auth->is_admin()))
            {
                $page['error'] = 'ERROR!  You must own this game to force another player done!';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Enable big selects...
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        $page['all_done'] = true;
        foreach( $page['players'] as $player )
        {
            // Check to see if everyone is done
            if ($player->combat_done == false && $player->user_id != $page['user']->id)
            {
                if ($player->player_id != 0)    // skip comstar...
                    $page['all_done'] = false;
            }
            // Check for is_playing and the current player
            if ( $player->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
        }
        
        // Handle game owner call to done
        if ($player_id != 0)
        {
            $page['is_playing'] = true;
            $page['player'] = $this->playermodel->get_by_id($player_id);
        }
        
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'You are not playing in this game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        
        // If the game is on hold, the card being played must match this card
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['game']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be under the hand size limit
        $this->load->model('cardmodel');
        $cards = $this->cardmodel->get_by_player($page['player']->player_id);
        if ( count($cards) > $page['game']->hand_size )
        {
            $page['error'] = 'You have too many cards in your hand and must discard down to '.$game->hand_size.' cards.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Handle turn type
        if ($page['game']->phase == 'Draw')
        {
            log_message('error', 'Draw phase activating! '.$page['player']->faction);
            $this->draw($page);
        }
        else if ($page['game']->phase == 'Production')
            $this->production($page);
        else if ($page['game']->phase == 'Movement')
            $this->movement($page);
        else if ($page['game']->phase == 'Combat')
            $this->combat($page);
        else if ($page['game']->phase == 'Player Setup')
            $this->player_setup($page);
        else
        {
            if ($this->debug > 3) log_message('error', 'Done on wrong phase for game Id '.$page['game']->game_id);
            $page['error'] = 'Awww snap!  Something bad happened!';
            $this->load->view('templatexml', $page);
        }
    }
    
    function player_setup($page)
    {
        // Player playing must match player id
        if ($page['player']->player_id != $page['game']->player_id_playing)
        {
            $page['error'] = 'It\'s not your turn.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must have all units, jumpships, and leaders placed
        $units = $this->db->query('select * from combatunits where
            owner_id = '.$page['player']->player_id.' and
            location_id is null and
            strength > 0')->result();
        if (count($units) > 0)
        {
            $page['error'] = 'You have unplaced combat units.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $jumpships = $this->db->query('select * from jumpships where
            owner_id = '.$page['player']->player_id.' and
            location_id is null')->result();
        if (count($jumpships) > 0)
        {
            $page['error'] = 'You have unplaced jumpships.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $leaders = $this->db->query('select * from leaders where
            controlling_house_id = '.$page['player']->player_id.' and
            location_id is null')->result();
        if (count($leaders) > 0)
        {
            $page['error'] = 'You have unplaced leaders.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go...
        $page['notice'] = 'Done with setup!';
        game_message($page['game']->game_id, $page['player']->faction.' is done with setup.');
        
        // Change player id playing to next setup order...
        $next_id = $page['player']->setup_order + 1;
        
        if ($next_id > count($page['players']))
        {
            // Start the game... maybe?
            if ($this->debug > 3) log_message('error', 'ready for game start');
            
            $page['game']->player_id_playing = null;
            $page['game']->last_action = null;
            $this->gamemodel->update($page['game']->game_id, $page['game']);
            
            $this->load->view('templatexml', $page);
            return;
        }
        else
        {
            // Advance to next player
            foreach($page['players'] as $p)
            {
                if ($p->setup_order == $next_id)
                {
                    $page['game']->player_id_playing = $p->player_id;
                    $page['game']->last_action = null;
                    $this->gamemodel->update($page['game']->game_id, $page['game']);
                    $this->load->view('templatexml', $page);
                    
                    // Email next player that action is required
                    
                    return;
                }
            }
        }
    }
    
    function kill($combatunit_id=0)
    {
        $page = $this->page;
          
        // Make sure an id is provided
        if ( $combatunit_id == 0 )
        {
            $page['error'] = 'No such unit!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !isset($page['user']->id) )
        {
            $page['error'] = 'Error';
            $this->load->view('templatexml', $page);
            return;
        }

        // Make sure that the owner of the combat unit belongs to the logged in user
        $this->load->model('combatunitmodel');
        $combatunit = $this->combatunitmodel->get_by_id($combatunit_id);
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($combatunit->owner_id);
        
        log_message('error', 'KILL CHECK ON GAME '.$combatunit->game_id);
        log_message('error', 'KILL CHECK: combatunit_id is '.$combatunit_id);
        log_message('error', 'KILL CHECK: player_id is '.$player->player_id);
        log_message('error', 'KILL CHECK: owner_id is '.$combatunit->owner_id);
        log_message('error', 'KILL CHECK: user_id is '.$page['user']->id);
        log_message('error', 'KILL CHECK: user_id is '.$player->user_id);
          
        if ( $player->user_id != $page['user']->id )
        {
            $page['error'] = 'You are not playing in this game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure it is in fact the combat phase
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($player->game_id);
        if ($game->phase != 'Combat')
        {
            $page['error'] = 'Wrong phase!';
            $this->load->view('templatexml', $page);
            return;
        }                
                
        // Location of unit must also be contested
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($combatunit->location_id);
        if ( !$territory->is_contested )
        {
            $page['error'] = 'Territory is not contested!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must have more casualties to assign
        $this->db->trans_start();
        $this->load->model('combatlogmodel');
        $combatlog = $this->combatlogmodel->get_by_player_territory($player->player_id, $combatunit->location_id);
        if ($combatlog->casualties_owed < 1)
        {
            $this->db->trans_complete();
            $page['error'] = 'Error, no casualties to assign!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Die!
        $combatunitupdate = new stdClass();
        $combatunitupdate->combatunit_id = $combatunit->combatunit_id;
        $combatunitupdate->die = true;
        
        $combatunitupdate->combine_with = null;
        $this->combatunitmodel->update($combatunit_id, $combatunitupdate);

        // Update combatlog
        // 
        // If the player has no other combat units in the territory,
        // casualties owed goes to zero
        $combatunits = $this->combatunitmodel->get_by_location_player($territory->territory_id, $player->player_id);
        if ( count($combatunits) == 0 )
        {
            $this->db->query('update combatlog set casualties_owed=0 where combatlog_id='.$combatlog->combatlog_id);
        }
        else
        {
            $this->db->query('update combatlog set casualties_owed=casualties_owed-1 where combatlog_id='.$combatlog->combatlog_id);
        }
        
        // As a precaution, run a query to zero out any combat logs for this game that get reduced to 
        // below zero
        $this->db->query('update combatlog set casualties_owed=0 where game_id='.$game->game_id.' and casualties_owed < 0');
        $this->db->trans_complete();
        
        // Reload the viewcombat view
        $this->viewcombat($combatunit->location_id);
    }
    
    function viewcombats($game_id=0)
    {
        // Make sure an id is provided
        if ($game_id == 0)
        {
            $page['error'] = 'ERROR! Bad Game ID provided to viewcombats()';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
            redirect('auth/login', 'refresh');
        
        // Enable big selects
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        $page['all_done'] = true;
        foreach( $page['players'] as $player )
        {   
            // Check for is_playing and the current player
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
        }
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'Error! Cannot view combats for a game you are not playing in.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be combat phase
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ($page['game']->phase != 'Combat')
        {
            $page['error'] = 'Cannot view combats as this is not the Combat Phase.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        // Fetch all contested territories in the game
        $this->load->model('territorymodel');
        //$page['territories'] = $this->territorymodel->get_contested($page['game']->game_id);
        $page['territories'] = $this->territorymodel->get_contested_involved($page['game']->game_id, $page['player']->player_id);
        
        // Load the view and away we go
        $this->load->view('combats',$page);
        
    }
    
    function viewcombat($territory_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $territory_id == 0 )
        {
            $page['error'] = 'Error! Cannot view combat without a supplied ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
            redirect('auth/login', 'refresh');
        
        // Territory must exist
        $this->load->model('territorymodel');
        $page['territory'] = $this->territorymodel->get_by_id($territory_id);
        if (!isset($page['territory']->territory_id) )
        {
            $page['error'] = 'ERROR! Cannot view combat of a region that does not exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Territory must be contested
        if (!$page['territory']->is_contested)
        {
            $page['error'] = 'Error! Cannot view combat of a region that is not contested.';
            $this->load->view('templatexml', $page);
            return;
        }   
        
        $interdicted;   // tracking for house interdicted
        $houseinterdict;
        $has_units;
        
        // Enable big selects
        $this->db->query('SET SQL_BIG_SELECTS = 1');
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($page['territory']->game_id);
        $page['is_playing'] = false;
        $page['all_done'] = true;
        foreach( $page['players'] as $player )
        {   
            // Check for is_playing and the current player
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
                $page['viewing_player'] = $player;
            }
            
            if ($player->house_interdict > 0)
            {
                $houseinterdict[] = $player;
            }
        }
        if ( !$page['is_playing'] )
        {
            $page['error'] = "Error! Cannot view combat of a game you are not in.";
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be combat phase
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['territory']->game_id);
        if ($page['game']->phase != 'Combat')
        {
            $page['error'] = 'Cannot view combat as this is not the Combat phase.';
            $this->load->view('templatexml', $page);
            return;
        }  

        // if (auto_kill_all == 1) then kill units now
        if (isset($page['viewing_player'])  &&  $page['user']->auto_kill_all == 1)
        {
            $this->load->model('combatunitmodel');
            $viewing_players_units = $this->combatunitmodel->get_by_location_player($territory_id, $page['viewing_player']->player_id);
            $this->load->model('combatlogmodel');
            $viewing_players_combat_log = $this->combatlogmodel->get_by_player_territory($page['viewing_player']->player_id, $territory_id);
            if (isset($viewing_players_combat_log) 
                    && isset($viewing_players_units) 
                    && isset($viewing_players_combat_log->casualties_owed) 
                    && $viewing_players_combat_log->casualties_owed > 0)
            {
                if ($viewing_players_combat_log->casualties_owed >= count($viewing_players_units))
                {
                    $page['warning'] = 'All your units automatically marked as KIA.';
                    $combo_removed = [];
                    foreach ($viewing_players_units as $unit)
                    {
                        $update_unit = new stdClass();
                        // Remove leader military combos
                        if ($unit->combined_by != NULL  && !isset($combo_removed[$unit->combatunit_id]))
                        {
                            $update_unit->combined_by = NULL;
                            
                            $this->load->model('leadermodel');
                            $combining_leader = $this->leadermodel->get_by_id($unit->combined_by);
                            $combining_leader_update = new stdClass();
                            $combining_leader_update->leader_id = $combining_leader->leader_id;
                            $combining_leader_update->military_used = $combining_leader->military_used - 1;
                            if ($combining_leader_update->military_used < 0)
                            {
                                $combining_leader_update->military_used = 0;
                                log_message('error', '**** ERROR **** $leader->military_used reduced below 0 by auto_kill_all in function viewcombat($territory_id=0); $combining_leader->leader_id: '.$combining_leader->leader_id);
                            }
                            $this->leadermodel->update($combining_leader->leader_id, $combining_leader_update);
                            
                            // Don't perform this when looking at the combined unit
                            $combo_removed[$unit->combine_with] = TRUE;
                        }

                        // Remove combo info from the other unit
                        if ($unit->combine_with != NULL)
                        {
                            $update_combined_unit = new stdClass();
                            $update_combined_unit->combatunit_id = $unit->combine_with;
                            $update_combined_unit->combine_with = NULL;
                            $update_combined_unit->combined_by = NULL;
                            $this->combatunitmodel->update($update_combined_unit->combatunit_id, $update_combined_unit);
                        }
                        
                        /* THIS SHOULD BE DONE WHEN KILLED, NOT WHEN MARKED AS KIA (SO THE PLAYER CAN SEE THERE IS A LEADER BONUS APPLIED TO A KIA UNIT)
                        // However, DO do this, if KIAs are applied during combat
                        // Remove leader bonus
                        $this->load->model('combatbonusmodel');
                        $remove_bonuses = $this->combatbonusmodel->get_by_unit($unit_id);
                        if (isset($remove_bonuses))
                            $this->combatbonusmodel->delete($remove_bonuses->combatbonus_id);*/
                        
                        // Mark unit as KIA
                        $update_unit->combatunit_id = $unit->combatunit_id;
                        $update_unit->die = TRUE;
                        $update_unit->combine_with = NULL;     // Break any combo
                        $this->combatunitmodel->update($update_unit->combatunit_id, $update_unit);
                    }
                    // Set casualties owed to 0
                    $this->db->query('update combatlog set casualties_owed=0 where combatlog_id='.$viewing_players_combat_log->combatlog_id);
                    
                }  // end if ($viewing_players_combat_log->casualties_owed >= count($viewing_players_units))
            }  // end 
        }  // end if (isset($page['viewing_player'])  &&  $page['user']->auto_kill_all == 1)
        
        // Fetch all units in the territory
        $this->load->model('combatunitmodel');
        $this->db->order_by('owner_id');
        $page['units'] = $this->combatunitmodel->get_by_location($territory_id);
        foreach($page['units'] as $u)
        {
            if (!isset($has_units[$u->owner_id]))
            {
                $has_units[$u->owner_id] = true;
            }
        }
        
        // Fetch all the jumpships
        $this->load->model('jumpshipmodel');
        $page['jumpships'] = $this->jumpshipmodel->get_by_territory($territory_id);
        
        // Fetch all leaders in the territory
        $this->load->model('leadermodel');
        $page['leaders'] = $this->leadermodel->get_by_territory($territory_id);
        
        // Fetch the combat log for this battle and player
        $this->load->model('combatlogmodel');
        $page['combatlog'] = $this->combatlogmodel->get_by_player_territory($page['player']->player_id,$territory_id);
        $page['combatlogs'] = $this->combatlogmodel->get_by_territory($territory_id);
        
        // Fetch all combat bonuses in the territory
        $this->load->model('combatbonusmodel');
        $page['bonuses'] = $this->combatbonusmodel->get_by_territory($territory_id);
        
        // Fetch the factory if any
        $this->load->model('factorymodel');
        $factory = $this->factorymodel->get_by_location($territory_id);
        if (isset($factory->factory_id))
            $page['has_factory'] = true;
        else
            $page['has_factory'] = false;
        
        // Handle house interdict
        foreach($page['bonuses'] as $b)
        {
            if ($b->value == -2)
            {
                $interdicted[$b->player_id] = true;
            }
        }
        if (isset($houseinterdict) && count($houseinterdict) > 0)
        {
            foreach($houseinterdict as $hi)
            {
                if ( !isset($interdicted[$hi->player_id]) && isset($has_units[$hi->player_id]) )
                {
                    // add bonus
                    $new = new stdClass();
                    $new->value = -2;
                    $new->faction = $hi->faction;
                    $new->player_id = $hi->player_id;
                    $page['bonuses'][] = $new;
                }
            }
        }
        
        // A special case for comstar
        if ($page['territory']->player_id == 0)
        {
            $page['comstar'] = $this->playermodel->get_by_id(0);
        }
        
        // Load the view and away we go
        $page['content'] = 'combat_1';
        $this->load->view('templatexml', $page);
    }
    
    private function production($page)  // Function runs when Production is DONE
    {
        // Must be the players turn
        if ($page['player']->player_id != $page['game']->player_id_playing)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        } 

        // Advance phase to MOVEMENT
        $page['game']->phase = "Movement";
        $page['game']->last_action = null;
        $this->gamemodel->update($page['game']->game_id, $page['game']);
        
        // Zero out moves this turn for all jumpships from this player
        // We do this twice, at the start and at the end of movement
        // If you change this code, be sure to check for the other section
        $this->load->model('jumpshipmodel');
        $this->db->where('moves_this_turn >','0');
        $jumpships = $this->jumpshipmodel->get_by_player($page['player']->player_id);
        foreach($jumpships as $jumpship)
        {
            if ( $jumpship->moves_this_turn != 0 )
            {
                $js = new stdClass();
                $js->jumpship_id = $jumpship->jumpship_id;
                $js->moves_this_turn = 0;
                $this->jumpshipmodel->update($js->jumpship_id, $js);
            }
        }
        
        $page['notice'] = 'Status set to Done, Advancing turn to Movement Phase.';
        $this->load->view('templatexml', $page);
        
    }
 
    private function movement($page)  // Function runs when Movement is DONE
    {        
        // Must be the players turn
        if ($page['player']->player_id != $page['game']->player_id_playing)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Zero out moves this turn for all jumpships from this player
        // We do this twice, at the start and at the end of movement
        // If you change this code, be sure to check for the other section
        $this->load->model('jumpshipmodel');
        $this->load->model('combatunitmodel');
        $this->db->where('moves_this_turn >','0');
        $jumpships = $this->jumpshipmodel->get_by_player($page['player']->player_id);
        foreach($jumpships as $jumpship)
        {
            if ( $jumpship->moves_this_turn != 0 )
            {
                $js = new stdClass();
                $js->jumpship_id = $jumpship->jumpship_id;
                $js->moves_this_turn = 0;
                $this->jumpshipmodel->update($js->jumpship_id, $js);
            }
        }
        
        // Delete all movement logs in the game
        $this->load->model('movementlogmodel');
        $this->movementlogmodel->delete_by_game($page['game']->game_id);
        
        // Reset all was_loaded tags for leaders and combatunits
        // This will 'unload' as leaders and combat units used this turn
        $this->db->query('UPDATE combatunits SET was_loaded=0, loaded_in_id=NULL WHERE game_id='.$page['game']->game_id);
        $this->db->query('UPDATE leaders SET was_loaded=0, loaded_in_id=NULL WHERE game_id='.$page['game']->game_id);

        // Check for leaders alone in regions not controlled by their owner
        // They are captured by the region's owner
        $this->load->model('leadermodel');
        $leaders_to_capture = $this->leadermodel->get_not_in_own_territory($page['game']->game_id);
        foreach ($leaders_to_capture as $leader_being_captured)
        {
            if ($leader_being_captured->just_bribed == 0) //Don't capture leaders that were just bribed until after a barrage
            if ($leader_being_captured->new_controlling_player_id != 0) // COMSTAR doesn't capture (but this might be a game option later)
            {
                $friendly_units=$this->combatunitmodel->get_by_location_player($leader_being_captured->location_id, $leader_being_captured->controlling_house_id);
                if (count($friendly_units) == 0)
                {
                    capture_leader($leader_being_captured->leader_id, $leader_being_captured->new_controlling_player_id);
                }
            }
        }

        // Check for jumpships alone in regions not controlled by their owner
        //if ($this->debug>2) log_message('error', 'Capturing unprotected jumpships');
        $this->load->model('jumpshipmodel');
        $jumpships_to_capture = $this->jumpshipmodel->get_not_in_own_territory($page['game']->game_id);
        foreach ($jumpships_to_capture as $jumpship_being_captured)
        {
            if ($jumpship_being_captured->new_owner_player_id != 0) // COMSTAR doesn't capture (but this might be a game option later)
            {
                $friendly_units = $this->combatunitmodel->get_by_location_player($jumpship_being_captured->location_id, $jumpship_being_captured->owner_id);
                if (count($friendly_units) == 0)
                {
                    $jumpshipupdate = new stdClass();
                    $jumpshipupdate->owner_id = $jumpship_being_captured->new_owner_player_id;
                    $this->jumpshipmodel->update($jumpship_being_captured->jumpship_id, $jumpshipupdate);
                    game_message($page['game']->game_id, 'Jumpship '.($jumpship_being_captured->name==""?'('.$jumpship_being_captured->capacity.')':$jumpship_being_captured->name).' was left unprotected in '.$jumpship_being_captured->territory_name.' and is captured.');
                }
            }
        }
        
        // Advance phase to COMBAT, if required...
        // If there are no combats, skip to placement
        $this->load->model('territorymodel');
        $page['contested'] = $this->territorymodel->get_contested($page['game']->game_id);
        if (count($page['contested']) > 0)
        {
            $this->load->model('combatunitmodel');
            
            $page['game']->phase = "Combat";
            $page['game']->last_action = null;
            $this->gamemodel->update($page['game']->game_id, $page['game']);
                       
            // Generate combat log for each contested area for each involved
            $combatlogs;
            log_message('error', 'There are '.count($page['contested']).' contested territories.');
            foreach($page['contested'] as $territory)
            {
                // Make sure there actually are oppossing units in this territory
                $unique = $this->combatunitmodel->get_by_territory($territory->territory_id);     
                
                log_message('error', 'Num Unique is '.count($unique));
                
                if (count($unique) == 0)
                {
                    // No combat units, skip...
                    log_message('error', 'SKIPPING combat log for '.$territory->name.', no combat units detected...');
                    $tupdate = new stdClass();
                    $tupdate->is_contested = 0;
                    $this->territorymodel->update($territory->territory_id, $tupdate);
                }
                else if (count($unique) == 1 && $unique[0]->owner_id != $territory->player_id)
                {
                    // Only generate combat logs if there is an attacker
                    log_message('error', 'Owner 1: '.$unique[0]->owner_id);
                    log_message('error', 'Owner 2: '.$territory->player_id);
                    log_message('error', 'Generating combat log for lone attacker for '.$territory->name);
                    generate_combat_logs($territory, $page['game']);
                }
                else if (count($unique) > 1)
                {
                    // Generate combat logs normally
                    log_message('error', 'Generating NORMAL combat log for '.$territory->name);
                    generate_combat_logs($territory, $page['game']);
                }
                else
                {
                    // Skipping log generation, 
                    log_message('error', 'SKIPPING combat log for '.$territory->name.', no attacking combat unit detected...');
                    $tupdate = new stdClass();
                    $tupdate->is_contested = 0;
                    $this->territorymodel->update($territory->territory_id, $tupdate);
                }
 
            } // end foreach of contested territories
            
            // Email all players in the game that action is required, exclude the acting player
            email_game($page['game']->game_id, 'Your action is required in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$page['game']->game_id.'">'.
                    $page['game']->title.
                    '</a>. Please review combat and click done.', $page['player']->player_id);
            
            $page['notice'] = 'Status set to Done. Advancing turn to Combat.';
            $this->load->view('templatexml', $page);
            
        }   
        else
        {
            // There are no contested areas, skip to placement
            $page['notice'] = 'No contested territories, advancing turn to Placement.';
            $this->placement($page);
        }
    }    
    
    private function combat($page)  // Function runs when Combat is DONE
    {
        if ($this->debug>1) log_message('error', 'Game: '.$page['game']->game_id.'; controllers/sw.php/combat($page) // COMBAT');
        $this->benchmark->mark('combat_start');
        
        // Must have all casualties assigned
        $this->load->model('combatlogmodel');
        $playerlogs = $this->combatlogmodel->get_by_player($page['player']->player_id);
        foreach($playerlogs as $playerlog )
        {
            if ($playerlog->casualties_owed != 0)
            {
                $page['error'] = 'You have unassigned casualties. Please check combats.';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // All targets must be valid
        $this->load->model('combatunitmodel');
        if ( $this->combatunitmodel->num_unassigned($page['player']->player_id) > 0 )
        {
            if ($this->debug > 3) log_message('error', $this->db->last_query());
            $page['error'] = 'You have unassigned targets. Please check combats.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Determine target validity
        $nulltargets = $this->db->query('select * from combatunits where
            strength > 0 and
            target_id = null and
            owner_id='.$page['player']->player_id)->result();

        $total_targets = $this->db->query('SELECT combatunits.name,  combatunits.target_id, enemies.owner_id as enemy
            FROM `combatunits`
            join territories on territories.territory_id=combatunits.location_id
            left join combatunits as enemies on combatunits.location_id=enemies.location_id
            where 
            combatunits.owner_id='.$page['player']->player_id.' and   
            is_contested = 1 and
            combatunits.target_id <> 0
            group by combatunits.combatunit_id')->result();

        $valid_targets = $this->db->query('SELECT combatunits.name,  combatunits.target_id, enemies.owner_id as enemy
            FROM `combatunits`
            join territories on territories.territory_id=combatunits.location_id
            left join combatunits as enemies on combatunits.location_id=enemies.location_id
            where 
            combatunits.owner_id='.$page['player']->player_id.' and   
            is_contested = 1 and
            combatunits.target_id = enemies.owner_id AND (
                combatunits.target_id !=0
                AND enemies.owner_id !=0
                )
            group by combatunits.combatunit_id')->result();
        
        if ($this->debug > 3) log_message('error', 'Valid targets '.count($valid_targets).' Total targets '.count($total_targets));
        
        if (count($nulltargets) > 0)
        {
            if ($this->debug > 3) log_message('error', 'Null target');
            $page['error'] = 'You have unassigned targets. Please check combats.';
            $this->load->view('templatexml', $page);
            return;
        }
        else if (count($valid_targets) != count($total_targets))
        {
            if ($this->debug > 3) log_message('error', 'Invalid target');
            $page['error'] = 'You have invalid targets. Please check combats.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check to see if this player is already done
        // Prevent double tapping this method in unussual cases
        if ($page['player']->combat_done)
        {
            $page['error'] = 'ERROR! Can\'t be done twice.';
            if ($this->debug>0) log_message('error', 'ERROR: '.$page['player']->player_id.' managed to hit done twice!');
            $this->load->view('templatexml', $page);
            return;       
        }
        
        // Set this player to combat_done
        if ($this->debug>2) log_message('error', 'Setting player '.$page['player']->player_id.' ('.$page['player']->faction.') to done ');
        $playerupdate = new stdClass();
        $playerupdate->combat_done = true;
        $playerupdate->player_id = $page['player']->player_id;
        $this->playermodel->update($page['player']->player_id, $playerupdate);
        
        // If all players in the game are done...  then process combat
        if ($page['all_done'])
        {
            if ($this->debug > 3) log_message('error', 'Game: '.$page['game']->game_id.'; All players done');

            // Detect and eliminate ghost units
            $ghosts = $this->db->query('SELECT * FROM combatunits WHERE game_id='.$page['game']->game_id.' AND being_built=0 AND strength=0 AND location_id IS NOT NULL')->result();
            foreach ($ghosts as $ghost)
            {
                log_message('error', 'Fixing Ghost unit '.$ghost->name.', '.$ghost->combatunit_id);
                $newghost = new stdClass();
                $newghost->location_id = NULL;
                $this->combatunitmodel->update($ghost->combatunit_id, $newghost);
            }
            
            // Kill off all casualties from the previous combat round as applicable
            $this->load->model('combatunitmodel');
            $todie = $this->combatunitmodel->get_by_die($page['game']->game_id);
            if ($this->debug > 3) log_message('error', 'Ready to kill off '.count($todie).' casualties in game '.$page['game']->game_id);
            foreach($todie as $dead)
            {
                if ($this->debug > 3) log_message('error', 'Killing unit: '.$dead->combatunit_id.' '.$dead->name);
                kill_unit($dead);
            }
            if ($this->debug > 3) log_message('error', 'killing units marked KIA done');
            
            // Check for captured territories after eliminating casualties
            // Check for captured territories
            $this->load->model('territorymodel');
            $page['contested'] = $this->territorymodel->get_contested($page['game']->game_id);
            foreach($page['contested'] as $contested)
            {
                if ( $this->territorymodel->is_captured($contested) )
                {
                    if ($this->debug > 3) log_message('error', 'located a captured territory '.$contested->territory_id);
                    $captured[] = $contested;
                }
            }
            $garrison_units_flipped = false;
            if ( isset($captured) )
            {
                if ($this->debug > 3) log_message('error', 'Number of captured territories: '.count($captured));
                foreach( $captured as $territory )
                {
                    // Find out who captured it
                    $invader = $this->combatunitmodel->get_one_by_location($territory->territory_id);
                    $defender = $this->playermodel->get_by_id($territory->player_id);
                    if (!isset($invader->owner_id))
                    {
                        unset($invader);
                        if ($this->debug > 3) log_message('error', 'Captured territory, no defenders... revert to original'.$territory->name.' '.$territory->player_id);
                        $invader = new stdClass();
                        $invader->owner_id = $territory->player_id;
                    }
                    else
                    {
                        $attacker = $this->playermodel->get_by_id($invader->owner_id);
                        
                        if ( isset($defender->player_id) && $attacker->player_id == $defender->player_id )
                            unset($attacker);
                    }
                    
                    if (isset($attacker->player_id))
                    {
                        game_message($page['game']->game_id, $attacker->faction.' captured '.$territory->name.'!');

                        if ($territory->is_regional)
                        {
                            game_message($page['game']->game_id, $defender->faction.' lost a Regional Capital!');
                            tech_mod($defender, -2);
                            tech_mod($attacker, 2);
                        }
                        else if ($territory->is_capital)
                        {
                            game_message($page['game']->game_id, $defender->faction.' lost a House Capital!');
                            tech_mod($defender,-5);
                            tech_mod($attacker, 2);
                        }
                        
                        // Garrison units change ownership
                        $garrison_units_flipped = false;
                        if ($this->debug > 1) log_message('error', 'starting garrison check');
                        if (isset($territory->garrison_name) && $territory->garrison_name != '')
                        {
                            if ($this->debug > 1) log_message('error', 'Found garrison unit! '.$territory->garrison_name.', '.$territory->player_id);
                            $garrison_units = $this->combatunitmodel->get_by_name_and_owner($territory->garrison_name, $territory->player_id);

                            if ($this->debug > 1) log_message('error', 'count of garrison units is '.count($garrison_units).' '.$this->db->last_query());
                            
                            foreach ($garrison_units as $garrison_unit)
                            {
                                if ($this->debug > 1) log_message('error', 'updating garrison unit c_id='.$garrison_unit->combatunit_id);
                                
                                $updateunit = new stdClass();
                                $updateunit->combatunit_id  = $garrison_unit->combatunit_id;
                                $updateunit->owner_id       = $attacker->player_id;
                                $this->combatunitmodel->update($updateunit->combatunit_id, $updateunit);
                                
                                $g_territory = $this->territorymodel->get_by_id($garrison_unit->location_id);
                                if (isset($g_territory->territory_id))
                                {
                                    $territoryupdate = new stdClass();
                                    $territoryupdate->territory_id = $g_territory->territory_id;
                                    $territoryupdate->is_contested = true;
                                    $this->territorymodel->update($territoryupdate->territory_id, $territoryupdate);
                                    log_message('error', $this->db->last_query());

                                    generate_combat_logs($g_territory, $page['game']);
                                }
                                $garrison_units_flipped = true;
                            }
                            
                            if ($garrison_units_flipped)
                            {
                                game_message($page['game']->game_id, $attacker->faction.' gain\'s control of '.$territory->garrison_name.'.');
                            }
                        }
                    }
                    else
                    {
                        game_message($page['game']->game_id, $territory->name.' defended by '.$defender->faction.'.');
                    }
                    
                    // Capture the territory
                    $territoryupdate = new stdClass();
                    $territoryupdate->territory_id = $territory->territory_id;
                    $territoryupdate->player_id = $invader->owner_id;   // player_id is for the territory
                                                                        // owner_id is from the combatunit 
                    $territoryupdate->is_contested = false;
                    $this->territorymodel->update($territoryupdate->territory_id, $territoryupdate);
                    update_territory($territoryupdate->territory_id);
                    
                    // Perform factory damage rolls
                    $this->load->model('factorymodel');
                    
                    if ($this->debug > 3) log_message('error', 'Factory check for tid '.$territoryupdate->territory_id);
                    
                    $factory = $this->factorymodel->get_by_location($territoryupdate->territory_id);
                    if ( isset($factory->factory_id) )
                    {
                        // Damage roll
                        $damage = false;
                        $factorylog = $this->factorymodel->get_by_damage_location($territory->territory_id);
                        
                        if (isset( $factorylog->force_size ))
                        {
                            if ($this->debug > 3) log_message('error', 'Doing factory damage roll.');

                            $roll = roll_dice(1,10);

                            if ($this->debug > 3) log_message('error', 'Roll is '.$roll);

                            if ($factorylog->use_force_size)
                                $roll += factory_modifier( $factorylog->force_size );
                            else
                            {
                                if ($this->debug > 3) log_message('error', 'skipped factory dmg mod!');
                            }
                            if ($this->debug > 3) log_message('error', 'Modified MFC dmg roll is '.$roll);

                            if ( $roll > 7 )
                            {
                                // Damaged factory!
                                if ( $factorylog->is_damaged )
                                {
                                    // DESTROYED
                                    if ($this->debug > 3) log_message('error', 'Factory destroyed!');
                                    $this->factorymodel->delete($factorylog->factory_id);
                                    game_message($page['game']->game_id, 'The factory at '.$factorylog->name.' is destroyed by combat!');
                                }
                                else
                                {
                                    // DAMAGED
                                    if ($this->debug > 3) log_message('error', 'Factory damaged!');
                                    $factoryupdate = new stdClass();
                                    $factoryupdate->factory_id = $factorylog->factory_id;
                                    $factoryupdate->is_damaged = true;
                                    $this->factorymodel->update($factorylog->factory_id, $factoryupdate);
                                    game_message($page['game']->game_id, 'The factory at '.$factorylog->name.' is damaged by combat!');
                                    $damage = true;
                                }
                            }
                        }  // end if (isset( $factorylog->force_size ))

                        // Tech loss and gain
                        if (isset($attacker->player_id)) // then attacker took control of MFC
                        {
                            // Attacker +1 & Defender -2 tech if undamaged
                            if (!$damage && !$factory->is_damaged)
                            {
                                game_message($page['game']->game_id, $attacker->faction.' captured a functioning Manufacturing Center at '.$territory->name.'!');
                                tech_mod($attacker,1);
                                
                                game_message($page['game']->game_id, $defender->faction.' lost control of a functioning Manufacturing Center at '.$territory->name.'!');
                                tech_mod($defender, -2);
                            }
                            else if (!$damage && $factory->is_damaged)
                            {
                                game_message($page['game']->game_id, $defender->faction.'\'s damaged Manufacturing Center at '.$territory->name.' is lost!');
                                tech_mod($defender, -1);
                            }
                            else if ($damage && $factory->is_damaged)
                            {
                                game_message($page['game']->game_id, $defender->faction.'\'s damaged Manufacturing Center at '.$territory->name.' is destroyed and lost!');
                                tech_mod($defender, -1);
                            }
                            else if ($damage && !$factory->is_damaged)
                            {
                                game_message($page['game']->game_id, $defender->faction.'\'s Manufacturing Center at '.$territory->name.' is damaged and lost!');
                                tech_mod($defender, -2);
                            }
                        }
                        else if ($damage)         // Defender retains control but the MFC is damaged... -1 tech
                        {
                            game_message($page['game']->game_id, $defender->faction.'\'s Manufacturing Center at '.$territory->name.' is damaged!');
                            tech_mod($defender, -1);
                        }
  
                    }  // end isset(factory)
                    
                    // Capture jumpships
                    $this->load->model('jumpshipmodel');
                    $jumpships = $this->jumpshipmodel->get_captured_by_territory($territory->territory_id);
                    foreach( $jumpships as $js )
                    {
                        if ( $js->owner_id != $territoryupdate->player_id )
                        {
                            if (!$page['game']->destroy_jumpships)
                            {
                                $jsupdate = new stdClass();
                                $jsupdate->owner_id = $territoryupdate->player_id;
                                $jsupdate->jumpship_id = $js->jumpship_id;
                                $this->jumpshipmodel->update($js->jumpship_id, $jsupdate); 
                                if (isset($attacker->player_id))
                                    game_message($page['game']->game_id, $attacker->faction.' captured Jumpship ('.$js->capacity.').');
                                else
                                    game_message($page['game']->game_id, $defender->faction.' captured Jumpship ('.$js->capacity.').');
                            }
                            else
                            {
                                $this->jumpshipmodel->delete($js->jumpship_id);
                                game_message($page['game']->game_id, 'Jumpship ('.$js->capacity.') was destroyed in combat.');
                            }
                        }    
                    }

                    // Capture leaders or send them on the magic jumpship
                    $this->load->model('leadermodel');
                    $leaders = $this->leadermodel->get_captured_by_territory($territory->territory_id);
                    
                    foreach( $leaders as $leader )
                    {
                        if ( $leader->controlling_house_id != $territoryupdate->player_id )
                        {
                            capture_leader($leader->leader_id, (isset($attacker->player_id)?$attacker->player_id : $defender->player_id));  
                        }
                        else if ( !isset( $leader->allegiance_to_house_id ) )
                        {
                            // bribed leaders take the magic jumpship
                            if ( isset($attacker->leader_id) 
                                    && $attacker->official_capital != 0  
                                    || isset($defender->leader_id) 
                                    && $defender->official_capital != 0)
                            {
                                unset($leaderupdate);
                                $leaderupdate = new stdClass();
                                $leaderupdate->leader_id = $leader->leader_id;
                                
                                if (isset($attacker->player_id) )
                                {
                                    // Refetch leader to get proper columns like territory names
                                    $leader = $this->leadermodel->get_by_id($leader->leader_id);
                                    magic_jumpship($leader, $attacker->player_id);
                                }
                                else
                                {
                                    // Refetch leader to get proper columns like territory names
                                    $leader = $this->leadermodel->get_by_id($leader->leader_id);
                                    magic_jumpship($leader, $defender->player_id);
                                }
                                $leaderupdate->just_bribed=0;
                                $this->leadermodel->update($leader->leader_id, $leaderupdate);
                                
                                game_message($page['game']->game_id, 'The traitor '.$leader->name.' is sent to '.$attacker->faction.'\'s capital.');
                            }
                        }
                    }  // end foreach leader
                    
                    // Capture TERRA
                    if ($territory->name == 'Terra' && isset($attacker->player_id) && $page['game']->use_terra_loot)
                    {
                        game_message($page['game']->game_id, 'Terra has been Captured!');
                        if ($defender->player_id == 0)
                        {
                            // This is the first time Terra has been captured
                            // +10 Technology
                            // 25 MM Cbills
                            // 2 Free Units
                            // Perma House Interdict
                            
                            $attacker->money += 25;
                            if ($attacker->house_interdict == 0 && $page['game']->use_terra_interdict)
                                $attacker->house_interdict = 1;
                            $this->playermodel->update($attacker->player_id, $attacker);
                            
                            $freeunit = new stdClass();
                            $freeunit->name = 'Star League Cache';
                            $freeunit->game_id = $page['game']->game_id;
                            $freeunit->strength = 7;
                            $freeunit->prewar_strength = 7;
                            $freeunit->owner_id = $attacker->player_id;
                            $freeunit->location_id = $territory->territory_id;
                            $freeunit->being_built = 0;
                            $this->combatunitmodel->create($freeunit);
                            $this->combatunitmodel->create($freeunit);
                            
                            game_message($page['game']->game_id, $attacker->faction.' gains +10 Technology.');
                            tech_mod($attacker,10);
                            game_message($page['game']->game_id, $attacker->faction.' gains +25 CBills.');
                            game_message($page['game']->game_id, $attacker->faction.' uncovers a Star League Cache. Two free units on Terra.');
                            
                        }
                        else
                        {
                            // This is NOT the first time Terra has been captured
                            // +10 Technology
                            // Perma House Interdict
                            // ...
                            // -10 Technology to the loser
                            if ($attacker->house_interdict == 0  && $page['game']->use_terra_interdict)
                                $attacker->house_interdict = 1;
                            $this->playermodel->update($attacker->player_id, $attacker);
                            $this->playermodel->update($defender->player_id, $defender);
                            
                            game_message($page['game']->game_id, $attacker->faction.' gains +10 Technology.');
                            tech_mod($attacker,10);
                            game_message($page['game']->game_id, $defender->faction.' loses -10 Technology.');
                            tech_mod($defender,-10);
                        }
                    }
                    
                    // Delete combat logs associated with this territory
                    $this->db->query('delete from combatlog where territory_id='.$territory->territory_id);
                    
                }  // end foreach captured territory
                
            }  // end isset captured

            // Check for contested territories again after eliminating casualties
            // and capturing territories
            if ($this->debug > 3) log_message('error', 'Checking for contested territories');
            $this->load->model('territorymodel');
            $page['contested'] = $this->territorymodel->get_contested($page['game']->game_id);
            if ( count($page['contested']) > 0 )
            {
                foreach($page['contested'] as $territory)
                {
                    if ($this->debug > 3) log_message('error','rechecking for contested...');
                    if ( !$this->territorymodel->is_contested($territory->territory_id) )
                    {
                        // Update...
                        $territoryupdate->is_contested = false;
                        $territoryupdate->territory_id = $territory->territory_id;
                        $this->territorymodel->update($territory->territory_id, $territoryupdate);
                        if ($this->debug > 3) log_message('error','1 territory no longer contested!');
                    }
                    
                }  // end foreach($page['contested'] as $territory)
                
            }  // end if ( count($page['contested']) > 0 )
            
            // In the event that garrison units switched sides, we have to kick
            // out of the combat round and allow players to click done so that 
            // Target validity check are made.
            if ($garrison_units_flipped)
            {
                // TODO autoassign targets if onl one choice, then don't kick out of combat
                // TODO put this in a helper
                foreach($page['players'] as $player)
                {
                    // Only if you have a combat log...
                    $cl = $this->combatlogmodel->get_by_player($player->player_id);
                    if ( count($cl) > 0 )
                    {
                        // Check for defeated players...
                        if ($player->turn_order != 0)
                        {
                            unset($playerupdate);   // just in case...
                            $playerupdate = new stdClass();
                            $playerupdate->combat_done = false;
                            $playerupdate->player_id = $player->player_id;
                            $this->playermodel->update($player->player_id, $playerupdate);

                            // Email player that action is required...
                            email_player($player->player_id, 
                                    'Garrison units have switched sides in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$page['game']->game_id.'">'.
                                    $page['game']->title.
                                    '"</a>. Please review combat, assign casualties as needed, and click Done.');
                        }
                        else
                        {
                            // TODO autoassign casualties?
                        }
                    }
                    
                }
                
                $page['notice'] = 'Status set to Done. Periphery Capture Detected!';
                $this->load->view('templatexml', $page);

                $this->benchmark->mark('combat_end');
                log_message('error', 'Combat done for Game Id '.$page['game']->game_id.' in '.$this->benchmark->elapsed_time('combat_start','combat_end').' seconds using '.$this->db->total_queries().' sql queries.');
                return;
            }
            
            // If there are contested areas, execute a round of combat
            $page['contested'] = $this->territorymodel->get_contested($page['game']->game_id);

            if (count($page['contested']) > 0)
            {                
                // Handle bribed leaders business
                $this->load->model('leadermodel');
                $bribedleaders = $this->db->query('select * from leaders where game_id='.$page['game']->game_id.' and just_bribed = 1')->result();
                $bribedanddone = $this->db->query('select * from leaders where game_id='.$page['game']->game_id.' and just_bribed = 2')->result();
                foreach( $bribedleaders as $l )
                {
                    $l->just_bribed = 2;
                    $this->leadermodel->update($l->leader_id, $l);
                }
                foreach( $bribedanddone as $l )
                {
                    $l->just_bribed = 0;
                    $l->allegiance_to_house_id = 0;
                    $p = $this->db->query('select * from players where player_id='.$l->controlling_house_id)->row();
                    $l->location_id = $p->official_capital;
                    $this->leadermodel->update($l->leader_id, $l);
                }
                
                
                // Results from combat are stored for later in here...
                $result;
                
                // Stay with me here...
                // Grab all of the combat logs and sort them into an object
                // This will all make sense later, I promise...
                $combatlogs = $this->combatlogmodel->get_by_game($page['game']->game_id);
                $finallogs;     // Storing logs here
                foreach( $combatlogs as $log )
                {
                    if (!isset($finallogs[$log->player_id][$log->territory_id]->combatlog_id))
                        $finallogs[$log->player_id][$log->territory_id] = new stdClass();
                    $finallogs[$log->player_id][$log->territory_id]->combatlog_id = $log->combatlog_id;
                }
                
                // Execute combat in all contested territories
                
                $tech_bonus;
                $houseinterdict;
                $eliminated_players;
                $page['players'] = $this->playermodel->get_by_game($page['game']->game_id); // Refresh or copy of players
                foreach( $page['players'] as $player )
                {
                    // Track technology bonus
                    if ( $player->tech_level <= -10 )
                        $tech_bonus[$player->player_id] = -2;
                    else if ( $player->tech_level <= -5 )
                        $tech_bonus[$player->player_id] = -1;
                    else if ( $player->tech_level < 24 )
                        $tech_bonus[$player->player_id] = 0;
                    else
                        $tech_bonus[$player->player_id] = 2;
                    
                    if ( $player->tech_level <= -10 )
                        $tech_bonus_elem[$player->player_id] = -2;
                    else if ( $player->tech_level <= -5 )
                        $tech_bonus_elem[$player->player_id] = -1;
                    else if ( $player->tech_level < 21 )
                        $tech_bonus_elem[$player->player_id] = 0;
                    else
                        $tech_bonus_elem[$player->player_id] = 1;
                    
                    // Track house interdicts
                    if ($player->house_interdict > 0)
                        $houseinterdict[$player->player_id] = true;
                    
                    // Track eliminated players
                    if ($player->turn_order == 0 || $player->eliminate)
                        $eliminated_players[$player->player_id] = true;
                }
                
                // Get territory bonuses from cards
                // There can be only 1 star league or 1 interdict per region
                $card_bonus;
                $this->load->model('combatbonusmodel');
                $bonuses = $this->combatbonusmodel->get_by_contested($page['game']->game_id);
                foreach( $bonuses as $bonus )
                {   
                    // Create quick lookup table...
                    // Format is $card_bonus[player][location][interdict/starleague] 
                    if ($bonus->value == 2)
                    {
                        $card_bonus[$bonus->player_id][$bonus->location_id]['starleague'] = true;
                    }
                    else if ($bonus->value == -2)
                    {
                        $card_bonus[$bonus->player_id][$bonus->location_id]['interdict'] = true;
                    }
                }
                
                foreach( $page['contested'] as $territory )
                {
                    // Roll a combat die for each unit in the territory
                    $units = $this->combatunitmodel->get_by_location($territory->territory_id);
                    $unitsrolled;   // track what units have been rolled for, used when rolling for combinations
                    foreach($units as $unit)
                    {
                        if (isset($unitsrolled[$unit->combatunit_id]))
                        {
                            continue;
                        }
                        $unitsrolled[$unit->combatunit_id] = 1;
                        
                        $unitupdate = new stdClass();
                        $unitupdate->combatunit_id = $unit->combatunit_id;
                        
                        // Find combination if applicable
                        unset($combo);
                        if (isset($unit->combine_with))
                        {
                            foreach($units as $u)
                            {
                                if ($u->combatunit_id == $unit->combine_with)
                                {
                                    $combo = $u;
                                    $unitsrolled[$u->combatunit_id] = 1;
                                    break;
                                }
                            }
                        }
                        
                        // Die roll
                        $roll = roll_dice(1,10);                   
                        
                        $unitupdate->last_roll = $roll;
                        $this->combatunitmodel->update($unit->combatunit_id, $unitupdate);
                        if (isset($combo->combatunit_id))
                        {
                            $comboupdate = new stdClass();
                            $comboupdate->last_roll = $roll;
                            $this->combatunitmodel->update($combo->combatunit_id, $comboupdate);
                        }
                              
                        $interdict = 0;
                        $starleague = 0;
                        if ( isset( $card_bonus[$unit->owner_id][$unit->location_id]['interdict'] ) || isset($houseinterdict[$unit->owner_id]) )
                        {
                            if ($unit->owner_id != 0)   // Comstar can never be interdicted
                                $interdict = -2;
                        }
                        if ( isset( $card_bonus[$unit->owner_id][$unit->location_id]['starleague'] ) )
                        {
                            $starleague = 2;
                        }
                  
                        $strength = $unit->strength
                                + $this->combatbonusmodel->get_unit_bonus($unit->combatunit_id)->value
                                + $interdict
                                + $starleague;
                        
                        // House Technology bonus
                        if ( isset($tech_bonus[$unit->owner_id]))
                        {
                            if ($unit->is_conventional && $tech_bonus[$unit->owner_id] < 0)
                                $strength += $tech_bonus[$unit->owner_id];
                            else if (!$unit->is_conventional && !$unit->is_elemental)
                                $strength += $tech_bonus[$unit->owner_id];
                        }
                        
                        if ( isset($tech_bonus_elem[$unit->owner_id]))
                        {
                            if ($unit->is_elemental)
                                $strength += $tech_bonus_elem[$unit->owner_id];
                        }
                        
                        if (isset($combo))
                        {
                            $strength += $combo->strength
                                    + $this->combatbonusmodel->get_unit_bonus($combo->combatunit_id)->value;
                        }

                        if ( !isset( $result[$unit->target_id][$territory->territory_id] ) )
                        {
                            $result[$unit->target_id][$territory->territory_id] = new stdClass();
                            $result[$unit->target_id][$territory->territory_id]->casualties_owed = 0;
                            $result[$unit->target_id][$territory->territory_id]->player_id = $unit->target_id;
                            $result[$unit->target_id][$territory->territory_id]->territory_id = $territory->territory_id;

                            // List the territory name(s)
                            $log_territories[$territory->territory_id] = $territory->name;
                        }
                        
                        if ( $strength >= $roll )
                        {
                            $result[$unit->target_id][$territory->territory_id]->casualties_owed += 1;

                            // If this is COMSTAR, we need to automatically assign kills
                            // ...
                            if ($unit->target_id == 0)
                            {

                                $comstar = $this->combatunitmodel->get_comstar_by_territory($page['game']->game_id, $unit->location_id);
                                
                                log_message('error', 'Count of comstar is '.count($comstar));
                                
                                if (isset($comstar->name))
                                {
                                    log_message('error', 'Autokilling unit '.$comstar->name.' '.$comstar->combatunit_id.' located at '.$comstar->location_id);

                                    // Kill them...
                                    $comstarupdate = new stdClass();
                                    $comstarupdate->combatunit_id = $comstar->combatunit_id;
                                    $comstarupdate->die = 1;
                                    $this->combatunitmodel->update($comstar->combatunit_id, $comstarupdate);
                                    
                                    // Reset casualties owed to zero
                                    $result[$unit->target_id][$territory->territory_id]->casualties_owed = 0;
                                    $result[$unit->target_id][$territory->territory_id]->player_id = $unit->target_id;
                                    $result[$unit->target_id][$territory->territory_id]->territory_id = $territory->territory_id;
                                }
                            }
                            // Also applies to neutral forces
                            // ...
                            if (isset($eliminated_players[$unit->target_id]))
                            {
                                log_message('error', 'Killing neutral Unit '.$unit->target_id);
                                $neutral = $this->combatunitmodel->get_by_player_territory($unit->target_id, $unit->location_id);
                                if (isset($neutral->name))
                                {
                                    $neutral->die = 1;
                                    $this->combatunitmodel->update($neutral->combatunit_id, $neutral);
                                }
                            }
                             
                        }  // end if ( $strength >= $roll )
                    }  // end foreach($units as $unit)
                    
                    // List the faction name(s)
                    foreach( $page['players'] as $player )
                    {
                        if (isset($result[$player->player_id]))
                            $log_factions[$player->player_id] = $player->faction;
                    }
                    
                    // Add in  comstar to the list
                    $log_factions[0] = 'Comstar';
                        
                    // Store combatlogs
                    if ( isset($result) )
                    {
                        foreach($result as $log1)
                        {
                            foreach($log1 as $log)
                            {
                                // Holy crap that looks complicated...
                                // Only set casualties owed if the player is NOT eliminated!
                                // ...
                                if (!isset($eliminated_players[$log->player_id]))
                                {
                                    if (isset($finallogs[$log->player_id][$log->territory_id]->combatlog_id))
                                    {
                                        $logupdate = new stdClass();
                                        $logupdate->combatlog_id = $finallogs[$log->player_id][$log->territory_id]->combatlog_id;
                                        $logupdate->casualties_owed = $log->casualties_owed;
                                        
                                        $this->combatlogmodel->update($logupdate->combatlog_id, $logupdate);
                                    }
                                }              //$page['game']->game_id  OR  $page['players'][0]->game_id
                                else
                                    log_message('error', 'Skipping eliminated players combat log! ');
                            }
                        }
                    }  // end if ( isset($result) )
                    
                    // Check one more time to identify a no retreat allowed condition in the event that 
                    if ( isset($result) )
                    {
                        foreach($result as $log1)
                        {
                            foreach($log1 as $log)
                            {
                                // Holy crap that looks complicated...
                                // Only set casualties owed if the player is NOT eliminated!
                                // ...
                                if (!isset($eliminated_players[$log->player_id]))
                                {
                                    if (isset($finallogs[$log->player_id][$log->territory_id]->combatlog_id))
                                    {
                                        // Determine if this player is allowed to retreat from this territory
                                        $numFriendlyUnits           = $this->db->query('SELECT count(*) AS num FROM combatunits WHERE location_id='.$log->territory_id.' AND owner_id='.$log->player_id)->row()->num;
                                        $numOppossingCombatUnits    = $this->db->query('SELECT count(*) AS num FROM combatunits WHERE location_id='.$log->territory_id.' AND owner_id!='.$log->player_id)->row()->num;
                                        $numOppossingCasualties     = $this->db->query('SELECT sum(casualties_owed) AS num FROM combatlog WHERE territory_id='.$log->territory_id.' AND player_id!='.$log->player_id)->row()->num;

                                        $logupdate = new stdClass();   
                                        $logupdate->combatlog_id = $finallogs[$log->player_id][$log->territory_id]->combatlog_id;
                                        
                                        if ($this->debug > 3) log_message('error', 'Calculating Retreat: Opfor: '.$numOppossingCombatUnits.' OpCas: '.$numOppossingCasualties.' FrFor: '.$numFriendlyUnits.' FrCas: '.$log->casualties_owed);
                                        if ($numOppossingCombatUnits <= $numOppossingCasualties || $numFriendlyUnits <= $log->casualties_owed)
                                        {
                                            $logupdate->is_retreat_allowed = false;
                                        }
                                        else
                                        {
                                            $logupdate->is_retreat_allowed = true;
                                        }
                                        $this->combatlogmodel->update($logupdate->combatlog_id, $logupdate);
                                    }
                                }  // end if (!isset($eliminated_players[$log->player_id]))
                            }  // end foreach($log1 as $log)
                        }  // end foreach($result as $log1)
                    }  // end retreat check
                    
                }  // end foreach( $page['contested'] as $territory )
                
                // Set affected players to not done for casualties and retreats
                // Dont set to not-done if they have a turn_order of zero
                 // TODO put this in a helper
                foreach($page['players'] as $player)
                {
                    // Only if you have a combat log...
                    $cl = $this->combatlogmodel->get_by_player($player->player_id);
                    if ( count($cl) > 0 )
                    {
                        // Check for defeated players...
                        if ($player->turn_order != 0 && $player->eliminate != 1)
                        {
                            unset($playerupdate);   // just in case...
                            $playerupdate = new stdClass();
                            $playerupdate->combat_done = false;
                            $playerupdate->player_id = $player->player_id;
                            $this->playermodel->update($player->player_id, $playerupdate);

                            // Email player that action is required...
                            email_player($player->player_id, 
                                    'Your action is required in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$page['game']->game_id.'">'.
                                    $page['game']->title.
                                    '"</a>. Please review combat, assign casualties as needed, and click Done.');
                        }
                    }
                }
                
                // Increment combat cound...
                $gameupdate                 = new stdClass();
                $gameupdate->game_id        = $page['game']->game_id;
                $gameupdate->combat_rnd     = $page['game']->combat_rnd + 1;
                $gameupdate->last_action    = null;
                $this->gamemodel->update($gameupdate->game_id, $gameupdate);

            }
            else
            {
                // If there are no contested areas, clean up then 
                // advance the phase to PLACEMENT         

                // Set affected players to not done for the next phase
                // Reset tech_bonus counter
                
                // Set all combatunits last roll to 0
                
                $this->db->query('update combatunits set last_roll=0, target_id=null 
                    where game_id='.$page['game']->game_id.'
                        and last_roll>0'); 
                
                // Set all combat and military used for all leaders to zero
                $this->db->query('update leaders set combat_used=0, military_used=0 where game_id='.$page['game']->game_id);
                
                // Eliminate all combinations
                $units = $this->combatunitmodel->get_by_game_combined($page['game']->game_id);
                foreach($units as $unit)
                {
                    $unitup = new stdClass();
                    $unitup->combine_with = null;  // !!!
                    $unitup->combatunit_id = $unit->combatunit_id;
                    $this->combatunitmodel->update($unit->combatunit_id, $unitup);
                }
                $this->db->query('update combatunits set combo_broken=0
                    where game_id='.$page['game']->game_id.'
                    and combo_broken = 1'); 
                
                // Reset combat round
                unset($gameupdate);
                $gameupdate                 = new stdClass();
                $gameupdate->game_id        = $page['game']->game_id;
                $gameupdate->combat_rnd     = 0;
                $gameupdate->last_action    = null;
                                
                $this->gamemodel->update($gameupdate->game_id, $gameupdate);
                
                // Advance turn
                $this->placement($page);
            }   
        }
        
        $page['notice'] = 'Status set to Done.';
        $this->load->view('templatexml', $page);
        
        $this->benchmark->mark('combat_end');
        if ($this->debug > 1) log_message('error', 'Combat done for Game Id '.$page['game']->game_id.' in '.$this->benchmark->elapsed_time('combat_start','combat_end').' seconds using '.$this->db->total_queries().' sql queries.');
        
    }  // end combat
    
    function combinations($territory_id=0)
    {
        $page = $this->page;
        
        // Make sure and iD is provided
        if ($territory_id == 0)
        {
            $page['error'] = 'No ID provided!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($territory_id);
        
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($territory->game_id);
        
        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
                $player = $p;
        }

        if (!isset($player->player_id))
        {
            $page['error'] = 'Error! Cannot modify combination in a game that you are not playing in.';
            $this->load->view('templatexml', $page);
            return;
        }
        $this->load->model('gamemodel');
        $game  = $this->gamemodel->get_by_id($player->game_id);

        if ($game->phase != 'Combat')
        {
            $page['error'] = 'Error! Cannot modify combinations in the wrong phase.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go!
        $this->load->model('combatunitmodel');
        $page['units'] = $this->combatunitmodel->get_by_location_player($territory->territory_id, $player->player_id);
        $page['territory'] = $territory;
        $page['content'] = 'combinations';
        $this->load->view('templatexml', $page);
    }
    
    function combine($unitA_id=0, $unitB_id=0, $leader_id=0)
    {
        $page = $this->page;
        
        // make sure an id is provided
        if ($unitA_id == 0)
        {
            $page['error'] = 'Wrong phase!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Get unit
        $this->load->model('combatunitmodel');
        $unitA = $this->combatunitmodel->get_by_id($unitA_id);
        
        // Get player
        $this->load->model('territorymodel');
        $location = $this->territorymodel->get_by_id($unitA->location_id);
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($location->game_id);
        
        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
                $player = $p;
        }

        if (!isset($player->player_id))
        {
            $page['error'] = 'Error! Cannot combine units in a game that you are not playing in.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->load->model('gamemodel');
        $game  = $this->gamemodel->get_by_id($player->game_id);
        
        if ($unitB_id == -1)
        {
            $page['territory'] = $location;
            $page['unit'] = $unitA;
            $page['units'] = $this->combatunitmodel->get_by_location($location->territory_id);
            
            $this->load->model('leadermodel');
            $page['leaders'] = $this->leadermodel->get_by_player_territory($player->player_id, $location->territory_id);
            
            // Show combination view
            $page['content'] = 'combine';
            $this->load->view('templatexml', $page);            
        }
        else if($unitB_id == -2)
        {
            // Break a combination!
            
            // fetch a leader... and validate
            if ( $leader_id != 0 )
            {
                $this->load->model('leadermodel');
                $traitor = $this->leadermodel->get_by_id($leader_id);
                
                if ( $traitor->just_bribed != 1 || $unitA->owner_id != $traitor->original_house_id)
                {
                    $page['error'] = 'Error! Cannot combine units using a bribed leader.';
                    $this->load->view('templatexml', $page);
                    return;
                } 
            }
            
            // Fetch other unit
            $unitB = $this->combatunitmodel->get_by_id($unitA->combine_with);
            
            // Must be on the same team unless bribed...
            if ($unitA->owner_id != $unitB->owner_id && !isset($traitor->leader_id))
            {
                $page['error'] = 'Error! Cannot combine a combat unit that you do not own.';
                $this->load->view('templatexml', $page);
                return;
            }
            
            if ( isset( $unitA->combined_by ) && !isset($traitor->leader_id) )
            {
                // give back military used
                $this->load->model('leadermodel');
                $leader = $this->leadermodel->get_by_id($unitA->combined_by);
                $leaderupdate = new stdClass();
                $leaderupdate->military_used = $leader->military_used - 1;
                $this->leadermodel->update($leader->leader_id, $leaderupdate);
            }
            
            if ( isset($traitor->leader_id) )
            {
                // Target must be from original house
                if ( $unitA->owner_id != $traitor->original_house_id )
                {
                    $page['error'] = 'Error! Cannot break combinations of combat units the leader has no influence on.';
                    $this->load->view('templatexml', $page);
                    return;
                }
                
                log_message('error', 'TRAITOR: Mil: '.$traitor->military.', MilUsed: '.$traitor->military_used);
                if ( $traitor->military_used == $traitor->military )
                {
                    $page['error'] = 'Error! Cannot break any more combinations.';
                    $this->load->view('templatexml', $page);
                    return;
                }
                $traitorupdate                  = new stdClass();
                $traitorupdate->military_used   = $traitor->military_used + 1;
                $traitorupdate->leader_id       = $traitor->leader_id;
                $this->leadermodel->update($traitor->leader_id, $traitorupdate);
            }
             
            // Break 'em up!
            $unitA->combine_with = null;
            $unitB->combine_with = null;
            $unitA->combined_by = null;
            $unitB->combined_by = null;
            
            if ( isset($traitor->leader_id) )
            {
                $unitA->combo_broken = 1;
                $unitB->combo_broken = 1;
                
                game_message($game->game_id, $player->faction.' uses the traitor '.$traitor->name.' to break a combination!');
            }

            $this->combatunitmodel->update($unitA->combatunit_id, $unitA);
            $this->combatunitmodel->update($unitB->combatunit_id, $unitB);
            
            if (!isset($traitor->leader_id) )
            {
                $this->page['notice'] = 'Combination between '.$unitA->name.', '.$unitA->strength.' and '.$unitB->name.', '.$unitB->strength.' is canceled.';
                $this->combinations($location->territory_id);
            }
            else
            {
                $page['notice'] = 'Combination between '.$unitA->name.', '.$unitA->strength.' and '.$unitB->name.', '.$unitB->strength.' is canceled.';
                
                $page['leader'] = $traitor;
                $page['content'] = 'leader_combo_break_success';
                $this->load->view('templatexml', $page);
            }
        }
        else
        {
            // Make a new combination
            
            // Fetch the other unit
            $unitB = $this->combatunitmodel->get_by_id($unitB_id);
            
            // Must be on the same team
            if ($unitA->owner_id != $unitB->owner_id)
            {
                $page['error'] = 'Error! Cannot combine combat units that you do not own.';
                $this->load->view('templatexml', $page);
                return;
            }
            
            // Must be at the same location
            if ($unitA->location_id != $unitB->location_id)
            {
                $page['error'] = 'Error! Cannot combine units in different locations.';
                $this->load->view('templatexml', $page);
                return;
            }
            /*  I left this code just in case we want to make it a game option.  Maybe for clan players or something.
            // Unit type must match unless either is conventional
            if (!$unitA->is_elemental && !$unitB->is_elemental)
            {
                if ($unitA->is_conventional)
                {
                    if (!$unitB->is_conventional)
                    {
                        $page['error'] = 'You can\'t combine different unit types!';
                        $this->load->view('templatexml', $page);
                        return;
                    }
                }
                else
                {
                    if ($unitB->is_conventional)
                    {
                        $page['error'] = 'You can\'t combine different unit types!';
                        $this->load->view('templatexml', $page);
                        return;
                    }
                }
            }*/
            
            // If no leader is provided, the names must match, unless either is an elemental
            if ($leader_id == 0)
            {
                if ($unitA->name != $unitB->name)
                {
                    if (!$unitA->is_elemental && !$unitB->is_elemental)
                    {
                        $page['error'] = 'Error! Cannot combine units with different names without a leader.';
                        $this->load->view('templatexml', $page);
                        return;
                    }
                }
            }
            else
            {
                // If a leader is provided, fetch the leader
                $this->load->model('leadermodel');
                $leader = $this->leadermodel->get_by_id($leader_id);
                
                // Leader must be on the same team
                if ($leader->controlling_house_id != $player->player_id && $leader->allegiance_to_house_id != $player->player_id && ($leader->original_house_id != $player->player_id || $leader->associated_units != NULL))
                {
                    $page['error'] = 'Error! Cannot combine units with a leader that is not yours.';
                    $this->load->view('templatexml', $page);
                    return;
                }
                
                // If leader is 'merc then both units must be 'merc
                if ($leader->associated_units != NULL && ($unitA->is_merc == FALSE || $unitB->is_merc == FALSE ))
                {
                    $page['error'] = 'Cannot combine non-mercenary units with a mercenary leader.';
                    $this->load->view('templatexml', $page);
                    return;
                }
                
                // Leader must have available military ability
                if ($leader->military_used >= $leader->military)
                {
                    $page['error'] = 'Error! Cannot combine units with a leader that has used all of their Military ability this turn.';
                    $this->load->view('templatexml', $page);
                    return;
                }
                
                $combined_by = $leader->leader_id;
            }
            
            // Must not be slated to die!
            if ($unitA->die || $unitB->die)
            {
                $page['error'] = 'Error! Cannot combine units that are going to die.';
                $this->load->view('templatexml', $page);
                return;
            }
            
            // Must not of been broken
            if ( $unitA->combo_broken || $unitB->combo_broken )
            {
                $page['error'] = 'Error! Cannot combine units whose combo has been broken this combat.';
                $this->load->view('templatexml', $page);
                return;
            }
                
            // Away we go...
            $unitA->combine_with = $unitB->combatunit_id;
            $unitB->combine_with = $unitA->combatunit_id;
            if ( isset($combined_by) )
            {
                $unitA->combined_by = $combined_by;
                $unitB->combined_by = $combined_by;
            }
            $this->combatunitmodel->update($unitA_id, $unitA);
            $this->combatunitmodel->update($unitB_id, $unitB);
            
            if (isset($leader->leader_id))
            {
                $leaderupdate = new stdClass();
                $leaderupdate->military_used = $leader->military_used + 1;
                $this->leadermodel->update($leader_id, $leaderupdate);
            }
            
            $this->page['notice'] = $unitA->name.', '.$unitA->strength.' and '.$unitB->name.', '.$unitB->strength.' are now combined.';
            $this->combinations($location->territory_id);
            
        }
    }
    
    /**
     * 
     */
    function mercs($game_id)
    {
        // Show mercenaries for hire or mercs ready to be placed
        $page = $this->page;
        
        $this->load->model('combatunitmodel');
        $page['mercs'] = $this->combatunitmodel->mercs_for_hire($game_id);
        
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game_id);
        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
                $player = $p;
        }
        $page['player'] = $player;
        
        if (isset($page['mercs'][0]->combatunit_id))
        {
            $this->load->model('offermodel');
            $page['bids'] = $this->offermodel->get_by_merc_player($page['mercs'][0]->combatunit_id, $player->player_id);
        }
        
        $page['mercs_to_place'] = $this->combatunitmodel->mercs_to_place($game_id);
        
        // Fetch their leader
        $this->load->model('leadermodel');
        if (isset($page['mercs'][0]->combatunit_id))
        {
            $page['leaders'] = $this->leadermodel->get_by_merc($game_id, $page['mercs'][0]->name);
        }
        else
        {
            $page['leaders'] = $this->leadermodel->get_not_placed($player->player_id);
        }
        
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        $page['content'] = 'mercs';
        $this->load->view('templatexml', $page);
    }
    
    /**
     * Bid on a mercenary unit
     */
    function bid($combatunit_id=0, $offer=-1)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if($combatunit_id == 0)
        {
            $page['error'] = 'Error! Cannot bid without a combat unit ID.';
            $this->load->view('templatexml');
            return;
        }
        
        // Start our transaction
        $this->db->trans_start();
                
        // Bid on a mercenary unit
        $this->load->model('combatunitmodel');
        $page['merc'] = $this->combatunitmodel->get_by_id($combatunit_id);
        $merc = $page['merc'];
        
        // Make sure the merc unit in question is actually up for bid!
        if (isset($merc->owner_id))
        {
            $this->page['error'] = 'Error! Cannot bid on this mercenary!';
            $this->load->view('templatexml', $page);
            $this->db->trans_complete();
            return;
        }
        
        $this->load->model('territorymodel');
        $page['territory'] = $this->territorymodel->get_by_id($page['merc']->location_id);
        
        // If no offer is being submitted, display the bid form.
        if($offer == -1)
        {
            $this->load->model('playermodel');
            $players = $this->playermodel->get_by_game($merc->game_id);
            foreach($players as $p)
            {
                if ($p->user_id == $page['user']->id)
                    $player = $p;
            }
            if (!isset($player->player_id))
            {
                $this->page['error'] = 'Error! Cannot bid in a game that you are not playing in.';
                $this->load->view('templatexml', $page);
                $this->db->trans_complete();
                return;
            }
            
            $this->load->model('offermodel');
            $page['bids'] = $this->offermodel->get_by_merc_player($page['merc']->combatunit_id, $player->player_id);
            $page['content'] = 'bid';
            $this->load->view('templatexml', $page);
            $this->db->trans_complete();
            return;
        }  // end of bid form display
        
        // Amount must be numeric and positive
        if (!is_numeric($offer))
        {
            $this->page['error'] = 'Error! Cannot bid anything but cbills.';
            $this->bid($combatunit_id);
            $this->db->trans_complete();
            return;
        }
        if ($offer < 0)
        {
            $this->page['error'] = 'Cannot bid a negative value!';
            $this->bid($combatunit_id);
            $this->db->trans_complete();
            return;
        } 
        // cbills must be an integer
        if (!ctype_digit($offer))
        {
            $this->page['error'] = 'Cannot bid except in discrete cBills incriments.';
            $this->bid($combatunit_id);
            $this->db->trans_complete();
            return;
        }
        
        // Must have the money
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($merc->game_id);
        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
                $player = $p;
        }
        if (!isset($player->player_id))
        {
            $this->page['error'] = 'Error! Cannot bid if you are not playing in this game.';
            $this->load->view('templatexml', $page);
            $this->db->trans_complete();
            return;
        }
        if ($player->money < $offer)
        {
            $this->page['error'] = 'You cannot bid an amount you cannot afford!';
            $this->bid($combatunit_id);
            $this->db->trans_complete();
            return;
        }
        
        // Away we go!
        // Get all mercs with the same name, sorted by combatunit_id
        $mercs = $this->combatunitmodel->get_by_game_name($player->game_id, $merc->name);
        $merc = $mercs[0];
        if ($this->debug > 3) log_message('error', 'sw/bid combatunitid is '.$merc->combatunit_id);
        merc_bid($player, $merc, $offer, false);

        // Nothing more to see
        $this->page['notice'] = 'Bid submitted for *'.$merc->name.'.';
        $this->mercs($player->game_id);
        $this->db->trans_complete();
        
    }
    
    /**
     * Place combatunits, jumpships and factories onto the game board
     */
    private function placement($page)
    {
        if ($this->debug > 3) log_message('error', 'Doing placement '. $page['game']->player_id_playing);
        
        // Object to keep track of territories
        $territories;
        $this->load->model('territorymodel');
        
        // Track territories that need to be updated
        $toupdate;
        
        if ($this->debug > 3) log_message('error', 'Placing units');
        
        // Place all units being built
        $this->load->model('combatunitmodel');
        $combatunits = $this->combatunitmodel->get_under_construction_by_game($page['game']->game_id);
        foreach( $combatunits as $combatunit )
        {
            // Add territory to fast lookup table if not already added
            if (!isset($territories[$combatunit->location_id]))
                $territories[$combatunit->location_id] = $this->territorymodel->get_by_id($combatunit->location_id);
            
            // Make sure owner is in control and the factory is not damaged
            if ( $territories[$combatunit->location_id]->player_id == $combatunit->owner_id )
            {
                if ( $combatunit->is_rebuild )
                    $combatunit->strength = 4;
                else
                {
                    $combatunit->strength = $combatunit->prewar_strength;
                    $combatunit->is_rebuild = true;
                }
            }
            else
            {
                log_message('error', 'Placement, combat unit not in owner controlled territory!!!');
            }
            $combatunit->being_built = false;
            $this->combatunitmodel->update($combatunit->combatunit_id, $combatunit);
            
            if (!isset($toupdate[$combatunit->location_id]))
                $toupdate[$combatunit->location_id] = $combatunit->location_id;
        }
        
        if ($this->debug > 3) log_message('error', 'Placing new factories');
        
        // Place all new Manufacturing Centers
        $this->load->model('factorymodel');
        $factories = $this->factorymodel->get_under_construction($page['game']->player_id_playing);
        foreach( $factories as $factory )
        {
            $factoryupdate = new stdClass();
            $factoryupdate->factory_id = $factory->factory_id;
            $factoryupdate->being_built = false;
            $this->factorymodel->update($factoryupdate->factory_id, $factoryupdate);
            
            // owner +1 technology
            $location = $this->territorymodel->get_by_id($factory->location_id);
            $owner = $this->playermodel->get_by_id($location->player_id);
            $territory = $this->territorymodel->get_by_id($factory->location_id);
            game_message($page['game']->game_id, $owner->faction.' completes construction of a manufacturing facility in '.$territory->name.', +1 Technology!');
            tech_mod($owner, 1);
        }
        
        if ($this->debug > 3) log_message('error', 'Repairing factories');
        
        // Repair factories
        unset($factories);
        $factories = $this->factorymodel->get_repair($page['game']->game_id);
        foreach($factories as $factory)
        {
            $factoryupdate = new stdClass();
            $factoryupdate->factory_id = $factory->factory_id;
            $factoryupdate->being_repaired = false;
            $factoryupdate->is_damaged = false;
            $this->factorymodel->update($factoryupdate->factory_id, $factoryupdate);
            
            // owner +1 technology
            $location = $this->territorymodel->get_by_id($factory->location_id);
            $owner = $this->playermodel->get_by_id($location->player_id);
            game_message($page['game']->game_id, $owner->faction.' repairs a factory.');
            tech_mod ($owner, 1);
        }
        
        if ($this->debug > 3) log_message('error', 'Placing jumpships');
        
        // Place all new jumpships
        $this->load->model('jumpshipmodel');
        $jumpships = $this->jumpshipmodel->get_under_construction( $page['game']->player_id_playing );
        foreach($jumpships as $jumpship)
        {
            if (!isset($territories[$jumpship->location_id]))
                $territories[$jumpship->location_id] = $this->territorymodel->get_by_id($jumpship->location_id);
            
            if ( $territories[$jumpship->location_id]->player_id == $jumpship->owner_id )
            {
                $jumpship->being_built = false;
                $this->jumpshipmodel->update($jumpship->jumpship_id, $jumpship);
            }
            else 
            {
                $this->jumpshipmodel->delete($jumpship->jumpship_id);
            }
            
            if (!isset($toupdate[$jumpship->location_id]))
                $toupdate[$jumpship->location_id] = $jumpship->location_id;
        }
        
        if ($this->debug > 3) log_message('error', 'updating territories');
        
        // Update territories if needed
        if ( isset( $toupdate ) && count($toupdate) > 0 )
        {
            foreach( $toupdate as $t )
            {
                update_territory($t);
            }
        }
        
        // Advance turn to TAXES if there are no units to be placed
        $this->taxes($page);
        
    }  // end placement
    
    private function taxes($page)
    {     
        if ($this->debug>2) log_message('error', 'private function taxes($page)');
        
        // The active player makes income from all controlled areas 
        $activeplayer = $this->playermodel->get_by_id($page['game']->player_id_playing);
        $resources = $this->territorymodel->get_by_player($activeplayer->player_id);
        $money = 0;
        foreach($resources as $resource)
        {
            $money += $resource->resource;
        }
        
        // Adjust for technology level
        if( $activeplayer->tech_level > 16 )
        {
            $money += 7;
        }
        
        if ($this->debug > 3) log_message('error', 'debug in taxes '.$activeplayer->player_id);
        
        // Add up admin of leaders
        $this->load->model('leadermodel');
        $money += $this->leadermodel->get_admin_tax($activeplayer->player_id);
        if ($this->debug > 3) log_message('error', 'Taxes... '.$money.' to '.$activeplayer->faction);
        
        game_message($page['game']->game_id, $activeplayer->faction.' takes in '.$money.' in taxes.');
        
        $playerupdate = new stdClass();
        $playerupdate->player_id = $activeplayer->player_id;
        $playerupdate->money = $activeplayer->money + $money;
        $this->playermodel->update($activeplayer->player_id, $playerupdate);

        // Handle combatbonus tasks
        // 
        // Decrement all ttl and delete used up bonuses
        $this->load->model('combatbonusmodel');
        $bonuses = $this->combatbonusmodel->get_by_game($page['game']->game_id);
        foreach( $bonuses as $bonus )
        {
            if ( $bonus->ttl == 1 )
                $this->combatbonusmodel->delete($bonus->combatbonus_id);
            else 
            {
                $bonus->ttl = $bonus->ttl - 1;
                $this->combatbonusmodel->update($bonus->combatbonus_id, $bonus);
            }
        }
        
        // Decrement house interdicts, if zero and not controlling terra go to 0
        foreach($page['players'] as $player)
        {
            unset($playerupdate);   // just in case...
            $playerupdate = new stdClass();
            // Defeated players
            if ($player->turn_order != 0)
                $playerupdate->combat_done = false;
            $playerupdate->player_id = $player->player_id;
            $playerupdate->tech_bonus = 0;

            if ($player->house_interdict > 0)
            {
                if ( $player->house_interdict == 1 )
                {
                    // Check to see if we are using the terra interdict option
                    // Does this player own terra?
                    $terra = $this->territorymodel->get_by_game_name( $page['game']->game_id, 'Terra' );
                    if ($terra->player_id != $player->player_id  || !$page['game']->use_terra_interdict)
                    {
                        $playerupdate->house_interdict = 0;
                        game_message($page['game']->game_id, 'House Interdict on '.$player->faction.' has ended!');
                    }
                }
                else
                    $playerupdate->house_interdict = $player->house_interdict - 1;
            }

            $this->playermodel->update($player->player_id, $playerupdate);
        }
        
        // Set next player to playing
        $numplayers = 0;
        foreach($page['players'] as $player)
        {
            if ($player->turn_order != 0)
                $numplayers++;
        }

        if ($this->debug > 3) log_message('error', 'count players is '.count($page['players']));
        
        // For stats
        $currentturnorder = $activeplayer->turn_order;
        
        // Where we are going next
        $nextturnorder = $activeplayer->turn_order + 1;

        if ($this->debug > 3) log_message('error', 'Next turn order = '.$nextturnorder);
        
        $nextplayer;
        $increment_year = false;
        
        if ( $nextturnorder > $numplayers )
        {
            $increment_year = true;
            $nextturnorder = 1;
        }
        
        $count = 0;
        
        // Have to fetch players again to make sure our copy is fresh!
        $page['players'] = $this->playermodel->get_by_game($page['game']->game_id);
        
        while (!isset($nextplayer->player_id) && $count < $numplayers + 1)
        {
            foreach($page['players'] as $player)
            {
                if ($this->debug > 3) log_message('error', 'Looking at player '.$player->faction.' turn order '.$player->turn_order);
                
                if ($player->turn_order == $nextturnorder)
                {
                    if ($player->eliminate)
                    {
                        if ($this->debug > 3) log_message('error', 'Found eliminated player!');
                        
                        // Not the player we are looking for
                        $nextturnorder++;   // Our local copy is not yet updated
                        if ($nextturnorder > $numplayers)
                        {
                            $increment_year = true;
                            $nextturnorder = 1;
                        }
                    }
                    else
                    {
                        if ($this->debug > 3) log_message('error', 'Found our player!');
                        $nextplayer = $player;
                        break;
                    }
                }
            }
            $count++;
        }
        
        if ($count > $numplayers)
        {
            log_message('error', 'Error! Unable to find next player!  What the hell!?!?');
        }
        
        // Eliminate players
        foreach($page['players'] as $player)
        {
            if ($player->eliminate  && $player->turn_order > 0)
            {
                if ($this->debug > 3) log_message('error', 'Eliminating '.$player->faction);
                unset($playerupdate);
                $playerupdate = new stdClass();
                $playerupdate->turn_order = 0;
                $playerupdate->eliminate = 0;
                $playerupdate->combat_done = 1;
                $this->playermodel->update($player->player_id, $playerupdate);
                $this->db->query('update players set turn_order=turn_order-1 where turn_order > '.$player->turn_order.' and game_id='.$player->game_id);
            }
        }
        
        // Do stats!
        /*
        $this->load->model('gamestatmodel');
        foreach($page['players'] as $p)
        {
            unset($stat);
            log_message('error', 'Generating stats for game id '.$page['game']->game_id);
            $stat->game_id = $page['game']->game_id;
            $stat->player_id = $p->player_id;
            $stat->tech_level = $p->tech_level;
            $stat->military_size = $this->db->query('select COUNT(DISTINCT combatunit_id) as count from combatunits where owner_id='.$p->player_id)->row()->count;
            $stat->military_strength = $this->db->query('select SUM(strength) as sum from combatunits where owner_id='.$p->player_id)->row()->sum;
            $stat->jumpship_size = $this->db->query('select COUNT(DISTINCT jumpship_id) as count from jumpships where owner_id='.$p->player_id)->row()->count;
            $stat->jumpship_capacity = $this->db->query('select SUM(capacity) as sum from jumpships where owner_id='.$p->player_id)->row()->sum;
            $stat->num_territories = $this->db->query('select COUNT(DISTINCT territory_id) as count from territories where player_id='.$p->player_id)->row()->count;
            $stat->tax_revenue = $this->db->query('select SUM(resource) as sum from territories join map on map.map_id=territories.map_id where player_id='.$p->player_id)->row()->sum;
            $stat->cbills = $p->money;
            $stat->round = $page['game']->turn;
            $stat->turn_order = $currentturnorder;
            $this->gamestatmodel->create($stat);
        }
         * */
        
        // Check for victory condition(s)
        $this->load->helper('victory');
        $we_have_a_winner = false;
        if ($page['game']->alt_victory)
        {
            log_message('error', 'Checking alternate victory conditions');
            $we_have_a_winner = check_alt_victory($page['game'], $nextplayer);
        }
        else 
        {
            $we_have_a_winner = check_default_victory($page['game'], $nextplayer);
        }
        
        if ($we_have_a_winner->result)
        {
            // GAME IS WON!!!
            $page['game']->phase = "Game Over";
            $this->gamemodel->update($page['game']->game_id, $page['game']);
            
            game_message($page['game']->game_id, '***** VICTORY *****');
            game_message($page['game']->game_id, 'Congratulations to '.$nextplayer->faction.' and good game everyone!');
        }
        else
        {
            // Reset combat round
            unset($gameupdate);
            $gameupdate = new stdClass();
            $gameupdate->game_id = $page['game']->game_id;
            $gameupdate->phase='Draw';
            $gameupdate->combat_rnd = 0;
            $gameupdate->player_id_playing = $nextplayer->player_id;
            
            if ($increment_year)
            {
                $gameupdate->turn = $page['game']->turn + 1;
            }
            $this->gamemodel->update($gameupdate->game_id, $gameupdate);
            
            // Reset movement for all jumpships
            $this->db->query('UPDATE jumpships JOIN territories on territories.territory_id=jumpships.location_id '
                    . 'SET moves_this_turn=0 '
                    . 'WHERE game_id='.$page['game']->game_id);
            
            game_message($page['game']->game_id, 'A new turn begins, '.$nextplayer->faction.' is up next.');
            //if ($this->debug > 3) log_message('error', 'Next player id = '.$page['game']->player_id_playing);
            
            // Notify next player that its their turn
            //if ($this->debug>3)
            //    log_message('error', 'About to call: helpers/email_helper.php email_player($nextplayer->player_id='.$nextplayer->player_id.', $message=It is your turn in the game "'.$page['game']->title.'" as '.$nextplayer->faction.'.');
            email_player($nextplayer->player_id,
                    'It is your turn in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$page['game']->game_id.'">'.
                    $page['game']->title.'"</a> as '.$nextplayer->faction.'.');
            
            // Advance phase to the DRAW phase
            $page['nextplayer'] = $nextplayer;
        }
        
        $page['notice'] = 'Status set to Done. No combat this turn.  Advancing to '.$nextplayer->faction.'\'s turn.';
        $this->load->view('templatexml', $page);
    }  // end taxes
    
    private function draw($page)
    {      
        // Must be the players turn
        if ($page['player']->player_id != $page['game']->player_id_playing)
        {
            return;
        }
        
        if ($this->debug>2) log_message('error', 'private function draw($page)');
        
        // Draw cards for the active player, up to max hand size
        $maxhand = 4;
        $this->load->model('cardmodel');
        $cards = $this->cardmodel->get_by_player($page['player']->player_id);

        for ( $i = count($cards); $i < $maxhand; $i++)
        {
            $card = $this->cardmodel->draw($page['game']->game_id);
            $card->owner_id = $page['player']->player_id;
            $this->cardmodel->update($card->card_id, $card);    
            game_message($page['game']->game_id, $page['player']->faction.' draws a card from the deck.');
        }
        
        // Advance phase to PRODUCTION or merc phase as required
        unset($gameupdate);
        $gameupdate = new stdClass();
        
        if ($page['game']->use_merc_phase)
            $gameupdate->phase = 'Mercenary Phase';
        else
        {
            log_message('error', 'Setting production phase...');
            $gameupdate->phase = 'Production';
        }
        $gameupdate->game_id = $page['game']->game_id;
        $this->gamemodel->update($page['game']->game_id, $gameupdate);
        
        if ($page['game']->use_merc_phase)
        {
            $this->merc_phase($page);
        }
        else
        {
            $page['notice'] = 'Status set to Done. Drawing cards and advancing to Production.';
            $this->load->view('templatexml', $page);
        }
        
    }  // end draw
    
    /**
     * Trigger the Mercenary Phase to occur
     * 
     * This is called by game/start($game_id) to trigger the first mercenary phase 
     * of the game.  Normally the private function is called at the end of the draw
     * phase, but for the first turn, it needs to be triggered differently.
     */
    public function trigger_merc_phase($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
	if ($game_id == 0)
	{
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be the game owner
	$this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
	if ( isset($page['user']->id) && $game->creator_id != $page['user']->id )
	{
            $page['error'] = 'You do not own this game!';
            $this->load->view('templatexml', $page);
            return;
	}
        $page['game'] = $game;
        
        // Game must be in setup phase
	if ( $game->phase != 'Player Setup' )
	{
            $this->page['error'] = 'You must be in Player Setup phase to start a game!';
            $this->view($game_id);
            return;
	}
        
        // Must have the game built
        if ($game->built != 1)
        {
            $this->page['error'] = 'The game has not been built yet!';
            $this->view($game_id);
            return;
        }
        
        // Must be turn 1
        if ($game->turn != 1)
        {
            $this->page['error'] = 'Can\'t do that unless the game has not already been started!';
            $this->view($game_id);
            return;
        }
        
        // Away we go...
        
        $gameupdate->game_id = $game_id;
        $gameupdate->phase = 'Mercenary Phase';
        
        // Update game
        $this->gamemodel->update($game_id, $gameupdate);
        
        // Trigger first merc phase
        $this->merc_phase($page);
        
        $this->session->set_flashdata('notice', 'Game Started!');
        redirect('game/view/'.$game_id, 'refresh');
    }
    
    /**
     * Mercenary Phase
     * 
     * Pull a random mercenary unit for bidding.
     * Skip if no mercenary forces are available.
     */
    private function merc_phase($page)
    {
        if ($this->debug > 3) log_message('error', 'merc_phase/'.$page['game']->game_id);
        
        $this->load->model('combatunitmodel');
        
        // Pull random merc force
        $merc = $this->combatunitmodel->get_random_merc($page['game']->game_id);
        if ($this->debug > 3) log_message('error', $this->db->last_query());
                
        // Skip if no merc forces are left
        if (!isset($merc->combatunit_id))
        {
            game_message($page['game']->game_id, 'No Mercenary Units Available!  Skipping Mercenary Phase.');
            $gameupdate = new stdClass();
            $gameupdate->phase = "Production";
            $gameupdate->game_id = $page['game']->game_id;
            $this->gamemodel->update($page['game']->game_id, $gameupdate);
            return;
        }
        
        // Check for multiple mercs with the same name
        $mercs = $this->combatunitmodel->get_by_name($page['game']->game_id, $merc->name);
        
        // Put merc units up for bid
        foreach($mercs as $m)
        {
            $mu = new stdClass();
            $mu->combatunit_id = $m->combatunit_id;
            $mu->strength = $m->prewar_strength;
            $this->combatunitmodel->update($mu->combatunit_id, $mu);
        }
        
        // Fetch their leader and also make them available
        $this->load->model('leadermodel');
        $leader = $this->leadermodel->get_by_merc($page['game']->game_id, $merc->name);
        
        // Game message
        game_message($page['game']->game_id, 'The Mercenary Unit '.$merc->name.' are up for bid!');
        
        // Email other players that action is required
        email_game($page['game']->game_id, 'The contract for Mercenary unit '.$merc->name.' is up for bid in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$page['game']->game_id.'">'.
                $page['game']->title.
                '</a>.');
        
        // Automatically set the bid of any defeated players
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($page['game']->game_id);
        foreach($players as $p)
        {
            if ($p->turn_order == 0)
                merc_bid($p, $merc, 0, false);
            else  // set a null bid
                merc_bid($p, $merc, NULL, false);
        }
        
        if ($this->debug > 3) log_message('error', 'merc_phase/'.$page['game']->game_id.' DONE');
        
    }  // end merc_phase
    
    private function check($game_id)
    {
        if ($this->debug>2) log_message('error', 'private function check($game_id='.$game_id.')');
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'No such game!';
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
        if ( count($page['game']) != 1 )
        {
            $page['error'] = 'No such game!';
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
            $page['error'] = 'Not playing in that game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        return $page;
    }
    
    /**
     * Toggle weather or not to use the force size modifier
     */
    function toggle_factory_dmg_mod($territory_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if($territory_id==0)
        {
            $page['error'] = 'No such territory.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the territory, must exist
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($territory_id);
        if ( !isset($territory->territory_id) )
        {
            $page['error'] = 'No such territory.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must have a factory
        $this->load->model('factorymodel');
        $factory = $this->factorymodel->get_by_location($territory_id);
        if (!isset($factory->factory_id))
        {
            $page['error'] = 'No such factory.';
            $this->load->view('templatexml', $page);
            return;
        } 
        
        // Must be playing in the game
        $this->load->model('gamemodel');
        $this->load->model('playermodel');
        
        $game = $this->gamemodel->get_by_id($territory->game_id);
        
        if (!isset($game->game_id))
        {
            $page['error'] = 'Something bad happened!';
            $this->load->view('templatexml', $page);
            return;
        }
        $players = $this->playermodel->get_by_game($game->game_id);
        $page['is_playing'] = false;

        foreach( $players as $p )
        {
            if ( $p->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $player = $p;
            }
        }        
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'Not playing in that game!';
            $this->load->view('templatexml', $page);
            return;
        }
        // Must be allowed in the game
        if ($game->auto_factory_dmg_mod)
        {
            $page['error'] = 'Game does not allow toggling the factory damage modifier!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must have a valid combat log with a non null force size
        $this->load->model('combatlogmodel');
        $combatlog = $this->combatlogmodel->get_by_player_territory($player->player_id, $territory_id);
        if (!isset($combatlog->force_size) || $combatlog->force_size == null)
        {
            $page['error'] = 'Error!';
            $this->load->view('templatexml', $page);
            return;
        }

        // Away we go!
        $combatlogupdate = new stdClass();
        $combatlogupdate->use_force_size = !$combatlog->use_force_size;
        $this->combatlogmodel->update($combatlog->combatlog_id, $combatlogupdate);

        $this->page['notice'] = 'Factory Damage Roll Toggled!';
        $this->viewcombat($territory_id);
    }
}