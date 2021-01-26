<h3>Combine Combat Units | Battle for <?php echo $territory->name; ?> </h3>

<?php echo anchor('sw/combinations/'.$territory->territory_id, '<< Back to Modify Combinations', 'class="menu"'); ?>
<br /><br />

<h4> <?php echo ($unit->is_merc==TRUE?'*':'').$unit->name; ?> May Combine Freely With: </h4>

<?php

    echo '<table>';
    foreach($units as $u)
    {  
        if ( ($u->name == $unit->name || ($u->is_elemental || $unit->is_elemental)) && $u->combatunit_id != $unit->combatunit_id && !isset($u->combine_with) && $unit->owner_id == $u->owner_id && !$u->die)
        {
            echo '<tr><td>';
            echo ($u->is_merc==TRUE?'*':'').$u->name.', '.$u->strength;
            echo '</td><td> | '.anchor('sw/combine/'.$unit->combatunit_id.'/'.$u->combatunit_id,'COMBINE', 'class="menu"');   
        }
        echo '</td></tr>';
    }
    echo '</table>';

    echo '<br />';

    foreach($leaders as $leader)
    {
        if ($leader->controlling_house_id == $leader->allegiance_to_house_id && ($leader->original_house_id == NULL || $leader->original_house_id == $leader->controlling_house_id))
        {
            echo '<h4>Using '.($leader->associated_units!=NULL?'*':'').$leader->name.', Military: '.$leader->military_used.'/'.$leader->military.'</h4>';
            if ($unit->is_elemental==FALSE)
            if ($unit->is_merc==TRUE ||($unit->is_merc==FALSE && $leader->associated_units==NULL))
            if ($leader->military_used < $leader->military)
            {
                echo '<table>';
                foreach($units as $u)
                {
                    if ($u->is_elemental==FALSE)
                    if ($leader->associated_units==NULL || ($leader->associated_units!=NULL && $u->is_merc == TRUE))
                    if ($u->name != $unit->name && $u->combatunit_id != $unit->combatunit_id && !isset($u->combine_with) && $unit->owner_id == $u->owner_id && !$u->die)
                    {
                        echo '<tr><td>';
                        echo ($u->is_merc==TRUE?'*':'').$u->name.', '.$u->strength;
                        echo '</td><td> | '.anchor('sw/combine/'.$unit->combatunit_id.'/'.$u->combatunit_id.'/'.$leader->leader_id,'COMBINE', 'class="menu"');   
                    }
                    echo '</td></tr>';
                }
                echo '</table>';
            }
        }
    }
    
?>
