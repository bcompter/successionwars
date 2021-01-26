<h3>Assign Targets</h3>
<h4>Location: <?php echo $territory->name; ?></h4>
<?php echo anchor('sw/viewcombat/'.$territory->territory_id, '<< Back to the Combat View', 'class="menu"'); ?>
<br /><br />
<?php

    // Setup array to fetch values
    $validtargets;
    foreach ($units as $unit)
    {
        if ($unit->owner_id != $player->player_id)
            $validtargets[$unit->owner_id] = 1;
    }

    $targets;
    foreach($players as $p)
    {
        if (isset($validtargets[$p->player_id]))
            $targets[$p->player_id] = $p;
    }

    echo '<table>';
    foreach($units as $unit)
    {
        if ($unit->owner_id == $player->player_id)
        {
            $target_is_invalid = false;
            echo '<tr><td>';
            echo $unit->name.', '.$unit->strength.' : Target is ';
            if(isset($unit->target_id))
            {
                if ($unit->target_id == 0)
                    echo 'Default';
                else if (isset($targets[$unit->target_id]->faction))
                    echo $targets[$unit->target_id]->faction;
                else
                {
                    $target_is_invalid = true;
                    echo 'Invalid';
                }
            }
            else 
                echo 'Unassigned';

            echo '</td><td>';
            
            if (isset($targets))
            {
                foreach($targets as $t)
                {           
                    if ($t->player_id != $player->player_id)
                        echo ' | '.anchor('sw/assign_targets/'.$territory->territory_id.'/'.$unit->combatunit_id.'/'.$t->player_id,$t->faction,'class="menu"');
                }
            }
            else
            {
                // No valid targets!
                if ( $unit->target_id == null )
                    echo ' | '.anchor('sw/assign_targets/'.$territory->territory_id.'/'.$unit->combatunit_id.'/0','Default','class="menu"');
                else
                {
                    if ($target_is_invalid)
                        echo ' | '.anchor('sw/assign_targets/'.$territory->territory_id.'/'.$unit->combatunit_id.'/0','Default','class="menu"');
                    else
                        echo ' | None';
                }
            }
                
            echo '</td></tr>';
        }
            
    }
    echo '</table>';

?>


