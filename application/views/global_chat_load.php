<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
    
    echo '<chats>';
    $chats = array_reverse($chats);
    
    foreach($chats as $msg)
    {
        echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | '.$msg->username.': </div>'.$msg->message.'<br />';
        
        $chattime = $msg->time_stamp;
    }
    echo '</chats>';

    if (isset($chattime))
        echo "<chattime>".$chattime."</chattime>";
    
    echo '<users>';
    foreach($users as $user)
    {
        echo '<p>'.$user->username.'</p>';
        echo '<br />';
    }
    echo '</users>';
    
    echo "</response>";
?>