<?php   
        echo '<h2>'.($jumpship->jumpship_name != '' ? 'Jumpship: '.$jumpship->jumpship_name.' ('.$jumpship->capacity.')' : 'Jumpship '.$jumpship->capacity).'</h2>';
        echo 'Name: '.($jumpship->jumpship_name != "" ? $jumpship->jumpship_name : 'Unnamed').
                ($is_playing && $player->player_id == $jumpship->owner_id ? ' | '.anchor('jumpship/name/'.$jumpship->jumpship_id, 'RENAME', 'class="menu"') : '').'<br />';
        echo 'Owner: '.anchor('player/view/'.$jumpship->owner_id,$jumpship->faction,'class="menu"').'<br />';
                //$jumpship->faction.'<br />';
        $id = $jumpship->name;
        $id = str_replace('.', '', $id);
        $id = str_replace(' ', '', $id);
        echo 'Location: '.anchor('sw/location/'.$jumpship->location_id,$jumpship->name,'class="menu hoverlink" hoverid="'.'#'.$id.'"').'<br />';
        
        if ($jumpship->being_built)
            echo 'UNDER CONSTRUCTION <br />';
        
        // Calculate moves remaining
        if ( $owner->tech_level > 14 && !$jumpship->being_built)
            $jumpship->moves_remaining = 4-$jumpship->moves_this_turn;
        else if ( $owner->tech_level < -6 && !$jumpship->being_built)
            $jumpship->moves_remaining = 2-$jumpship->moves_this_turn;
        else if (!$jumpship->being_built)
            $jumpship->moves_remaining = 3-$jumpship->moves_this_turn;
        if ($jumpship->moves_remaining < 0)
            $jumpship->moves_remaining = 0;
        
        echo 'Jumps Remaining: '. $jumpship->moves_remaining.'<br />';
        
        if ($is_playing && $player->player_id == $jumpship->owner_id && !$jumpship->being_built)
        {
            echo anchor('jumpship/scuttle/'.$jumpship->jumpship_id, 'SCUTTLE', 'class="menu"').'<br />';
        }
        
        $displayjumplinks = false;
        if ($is_playing)
            $displayjumplinks = $game->player_id_playing == $player->player_id && $game->phase == 'Movement' && $player->player_id == $jumpship->owner_id;
        $retreat = $game->phase == 'Combat' && $game->combat_rnd > 0;
        $displayjumplinks |= $retreat;
        
        if ($is_playing && $player->player_id != $jumpship->owner_id)
            $displayjumplinks = false;
        
        // Not allowed to jump or load if you owe casualties
        if ($retreat && $owes_casualties)
            $displayjumplinks = false;
        
        // Handle jumpship distance technology bonus
        if ($is_playing)
        {
            if ($player->tech_level > 14 && $jumpship->moves_this_turn == 4 && $game->phase != 'Combat')
                $displayjumplinks = false;
            else if ( $player->tech_level < 15 && $jumpship->moves_this_turn == 3  && $game->phase != 'Combat')
                $displayjumplinks = false;
            else if ($player->tech_level < -6 && $jumpship->moves_this_turn == 2  && $game->phase != 'Combat')
                $displayjumplinks = false;
        }
        
        $numloadedunits = 0;
        $numotherunits = 0;
        foreach($units as $unit)
        {
            if ( $unit->loaded_in_id == $jumpship->jumpship_id )
                $numloadedunits++;
            else
                $numotherunits++;
        }
        foreach($leaders as $unit)
        {
            if ( $unit->loaded_in_id == $jumpship->jumpship_id )
                $numloadedunits++;
            else
                $numotherunits++;
        }
        
        echo '<h3>Loaded:</h3>'.($numloadedunits > 0 && $jumpship->owner_id == $player->player_id ? anchor('jumpship/drop_all/'.$jumpship->jumpship_id,'DROP ALL','class="menu inline"') : '');
        echo '<table rowspacing="10px">';
        if ($numloadedunits != 0)
        {
            foreach($units as $unit)
            {
                if ( $unit->loaded_in_id == $jumpship->jumpship_id )
                {
                    echo '<tr>';
                    if ($unit->owner_id == $player->player_id)                    
                        echo '<td>'.($unit->is_merc?'*':'').$unit->name.', '.$unit->strength.((($unit->strength==4)&&($unit->prewar_strength>4))?' ('.$unit->prewar_strength.')':'').'</td><td> | '.anchor('jumpship/drop_unit/'.$unit->combatunit_id,'DROP','class="menu"').'</td>';
                    else
                        echo '<td>'.($unit->is_merc?'*':'').$unit->name.', '.$unit->strength.((($unit->strength==4)&&($unit->prewar_strength>4))?' ('.$unit->prewar_strength.')':'').' ['.$unit->faction.']'.'</td><td></td>';  
                    echo '</tr>'; 
                }    
            }
            foreach($leaders as $leader)
            {
             if ( $leader->loaded_in_id == $jumpship->jumpship_id )
                {
                    echo '<tr>';
                    if ($leader->controlling_house_id == $player->player_id)                    
                        echo '<td>'.($leader->original_house_id==0?'*':'').$leader->name.'</td><td> | '.anchor('jumpship/drop_leader/'.$leader->leader_id,'DROP','class="menu"').'</td>';
                    else
                       echo '<td>'.$leader->name.'</td><td></td>';  
                    echo '</tr>';
                }   
                
            }
            
        }
        else
        {
            echo '<tr><td>None</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>Other '.$jumpship->faction.' units here:</h3>';
        echo '<table rowspacing="8px">';
        if ($numotherunits != 0)
        {
            foreach($units as $unit)
            {
                if ( $unit->loaded_in_id != $jumpship->jumpship_id AND $jumpship->owner_id == $unit->owner_id)
                {
                    echo '<tr>';
                    if ($displayjumplinks && $unit->owner_id == $player->player_id && !$jumpship->being_built && $unit->was_loaded==$jumpship->jumpship_id && !$unit->being_built && !$unit->die && $unit->loaded_in_id == NULL)
                        echo '<td>'.$unit->faction.'</td><td> '.($unit->is_merc?'*':'').$unit->name.'</td><td>'.$unit->strength.((($unit->strength==4)&&($unit->prewar_strength>4))?' ('.$unit->prewar_strength.')':'').'</td><td> | '.anchor('jumpship/load_unit/'.$jumpship->jumpship_id.'/'.$unit->combatunit_id,'RELOAD','class="menu"').'</td>';
                    else if ($displayjumplinks && $unit->owner_id == $player->player_id && !$jumpship->being_built && $unit->was_loaded==0 && !$unit->being_built && !$unit->die && $unit->loaded_in_id == NULL)
                        echo '<td>'.$unit->faction.'</td><td> '.($unit->is_merc?'*':'').$unit->name.'</td><td>'.$unit->strength.((($unit->strength==4)&&($unit->prewar_strength>4))?' ('.$unit->prewar_strength.')':'').'</td><td> | '.anchor('jumpship/load_unit/'.$jumpship->jumpship_id.'/'.$unit->combatunit_id,'LOAD','class="menu"').'</td>';
                    else if ($displayjumplinks && $unit->owner_id == $player->player_id && !$jumpship->being_built && $unit->was_loaded==0 && !$unit->being_built && !$unit->die && $unit->loaded_in_id != $jumpship->jumpship_id)
                        echo '<td>'.$unit->faction.'</td><td> '.($unit->is_merc?'*':'').$unit->name.'</td><td>'.$unit->strength.((($unit->strength==4)&&($unit->prewar_strength>4))?' ('.$unit->prewar_strength.')':'').'</td><td> | '.anchor('jumpship/load_unit/'.$jumpship->jumpship_id.'/'.$unit->combatunit_id,'TRANSFER','class="menu"').'</td>';
                    else if ($jumpship->moves_remaining == 0 || !$is_playing || $unit->owner_id != $player->player_id)
                        echo '<td>'.$unit->faction.'</td><td>'.($unit->is_merc?'*':'').$unit->name.'</td><td>'.$unit->strength.((($unit->strength==4)&&($unit->prewar_strength>4))?' ('.$unit->prewar_strength.')':'').'</td><td></td>';                        
                    else
                        echo '<td>'.$unit->faction.'</td><td>'.($unit->is_merc?'*':'').$unit->name.'</td><td>'.$unit->strength.((($unit->strength==4)&&($unit->prewar_strength>4))?' ('.$unit->prewar_strength.')':'').'</td><td>MOVED</td>';
                    echo '</tr>';                    
                }
            }
            foreach($leaders as $leader)
            {
                if ( $leader->loaded_in_id != $jumpship->jumpship_id AND $jumpship->owner_id == $leader->controlling_house_id)
                {
                    echo '<tr>';
                    if ($displayjumplinks && $leader->controlling_house_id == $player->player_id && !$jumpship->being_built && ($leader->was_loaded==$jumpship->jumpship_id ||  $leader->was_loaded==0))
                        echo '<td>'.$leader->faction.'</td><td>'.$leader->name.'</td><td></td><td>'.anchor('jumpship/load_leader/'.$jumpship->jumpship_id.'/'.$leader->leader_id,'LOAD','class="menu"').'</td>';
                    else
                        echo '<td>'.$leader->faction.'</td><td>'.$leader->name.'</td><td></td><td></td>';
                    echo '</tr>';
                } 
            }
        }
        else
        {
            echo '<tr><td>None</td></tr>';
        }
        echo '</table>';
        
        
        if ( $displayjumplinks && !$jumpship->being_built )
        {
            if (!$retreat)
                echo '<h3>Jump to:</h3>';
            else 
                echo '<h3>Retreat to:</h3>';
            
            echo '<table rowspacing="10px">';
            foreach ($adjacent as $territory)
            {
                $id = $territory->name;
                $id = str_replace('.', '', $id);
                $id = str_replace(' ', '', $id);
                
                echo '<tr>';
                if (!$retreat)
                {
                    echo '<td>'.$territory->name.'</td><td> | '.anchor('jumpship/move/'.$jumpship->jumpship_id.'/'.$territory->territory_id,'JUMP','class="menu hoverlink" hoverid="'.'#'.$id.'"').'</td>';
                }
                else
                {
                    if ($territory->player_id == $player->player_id && !$territory->is_contested)
                        echo '<td>'.$territory->name.'</td><td> | '.anchor('jumpship/move/'.$jumpship->jumpship_id.'/'.$territory->territory_id,'RETREAT','class="menu hoverlink" hoverid="'.'#'.$id.'"').'</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }
?>