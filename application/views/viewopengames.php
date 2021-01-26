<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/tablesorter.js"'; ?>></script> 

<script type="text/javascript">
    $(document).ready(function() 
        { 
            $(".sortable").tablesorter(); 
        } 
    ); 
</script>

<h1>Succession Wars | Open Games</h1>

<p>
<table class="sortable tablesorter" cellspacing=10>
    <thead>
    <tr>
        <th>Game Name</th>
        <th>Created On</th>
        <th>Privacy Setting</th>
        <th>Open Slots</th>
    </tr>
    </thead>
    <tbody>
<?php

    foreach( $games as $game )
    {
        echo '<tr>
                <td>'.$game->title.'</td>
                <td> | '.$game->created_on.'</td> 
                <td> | '.($game->password=='' ? 'Public' : 'Private').'</td>
                <td> | '.$game->num_open_slots.' Open Slot'.($game->num_open_slots > 1 ? 's' : '').' </td><td> | '.anchor('game/view/'.$game->game_id,'VIEW').'</td>  
            </tr>';
    }

?>
    </tbody>
</table>
</p>
