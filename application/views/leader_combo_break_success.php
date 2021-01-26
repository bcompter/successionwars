<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            
            echo anchor('leader/view/'.$leader->leader_id, '<< Back to Leader View', 'class="menu"');
        
        echo '</info>';
    echo "</response>";
?>