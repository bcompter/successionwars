<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            
        echo '<h3>Star League Combat Bonus</h3>';       
        
        echo '<select id="option">';
        echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$player->player_id.'">'.$player->faction.'</option>';
        foreach($players as $play)
        {
            echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$play->player_id.'">'.$play->faction.'</option>';
        }
        echo '</select> ';
        
        echo '<select id="option2">';
        echo '<option value="0">- Select Region -</option>';
        foreach($territories as $territory)
        {
            echo '<option value="/'.$territory->territory_id.'">'.$territory->name.'</option>';
        }
        echo '</select> | ';
        
        echo anchor('#','SUBMIT' ,'class="doubleoption"');
        
        echo '</info>';
    echo "</response>";
?>