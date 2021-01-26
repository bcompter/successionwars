<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            echo '<h3>'.$target->faction.'\'s Play Hand</h3>';
            
            if ( count($cards) == 0 )
            {
                echo 'No cards.  Bad time to play a spy card huh?';
            }
            else
            {
                foreach( $cards as $card )
                {
                    echo '<b>'.$card->title.'</b>: '.$card->text;
                    echo '<br /><br />';
                }
            }
            
        echo '</info>';
    echo "</response>";
?>