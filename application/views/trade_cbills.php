<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';

            echo '<h3>Give CBills to '.$player->faction.'</h3><br />';

            echo '<input type="text" name="" value="0" url="'.$this->config->item('base_url').'index.php/player/trade_cbills/'.$player->player_id.'/">';

            echo '<br />';
            echo anchor('#','Send C-Bills' ,'class="textinput"');
    
        echo '</info>';
    echo "</response>";
?>