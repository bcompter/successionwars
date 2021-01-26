<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('kill_unit'))
{
    // Requires that combatunitmodel be loaded before calling
    function kill_unit($combatunit)
    {
        $CI =& get_instance();
        $CI->db->trans_start();

        // Die die die!!!
        if ( $combatunit->is_conventional || !$combatunit->can_rebuild  || $combatunit->is_elemental)
        {
             $CI->combatunitmodel->delete($combatunit->combatunit_id);
        }
        else
        {
             $unit = new stdClass();
             $unit->combatunit_id = $combatunit->combatunit_id;
             $unit->strength = 0;
             $unit->die = 0;
             $unit->location_id = null;
             $unit->last_roll = 0;
             $unit->combine_with = null;
             $unit->was_loaded = 0;
             $unit->is_rebuild = true;
             $unit->target_id = null;
             $unit->combo_broken = 0;

             $CI->combatunitmodel->update($unit->combatunit_id, $unit);
        }
        
        // Delete combination if any
        if (isset($combatunit->combine_with))
        {
            $combo = $CI->combatunitmodel->get_by_id($combatunit->combine_with);
            if (isset($combo->combatunit_id))
            {
                $comboupdate = new stdClass();
                $comboupdate->combatunit_id = $combo->combatunit_id;
                $comboupdate->combine_with = null;
                $CI->combatunitmodel->update($combo->combatunit_id, $comboupdate);
            }
        }
        
        // Delete any leader bonuses
        $CI->load->model('combatbonusmodel');
        $CI->load->model('leadermodel');
        $bonuses = $CI->combatbonusmodel->get_by_unit($combatunit->combatunit_id);
        foreach($bonuses as $bonus)
        {
            if ($bonus->source_type == 1)
            {
                $CI->combatbonusmodel->delete($bonus->combatbonus_id);
                $leader = $CI->leadermodel->get_by_id($bonus->source_id);
                $leaderupdate = new stdClass();
                $leaderupdate->leader_id = $leader->leader_id;
                $leaderupdate->combat_used = false;
                $CI->leadermodel->update($leaderupdate->leader_id, $leaderupdate);
            }
        }
        
        $CI->db->trans_complete();
        
    }  // end kill unit
}  // end kill unit

if ( ! function_exists('factory_modifier'))
{
    function factory_modifier($force_size)
    {
        if ( $force_size < 6 )
            return 0;
        else if ( $force_size < 11 )
            return 1;
        else if ( $force_size < 16 )
            return 2;
        else if ( $force_size < 21 )
            return 3;
        else
            return 4;
    }
}

