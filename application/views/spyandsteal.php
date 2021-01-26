<h3>Steal a Card</h3>

<?php

    if (isset($players))
    {
        echo '<table>';
        foreach($players as $player)
        {
            if ($player->player_id != $attacker->player_id && $player->player_id != 0)
                echo '<tr><td>'.$player->faction.'</td><td> | '.anchor('cards/play/'.$card->card_id.'/'.$player->player_id, 'Steal Card', 'class="menu"').'</td></tr>';
        }
        echo '</table>';
    }
    
    if (isset($cards))
    {
        echo '<br />';
        foreach($cards as $card)
        {
            echo $card->title.': '.$card->text;
            echo anchor('cards/play/'.$originalcard->card_id.'/'.$card->owner_id.'/'.$card->card_id, 'Steal Card', 'class="menu float_right"');
            echo '<br /><hr /><br />';
        }
    }
?>