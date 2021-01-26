<h2>Game Owner Tools for <?php echo $game->title; ?></h2>
<?php echo anchor('game/game_tools/'.$game->game_id, '<< Back to Game Tools'); ?>

<h3>Adjusting House Interdict Turns for <?php echo $player->faction; ?></h3>

<p>Current House Interdict: <?php echo $player->house_interdict; ?></p>

<?php echo form_open("game/adjust_house_interdict/".$player->player_id);?>

<p>
<label for="status">New House Interdict:</label>
<?php 
    $data = array('size'=>'10', 'name'=>'house_interdict', 'value'=>$player->house_interdict, 'url'=>$this->config->item('base_url').'index.php/game/adjust_house_interdict/'.$player->player_id);
    echo form_input($data);
?>
</p>  

  <p><?php echo form_submit('submit', 'Submit');?></p>

