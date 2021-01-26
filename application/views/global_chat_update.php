<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
    echo '<chats>';
    $messages = array_reverse($messages);
    foreach($messages as $msg)
    {
        echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | '.$msg->username.': </div>'.$msg->message.'<br />';
        $time = $msg->time_stamp;
    }
    echo '</chats>';
    echo "<time>".$time."</time>";
    
    echo '<users>';
    foreach($users as $user)
    {
        echo '<p>'.$user->username.'</p>';
        echo '<br />';
    }
    echo '</users>';
    
    echo "</response>";
?>
