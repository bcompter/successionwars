<h2><?php echo $game->title; ?></h2>

<p>
    <?php echo $game->description; ?>
</p>

<p>
    Game Owner: <?php echo $owner->username; ?>
</p>
<p>
    Created On: <?php echo $game->created_on; ?>
</p>

<h2>Players / Slots

<?php 
    if ($is_playing && $game->phase != 'Setup')
    {
        echo ' | '.anchor('game/play/'.$game->game_id,'PLAY');
    }
    else if (!$is_playing && $game->phase != 'Setup')
    {
        echo ' | '.anchor('game/play/'.$game->game_id,'VIEW GAME');
    }
    else if ($game->phase == 'Setup' && !$is_playing)
    {
        if ( $game->built )
        {
            if ( $open_slots > 0 )
                echo ' | '.anchor('game/join/'.$game->game_id,'JOIN GAME').' | '.anchor('game/play/'.$game->game_id,'VIEW GAME');
            else
                echo ' | Game is Full'.' | '.anchor('game/play/'.$game->game_id,'VIEW GAME');
        }
        else
            echo ' | (Waiting On Game Build)';
    }
?>

</h2> 
<p>
<ul>
    <?php
    if ( count($players) == 0 )
        echo '<li>There are no players in this game.  How odd.</li>';
    else
    {
        echo '<table>';
        echo '<tr><th>Player</th><th>&nbsp</th><th>Last Login</th></tr>';
        foreach( $players as $player )
        {
            echo '<tr><td>'.$player->faction.'</td><td>';
            if ( isset($player->user_id) )
            {
                // display user information
                $date = new DateTime(date("c", $player->last_login));
                
                
                echo ' - Played by '.$player->username.'</td>';
                echo '<td class="space_left">'.$date->format('Y-m-d H:i:s').'</td>';
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
</ul>
    
</p>

<?php if ($is_playing): ?>

<table cell-padding='10'>
    <tr>
        <td>
            <h3>Game Owner Gone AFK?</h3>
            <p>
                <?php echo anchor('player/vote/'.$game->game_id, 'Vote For A New One Here'); ?>
            </p>
        </td>
        <td>
            <h3>Need a Break?</h3>
            <p>
                <?php echo anchor('game/boot/'.$this_player->player_id, 'Leave this Game'); ?>
            </p>
        </td>
    </tr>
</table>
<?php endif; ?>

<h2>
    Game Settings
    <?php 
    
        if ( isset($this->ion_auth->get_user()->id ) )  // Check from logged in
        {
            // display the deck view link 
            echo ' | '.anchor('game/view_deck/'.$game->game_id,'Game Deck');
            
            if ( $this->ion_auth->get_user()->id == $game->creator_id )
            {
                // display the change settings link for the game creator
                echo ' | '.anchor('game/edit/'.$game->game_id,'EDIT Game');
                // display the options link for the game creator
                echo ' | '.anchor('game/options/'.$game->game_id,'Game Options');
                // display the change settings link for the game creator if the game has been built
                if ($game->built)
                {
                    echo ' | '.anchor('game/game_tools/'.$game->game_id,'GAME TOOLS');
                }
                if (( $this->ion_auth->logged_in() ) && ( $this->ion_auth->is_admin() ))
                    echo ' | '.anchor('game/view_admin/'.$game->game_id,'VIEW ADMIN');
                
                // display build link or start link if the game creator
                if ($game->phase == 'Setup')
                {
                    if (!$game->built)
                        echo ' | '.anchor('game/new_build/'.$game->game_id,'BUILD GAME', 'class="menu"');
                    else if ($game->phase == 'Setup')
                    {
                        $open = true;
                        foreach($players as $player)
                        {
                            if (!isset($player->user_id))
                                $open = false;
                        }
                        if ($open && $game->turn != 0)
                            echo ' | '.anchor('game/playersetup/'.$game->game_id,'RESUME GAME', 'class="menu"');
                        else if ($open)
                            echo ' | '.anchor('game/playersetup/'.$game->game_id,'BEGIN PLAYER SETUP', 'class="menu"');
                        else
                            echo ' | (Waiting On Players To Join)';
                    }
                    
                }
                else if ($game->phase == 'Player Setup' && $game->turn == 0)
                    echo ' | '.anchor('game/start/'.$game->game_id,'START GAME', 'class="menu"');  
                else if ($game->phase == 'Player Setup' && $game->turn != 0)
                    echo ' | '.anchor('game/start/'.$game->game_id,'RESUME GAME', 'class="menu"'); 

                
            }
            // Display links for logged in Admin
            else if (( $this->ion_auth->logged_in() ) && ( $this->ion_auth->is_admin() ))
            {
                // display the change settings link for the game creator
                echo ' | '.anchor('game/game_tools/'.$game->game_id,'GAME TOOLS');
                echo ' | '.anchor('game/view_admin/'.$game->game_id,'VIEW ADMIN');
            }
        }
    ?>
     
</h2>
<?php if ($game->alt_victory): ?>
<p>
<h3>Victory Conditions 
    <small>
        (<?php echo anchor('victory_conditions/add/'.$game->game_id, 'add'); ?>) 
        (<?php echo anchor('victory_conditions/delete_all/'.$game->game_id, 'delete all'); ?>)
    </small>
</h3>
<table>
    <tr>
        <th>Player</th>
        <th>Victory Condition</th>
        <th>Threshold</th>
        <th>Duration</th>
        <th>Counter</th>
        <th>&nbsp;</th>
    </tr>
    
    <?php foreach ($conditions as $c): ?>
    <tr>
        <td><?php echo ($c->player_id == 0 ? 'Everyone' : $c->faction); ?></td>
        <td><?php echo $c->type; ?></td>
        <td><?php echo $c->threshold; ?></td>
        <td><?php echo $c->duration; ?></td>
        <td><?php echo $c->current_duration; ?></td>
        <td>
            <?php if (isset($user->id) && $user->id == $game->creator_id): ?>
                <?php echo anchor('victory_conditions/edit/'.$c->condition_id, 'edit'); ?> | 
                <?php echo anchor('victory_conditions/delete/'.$c->condition_id, 'delete'); ?>
            <?php endif; ?>
        </td>
    </tr> 
    <?php endforeach; ?>
    
</table>
</p>
<?php endif; ?>
<p>
    <h3>Game Options</h3>
    <table>
        <tr>
            <th>Option</th><th>Description</th><th>Setting</th>
        </tr>
        <tr>
            <td>Capitals to Win</td>
            <td>The number of Capitals you need to control at the beginning of your turn to win the game.</td>
            <td><?php echo ($game->capitals_to_win); ?></td>
        </tr>
        <tr>
            <td>Use Mercenary Phase</td>
            <td>Each turn, unaffiliated Mercenaries will be auctioned off before the production phase.</td>
            <td><?php echo ($game->use_merc_phase ? 'YES' : 'NO'); ?></td>
        </tr>
        <tr>
            <td>Destroy Jumpships</td>
            <td>Captured jumpships are destroyed instead of captured.</td>
            <td><?php echo ($game->destroy_jumpships ? 'YES' : 'NO'); ?></td>
        </tr>
        <tr>
            <td>Factory Damage Modifier</td>
            <td>Attackers may turn off the force size factory damage modifier.</td>
            <td><?php echo ($game->auto_factory_dmg_mod ? 'NO' : 'YES'); ?></td>
        </tr>
        <tr>
            <td>Use Comstar</td>
            <td>Players may bribe Comstar to interdict in regions.</td>
            <td><?php echo ($game->use_comstar ? 'YES' : 'NO'); ?></td>
        </tr>
        <tr>
            <td>Terra Loot</td>
            <td>The first house to take control of Terra will gain 10 Technology, 25 C-Bills, and two 7 strength units.<br> Subsequent houses that take control of Terra will gain 10 Technology.  Losing Terra results in losing 10 Technology.</td>
            <td><?php echo ($game->use_terra_loot ? 'YES' : 'NO'); ?></td>
        </tr>
        <tr>
            <td>Terra Interdict</td>
            <td>Controlling Terra will induce a special ComStar house interdict (aka the Terran Interdict).</td>
            <td><?php echo ($game->use_terra_interdict ? 'YES' : 'NO'); ?></td>
        </tr>
        <tr>
            <td>Expanded Jumpships</td>
            <td>Allows the building of jumpship sizes 7, 9, and 12.</td>
            <td><?php echo ($game->use_extd_jumpships ? 'YES' : 'NO'); ?></td>
        </tr>
    </table>
</p>

<p>
<ul>
    <li>Order of Battle: <?php echo $orderofbattle->name; ?></li>
    <li>Year: <?php echo $game->year; ?></li>
    <li>Turn: <?php echo $game->turn; ?></li>
    <li>Players: <?php echo count($players); ?></li>
    <li>Locations: <?php echo count($territories); ?></li>
    <li>Jumpships: <?php echo count($jumpships); ?></li>
    <li>Leaders: <?php echo count($leaders); ?></li>
    <li>Cards: <?php echo count($cards); ?></li>
    <li>Units: <?php echo count($units); ?></li>
    <li>Factories: <?php echo count($factories); ?></li>
</ul>
</p>
