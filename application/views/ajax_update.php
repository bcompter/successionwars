<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
    echo '<messages>';
    $messages = array_reverse($messages);
    foreach($messages as $msg)
    {
        echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' '.$msg->faction.': </div>'.$msg->message.'!!<br />';
        $time = $msg->time_stamp;
    }
    echo '</messages>';
    echo "<time>".$time."</time>";
    
    echo "</response>";
?>
