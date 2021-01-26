<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            
        echo '<h3>'.$title.'</h3>';       

        echo '<select id="option">';
        echo '<option value="">- Select Unit -</option>';
        foreach($combatunits as $combatunit)
        {
            if (($card->type_id!=7) && ($combatunit->is_rebuild))
                $strength = 4;
            else
                $strength = $combatunit->prewar_strength;
            
            if (($card->type_id==7)&&($combatunit->prewar_strength<5)) 
                echo '';
            else
                echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.
                    $combatunit->combatunit_id.'">'.
                    ($combatunit->is_merc?'*':'').$combatunit->name.', '.$strength.
                    ((($card->type_id!=7) && ($combatunit->is_rebuild) && ($combatunit->prewar_strength >4))?' ('.$combatunit->prewar_strength.')':'').'</option>';
        }
        echo '</select> ';
        
        echo '<select id="option2">';
        foreach($factories as $factory)
        {
            if (!$factory->is_damaged)
                echo '<option value="/'.$factory->factory_id.'">'.$factory->name.'</option>';
        }
        echo '</select> | ';
        
        echo anchor('#','BUILD' ,'class="doubleoption"');
        echo '<br />*Mercenary Units'.($card->type_id==7?'':'<br />(Prewar Strength)');
            
        echo '</info>';
    echo "</response>";
?>