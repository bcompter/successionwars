<h2>Admin Tools for <?php echo $game->title; ?></h2>

<p><?php echo anchor('game/view/'.$game->game_id, '<< Back to the Game View');?></p>

<p>
    Game Owner: <?php echo $owner->username; ?>
</p>

<h2>Players / Slots 
<?php 
    echo anchor('game/play/'.$game->game_id,'SPECTATE');
?>
    <br /><br />
<?php 
    echo anchor('game/view_admin_map/'.$game->game_id,'VIEW TERRITORIES');
?>

</h2> 
<p>
    <?php
    if ( count($players) == 0 )
        echo '<li>There are no players in this game.  How odd.</li>';
    else
    {
        echo '<table>';
        echo '<tr><th>P_ID</th><th>Player</th><th>U_ID</th><th>User</th><th>Last Login</th><th>Admin Swap</th><th>Set GO</th></tr>';
        foreach( $players as $player )
        {
            echo '<tr><td>'.$player->player_id.'</td><td>'.$player->faction.'</td><td>';
            if ( isset($player->user_id) )
            {
                // display user information
                $date = new DateTime(date("c", $player->last_login));
                
                echo $player->id.'</td>';
                echo '<td align="center">'.$player->username.'</td>';
                echo '<td class="space_left">'.$date->format('Y-m-d H:i:s').'</td>';
                echo '<td align="center">';
                    if (($playing==0) || $user->id!=$player->id)
                        echo anchor('game/swap_admin_and_player/'.$player->player_id, 'Swap').'</td>';
                    else if($swapped==1 && $user->id == $player->id)
                        echo anchor('game/swap_admin_and_player/'.$player->player_id, 'Un-Swap').'</td>';
                echo '</td>';
                echo '<td>'.anchor('game/set_game_owner/'.$game->game_id.'/'.$player->id, 'Set to Game Owner').'</td>';
            }
            else
            {
                echo ' - Open Slot</td><td></td>';   
            }
            
            echo '</tr>';
        }
        echo '</table>';
    }
    
    ?>    
    
    <p>
    Current Player Playing: <?php echo (isset($player_playing->faction) ? $player_playing->faction : 'None'); ?>
    </p>
    
    <p>
    Current Phase: <?php echo $game->phase; ?>
    </p>
    
    <p>
    <h3>Combat Logs</h3>
        <table>
            <tr><th>Territory</th><th>Territory ID</th><th>Player</th><th>Player ID</th><th>Casualties Owed</th><th>Retreat Allowed</th><th>&nbsp</th></tr>
        <?php foreach ($combatlogs as $log): ?>
            <tr>
                <td><?php echo $log->territory_name; ?></td>
                <td><?php echo $log->territory_id; ?></td>
                <td><?php echo $log->faction; ?></td>
                <td><?php echo $log->player_id; ?></td>
                <td align="center"><?php echo $log->casualties_owed; ?></td>
                <td><?php echo anchor ('game/toggle_retreat_allowed/'.$log->combatlog_id, ($log->is_retreat_allowed ? 'YES' : 'No') ); ?></td>
                <td>
                    <?php echo anchor('game/edit_combat_log/'.$log->combatlog_id,'EDIT'); ?> | 
                    <?php echo anchor('game/delete_combat_log/'.$log->combatlog_id,'DELETE'); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </table>
    <?php echo (count($combatlogs) == 0 ? 'No Combat Logs' : '') ?>
    </p>
    
    <p>
    <h3>Merc Offers</h3>
        <table>
            <tr><th>Mercenary</th><th>Player</th><th>Offer</th><th>&nbsp</th></tr>
        <?php foreach ($mercoffers as $offer): ?>
            <tr>
                <td><?php echo $offer->merc_id.' '.$offer->merc_name; ?></td>
                <td><?php echo $offer->player_name.' '.($offer->turn_order == 0 ? '[eliminated]' : ''); ?></td>
                <td><?php echo $offer->offer; ?></td>
                <td><?php echo anchor('game/edit_merc_offer/'.$offer->offer_id.'/'.$game->game_id,'EDIT'); ?> | <?php echo anchor('game/delete_merc_offer/'.$offer->offer_id.'/'.$game->game_id,'DELETE'); ?></td>
            </tr>
        <?php endforeach; ?>
        </table>
    <?php echo (count($mercoffers) == 0 ? 'No Merc Offers' : '') ?>
    </p>
    
    <p>
    <h3>Periphery Offers</h3>
        <table>
            <tr><th>Periphery</th><th>Player</th><th>Offer</th><th>&nbsp</th></tr>
        <?php foreach ($peripheryoffers as $offer): ?>
            <tr>
                <td><?php echo $offer->periphery_id.' '.$offer->periphery_name; ?></td>
                <td><?php echo $offer->player_name.' '.($offer->turn_order == 0 ? '[eliminated]' : ''); ?></td>
                <td><?php echo $offer->offer; ?></td>
                <td><?php echo anchor('periphery/delete/'.$offer->bid_id.'/'.$game->game_id,'DELETE'); ?></td>
            </tr>
        <?php endforeach; ?>
        </table>
    <?php echo (count($peripheryoffers) == 0 ? 'No Periphery Offers' : '') ?>
    </p>
    
    <p>
    <h3>Game Owner Votes</h3>   
        <table>
            <tr><th>User</th><th>Voted For</th><th>Voted On</th></tr>
        <?php foreach ($govotes as $v): ?>
            <tr>
                <td><?php echo $v->username; ?></td>
                <td><?php echo $v->vote_username; ?></td>
                <td><?php echo $v->created_on; ?></td>
            </tr>
        <?php endforeach; ?>
        </table>
     <?php echo (count($govotes) == 0 ? 'No Game Owner Votes' : '') ?>
    </p>
    
</p>

<hr />
<h4>Delete</h4>
<?php echo 'Delete This Game | '.anchor('game/delete/'.$game->game_id,'DELETE'); ?>
