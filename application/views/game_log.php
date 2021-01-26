<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>";
    
    echo '<chats>';
    
    // Display page links
    $has_older = false;
    $has_newer = false;
    if ($offset + 20 < $num_logs)
        $has_older = true;
    
    if ($offset != 0)
        $has_newer = true;    
    
    echo ($has_older ? anchor('game/game_log/'.$game->game_id.'/'.($offset+20),'<< Older', 'class="menu"') : '<< Older').
            ' | '.
         ($has_newer ? anchor('game/game_log/'.$game->game_id.'/'.($offset-20),'Newer >>', 'class="menu"') : 'Newer >>');    
    echo '<br />';
    $logs = array_reverse($logs);
    
    foreach($logs as $msg)
    {
        if ($msg->player_id == null || $msg->player_id == $player->player_id)
        {
            echo $msg->timestamp.' | '.$msg->message.'<br />';
            $logtime = $msg->timestamp;
        }
    }
    echo '</chats>';
    
    echo "</response>";
?>
