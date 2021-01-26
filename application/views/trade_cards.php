<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';

            echo '<h3>Give a card from your hand to '.$target->faction.'</h3>';
    
            echo '<table>';
            foreach($cards as $card)
            {
                if ($card->traded == 0)
                    echo '<tr><td>'.$card->title.'</td><td> | '.anchor('cards/trade_cards/'.$target->player_id.'/'.$card->card_id, 'Give Card', 'class="menu"').'</td></tr>';
                else
                    echo '<tr><td>'.$card->title.'</td><td> | Traded</td></tr>';
            }
            echo '</table><br /> * Cards may only be traded once while they are in play.';
    
        echo '</info>';
    echo "</response>";
?>