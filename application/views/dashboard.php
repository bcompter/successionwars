<div id="contentwrapper">
<div id="contentcolumn">
    <?php if (isset($admin->dashboard_message)): ?>
        <div class="notice"><?php echo $admin->dashboard_message; ?></div>
    <?php endif; ?>
        
    <h1>Game Dashboard</h1>
    <br />
    <p>
        Welcome to the game dashboard for Succession Wars.
    </p>
    
<p> Support the site by buying me a ko-fi! <br />
<?php echo anchor("https://ko-fi.com/successionwars", "https://ko-fi.com/successionwars"); ?>
</p>

    <table>
<tr>
<td>
<?php
    if ($gamesswapped != null && count($gamesswapped)>0)
    {
        echo '<div class="box2">';
        echo '<h3>Swapped in as Admin!!!</h3><br />';
        foreach($gamesswapped as $gameswapped)
        {
            echo anchor('game/view/'.$gameswapped->game_id, $gameswapped->title);
            echo '<br />';
        }
        echo '<br /></div>';
    }
    // DISPLAY LINK TO HELP LIST OF ADMIN AND HELP REQUESTS > 0 
    if ($helpgames != null && count($helpgames) > 0)
    {
        echo '<div class="box3">';
        echo '<h3>Games need help!</h3><br />';
        echo anchor('game/view_games_needing_help', 'View Games Needing Help ('.count($helpgames).')');
        echo '<br /><br /></div>';
    } 
?>

</td>
</tr>
    <tr>
    <td>
    
    <div class="box2">
        <h3>Right Now</h3>
        <br />
        There are <?php echo count($trackerstovote); ?> trackers you have yet to vote on. 
        <?php echo ' | '.anchor('bugtracker/view_to_vote','VIEW'); ?>
        
    </div>
    
    <div class="box3">
        <h3>You are the Game Owner of</h3>
        <br />
        
        <?php if (count($gamescreated) == 0): ?>
            <ul>

                <li>You Have Not Created any Games.</li>

            </ul>
        <?php endif; ?>
        
        <ul>
            <?php foreach($gamescreated as $game): ?>
            <li><?php echo anchor('game/view/'.$game->game_id, $game->title) ?></li>
            <?php endforeach; ?>
        </ul>
        
    </div>
    
    
    </td></tr>
    <tr><td>
    
    <div class="box2">
        <h3>Games You are Playing In</h3>
            ! Game requires your attention
        <br /><br />
        <ul>
            <?php foreach($gamesplaying as $game): ?>
                <?php if ($game->display): ?>
                    <li><?php echo ($game->needsattention == 1? '! ':'').anchor('game/view/'.$game->game_id, $game->title) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        
    </div>
    
    <div class="box3">
        <h3>Recent Games</h3>
        <br />
        <ul>
            <?php foreach($gamesrecent as $game): ?>
            <li><?php echo anchor('game/view/'.$game->game_id, $game->title) ?></li>
            <?php endforeach; ?>
        </ul>
        
    </div>
    </td>
    </tr>
    </table>
    
</div>
</div>

<?php $this->load->view('dashboard_sidebars'); ?>
