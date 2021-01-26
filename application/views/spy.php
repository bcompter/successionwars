<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            echo '<h3>Spy: Select a Target</h3>';
            
            if ( count($players) == 0 )
            {
                echo 'No available targets, that\'s really odd...';
            }
            else
            {
                echo '<select id="option">';
                foreach( $players as $play )
                {
                    if ( $player->player_id != $play->player_id )
                    echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$play->player_id.'">'.$play->faction.'</option>';     
                }
                echo '</select>';
                echo '<br /><br />';
                echo anchor('#','SPY' ,'class="dc"');
                
            }
        echo '</info>';
    echo "</response>";
?>