<h2>Game Owner Tools for <?php echo $game->title; ?></h2>
<?php echo anchor('game/game_tools/'.$game->game_id, '<< Back to Game Tools'); ?>

<h3>Adjusting Technology Level for <?php echo $player->faction; ?></h3>

<p>Current Technology Level: <?php echo $player->tech_level; ?></p>

<?php echo form_open("game/adjust_tech/".$player->player_id);?>

<p>
<label for="status">New Technology Level:</label>
<?php 
    $data = array('size'=>'3', 'name'=>'tech', 'value'=>intval($player->tech_level), 'url'=>$this->config->item('base_url').'index.php/game/adjust_tech/'.$player->player_id);
    echo form_input($data);
?>
</p>  

  <p><?php echo form_submit('submit', 'Submit');?></p>