if ( ! function_exists('generate_combat_logs') )
{
    /**
     * Generate combat logs for a given territory
     * 
     * @param type $territory A territory object
     * @param type $game A game object
     */
    function generate_combat_logs($territory, $game)
    {
        $CI =& get_instance();
        if ($CI->debug>2) log_message('error', 'Generating combat logs! Game Id: '.$game->game_id.' Territory Id: '.$territory->territory_id);
        
        // Make a combat log for each unique player in the battle
        $CI->load->model('combatunitmodel');
        $units = $CI->combatunitmodel->get_by_location($territory->territory_id);
        $numfactions = 0;
        foreach($units as $unit)
        {
            if (!isset($combatlogs[$unit->owner_id]->player_id))
            {
                $combatlogs[$unit->owner_id] = new stdClass();
                $combatlogs[$unit->owner_id]->player_id = $unit->owner_id;
                $combatlogs[$unit->owner_id]->territory_id = $territory->territory_id;
                $combatlogs[$unit->owner_id]->game_id = $game->game_id;
                $numfactions++;

                // if the current player is the attacker, set force_size
                if ( $territory->player_id != $unit->owner_id )
                {
                    $combatlogs[$unit->owner_id]->force_size = 0;
                }
            }

            // Plan for automagic target assignment
            // Sort of a hack, but gets the job done
            if (!isset( $factions[0] ))
                $factions[0] = $unit->owner_id;
            else if ( $factions[0] != $unit->owner_id )
                $factions[1] = $unit->owner_id;                    
        }

        // Create combat logs
        $CI->load->model('combatlogmodel');

        if (!isset($combatlogs))    // nocombat units found to load $combatlogs[] array
        {
            // At least set a combat log for the owner
            $new_combat_log = new stdClass();
            $new_combat_log->player_id = $territory->player_id;
            $new_combat_log->territory_id = $territory->territory_id;
            $new_combat_log->game_id = $game->game_id;
            $CI->combatlogmodel->create($new_combat_log);
            if ($CI->debug>2) log_message('error', 'No combat units in this territory.  Skipping combat log creation. t_id='.$territory->territory_id);
            return;
        }
        
        foreach( $combatlogs as $log )
        {
            // Must add in the total force size for the attacking/defending force
            // This will be used later on to determine the force size
            // modifier for factory damage rolls
            if ( isset( $log->force_size ) )
            {
                $log->force_size = $CI->combatunitmodel->get_force_size($log->player_id, $log->territory_id)->strength;
            }
            
            // No duplicate logs
            unset($existinglog);
            $existinglog = $CI->combatlogmodel->get_by_player_territory($log->player_id,$log->territory_id);
            if (!isset($existinglog->combatlog_id))
            {
                // Create the completed combat log
                if ($CI->debug>2) log_message('error', 'creating a combat log...player id = '.$log->player_id.', territory = '.$log->territory_id);
                
                // Don't assign a force size if there is no defender
                if ($numfactions == 1)
                    unset($log->force_size);
                
                $CI->combatlogmodel->create($log);
            }
            else
            {
                if ($CI->debug>2) log_message('error', 'Duplicate combat log!  Skipping for player ID: '.$log->player_id);
            }
            
            // Set players to not done...
            $CI->db->query('update players set combat_done=false where game_id='.$game->game_id.' and turn_order != 0');
        }

        // Automatically assign targets in the event there are only
        // two factions involved (which is most of the time)
        if ( $numfactions == 2 )
        {
            foreach($units as $unit)
            {
                $unitupdate = new stdClass();
                if ( $unit->owner_id == $factions[0] )
                {
                    $unitupdate->target_id = $factions[1];
                }
                else
                {
                    $unitupdate->target_id = $factions[0];
                }
                $unitupdate->combatunit_id = $unit->combatunit_id;
                $CI->combatunitmodel->update($unit->combatunit_id, $unitupdate);    
            }
        }
        else if ( $numfactions == 1 )
        {
            // There is nobody to defend, assign target accordingly
            foreach($units as $unit)
            {
                $unitupdate = new stdClass();
                $unitupdate->target_id = 0;
                $unitupdate->combatunit_id = $unit->combatunit_id;
                $CI->combatunitmodel->update($unit->combatunit_id, $unitupdate);
            }
        }
        else
        {
            // PANIC!!!
            if ($CI->debug>2) log_message('error', 'More than two factions in combat!!!');
        }
    }
}

