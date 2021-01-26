<?php

/*
 * Handles leaders
 */

class Leader extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    } 

    /**
     * View a leader.
     * 
     */
    function view($leader_id = 0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $leader_id == 0 )
        {
            $page['error'] = 'No such leader';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the leader
        $this->load->model('leadermodel');
        $page['leader'] = $this->leadermodel->get_by_id($leader_id);
        
        // Leader must exist
        if( !isset($page['leader']->leader_id) )
        {
            $page['error'] = 'No such leader';
            $this->load->view('templatexml', $page);
            return;    
        }
        
        // Also load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['leader']->game_id);
        
        // Fetch the requesting player
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($page['game']->game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( isset($player->user_id) && $player->user_id == $this->ion_auth->get_user()->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
        }
        
        // Must be playing in the game
        if( !isset($page['is_playing'] ))
        {
            $page['error'] = 'You are not playing in this game.';
            $this->load->view('templatexml', $page);
            return;    
        }

        // Load Tech targets
        $this->load->model('combatunitmodel');        
        if ($page['leader']->just_bribed==1) 
        {
            if ($page['player']->tech_level < 25)
                $page['targets'] = $this->combatunitmodel->get_by_location_player($page['leader']->location_id,$page['leader']->original_house_id);
            else
                $page['targets'] = $this->combatunitmodel->get_by_player($page['leader']->original_house_id);            
        }
        else
        {
            if (isset($page['player']->tech_level) && $page['player']->tech_level < 25)
                $page['targets'] = $this->combatunitmodel->get_by_location_player($page['leader']->location_id,$page['leader']->controlling_house_id);
            else
                $page['targets'] = $this->combatunitmodel->get_by_player($page['leader']->controlling_house_id);
        }
        
        // Does the page viewer have units present with the leader?
        if (isset($page['player']->player_id))
        {
            $page['viewers_units'] = $this->combatunitmodel->get_by_location_player($page['leader']->location_id,$page['player']->player_id);
            if (count($page['viewers_units']) > 0)
                $page['player_has_units_present'] = true;
            else
                $page['player_has_units_present'] = false;
        }
        else
        {
            $page['player_has_units_present'] = false;
        }
        
        // Fetch original house if not a 'Merc
        if ($page['leader']->original_house_id != null)
        {
            $page['original_house'] = $this->playermodel->get_by_id($page['leader']->original_house_id);
        }
        $page['controlling_house'] = $this->playermodel->get_by_id($page['leader']->controlling_house_id);
        $page['allegiance_to_house'] = $this->playermodel->get_by_id($page['leader']->allegiance_to_house_id);
        
        // Fetch this players cards...
        if (isset($page['player']->player_id))
        {
            $this->load->model('cardmodel');
            $page['cards'] = $this->cardmodel->get_by_player($page['player']->player_id);
        }
        
        // Fetch combat bonuses
        $this->load->model('combatbonusmodel');
        $page['bonus'] = $this->combatbonusmodel->get_by_leader($page['leader']->leader_id);
        
        // Fetch associated units
        if ($this->debug>2) log_message('error', 'Fetch associated units "'.$page['leader']->associated_units.'"');
        if ($page['leader']->associated_units !='')
        {
            $page['associated_units'] = $this->combatunitmodel->get_by_leader( $page['leader']->game_id, $page['leader']->associated_units );
            if ($this->debug>2) log_message('error', 'Count($page[\'associated_units\'])='.count($page['associated_units']));
        }
        
        // Fetch combat log if there is combat in this leaders territory
        $this->load->model('combatlogmodel');
        $combatlog = $this->combatlogmodel->get_by_territory($page['leader']->location_id);
        if (count($combatlog) > 0)
            $page['show_combat_link'] = true;

        // Away we go
        $page['content'] = 'leader';
        $this->load->view('templatexml', $page);
    }
    
    /**
     * Give a combat bonus to a friendly unit
     * 
     * @param type $leader_id
     * @param type $target_id 
     */
    function combat( $leader_id=0, $target_id=0, $is_negative=0 )
    {
        // Make sure ids are provided
        if ( $leader_id == 0 || $target_id == 0 )
        {
            $page['error'] = 'No such leader';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the leader
        $this->load->model('leadermodel');
        $page['leader'] = $this->leadermodel->get_by_id($leader_id);
        $leader = $page['leader'];
        
        // Fetch the target
        $this->load->model('combatunitmodel');
        $page['target'] = $this->combatunitmodel->get_by_id($target_id);
        $target = $page['target'];
        
        // Fetch the player
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['leader']->controlling_house_id);
        $player = $page['player'];
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        
        
        // Must control leader unless he has been bribed
        if ( $page['player']->player_id != $leader->controlling_house_id || 
                $leader->controlling_house_id != $leader->allegiance_to_house_id  )
        {
            if ( !$is_negative )
            {
                $this->page['error'] = 'You can\'t use this leaders abilities.';
                $this->view($page['leader']->leader_id);
                return;
            }
        }
        
        // Leader must not have used his/her bonus already
        if ( $page['leader']->combat_used )
        {
            $this->page['error'] = 'You have already used '.$page['leader']->name.'\'s combat bonus this turn!';
            $this->view($page['leader']->leader_id);
            return;
        }
                 
        // Target and leader are in the same location
        // Unless the player is at 25 tech level
        if ( $page['leader']->location_id != $page['target']->location_id )
        {
            if ($page['player']->tech_level < 25)
            {
                $this->page['error'] = 'Leader and target are not in the same location!';
                $this->view($page['leader']->leader_id);
                return;
            }
        }
        
        $this->load->model('territorymodel');
        $page['location'] = $this->territorymodel->get_by_id($page['target']->location_id);
        
        // Target must be alive
        if ( $page['target']->strength == 0 )
        {
            $this->page['error'] = 'That combat unit is destroyed!';
            $this->view($page['leader']->leader_id);
            return;
        }
        
        // Target and leader are on the same team, unless 
        if ( $page['target']->owner_id != $page['leader']->controlling_house_id )
        {
            if ( !$is_negative )
            {
                $this->page['error'] = 'Leader Combat unit mismatch!';
                $this->view($page['leader']->leader_id);
                return;
            }
        }
        
        // Combat phase only
        if ( $page['game']->phase != 'Combat' )
        {
            $this->page['error'] = 'You can only do that during the combat phase!';
            $this->view($page['leader']->leader_id);
            return;
        }
        
        // If a negative bonus, needs to have been bribed, and lots more
        $modifier = 1;
        if ( $is_negative == 1 )
        {
            // Must be just bribed and 
            if ( $player->player_id != $leader->controlling_house_id 
                    || $leader->just_bribed != 1 || $target->owner_id != $leader->original_house_id) 
            {
                $this->page['error'] = 'You can\'t use a negative combat bonus that way!  Think of the children!!!';
                $this->view($page['leader']->leader_id);
                return;
            }
            $modifier = -1;
            
            
        }
        else
        {
            // must own the target and the leader
            if ( $target->owner_id != $leader->controlling_house_id )
            {
                $this->page['error'] = 'You don\'t own that combat unit!';
                $this->view($page['leader']->leader_id);
                return;
            }
        }
            
        // Away we go!
        $leaderupdate = new stdClass();
        $leaderupdate->leader_id = $page['leader']->leader_id;
        $leaderupdate->combat_used = true;
        $this->leadermodel->update($leader_id, $leaderupdate);
        
        $this->load->model('combatbonusmodel');
        $bonus = new stdClass();
        $bonus->game_id = $page['game']->game_id;
        $bonus->value = $page['leader']->combat * $modifier;
        $bonus->ttl = 1;
        $bonus->combatunit_id = $target_id;
        $bonus->source_id = $leader_id;
        $bonus->source_type = 1;
        $this->combatbonusmodel->create($bonus);
        
        if ( $is_negative )
        {
            game_message($page['game']->game_id, $page['player']->faction.' uses the traitor '.$page['leader']->name.' to give a negative combat bonus to '.$page['target']->name.' '.$page['target']->strength.' on '.$page['location']->name.'.');
        }
        else
        {
            game_message($page['game']->game_id, $page['player']->faction.' uses '.$page['leader']->name.' to give a combat bonus to '.$page['target']->name.' '.$page['target']->strength.' on '.$page['location']->name.'.');
        }
        // Back to the leader view
        $this->page['notice'] = 'Combat bonus given to '.$page['target']->name.' '.$page['target']->strength.' on '.$page['location']->name.'.';
        $this->view($leader_id);
    }
    
    /**
     * Cancel a combat bonus to a friendly unit
     */
    function cancel($leader_id=0)
    {
        // Make sure a target is given
        if ($leader_id == 0)
        {
            $page['error'] = 'No such leader';
            $this->load->view('templatexml', $page);
            return;    
        }
        
        // Fetch the leader
        $this->load->model('leadermodel');
        $leader = $this->leadermodel->get_by_id($leader_id);
        
        // Fetch the bonus
        $this->load->model('combatbonusmodel');
        $bonus = $this->combatbonusmodel->get_by_leader($leader_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($leader->game_id);
        
        // Bonus must exist
        if (!isset($bonus->combatbonus_id))
        {
            $page['error'] = 'Could not find what you were looking for.';
            $this->load->view('templatexml', $page);
            return;  
        }
        
        // Fetch the player
        $this->load->model('playermodel');
        $player= $this->playermodel->get_by_id($leader->controlling_house_id);
        
        
        // Current user must own the player
        // Leader is owned by the current player
        if ( $player->user_id != $this->ion_auth->get_user()->id )
        {
            $this->page['error'] = 'You do not own that leader!';
            $this->view($leader_id);
            return;
        }
        
        // Away we go...
        
        $this->combatbonusmodel->delete($bonus->combatbonus_id);
        $leaderupdate = new stdClass();
        $leaderupdate->leader_id = $leader_id;
        $leaderupdate->combat_used = false;
        $this->leadermodel->update($leader_id, $leaderupdate);
        
        $this->page['notice'] = 'Combat bonus canceled.';
        game_message($game->game_id, $player->faction.' cancels '.$leader->name.'\'s combat bonus.');

        $this->view($leader_id);
    }
    
    /**
     * Bribe an oppossing leader
     * @param type $leader_id
     * @param type $card_id The bribe card being played if applicable
     * @param type $card2_id The blackmail card being played if applicable
     * @param type $free If this bribes uses a free bribe
     */
    function bribe($leader_id=0, $card_id=0, $card2_id=0, $free=0)
    {
        $page = $this->page;
        
        // Make sure a target is given
        if ($leader_id == 0)
        {
            $page['error'] = 'No such leader';
            $this->load->view('templatexml', $page);
            return;    
        }
        
        // Fetch target
        $this->load->model('leadermodel');
        $target = $this->leadermodel->get_by_id($leader_id);
        if (!isset($target->leader_id))
        {
            $page['error'] = 'No such leader';
            $this->load->view('templatexml', $page);
            return;    
        }
        if($target->loyalty == 0 || $target->official_leader)
        {
            $this->page['error'] = 'This leader cannot be bribed.';
            $this->view($leader_id);
            return;    
        }
        
        // Fetch the owner of the target
        $this->load->model('playermodel');
        $owner = $this->playermodel->get_by_id($target->controlling_house_id);
        
        // Fetch the user's player in the game
        $players = $this->playermodel->get_by_game($owner->game_id);
        $attacker = new stdClass();
        foreach($players as $p)
        {
            if ( $p->user_id == $page['user']->id )
                $attacker = $p;
        }
        if (!isset($attacker->player_id))
        {
            $this->page['error'] = 'You are not playing in the same game as the target leader.';
            $this->view($leader_id);
            return; 
        }
        
        if ( $owner->player_id != $target->controlling_house_id || 
                $target->controlling_house_id != $target->allegiance_to_house_id )
        {
            $this->page['error'] = 'You can\'t bribe that leader.';
            $this->view($leader_id);
            return; 
        }
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($owner->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->load->model('cardmodel');
        if ($card_id != 0)
        {
            // fetch and validate the bribe card
            $bribecard = $this->cardmodel->get_by_id($card_id);
            if ($bribecard->owner_id != $attacker->player_id)
            {
                unset($bribecard);
            }
        }
        if ($card2_id != 0)
        {
            // fetch and validate the blackmail card
            $blackmailcard = $this->cardmodel->get_by_id($card2_id);
            if ($blackmailcard->owner_id != $attacker->player_id)
            {
                unset($blackmailcard);
            }
        }
        
        if ( $free )
        {
            // Check to see if this player has an available attempt
            if ($attacker->free_bribes < 1)
            {
                $free = 0;
            }
        }
        
        // Away we go
        if (!isset($bribecard) && !$free)
        {
            //Pay for it...
            if ($attacker->money < 10)
            {
                $this->page['error'] = 'You cannot afford it.';
                $this->view($leader_id);
                return; 
            }
            else
            {
                $playerupdate = new stdClass();
                $playerupdate->player_id = $attacker->player_id;
                $playerupdate->money = $attacker->money - 10;
                $this->playermodel->update($attacker->player_id, $playerupdate);
            }
        }
        else if (isset($bribecard))
        {
            game_message($attacker->game_id, $attacker->faction.' uses a bribery card!');
            discard($bribecard);
        }
        
        if ( $free )
        {
            unset($playerupdate);
            $playerupdate = new stdClass();
            $playerupdate->player_id = $attacker->player_id;
            $playerupdate->free_bribes = $attacker->free_bribes - 1;
                        
            game_message($attacker->game_id, $attacker->faction.' uses a free bribe attempt!');
            $this->playermodel->update($attacker->player_id, $playerupdate);
        }
        
        $roll = roll_dice(1,10);
        if (isset($blackmailcard))
        {
            $roll += 2;
            discard($blackmailcard);
            game_message($attacker->game_id, $attacker->faction.' uses a blackmail card!');
        }

        if ( $roll > $target->loyalty )
        {
            // Set as bribed
            leader_bribed($target->leader_id,$attacker->player_id);
            $this->page['notice'] = 'Bribe is successful!';
            if ($this->debug > 2) log_message('error', 'player '.$attacker->player_id.' bribes leader '.$target->leader_id);            
            game_message($attacker->game_id, $attacker->faction.' attempts to bribe '.$target->name.' and succeeds! '.$target->name.' switches loyalty!');  
        }
        else
        {
            $this->page['notice'] = 'Bribe failed!';
            game_message($attacker->game_id, $attacker->faction.' attempts to bribe '.$target->name.' and fails! '.$target->name.' remains loyal!');
            // Pay bribe to controller instead
            if ( !isset( $bribecard ) && !$free )
            {
                $playerupdate->player_id = $owner->player_id;
                $playerupdate->money = $owner->money + 10;
                $this->playermodel->update($owner->player_id, $playerupdate);
                game_message($attacker->game_id, $target->name.' donates the 10M C-Bill bribe to their house treasury.');
            }
        }

        // Load leader view
        $this->view($leader_id);
        
    }  // end bribe
    
    /**
     * Turn over control of a captured or bribed leader to the specified player
     * The leader will be transported to the receivers official capital
     * @param type $leader_id The leader to ransom
     * @param type $player_id The receiving player
     */
    function ransom($leader_id = 0, $player_id = 0)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($leader_id == 0 )
        {
            $page['error'] = 'Error! Cannot ransom a leader without proper ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->load->model('leadermodel');
        $this->load->model('playermodel');
        $leader = $this->leadermodel->get_by_id($leader_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($leader->game_id);
        
        // User must have a player in this game
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game->game_id);
        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
                $player = $p;
        }
        if (!isset($player->player_id))
        {
            $page['error'] = 'Error! Cannot ransom a leader of a game you are not playing in.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        if ($game->player_id_playing != $player->player_id)
        {
            $this->page['error'] = 'You cannot ransom a leader to another house unless it is your turn.';
            $this->view($leader_id);
            return;
        }
        
        if ( $player_id == 0 )
        {
            // Load available targets and show dialog
            $page['players'] = $players;
            $page['player'] = $player;
            $page['leader'] = $leader;
            $page['content'] = 'ransom';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the leader and target player
        
        $receiver = $this->playermodel->get_by_id($player_id);
        if (!isset($leader->leader_id))
        {
            $page['error'] = 'Error! Cannot ransom a leader that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        if (!isset($receiver->player_id))
        {
            $page['error'] = 'Error! Cannot ransom a leader to a player that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Leader must be captured
        if ( $leader->controlling_house_id == $leader->allegiance_to_house_id)
        {
            $this->page['error'] = 'Error! Cannot ransom a leader that was not captured.';
            $this->view($leader_id);
            return;
        }
        
        // Receiver must not be eliminated or is marked to be eliminated
        if ($receiver->turn_order == 0 || $receiver->eliminate)
        {
            $this->page['error'] = 'Error! Cannot ransom a leader to an eliminated player.';
            $this->view($leader_id);
            return;
        }
        
        // Away we go
        game_message($game->game_id, $player->faction.' ransomed '.($leader->associated_units!=NULL?'*':'').$leader->name.' to '.$receiver->faction.'.');
        
        // Perform swap...
        $leaderupdate = new stdClass();
        $leaderupdate->leader_id = $leader_id;        
        $leaderupdate->controlling_house_id = $receiver->player_id;
        $this->leadermodel->update($leader_id, $leaderupdate);
        
        // Send to correct territory
        magic_jumpship($leader, $receiver->player_id);
        
        // Set the recipient house to not done if they are the original_house
        // Prevent giving a leader back to a house right before combat ends without them having the chance to execute the leader
        if ($game->phase=='Combat' && $game->player_id_playing==$receiver->player_id && $receiver->combat_done==1)
        {
            $playerupdate = new stdClass();
            $playerupdate->player_id = $receiver->player_id;
            $playerupdate->combat_done = 0;
            $this->load->model('playermodel');
            $this->playermodel->update($playerupdate->player_id, $playerupdate);
        }
        
        // Check for house elimination
        $playerToCheck = $this->playermodel->get_by_id($leader->original_house_id);
        check_house_capture($playerToCheck);
        
        $this->page['notice'] = 'Leader has been given to '.$receiver->faction.'.';
        $this->view($leader_id);
        
    }  // end ransom
    
    /**
     * Execute a leader
     * 
     * Applies to captured leaders or in certain circumstances those in combat
     * who have been recently bribed
     * 
     * @param type $leader_id The leader to execute
     */
    function execute($leader_id)
    {
        $page = $this->page;
        
        // Make sure ids are provided
        if ($leader_id == 0 )
        {
            $page['error'] = 'Error! Cannot execute a leader without a leader id provided.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the leader
        $this->load->model('leadermodel');
        $page['leader'] = $this->leadermodel->get_by_id($leader_id);
        
        // Must exist
        if( !isset($page['leader']->leader_id) )
        {
            $page['error'] = 'Error! Cannot execute a leader that is not found.';
            $this->load->view('templatexml', $page);
            return;    
        }
        
        // Also load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['leader']->game_id);
        
        // Fetch the requesting player
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
        
        // Must be playing in the game
        if( !isset($page['is_playing'] ))
        {
            $page['error'] = 'Error! Cannot execute a leader in a game you are not playing in.';
            $this->load->view('templatexml', $page);
            return;    
        }
        
        if ($page['leader']->original_house_id == null)
        {
            $page['error'] = 'Mercenary leaders cannot be executed.';
            $this->load->view('templatexml', $page);
            return;    
        }
        
        // Executioner must have friendly combat units in the region
        $this->load->model('combatunitmodel');
        $units_present = $this->combatunitmodel->get_by_location_player($page['leader']->location_id, $page['player']->player_id);
        if (count($units_present) == 0)
        {
            $page['error'] = 'You have no units present to enforce the execution.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Default for "Can you execute this leader?"
        $canExecute = false; 

        if (    //$player_has_units_present &&                              // You MUST have units present
                $page['leader']->just_bribed != 1                            // NOBODY can execute if JUST bribed
                &&      // AND EITHER
                (       
                    (   // BRIBED
                        $page['leader']->allegiance_to_house_id == null // This needs to be negative insead!!!
                        &&      
                        ( $page['player']->player_id == $page['leader']->original_house_id      // If you are the Original House
                        && $page['player']->player_id != $page['leader']->controlling_house_id  // You no longer control the leader
                        && $page['leader']->just_bribed == 2)                           // Just after being bribed & used against you
                    )                           
                    ||  // OR 
                    (   // You bribed or captured the leader
                        $page['leader']->just_bribed == 0                             // After the bribing dust settles 
                        && $page['player']->player_id != $page['leader']->allegiance_to_house_id
                        && $page['player']->player_id == $page['leader']->controlling_house_id 
                        && $page['game']->combat_rnd == 0
                    )
                )
            )
        {
            $canExecute = true;
        }
        
        if ( ! $canExecute )
        {
            $page['error'] = 'You can\'t execute that leader.';
            $this->load->view('templatexml', $page);
            return;  
        }
        
        // Must have all casualties assigned and have a live unit left to do the killing
        if ( $page['game']->phase == 'Combat' )
        {
            $this->load->model('combatlogmodel');
            $log = $this->combatlogmodel->get_by_player_territory($page['player']->player_id, $page['leader']->location_id);
            if ( $log->casualties_owed > 0 )
            {
                $page['error'] = 'Cannot execute leader until all your casualties are assigned.';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Must not be the last leader a faction owns!
        $numleaders = $this->leadermodel->get_original_by_player($page['leader']->original_house_id);
        if (count($numleaders) == 1 && isset($page['leader']->original_house_id))
        {
            $page['error'] = 'Cannot execute the last leader of a faction.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // away we go!
        $leader = $page['leader'];
        $this->leadermodel->delete($leader_id);
        
        // Check for house elimination
        $playerToCheck = $this->playermodel->get_by_id($leader->original_house_id);
        check_house_capture($playerToCheck);
        
        game_message($page['game']->game_id, ( $page['leader']->just_bribed == 1 ? 'The traitor ' : '' ).$page['leader']->name.' in '.$page['leader']->territory_name.' was executed by '.$page['player']->faction.'.');
        
        $page['notice'] = 'Execution of '.$page['leader']->name.' successful!';
        $this->load->view('templatexml', $page);
        
    }  // end execute
    
}  // end leader