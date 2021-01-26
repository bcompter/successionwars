<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            echo '<h2>'.$leader->name.'</h2>';
            
            if (isset($show_combat_link) && $show_combat_link === true)
                echo anchor('sw/viewcombat/'.$leader->location_id, '<< View '.$leader->territory_name.' Combat', 'class="menu"').'<br />';
            
            $id = $leader->territory_name;
            $id = str_replace('.', '', $id);
            $id = str_replace(' ', '', $id);
            echo 'Location: '.anchor('sw/location/'.$leader->location_id,$leader->territory_name,'class="menu hoverlink" hoverid="'.'#'.$id.'"').'<br />';
            echo '<table>';
            echo '<tr><th>Military</th><th>Combat</th><th>Admin</th><th>Loyalty</th></tr>';
            echo '<tr><td align="center">'.
                    $leader->military.'</td><td align="center">'.
                    $leader->combat.'</td><td align="center">'.
                    $leader->admin.'</td><td align="center">'.
                    ($leader->loyalty == 0 ? '*' : $leader->loyalty).'</td></tr>';
            echo '</table>';
            echo '<br />';
            if ($leader->just_bribed == 1)
                echo 'Just bribed.<br />';
            else if ($leader->just_bribed == 2)
                echo 'Recently bribed.<br />';
            if ($leader->original_house_id == null)
            {
                echo '<h4>Mercenary leader</h4>';
                echo 'Allegiance: '.$allegiance_to_house->faction.'<br />';
                if ($leader->controlling_house_id != $leader->allegiance_to_house_id)
                    echo 'Captured by: '.$controlling_house->faction.'<br />';
            }
            else
            {
                echo '<h4>Faction leader</h4>';
                if ($leader->official_leader)
                    echo 'Official leader (cannot be bribed)<br />';
                if ($leader->allegiance_to_house_id != null) // Not Bribed
                    if ($leader->allegiance_to_house_id != $leader->controlling_house_id) // Not Bribed, Captured
                    {
                        echo 'Captured by: '.$controlling_house->faction.'<br />';
                        echo 'Allegiance to: '.$allegiance_to_house->faction.'<br />';
                    }
                    else // Not Bribed, Not Captured
                        echo 'Allegiance to: '.$allegiance_to_house->faction.'<br />';
                else // Bribed
                    if ($leader->allegiance_to_house_id != $leader->original_house_id) // Bribed, Captured
                    {
                        echo 'Controlled by: '.$controlling_house->faction.'<br />';
                        echo 'Bribed away from: '.$original_house->faction.'<br />';
                    }
                    else // Bribed, Not Captured
                    {
                        echo 'Previously bribed away from, but controlled by: '.$controlling_house->faction.'<br />';
                    }
            }
            if ($is_playing)
            {
                if ( $leader->controlling_house_id == $player->player_id && $leader->original_house_id != $player->player_id && $leader->just_bribed == 0)
                    if ($leader->original_house_id == null && $leader->controlling_house_id != $leader->allegiance_to_house_id)
                        echo anchor('leader/ransom/'.$leader->leader_id,'GIVE', 'class="menu"').' this Leader to another Player<br />';
                    else if ($leader->original_house_id != null)
                        echo anchor('leader/ransom/'.$leader->leader_id,'GIVE', 'class="menu"').' this Leader to another Player<br />';
            }
            // Uncomment the following for debugging
            /*if (isset($leader->original_house_id))
                echo 'Original house: '.$leader->original_house_id.' - '.$original_house->faction.'<br />';
            else echo 'Original house: 'Merc<br />';
            echo 'Controlling house: '.$leader->controlling_house_id.' - '.$controlling_house->faction.'<br />';
            if (isset($leader->allegiance_to_house_id))
                echo 'Allegiance to house: '.$leader->allegiance_to_house_id.' - '.$allegiance_to_house->faction.'<br />';
            else echo 'Allegiance to house: null'<br />';
             */
            
            // COMBAT BONUS
            if ( $is_playing && $player->player_id == $leader->controlling_house_id 
                    && $player->player_id == $leader->allegiance_to_house_id  && $leader->just_bribed == 0 && $leader->combat > 0) 
            {
                echo '<h3>Combat Bonuses</h3>';
                
                if ( $leader->combat_used == true )
                {
                    $id = $bonus->territory_name;
                    $id = str_replace('.', '', $id);
                    $id = str_replace(' ', '', $id);
                    echo 'Combat bonus has been used on:<br />';
                    echo ($bonus->is_merc?'*':'').$bonus->name.', '.$bonus->strength.($bonus->value > 0 ? ' (+' : ' (-').$bonus->value.') '.
                            anchor('sw/location/'.$bonus->location_id,$bonus->territory_name,'class="menu hoverlink" hoverid="'.'#'.$id.'"').
                            ' | '.anchor('leader/cancel/'.$leader->leader_id, 'CANCEL', 'class="menu"').'<br />';
                }
                else
                {
                    echo 'Combat Bonus Target: ';
                    if ( isset( $targets ) &&  count($targets) > 0 && $player->player_id == $leader->controlling_house_id)
                    {
                        echo '<select id="option">';
                        foreach( $targets as $unit )
                        {
                            if ( $unit->owner_id == $leader->controlling_house_id  && !$unit->die )
                                echo '<option value="'.$this->config->item('base_url').'index.php/leader/combat/'.$leader->leader_id.'/'.$unit->combatunit_id.'">'.($unit->is_merc?'*':'').$unit->name.', '.$unit->strength.((($unit->prewar_strength > 4)&& ($unit->strength==4))?' ('.$unit->prewar_strength.')':'').($player->tech_level == 25 ? ' @ '.$unit->territory_name : '').'</option>';     
                        }
                        echo '</select> | '.anchor('#','BONUS' ,'class="dc"').'<br />';
                        echo '*Mercenary Units<br />';
                        echo '(Prewar Strength)<br />';
                    }
                    else 
                    {
                        echo 'None.<br />';
                    }   
                }
            }
            
            // Negative Bonuses and combination breaks
            if ( $is_playing && $player->player_id == $leader->controlling_house_id 
                    && $leader->just_bribed == 1) 
            {
                if ( $leader->combat > 0 )
                {
                    echo '<br /><h4>Combat Bonuses</h4>';
                    
                    if ( $leader->combat_used < $leader->combat )
                    {
                        
                        echo '<p>Negative Combat Bonus Targets</p>';
                        if ( isset( $targets ) &&  count($targets) > 0 && $player->player_id == $leader->controlling_house_id)
                        {
                            echo '<select id="option">';
                            foreach( $targets as $unit )
                            {
                                if (!$unit->die)
                                    echo '<option value="'.$this->config->item('base_url').'index.php/leader/combat/'.$leader->leader_id.'/'.$unit->combatunit_id.'/1">'.($unit->is_merc?'*':'').$unit->name.', '.$unit->strength.($player->tech_level >= 25 ? ' @ '.$unit->territory_name : '').'</option>';     
                            }
                            echo '</select>';
                            echo '<br /><br />';
                            echo anchor('#','NEGATIVE BONUS' ,'class="dc"');
                        }
                        else 
                        {
                            echo '<p>None.</p>';
                        }
                        
                    }
                    else
                    {
                        echo '<p>Negative combat bonus has been used this turn.</p>';
                    
                        echo '<ul>';
                            echo '<li>'.$bonus->name.', '.$bonus->strength.($bonus->value > 0 ? ' (+' : ' (').$bonus->value.') | '.anchor('leader/cancel/'.$leader->leader_id, 'CANCEL', 'class="menu"').'</li>';
                        echo '</ul>';
                    }
                }
                
                if ( $leader->military > 0 )
                {
                    
                    echo '<h3>Combination Breaks</h3>';
                    
                    if ( $leader->military_used < $leader->military )
                    {
                        echo '<p>May break combinations.</p>';
                        
                        echo '<p>Combination Break Targets</p>';
                        if ( isset( $targets ) &&  count($targets) > 0 )
                        {
                            echo '<select id="option2">';
                            foreach( $targets as $unit )
                            {
                                if ( $unit->owner_id == $leader->original_house_id && isset($unit->combine_with) )
                                    echo '<option value="'.$this->config->item('base_url').'index.php/sw/combine/'.$unit->combatunit_id.'/-2/'.$leader->leader_id.'">'.($unit->is_merc?'*':'').$unit->name.', '.$unit->strength.'</option>';     
                            }
                            echo '</select>';
                            echo '<br /><br />';
                            echo anchor('#','BREAK COMBINATION' ,'class="dc2"');
                        }
                        else 
                        {
                            echo '<p>No Targets available.</p>';
                        }
                    }
                    else
                    {
                        echo '<p>All combination breaks have been used.</p>';
                    }
                }  
            }
            
            // BRIBE            
            if ( $leader->just_bribed == 0 && !$leader->official_leader && $leader->loyalty != 0
                    &&
                    ($leader->original_house_id != null                                 // House Leader
                    && $leader->controlling_house_id == $leader->original_house_id      // Controlled by original house
                    && $leader->original_house_id != $player->player_id)                // but not yours
                     || 
                    ( $leader->original_house_id == null                                // Merc Leader
                    && $leader->allegiance_to_house_id == $leader->controlling_house_id  // Allegiance to Controlling house
                    && $player->player_id != $leader->allegiance_to_house_id ))         // but not yours
            {
                // Detect available cards
                // Bride is card type 12
                // Blackmail is card type 13
                $bribe = false;
                $blackmail = false;
                foreach($cards as $card)
                {
                    if ($card->type_id == 12)
                    {
                        $bribe = true;
                        $bribecard = $card->card_id;
                    }
                    else if ($card->type_id == 13)
                    {
                        $blackmail = true;
                        $blackmailcard = $card->card_id;
                    }
                }

                echo '<ul>';
                echo '<li>'.anchor('leader/bribe/'.$leader->leader_id, 'BRIBE', 'class="menu"').' (10M CBills)</li>';
                if ($bribe)
                    echo '<li>'.anchor('leader/bribe/'.$leader->leader_id.'/'.$bribecard, 'BRIBE with Bribe Card', 'class="menu"').' (0M CBills)</li>';
                if ($blackmail)
                    echo '<li>'.anchor('leader/bribe/'.$leader->leader_id.'/0/'.$blackmailcard, 'BRIBE with Blackmail Card', 'class="menu"').' (10M CBills, +2)</li>';
                if ($bribe && $blackmail)
                    echo '<li>'.anchor('leader/bribe/'.$leader->leader_id.'/'.$bribecard.'/'.$blackmailcard, 'BRIBE with Bribe Card and Blackmail Card', 'class="menu"').' (0M CBills, +2)</li>';
                if ( $player->free_bribes > 0 )
                {
                    echo '<li>'.anchor('leader/bribe/'.$leader->leader_id.'/0/0/1', 'BRIBE using a free bribe', 'class="menu"').' (0M CBills)</li>';
                    if ($blackmail)
                        echo '<li>'.anchor('leader/bribe/'.$leader->leader_id.'/0/'.$blackmailcard.'/1', 'BRIBE using a free bribe with a Blackmail Card', 'class="menu"').' (0M CBills, +2)</li>';     
                }
                echo '</ul>';
            }
            
            // Execute
            if (    $player_has_units_present                               // You MUST have units present
                    && $leader->just_bribed != 1                            // NOBODY can execute if JUST bribed
                    &&      // AND EITHER
                    (       
                        (   // BRIBED
                            $leader->allegiance_to_house_id == null // This needs to be negative insead!!!
                            &&      
                            ( $player->player_id == $leader->original_house_id      // If you are the Original House
                            && $player->player_id != $leader->controlling_house_id  // You no longer control the leader
                            && $leader->just_bribed == 2)                           // Just after being bribed & used against you
                        )                           
                        ||  // OR 
                        (   // You bribed or captured the leader
                            $leader->just_bribed == 0                             // After the bribing dust settles 
                            && $player->player_id != $leader->allegiance_to_house_id
                            && $player->player_id == $leader->controlling_house_id 
                            && $game->combat_rnd == 0
                        )
                    )
                )
            {
                echo '<p>Can be executed! '.anchor('leader/execute/'.$leader->leader_id, 'EXECUTE', 'class="menu"').'</p>';
            }
            
            // Associated Units
            if ($this->debug>2) log_message('error', 'View associated units');
            if ((isset($associated_units)) && ($leader->associated_units!=null) && ($leader->associated_units !=''))
            {
                echo '<h4>Associated Units: "'.$leader->associated_units.'"</h4>';
                foreach($associated_units as $associated_unit)
                {
                    $id = $associated_unit->territory_name;
                    $id = str_replace('.', '', $id);
                    $id = str_replace(' ', '', $id);
                    echo $associated_unit->name.' ';
                    if ($associated_unit->strength==0) echo 'KIA<br />';
                    else echo $associated_unit->strength.(($associated_unit->strength==4 && $associated_unit->prewar_strength>4)?'('.$associated_unit->prewar_strength.')':'').' '.anchor('sw/location/'.$associated_unit->location_id, $associated_unit->territory_name,'class="menu hoverlink" hoverid="'.'#'.$id.'"').'<br />';
                }
            }
            else
                echo '<h4>No Associated Units</h4>';
            
        echo '</info>';
    echo "</response>";
?>