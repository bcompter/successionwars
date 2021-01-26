<h2>Game Owner Tools for <?php echo $game->title; ?></h2>

<p><?php echo anchor('game/view/'.$game->game_id, '<< Back to the Game View');?></p>

<p>These game tools allow game owners to adjust certain values in the game to correct
    for code mistakes, grievances, or to otherwise screw with your buddies.
</p>
<p> Most features are implemented at this time.  
    Use with caution.    
</p>

<h3>Players / Slots </h3>

<?php


if ( count($players) == 0 )
    echo '<li>There are no players in this game.  How odd.</li>';
else
{
    echo '<table cellspacing="5px">';
    echo '<tr><th>Faction</th><th>Played By</th><th>Combat Done</th><th>Technology</th><th>Cbills</th><th>House Interdict Turns</th><th>Cards</th><th>Boot Player</th><th>Force To Done</th><th>Force Bid to Zero</th></tr>';
    
    foreach( $players as $player )
    { 
        echo '<tr>';
        echo '<td>'.$player->faction;
        if ( isset($player->user_id) )
        {
            // display user information
            echo '</td><td>'.$player->username;
        }
        else
        {
            echo '</td><td> - Open Slot';   
        }
        echo '</td>';
        echo '<td align="center">';
        echo ($player->combat_done ? 'Yes-' : 'No-');
        echo anchor('game/toggle_combat_done/'.$player->player_id, 'TOGGLE');
        echo '</td>';
        
        echo '<td align="center">';
        echo anchor('game/adjust_tech/'.$player->player_id, $player->tech_level);
        echo '</td>';
        
        echo '<td align="center">';
        echo anchor('game/adjust_cbills/'.$player->player_id, $player->money);
        echo '</td>';
        
        echo '<td align="center">';
        echo anchor('game/adjust_house_interdict/'.$player->player_id, $player->house_interdict);
        echo '</td>';
        
        echo '<td align="center">';
        echo anchor('game/draw_card/'.$player->player_id, 'DRAW CARD');
        echo '</td>';
        
        echo '<td align="center">';
        echo (isset($player->user_id) ? anchor('game/boot/'.$player->player_id, 'BOOT') : 'NA') ;
        echo '</td>';
        
        echo '<td align="center">';
        echo (isset($player->user_id) ? anchor('sw/done/'.$game->game_id.'/'.$player->player_id, 'FORCE DONE', 'class="menu"') : 'NA') ;
        echo '</td>';
        
        echo '<td align="center">';
        echo (isset($player->user_id) ? anchor('game/force_bid/'.$player->player_id, 'FORCE BID', 'class="menu"') : 'NA') ;
        echo '</td>';
        
        echo '</tr>';
    }
    echo '</table>';
}

    echo '<br />';
    
?>   

<p>
    <h3>Help Status</h3>
    <p>
        If you encounter a bug that prevents you from continuing your game we can help.  Setup a help request using the options below.
    </p>
    <?php if (isset($game_help->status)): ?>
    <?php if ($game_help->status == 1 || $game_help->status == 2): ?>
        <h3>Help Requested!</h3>
        <h4>Last Update: <?php echo $game_help->time_stamp; ?></h4>
        <?php if ($game_help->status == 2): ?>
            <div class="warn">This has been marked as RESOLVED by the admins. Please confirm below.</div>
            <h4><?php echo anchor('game/change_help_status/'.$game->game_id.'/0', 'All Fixed Up!'); ?></h4>
            <h4><?php echo anchor('game/change_help_status/'.$game->game_id.'/1', 'Still Broken!'); ?></h4>
        <?php endif; ?>
        <p>
            <h4>Description</h4>
            <?php echo $game_help->description; ?>
            <h5><?php echo anchor('game/edit_help_description/'.$game_help->help_id, 'EDIT'); ?></h5>
        </p>
        <p>
            <h4>Reply</h4>
            <?php echo $game_help->reply; ?>
            <h5><?php if ($is_admin) echo anchor('game/edit_help_reply/'.$game_help->help_id, 'EDIT'); ?></h5>
            <h5><?php if ($is_admin) echo anchor('game/change_help_status/'.$game_help->game_id.'/2', 'Mark Resolved'); ?></h5>
        </p>
    
    <?php endif; ?>
    <?php else: ?>
        <h3><?php echo anchor('game/change_help_status/'.$game->game_id.'/1', 'Ask for Help'); ?></h3>
    <?php endif; ?>
    
</p>

<div id="gameinfo"></div>

<h3>Advanced Options </h3>
<br />

<h4>Set Game Phase</h4>

Current Game Phase: <?php echo $game->phase; ?>

<ul>
    <?php echo ''.($game->use_merc_phase == TRUE ? '<li>'.anchor('game/set_game_phase/'.$game->game_id.'/mercenary_phase','Mercenary Phase').'</li>':'');
    ?>
    <li><?php echo anchor('game/set_game_phase/'.$game->game_id.'/setup','Setup'); ?></li>
    <li><?php echo anchor('game/set_game_phase/'.$game->game_id.'/draw','Draw'); ?></li>
    <li><?php echo anchor('game/set_game_phase/'.$game->game_id.'/production','Production'); ?></li>
    <li><?php echo anchor('game/set_game_phase/'.$game->game_id.'/movement','Movement'); ?></li>
    <li><?php echo anchor('game/set_game_phase/'.$game->game_id.'/combat','Combat'); ?></li>
    <li><?php echo anchor('game/set_game_phase/'.$game->game_id.'/game_over','Game Over'); ?></li>
</ul>

<h4>Set Player Playing</h4>

Current Player Playing: <?php echo (isset($player_playing->faction) ? $player_playing->faction : 'None'); ?>

<table>
    <?php foreach($players as $player):?>
    
    <tr><td><?php echo $player->faction.'</td><td>  | '. anchor('game/set_player_playing/'.$game->game_id.'/'.$player->player_id,'Set To Playing'); ?></td></tr>
    
    <?php endforeach; ?>
</table>

<br />

<h4>Delete</h4>
<?php echo 'Delete This Game | '.anchor('game/delete/'.$game->game_id,'DELETE'); ?>


<script type="text/javascript">
    
// Document load
$(document).ready(function() 
{   
    // Menu links
    $("body").delegate(".menu","click", function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Form the link to be used...
        $url = $(this).attr('href');

        // Send to server, handle xml response    
        $.post( $url,
        function(xml)
        {               
            // Set info content to the server response
            var msgs = $("info",xml).html();
            $("#gameinfo").html( msgs );
        });

    }); 
});
</script>
