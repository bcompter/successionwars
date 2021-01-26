<?php
echo '<h3>';
echo $title;
echo '</h3>';
    
echo '<h4>Select a Target</h4>';
echo '<table>';
    foreach($players as $p)
    {
        if ( $player->player_id != $p->player_id || $card->type_id == 16)
        {
            if ($player->turn_order != 0)
            {
                echo '<tr>';
                echo '<td>'.$p->faction.'</td><td> | ';
                echo anchor($this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$p->player_id, 'TARGET', 'class="menu"');
                echo '</td></tr>';
            }  // end if eliminated
        }  // end if
    }  // end for
echo '</table>';
?>