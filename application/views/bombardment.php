<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            echo '<h3>Bombardment: Select a Target</h3>';
            
            if ( count($combatunits) == 0 )
            {
                echo 'No available targets, that\'s really odd...';
            }
            else
            {
                echo '<select id="option">';
                echo '<option value="0">- Select -</options>';
                foreach( $combatunits as $combatunit )
                {
                    if (!$combatunit->die)
                        echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$combatunit->combatunit_id.'">'.$combatunit->faction.' '.($combatunit->is_merc?'*':'').$combatunit->name.', '.$combatunit->strength.((($combatunit->strength==4)&&($combatunit->prewar_strength>4))?' ('.$combatunit->prewar_strength.')':'').' @ '.$combatunit->territory_name.'</option>';     
                }
                echo '</select>';
                echo '<br /><br />';
                echo anchor('#','LAUNCH ATTACK' ,'class="dc"');
            }
        echo '</info>';
    echo "</response>";
?>