<script type="text/javascript">                                         
    // Set variables
    <?php echo 'var $loadMapUrl = \''.$this->config->item('base_url').'index.php/map/load/'.$game->game_id.'\';'; ?>
    <?php echo 'var $loadChatUrl = \''.$this->config->item('base_url').'index.php/chat/load_chat/'.$game->game_id.'\';'; ?>
    <?php echo 'var $updateurl = \''.$this->config->item('base_url').'index.php/sw/update/'.$game->game_id.'\';'; ?>
    <?php echo 'var $doneurl = \''.$this->config->item('base_url').'index.php/sw/done/'.$game->game_id.'\';'; ?>
        
    <?php echo 'var $regionalImg = \''.$this->config->item('base_url').'images/regional_10.png\';'; ?>
    <?php echo 'var $capitalImg = \''.$this->config->item('base_url').'images/capital_12.png\';'; ?>
    <?php echo 'var $factoryImg = \''.$this->config->item('base_url').'images/factory_12.png\';'; ?>
    <?php echo 'var $factoryDmgImg = \''.$this->config->item('base_url').'images/factory_damaged_12.png\';'; ?>
        
</script>
<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/playgame.js"'; ?>></script>
<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/tablesorter.js"'; ?>></script>

<div class="box1">
    <div id="playerinfo">
        <h3><?php echo (isset($player) ? $player->faction : 'Spectator'); ?></h1>
        <table>
            <tr><td><?php echo (isset($player) ? 'CBills: ' : ''); ?><div id="cbills" class="inline"> <?php echo (isset($player) ? $player->money : ''); ?></div></td></tr>
            <tr><td><?php echo (isset($player) ? 'Technology: ' : ''); ?><div id="tech" class="inline"><?php echo (isset($player) ? $player->tech_level : ''); ?></div></td></tr>
            <tr><td><?php echo (isset($player) ? ($player->house_interdict ? '*** UNDER HOUSE INTERDICT ***' : '') : ''); ?></td></tr>
        </table>
        <div id="done_container"><div id="done"><?php echo anchor('sw/done/'.$game->game_id,'Done', 'class="done"'); ?></div></div>
        <div id="zoom-buttons"> 
            <?php echo anchor('#', 'ZOOM IN', 'id="zoom_in"'); ?>
             | 
            <?php echo anchor('#', 'ZOOM OUT', 'id="zoom_out"'); ?>
        </div>
        <div id="undo_move">
            <?php echo anchor('jumpship/undo_movement/'.$game->game_id, 'UNDO LAST MOVE', 'class="menu"'); ?>
        </div>
    </div>

    <div id="gamestatus">
        <h3><?php echo $game->title; ?></h1>
        <table>
            <tr>
                <td>Year: <div id="year" class="inline"><?php echo $game->year; ?></div></td>
                <td>Update: <div id="update" class="inline">Running</div></td>
            </tr>
            <tr>
                <td>Round: <div id="turn" class="inline"> <?php echo $game->turn; ?></div></td>
                <td>Last Action: <div id="timer" class="inline"> loading... </div></td>
            </tr>
            <tr>
                <td colspan="2">Current Player: <div id="current_player" class="inline">
                <?php 
                if (isset($player_playing->faction))
                    echo $player_playing->faction; 
                else
                    echo 'None';
                ?></div>
                </td>
            </tr>
            <tr><td>Phase: <div id="phase" class="inline"> <?php echo $game->phase; ?></div></td></tr>
            <tr><td colspan="2">Waiting On: <div id="waitingon" class="inline"></div></td></tr>

        </table>
    </div>
</div>
    
<ul class="menub">
    
    <?php
        if ($game->phase == 'Player Setup' && isset($player))
        {
            echo '<li class="top">'.anchor('game/place/'.$game->game_id,'<span>Place Units</span>', 'class="menu top_link"').'</li>';
        }
        else if ($game->phase != 'Setup' && isset($player))
        {
            echo '<li class="top">'.anchor('player/view_all/'.$game->game_id,'<span>Factions</span>', 'class="menu top_link"').'</li>'; 
            echo '<li class="top">'.anchor('sw/mercs/'.$game->game_id,'<span>\'Mercs</span>', 'class="menu top_link"').'</li>';
            echo '<li class="top">'.anchor('periphery/view/'.$game->game_id,'<span>Periphery</span>', 'class="menu top_link"').'</li>'; 
            echo '<li class="top">'.anchor('jumpship/jumpships/'.$game->game_id,'<span>Jumpships</span>', 'class="menu top_link"').'</li>'; 
            echo '<li class="top">'.anchor('sw/viewcombats/'.$game->game_id,'<span>Combats</span>', 'class="menu top_link"').'</li>';
            echo '<li class="top">'.anchor('cards/view/'.$player->player_id,'<span>Cards</span>', 'class="menu top_link"').'</li>'; 
            if ($game->use_comstar)
                echo '<li class="top">'.anchor('comstar/view/'.$player->game_id,'<span>Comstar</span>', 'class="menu top_link"').'</li>';
            
            echo '<li class="top">'.anchor('production/view_all/'.$game->game_id,'<span>Production</span>', 'class="menu top_link"').'</li>'; 
            echo '<li class="top">'.anchor('technology/view/'.$player->player_id,'<span>Technology</span>', 'class="menu top_link"').'</li>';
        }
 
    ?>
</ul>
<div class="box1 fluid">
    <div id="mapcontainer">
        <div id="map">

        </div>
    </div>

    <div id="info"></div>
</div>

<div id="chatdiv"></div><div id="gamemsgs"></div>
<div class="box1 fluid">
<?php 
if ($is_playing)
{
    
    echo '<h3>Public Chat</h3>';
    $attributes = array('id'=>'public_chat');
    echo form_open('chat/chat_public/'.$game->game_id, $attributes);
    $message_input=array('name'=>'public_message','value'=>'','size'=>'80','maxlength'=>'200');
    echo form_input($message_input);  
    echo form_submit('chat', 'Public Message'); 
    echo form_close();
    
    echo '<h3>Private Chat</h3>';
    $attributes = array('id'=>'private_chat');
    echo form_open('chat/chat_private/'.$game->game_id, $attributes);
    $message_input=array('name'=>'private_message','value'=>'','size'=>'80','maxlength'=>'200');
    echo form_input($message_input);  
    
    echo '<select id="option">';
    echo '<option value="0">- Select -</option>';
    foreach($players as $p)
    {
        if ($p->player_id != $player->player_id)
            echo '<option value="'.$p->player_id.'">Private Message to '.$p->faction.'</option>';
    }
    echo '</select>';
    
    echo ' | '.anchor('chat/chat_log/'.$game->game_id, 'Chat Log', 'class="menu"');
    echo ' | '.anchor('game/game_log/'.$game->game_id, 'Game Log', 'class="menu"');
    echo form_submit('chat', 'Private Message'); 
    echo form_close();
}
?>
</div>


