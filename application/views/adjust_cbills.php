<h2>Game Owner Tools for <?php echo $game->title; ?></h2>
<?php echo anchor('game/game_tools/'.$game->game_id, '<< Back to Game Tools'); ?>

<h3>Adjusting CBills for <?php echo $player->faction; ?></h3>

<p>Current CBills: <?php echo $player->money; ?></p>

<?php echo form_open("game/adjust_cbills/".$player->player_id);?>

<p>
<label for="status">New CBills:</label>
<?php 
    $data = array('size'=>'10', 'name'=>'cbills', 'value'=>$player->money, 'url'=>$this->config->item('base_url').'index.php/game/adjust_cbills/'.$player->player_id);
    echo form_input($data);
?>
</p>  

<p><?php echo form_submit('submit', 'Submit');?></p>

