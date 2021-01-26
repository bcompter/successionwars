<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/tablesorter.js"'; ?>></script> 

<script type="text/javascript">
    $(document).ready(function() 
        { 
            $(".sortable").tablesorter(); 
        } 
    ); 
</script>

<h1>Succession Wars | Inactive Games</h1>

<p>
<table class="sortable tablesorter" cellspacing=10>
    <thead>
    <tr>
        <th>Game Name</th>
        <th>Created On</th>
        <th>Privacy Setting</th>
        <th>Game Turn</th>
        <th>Phase</th>
        <th>Last Game Message</th>
        <th></th><th></th>
    </tr>
    </thead>
    <tbody>
    <?php if (isset($games) && count($games) > 0): ?>
    
        <?php foreach( $games as $game ): ?>
        <tr>  
            <td><?php echo $game->title; ?></td>
            <td><?php echo $game->created_on; ?></td>
            <td> | <?php echo ($game->password=='' ? 'Public' : 'Private'); ?></td>
            <td> | Round <?php echo $game->turn; ?></td> 
            <td> | <?php echo $game->phase; ?></td>
            <td> | <?php echo $game->timestamp; ?></td> 
            <td> | <?php echo $game->message; ?></td> 
            <td> | <?php echo anchor('game/view/'.$game->game_id,'VIEW')?></td>
            <td> | <?php echo anchor('game/game_tools/'.$game->game_id,'Game Tools'); ?></td> 
        </tr>
        <?php endforeach; ?>
    
    <?php else: ?>
    
        <tr><td>No Inactive Games!</td></tr>
    
    <?php endif; ?>
    </tbody>
    
    
</table>
</p>