if ( ! function_exists('generate_combat_logs_by_territory_id') )
{
    /**
     * Generate combat logs for a given territory
     * 
     * @param type $territory_id A territory's ID
     * @param type $game A game object
     */
    function generate_combat_logs_by_territory_id($territory_id, $game)
    {
        $CI =& get_instance();
        if ($CI->debug>2) log_message('error', 'Generating combat logs! Game Id: '.$game->game_id.' Territory Id: '.$territory->territory_id);
     
        // Make a combat log for each unique player in the battle
        $CI->load->model('combatunitmodel');
        // Get existing logs to avoid duplicates
        $houses = $CI->combatunitmodel->get_houses_by_location($territory_id);
        
        foreach($houses as $house)
        {
            if (!isset($combatlogs[$house->owner_id]->player_id))
            {
                $combatlogs[$unit->owner_id] = new stdClass();
                $combatlogs[$unit->owner_id]->player_id = $unit->owner_id;
                $combatlogs[$unit->owner_id]->territory_id = $territory_id;
                $combatlogs[$unit->owner_id]->game_id = $game->game_id;
                $numfactions++;

                // if the current player is the attacker, set force_size
                if ( $territory->player_id != $unit->owner_id )
                {
                    $combatlogs[$unit->owner_id]->force_size = 0;
                }
            }

            // Plan for automagic target assignment
            // Sort of a hack, but gets the job done
            if (!isset( $factions[0] ))
                $factions[0] = $unit->owner_id;
            else if ( $factions[0] != $unit->owner_id )
                $factions[1] = $unit->owner_id;                    
        }

        // Create combat logs
        $CI->load->model('combatlogmodel');

        if (!isset($combatlogs))
        {
            if ($CI->debug>2) log_message('error', 'No combat units in this territory.  Skipping combat log creation. t_id='.$territory->territory_id);
            return;
        }
        
        foreach( $combatlogs as $log )
        {
            // Must add in the total force size for the attacking/defending force
            // This will be used later on to determine the force size
            // modifier for factory damage rolls
            if ( isset( $log->force_size ) )
            {
                $log->force_size = $CI->combatunitmodel->get_force_size($log->player_id, $log->territory_id)->strength;
            }
            
            // No duplicate logs
            unset($existinglog);
            $existinglog = $CI->combatlogmodel->get_by_player_territory($log->player_id,$log->territory_id);
            if (!isset($existinglog->combatlog_id))
            {
                // Create the completed combat log
                if ($CI->debug>2) log_message('error', 'creating a combat log...player id = '.$log->player_id.', territory = '.$log->territory_id);
                
                // Don't assign a force size if there is no defender
                if ($numfactions == 1)
                    unset($log->force_size);
                
                $CI->combatlogmodel->create($log);
            }
            else
            {
                if ($CI->debug>2) log_message('error', 'Duplicate combat log!  Skipping for player ID: '.$log->player_id);
            }
            
            // Set players to not done...
            $CI->db->query('update players set combat_done=false where game_id='.$game->game_id.' and turn_order != 0');
        }

        // Automatically assign targets in the event there are only
        // two factions involved (which is most of the time)
        if ( $numfactions == 2 )
        {
            foreach($units as $unit)
            {
                $unitupdate = new stdClass();
                if ( $unit->owner_id == $factions[0] )
                {
                    $unitupdate->target_id = $factions[1];
                }
                else
                {
                    $unitupdate->target_id = $factions[0];
                }
                $unitupdate->combatunit_id = $unit->combatunit_id;
                $CI->combatunitmodel->update($unit->combatunit_id, $unitupdate);    
            }
        }
        else if ( $numfactions == 1 )
        {
            // There is nobody to defend, assign target accordingly
            foreach($units as $unit)
            {
                $unitupdate = new stdClass();
                $unitupdate->target_id = 0;
                $unitupdate->combatunit_id = $unit->combatunit_id;
                $CI->combatunitmodel->update($unit->combatunit_id, $unitupdate);
            }
        }
        else
        {
            // PANIC!!!
            if ($CI->debug>2) log_message('error', 'More than two factions in combat!!!');
        }
    }
}

if ( ! function_exists('cancel_leaders_combat_and_military_abilities'))
{
    // Requires that combatunitmodel be loaded before calling
    function cancel_leaders_combat_and_military_abilities($leader_id)
    {
        $CI =& get_instance();

        // Cancel all of this leaders combat bonuses
        $CI->load->model('combatbonusmodel');
        $bonus = $CI->combatbonusmodel->get_by_leader( $leader_id );
        if (count($bonus)>0)
            $CI->combatbonusmodel->delete( $bonus->combatbonus_id );
        
        // Cancel all of this leaders military combinations
        $CI->load->model('combatunitmodel');
        $combos = $CI->combatunitmodel->get_by_leader_combined( $leader_id );
        foreach( $combos as $c )
        {
            $c->combined_by = null;
            $c->combine_with = null;
            $CI->combatunitmodel->update($c->combatunit_id, $c);
        }
        
        // Update the leader's data
        $leaderupdate = new stdClass();
        $leaderupdate->military_used = 0;
        $leaderupdate->combat_used = 0;
        $CI->load->model('leadermodel');        
        $CI->leadermodel->update($leader_id, $leaderupdate);     
    }
}

