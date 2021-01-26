<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
        
        echo '<h3>Current Battles</h3><br />';        
        echo '<ul>';
        //echo print_r($territories);
        foreach($territories as $territory)
        {
            $id = $territory->name;
            $id = str_replace('.', '', $id);
            $id = str_replace(' ', '', $id);
            
            echo '<li>'.($territory->involved==1? '! ': ($territory->involved==2? '!^ ': ''));
            echo anchor('sw/viewcombat/'.$territory->territory_id,$territory->name,'class="menu hoverlink" hoverid="'.'#'.$id.'"');
            echo '</li>';
        }
        echo '</ul>';
        echo '<p>! You are involved in these combats<br>';
        echo '^ You need to declare casualties</p>';
        echo '</info>';
    echo "</response>";
?>
