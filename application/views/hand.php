<?php

echo '<h3>Play Hand</h3>';

if ( count($cards) == 0 )
{
    echo 'No cards.';
}
else
{
    foreach( $cards as $card )
    {
        echo $card->title.($card->traded==TRUE ? ' (traded)' : '').': '.$card->text;
        if ( $game->phase == $card->phase || $card->phase == 'Any' )
        {
            // Display play link if valid phase
            echo '<br />'.anchor('cards/play/'.$card->card_id, 'PLAY', 'class="menu"');
        }
        else
        {
            echo  '<br />'.strtoupper($card->phase).' PHASE ONLY';
        }
        echo anchor('cards/discard/'.$card->card_id, 'DISCARD', 'class="menu float_right"');
        echo '<br /><hr /><br />';
    }
}

?>