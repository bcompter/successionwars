<?php
if ($card->type_id == 15)
    echo '<h3>Comstar House Interdict</h3>';
else
    echo '<h3>Lift House Interdict</h3>';
    
echo '<h4>Select a Target</h4>';

    foreach($players as $p)
    {
        if ( $player->player_id != $p->player_id || $card->type_id == 16)
        {
            echo $p->faction.' | ';
            echo anchor($this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$p->player_id,
            'TARGET', 'class="menu"');
            echo '<br />';
        }
    }

?>