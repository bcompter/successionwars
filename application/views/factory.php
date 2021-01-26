<?php
    $nomcs;
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            
        echo '<h3>Manufacturing Center at '.$territory->name.'</h3>';        
        
        if ( $game->player_id_playing == $player->player_id && $game->phase!='Production' )
            echo 'CANNOT BUILD DURING THIS PHASE';
        elseif ( $game->player_id_playing != $player->player_id)
            echo 'CANNOT BUILD DURING ANOTHER PLAYER\'S TURN';

        if ( $factory->being_built )
        {
            echo '<p>UNDER CONSTRUCTION</p>';
        }
        else if ( $factory->is_damaged )
        {
            echo '<p>DAMAGED,'.($factory->being_repaired ? ' Repairs Underway...' : 'repair for 20 million cBills').'</p>';
            if ( !$factory->being_repaired )
                echo anchor('production/repair/'.$factory->factory_id,'REPAIR', 'class="menu"');
        }
        else
        {
            echo '<h4>\'Mech Units </h4>';
            $houseprice = get_price(false, $player->tech_level);
            $mercprice = get_price(true, $player->tech_level);
            echo '<select id="combatunit">';
            echo '<option value="0"> - Select Unit -</option>';
            foreach($combatunits as $combatunit)
            {
                // STRENGTH
                if ( $combatunit->is_rebuild )
                    $strength = 4;
                else
                    $strength = $combatunit->prewar_strength;
                
                // COST
                $cost = $houseprice;
                if ( $combatunit->is_merc )
                    $cost = $mercprice;
                
                echo '<option value="'.$this->config->item('base_url').'index.php/production/combatunit/'.$combatunit->combatunit_id.'/'.$factory->factory_id.'">'.($combatunit->is_merc?'*':'').$combatunit->name.', '.$strength.(($combatunit->prewar_strength > 4)&& ($strength==4)?' ('.$combatunit->prewar_strength.')':'').' Cost: '.$cost.'MM</option>';
            } 
            echo '</select>';
            
            if ( $game->player_id_playing == $player->player_id && $game->phase=='Production' )
                echo ' | '.anchor('#','BUILD' ,'class="cu"');
            echo '<br />*Mercenary Units<br />';
            echo '(Prewar Strength)<br />';
            
            echo '<br /><h4>Conventional Units</h4>';
            echo '<select id="conventional">';
            echo '<option value="'.$this->config->item('base_url').'index.php/production/conventional/'.$factory->factory_id.'">Conventional, 3 Cost: 3MM</option>';
            echo '</select>';
            if ( $game->player_id_playing == $player->player_id && $game->phase=='Production' )
                echo ' | '.anchor('#','BUILD' ,'class="cu"');    
            
            if (isset($player->may_build_elementals))
            {
                echo '<br /><h4>'.$player->may_build_elementals.'</h4>';
                echo '<select id="elementals">';
                echo '<option value="'.$this->config->item('base_url').'index.php/production/elemental/'.$factory->factory_id.'">'.$player->may_build_elementals.', 2 Cost: '.($player->tech_level >= 13 ?'4':'5').'MM</option>';
                echo '</select>';
                if ( $game->player_id_playing == $player->player_id && $game->phase='Production' )
                    echo ' | '.anchor('production/elemental/'.$factory->factory_id,'BUILD', 'class="menu"');    
            }
            
            echo '<br /><br /><h4>Jumpships</h4>';
            echo '<select id="jumpship">';
            if ($game->use_extd_jumpships)
            {
                $jumpships = array(1,2,3,5,7,9,12);
                $costs = array(12,16,20,25,30,34,40);
            }
            else
            {
                $jumpships = array(1,2,3,5);
                $costs = array(12,16,20,25);
            }
            $index = 0;
            foreach($jumpships as $jumpshipsize)
            {
                echo '<option value="'.$this->config->item('base_url').'index.php/production/jumpship/'.$jumpshipsize.'/'.$factory->factory_id.'">Jumpship ('.$jumpshipsize.') Cost:'.$costs[$index].'MM</option>';
                $index++;
            }
            echo '</select>';
            
            if ( $game->player_id_playing == $player->player_id && $game->phase=='Production' )
                echo ' | '.anchor('#','BUILD' ,'class="js"');
            
            echo '<br /><br /><h4>Upgrade \'Mechs to Strength 4 (Cost: 5MM):</h4>';
            echo '<ul>';
            if (count($upgradable) > 0)
            {
                foreach($upgradable as $unit)
                {
                    echo '<li>'.$unit->name.' '.$unit->strength.($game->player_id_playing == $player->player_id && $game->phase=='Production' ? ' | '.anchor('production/upgrade/'.$unit->combatunit_id, 'UPGRADE', 'class="menu"') : '').'</li>';
                }
            }
            else
                echo '<li>None</li>';
            echo '</ul>';
            
            echo '<h4>Production Line</h4>';
            echo '<table>';
            foreach($units_being_built as $unit)
            {
                echo '<tr><td>'.$unit->name.', '.($unit->is_rebuild ? '4' : $unit->prewar_strength);
                echo '</td>';
                if ($game->phase == 'Production' && $game->player_id_playing == $player->player_id && $unit->price_paid > 0)
                {
                    echo '<td> | '.anchor('production/cancel/'.$factory->factory_id.'/'.$unit->combatunit_id.'/1', 'CANCEL', 'class="menu"').'</td>';
                }
                else
                {
                    echo '<td></td>';
                }
                
                echo '</tr>';
            }
            foreach($ships_being_built as $ship)
            {
                echo '<tr><td>Jumpship '.$ship->capacity.'</td></tr>';
            }
            echo '</table>';
            
        }
        
        echo '</info>';
    echo "</response>";
?>