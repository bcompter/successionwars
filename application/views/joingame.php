<h1>Succession Wars | Join Game</h1>
<br /><br />
<?php echo form_open("game/join/".$game->game_id);?>
    	
<p>
    <label for="faction">Choose a Faction:</label>
    <select name="faction">
        <?php
            foreach( $players as $player )
            {
                echo '<option value="'.$player->player_id.'">'.$player->faction.'</option>';
            }
        ?>
    </select>
    
</p>

<?php
    // Display the password entry if the game is private
    if ( $is_private )
    {
        echo '<p> This game is private, please enter the password.';
        echo form_input($password);
        echo '</p>';
    }
?>

<p><?php echo form_submit('submit', 'Join the Game');?></p>
      
<?php echo form_close();?>