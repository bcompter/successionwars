<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            echo '<h2>Send '.$leader->name.'</h2>';
            
            echo 'Send '.$leader->name.' to the capital of Player:';
            echo '<select id="option">';
            echo '<option value="0">- Select a Player -</option>';
            foreach( $players as $p )
            {
                if ( $p->player_id != $player->player_id && $player->turn_order != 0 && !$player->eliminate )
                echo '<option value="'.$this->config->item('base_url').'index.php/leader/ransom/'.$leader->leader_id.'/'.$p->player_id.'">'.$p->faction.'</option>';     
            }
            echo '</select> | '.anchor('#','SEND' ,'class="dc"');

        echo '</info>';
    echo "</response>";
?>