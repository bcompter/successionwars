<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
        
        echo $player->faction.' Military Assets ';
        echo '<br /><br />';
        
        echo '<ul>';
        foreach( $combatunits as $unit )
        {
            echo '<li>';
            echo ($unit->is_merc ? '* ' : '').$unit->name.' Strength:'.$unit->strength.' Location: '.$unit->territory_name;
            echo '</li>';
        }
        echo '</ul>';
        
        echo '</info>';
    echo "</response>";
?>
