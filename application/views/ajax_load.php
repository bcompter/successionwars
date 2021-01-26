<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
    
    echo '<chats>';
    $chats = array_reverse($chats);

    if (!isset($player->faction))
    {
        $player = new stdClass();
        $player->faction = 'None';
    }
    
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
    
    echo '<messages>';
    $messages = array_reverse($messages);
    foreach($messages as $msg)
    {
        if ($msg->player_id == null || (isset($player->player_id) && $msg->player_id == $player->player_id))
        {
            echo $msg->timestamp.' | '.$msg->message.'<br />';
            $msgtime = $msg->timestamp;
        }
    }
    echo '</messages>';
    
    if (isset($chattime))
        echo "<chattime>".$chattime."</chattime>";
    if (isset($msgtime))
        echo "<msgtime>".$msgtime."</msgtime>";
    
    $maptime = new DateTime();
    $maptime = $maptime->format('Y-m-d H:i:s');
    echo "<maptime>".$maptime."</maptime>";
    
    echo "</response>";
?>