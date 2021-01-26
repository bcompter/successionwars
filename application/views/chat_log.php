<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>";
    
    echo '<chats>';
    
    // Display page links
    $has_older = false;
    $has_newer = false;
    if ($offset + 20 < $num_chats)
        $has_older = true;
    
    if ($offset != 0)
        $has_newer = true;    
    
    echo ($has_older ? anchor('chat/chat_log/'.$game->game_id.'/'.($offset+20),'<< Older', 'class="menu"') : '<< Older').
            ' | '.
         ($has_newer ? anchor('chat/chat_log/'.$game->game_id.'/'.($offset-20),'Newer >>', 'class="menu"') : 'Newer >>');    
    echo '<br />';
    $chats = array_reverse($chats);
    
    if (!isset($player->faction))
        $player->faction = 'None';
    
    foreach($chats as $msg)
    {
        if ( isset($msg->to_faction) && $msg->to_faction == $player->faction )
            echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | Private Message from '.$msg->faction.': </div>'.$msg->message.'<br />';
        else if (isset($msg->to_faction) && $msg->faction == $player->faction)
            echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | Private Message To '.$msg->to_faction.': </div>'.$msg->message.'<br />';
        else if (!isset($msg->to_faction))
            echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | '.$msg->faction.': </div>'.$msg->message.'<br />';
        
        $chattime = $msg->time_stamp;
    }
    echo '</chats>';
    
    echo "</response>";
?>
