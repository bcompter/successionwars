<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
        echo '<ul>';
        foreach($locations as $local)
        {
            echo '<li>';
            echo anchor('sw/location/'.$local->territory_id,$local->name,'class="menu"');
            //echo ' ('.$territory->faction.')';
            echo '</li>';
        }
        echo '</ul>';
        echo '</info>';
    echo "</response>";
?>