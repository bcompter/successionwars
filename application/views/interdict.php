<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            
        echo '<h3>Comstar Regional Interdict</h3>';       
        
        echo '<select id="option">';
        foreach($players as $play)
        {
            if ( $player->player_id != $play->player_id )
                echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$play->player_id.'">'.$play->faction.'</option>';
        }
        echo '</select> ';
        
        echo '<select id="option2">';
        foreach($territories as $territory)
        {
            echo '<option value="/'.$territory->territory_id.'">'.$territory->name.'</option> ';
        }
        echo '</select> | ';
        
        echo anchor('#','INTERDICT' ,'class="doubleoption"');
        
        echo '</info>';
    echo "</response>";
?>