if ( ! function_exists('capture_leader'))
{
    // Requires that combatunitmodel be loaded before calling
    function capture_leader($leader_id, $new_controlling_house_id)
    {
        $CI =& get_instance();
        if ($CI->debug>2) log_message('error', 'capture_leader($leader_id='.$leader_id.', $new_controlling_house_id='.$new_controlling_house_id.')');         
     
        $CI->load->model('leadermodel');
        $leader = $CI->leadermodel->get_by_id($leader_id);  
     
        $CI->load->model('playermodel');
        $house = $CI->playermodel->get_by_id($leader->controlling_house_id);
        $new_controlling_house = $CI->playermodel->get_by_id($new_controlling_house_id);
   
        $leaderupdate = new stdClass();
        $leaderupdate->leader_id = $leader->leader_id;
        $leaderupdate->loaded_in_id=null;
        $leaderupdate->was_loaded=0;
        $leaderupdate->controlling_house_id = $new_controlling_house->player_id;
        $leaderupdate->just_bribed=0;
        $leaderupdate->official_leader=0;
        $CI->leadermodel->update($leader->leader_id, $leaderupdate);
        if ($CI->debug>2) log_message('error', $new_controlling_house->player_id.' captured '.($leader->associated_units!=NULL?'*':'').$leader->name.' in '.$leader->location_id);
        game_message($leader->game_id, $new_controlling_house->faction.' captured '.($leader->associated_units!=NULL?'*':'').$leader->name.' in '.$leader->territory_name.'.');
        
        cancel_leaders_combat_and_military_abilities($leader_id);
        
        // Ignore the magic jumpship if we are talking about Comstar
        if ($new_controlling_house->player_id != 0)
            magic_jumpship($leader, $new_controlling_house->player_id);
        
        // Check for house elimination
        $playerToCheck = $CI->playermodel->get_by_id($leader->original_house_id);
        if (isset($playerToCheck->player_id))      // Don't bother checking merc leaders...
            check_house_capture($playerToCheck);
    }
}

