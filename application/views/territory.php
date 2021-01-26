<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
        
        echo ( $territory->is_capital ? '<img src="'.base_url().'images/capital.png">' : '' );
        echo ( $territory->is_regional ? '<img src="'.base_url().'images/regional.png">' : '' );
        
        echo '<h1 class="inline"> '.$territory->name.'</h1><br />';
        echo ( $territory->is_regional ? 'Regional Capital<br />' : '' );
        echo ( $territory->is_capital ? 'Capital<br />' : '' );
        echo 'Owned by: '.(isset($territory->faction) ? $territory->faction : 'Neutral').'<br />';
        echo 'Resources: '.$territory->resource;
        echo '<br />';
        if (isset($factory->factory_id))
        {
            echo '<img src="'.base_url().'/images/factory.png">';
            echo anchor('production/view/'.$factory->factory_id, ('Manufacturing Center'.($factory->is_damaged ? ' [Damaged]' : '')).($factory->being_built ? ' [Under Construction]' : ''), 'class="menu"');
        }
        echo '<br />';
        
        if ( count($bonuses) > 0 )
        {
            $only_one=true;
            echo '<h3>Territory Bonuses</h3>';
            $interdicted = false;
            foreach( $bonuses as $bonus )
            {
                echo ($only_one==true?'':', ').$bonus->faction.' ';
                if ($bonus->value == 2)
                    echo 'Star League +2';
                else if ($bonus->value == -2)
                {
                    $interdicted = true;
                    echo 'Interdicted -2';
                }
                $only_one=false;
            }
            if (!$interdicted )
            
            echo '<br />';            
        }
        
        echo '<h2>Units:</h2>';
        echo '<ul>';
        if (count($units) == 0)
        {
            echo '<li>There are no combat units here.</li>';
        }
        else
        {
            echo '<table>';
            foreach( $units as $unit )
            {
                echo '<tr><td>'.(isset($unit->faction) ? $unit->faction : 'Neutral').'&nbsp;&nbsp;</td><td>'.($unit->is_merc?'*':'')
                    .( ($unit->prewar_strength > 4) && ($unit->strength==4) ? '<span style="border-bottom: dashed thin;" title="Pre-War Strength: '.$unit->prewar_strength.'">' : '')
                    .$unit->name.'&nbsp;&nbsp;</td>'
                    . '<td align="center">'.$unit->strength
                    .( ($unit->prewar_strength > 4) && ($unit->strength==4) ? '</span>' : '')
                    .(isset($player->player_id) && $game->phase == 'Player Setup' && $unit->can_undeploy && $unit->owner_id == $player->player_id ? '</td><td> | '.anchor('game/undeploy/1/'.$unit->combatunit_id, 'UNDEPLOY', 'class="menu"') : '</td><td>')
                    .'</td>'
                    .($unit->die ? '<td>KIA</td>' : '<td>&nbsp</td>')
                    . '</tr>';
            }
            echo '</table>';
            echo '<br />';
            echo '*Mercenary Units<br />';
        }
        echo '</ul>';

        echo '<h2>Jumpships:</h2>';
        echo '<ul>';
        if (count($jumpships) == 0)
        {
            echo '<li>There are no jumpships here.</li>';
        }
        else
        {
            foreach( $jumpships as $jumpship )
            {
                echo '<li>('.(isset($jumpship->faction) ? $jumpship->faction : 'Neutral').
                        ($jumpship->name != "" ? ', '.$jumpship->name : '').') '.
                        anchor('jumpship/view/'.$jumpship->jumpship_id,'Jumpship '.$jumpship->capacity,'class="menu"').($jumpship->being_built==1?' *Under Construction':'')
                        .(isset($player->player_id) && $game->phase == 'Player Setup' && $jumpship->can_undeploy && $jumpship->owner_id == $player->player_id ? ' | '.anchor('game/undeploy/2/'.$jumpship->jumpship_id, 'UNDEPLOY', 'class="menu"') : '')
                        .'</li>';
            }
        }
        echo '</ul>';
        
        echo '<h2>Leaders:</h2>';
        echo '<ul>';
        if (count($leaders) == 0)
        {
            echo '<li>There are no leaders here.</li>';
        }
        else
        {
            foreach( $leaders as $leader )
            {
                echo '<li>('.(isset($leader->faction) ? $leader->faction : 'neutral').($leader->allegiance_to_house_id != $leader->controlling_house_id ? ', POW' : '').') '
                        .anchor('leader/view/'.$leader->leader_id,$leader->name,'class="menu"')
                        .(isset($player->player_id) && $game->phase == 'Player Setup' && $leader->can_undeploy && $leader->controlling_house_id == $player->player_id ? ' | '.anchor('game/undeploy/3/'.$leader->leader_id, 'UNDEPLOY', 'class="menu"') : '')
                        .'</li>';
            }
        }
        echo '</ul>';
        
        echo '</info>';
    echo "</response>";
?>