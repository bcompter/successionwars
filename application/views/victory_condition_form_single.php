<div class="center">
    <h1>Edit a Victory Condition for <?php echo $game->title; ?></h1>
    <h3>Faction: <?php echo $player->faction; ?></h3>
    <br /><br />

    <?php 

        $errors = validation_errors();
        if ($errors != '')
            echo '<div class="error">'.$errors.'</div>';
    ?>

    <?php echo form_open("victory_conditions/edit/".$condition->condition_id);?>
    
    <table>
        <tr>
            <th>&nbsp;</th>
            <th>Threshold</th>
            <th>Duration</th>
        </tr>
        <tr>
            <td><strong><?php echo $condition->type; ?></strong></td>
            <td><input type="text" name="threshold" value="<?php echo $condition->threshold; ?>"></td>
            <td><input type="text" name="duration" value="<?php echo $condition->duration; ?>"></td>
        </tr>
    </table>
    <br />
    <p><?php echo form_submit('submit', 'Submit');?></p>
    
    <?php echo form_close();?>
    
</div>