if ( ! function_exists('leader_bribed'))
{
    // Requires that combatunitmodel be loaded before calling
    function leader_bribed($leader_id, $new_controlling_house_id)
    {
        $CI =& get_instance();
        if ($CI->debug>2) log_message('error', 'leader_bribed($leader_id='.$leader_id.', $new_controlling_house_id='.$new_controlling_house_id.')');
        
        $CI->load->model('leadermodel');
        $leader = $CI->leadermodel->get_by_id($leader_id);  
        
        $CI->load->model('playermodel');
        $house = $CI->playermodel->get_by_id($new_controlling_house_id);        
        
        $leaderupdate = new stdClass();
        $leaderupdate->leader_id = $leader->leader_id;
        $leaderupdate->admin=min(-1*$leader->admin,$leader->admin);
        $leaderupdate->loaded_in_id=null;
        $leaderupdate->was_loaded=0;
        $leaderupdate->controlling_house_id = $new_controlling_house_id;        
        if ($leader->original_house_id == null) // Check if a Merc leader
        {
            $leaderupdate->allegiance_to_house_id = $new_controlling_house_id;
        }
        else
        {
            $leaderupdate->allegiance_to_house_id = null;
            $leaderupdate->just_bribed=1; 
        }
        
        $leaderupdate->official_leader=0;
        
        // Merc units switch sides when their leader is bribed
        // House units only switch when their house is taken over by a single other house
        if(isset($leader->associated_units) && $leader->original_house_id==null)
        {
            // find all units under this leaders control and turn them as well
            $CI->load->model('combatunitmodel');
            $units = $CI->combatunitmodel->get_by_leader( $leader->game_id, $leader->associated_units );
            if ($CI->debug>2) log_message('error', 'Leader '.$leader->leader_id.' in game '.($leader->associated_units!=NULL?'*':'').$leader->game_id.' has '.count($units).' associated units named '.$leader->associated_units.'.');
            $t_to_contest;
            foreach( $units as $unit )
            {
                if ($CI->debug>2) log_message('error', 'Combat unit '.$unit->combatunit_id.' switching sides');
                $unitupdate = new stdClass();
                $unitupdate->combatunit_id = $unit->combatunit_id;
                $unitupdate->owner_id = $new_controlling_house_id;
                $CI->combatunitmodel->update( $unit->combatunit_id, $unitupdate );

                // if combat phase make combat logs, otherwise contest territories as needed
                if ($unit->strength > 0 && !$unit->die)
                {
                    if (!isset($t_to_contest[$unit->location_id]))
                        $t_to_contest[$unit->location_id] = $unit->location_id;
                }
            }
            $CI->load->model('territorymodel');
            if (isset($t_to_contest) && count($t_to_contest) > 0)
            {
                foreach($t_to_contest as $t_id)
                {
                    // Grab territory
                    $t = $CI->territorymodel->get_by_id($t_id);

                    // Contest
                    if (!$t->is_contested)
                    {
                        $tu = new stdClass();
                        $tu->territory_id = $t_id;
                        $tu->is_contested = 1;
                        $CI->territorymodel->update($t_id, $tu);
                    }

                    // If combat phase generate combat logs
                    $CI->load->model('gamemodel');
                    $game = $CI->gamemodel->get_by_id($leader->game_id);
                    if ($game->phase == 'Combat')
                    {
                        generate_combat_logs($t, $game);
                    }
                }  // end foreach($t_to_contest as $t_id)
                
            }  // end if (isset($t_to_contest) && count($t_to_contest) > 0)
            
        }  // end if(isset($leader->associated_units) && $leader->original_house_id==null)
        
        // Update leader data
        $CI->leadermodel->update($leader->leader_id, $leaderupdate);
        if ($CI->debug>2) log_message('error', $house->player_id.' bribed '.($leader->associated_units!=NULL?'*':'').$leader->name.' in '.$leader->location_id);
        
        cancel_leaders_combat_and_military_abilities($leader->leader_id);

        // Check for house elimination
        $playerToCheck = $CI->playermodel->get_by_id($leader->original_house_id);
        check_house_capture($playerToCheck);
        
    }  // end leader_bribed
}

if ( ! function_exists('magic_jumpship'))
{
    /**
     * Send leaders on the magic jumpship to a capturing players current valid capital
     * 
     * @param type $leader A leader object
     * @param type $player_id The player_id of the capturing player
     */
    function magic_jumpship($leader, $player_id)
    {
        $CI =& get_instance();
        
        $CI->load->model('territorymodel');
        $capital = $CI->territorymodel->get_capital($player_id);
        if (!isset($capital->territory_id))
        {
            //if ($this->debug > 3) log_message('error', 'No capital in magic_jumpship!');
            return;
        }
        
        $CI->load->model('leadermodel');
        $lu = new stdClass();
        $lu->leader_id = $leader->leader_id;
        $lu->location_id = $capital->territory_id;
        $CI->leadermodel->update($leader->leader_id, $lu);
        
        update_territory($capital->territory_id);
        update_territory($leader->location_id);
        
        game_message($leader->game_id, ($leader->associated_units!=NULL?'*':'').$leader->name.' has been sent from '.$leader->territory_name.' to '.$capital->name.'.');
    }
}