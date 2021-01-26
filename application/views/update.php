<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>";
    
    echo '<chats>';
    $chats = array_reverse($chats);
    
    if (!isset($player->faction))
    {
        $player = new stdClass();
        $player->faction = 'None';
    }
    foreach($chats as $msg)
    {
        if (isset($player->player_id))
        {
            if ( isset($msg->to_faction) && $msg->to_faction == $player->faction )
                echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | Private Message from '.$msg->faction.': </div>'.$msg->message.'<br />';
            else if (isset($msg->to_faction) && $msg->faction == $player->faction)
                echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | Private Message To '.$msg->to_faction.': </div>'.$msg->message.'<br />';
            else if (!isset($msg->to_faction))
                echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | '.$msg->faction.': </div>'.$msg->message.'<br />';
        }
        else
        {
            echo '<div class="load" style=color:'.$msg->color.'>'.$msg->time_stamp.' | '.$msg->faction.': </div>'.$msg->message.'<br />';
        }
        $chattime = $msg->time_stamp;
    }
    echo '</chats>';
    
    echo '<messages>';
    $gamemsgs = array_reverse($gamemsgs);
    foreach($gamemsgs as $msg)
    {
        if ($msg->player_id == null || (isset($player->player_id) && $msg->player_id == $player->player_id))
        {
            echo $msg->timestamp.' | '.$msg->message.'<br />';
            $msgtime = $msg->timestamp;
        }
    }
    echo '</messages>';
    
    $maxmaptime = null;
    
    echo '<maps>';
        $mapstosend;
        $index = 0;
        $br = '-br-';
        
        $p = '*p*';
        $pend = '*zp*';
        
        foreach($maps as $map)
        {
            if (!isset($strength[$index]->sum_strength))
                $strength[$index]->sum_strength = 0;
            if (!isset($capacity[$index]->sum_capacity))
                $capacity[$index]->sum_capacity = 0;
            
            if (strlen($map->name) > 4 && $map->width < 100)
                $map->shortname = substr($map->name, 0, 4).'.';
            else
                $map->shortname = $map->name;
            
            $id = $map->name;
            $id = str_replace('.', '', $id);
            $id = str_replace(' ', '', $id);
            
            unset($m);
            $m = new stdClass();
            $m->id = $id;
            $m->html = $p.( $map->is_capital ? '*cap* ' : '' );
            $m->html .= ( $map->is_regional ? '*reg* ' : '' );
            
            if (isset($map->factory_id))
                $m->html .= ( $map->is_damaged == 0 ? '*fac*' : '*facdmg*' );
            if ($map->num_leaders > 0)
                $m->html .= ' -span-L-endspan-';
            
            $m->html .= $br;
            
            $m->html .= '-span-'.$map->shortname.' '.$map->resource.'-endspan-'.
                    $br.($map->num_units > 0 ? 'M:'.$map->num_units.'/'.$strength[$index]->sum_strength : '').''.
                    $br.($map->num_jumpships > 0 ? 'J:'.$map->num_jumpships.'/'.$capacity[$index]->sum_capacity : '').''.
                    $pend;
            $m->css = $map->color;
            $mapstosend[] = $m;
            $index++;
            
            // Map times
            if (!isset($maxmaptime))
                $maxmaptime = new DateTime($map->last_update);
            else
            {
                $thistime = new DateTime($map->last_update);
                if ($thistime > $maxmaptime)
                    $maxmaptime = $thistime;
            }
            
        }
        if (isset($mapstosend))
            echo json_encode($mapstosend);
        
    echo '</maps>';
    
    echo '<chattime>'.$chattime.'</chattime>';
    echo '<msgtime>'.$msgtime.'</msgtime>';
    
    if (isset($maxmaptime))
    {
        $maptime = $maxmaptime->format('Y-m-d H:i:s');
    }
    echo '<maptime>'.$maptime.'</maptime>';

    
    echo '<year>'.$game->year.'</year>';

    echo '<turn>'.$game->turn.'</turn>';
    if ( isset($current_player->faction) )
        echo '<current_player>'.$current_player->faction.'</current_player>';
    echo '<phase>'.$game->phase.'</phase>';
    echo '<cbills>'.(isset($player->money) ? $player->money : '').'</cbills>';
    echo '<tech>'.(isset($player->tech_level) ? $player->tech_level : '').'</tech>';
    
    // Handle waiting on list and look at what the done button text should be...
    
    echo '<waitingon>';
    if ($game->phase == 'Player Setup')
    {
        if ( isset( $current_player->faction ) )
            echo $current_player->faction;
        else
            echo 'NA';
    }
    else if ($mercs === true)
    {
        echo 'Mercenary Bids: ';
        $str = '';
        foreach($merc_bids as $b)
        {
            if (!isset($b->offer))
                $str .= $b->faction.', ';
        }
        echo substr($str, 0, -2);
    }
    else if ($periphery === true)
    {
        echo 'Periphery Bids: ';
        $str = '';
        foreach($bids as $b)
        {
            if (!isset($b->offer))
                $str .= $b->faction.', ';
        }
        echo substr($str, 0, -2);
    }
    else if (isset($player->player_id) && $player->combat_done && $game->phase == 'Combat')
    {
        $str = '';
        foreach($players as $p)
        {
            if (!$p->combat_done)
                $str .= $p->faction.', ';
        }
        echo substr($str, 0, -2);
    }
    else
    {
        if($game->phase == 'Combat')
            echo 'Click DONE to view...';
        else 
        {
            if ($game->phase == 'Mercenary Phase' && count($mercs_to_place) > 0)
            {
                foreach($players as $p)
                {
                    if ($p->player_id == $mercs_to_place[0]->owner_id)
                    {
                        echo $p->faction;
                        break;
                    }
                }
            }
            else if ( isset( $current_player->faction ) )
                echo $current_player->faction;
            else
                echo 'NA';
        }
    }
    echo '</waitingon>';
    
    if ( isset($player->player_id) && isset($current_player->player_id) )
    {
        if ($current_player->player_id == $player->player_id && $current_player->combat_done != true && $game->phase != 'Mercenary Phase')
        {
            echo '<enabledone>true</enabledone>';
        }
        else if ( $player->combat_done != true && $game->phase == 'Combat' )
        {
            echo '<enabledone>true</enabledone>';    
        }
        else
            echo '<enabledone>false</enabledone>';
    }
    else
        echo '<enabledone>false</enabledone>';
    
    // Handle undo link
    if ($enableundo)
    {
        echo('<enableundo>true</enableundo>');
    }
    else 
    {
        echo('<enableundo>false</enableundo>');
    }
    
    // Done Text
    echo '<donetext>';
        if ($game->phase == 'Combat' && $game->combat_rnd > 0)
            echo 'Done with Combat and Retreating';
        else if ($game->phase == 'Combat')
            echo 'Done with Combat';
        else if ($game->phase == 'Movement')
            echo 'Done with Movement';
        else if ($game->phase == 'Production')
            echo 'Done with Production';
        else if ($game->phase == 'Player Setup')
            echo 'Done with Unit Placement';
        else if ($game->phase == 'Draw')
            echo 'Draw Cards';
        else
            echo 'Done... Unknown state...';
    echo '</donetext>';
    
    
    echo '<timer>';
        echo $timer;
    echo '</timer>';
    
    echo "</response>";
?>
