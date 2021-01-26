<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/tablesorter.js"'; ?>></script> 

<script type="text/javascript">
    $(document).ready(function() 
        { 
            $(".sortable").tablesorter(); 
        } 
    ); 
</script>

<h1>Succession Wars | All Games</h1>

<p>
<table class="sortable tablesorter" cellspacing=10>
    <thead>
    <tr>
        <th>Game Name</th>
        <th>Created On</th>
        <th>Privacy Setting</th>
        <th>Game Turn</th>
        <th>Current Player</th>
        <th>Phase</th>
    </tr>
    </thead>
    <tbody>
<?php

    foreach( $games as $game )
    {
        echo '<tr>  <td>'.$game->title.'</td>
                    <td> | '.$game->created_on.'</td>
                    <td> | '.($game->password=='' ? 'Public' : 'Private').'</td>
                    <td> | Round '.$game->turn.'</td> 
                    
                    <td> | '.$game->faction.'</td>
                    <td> | '.$game->phase.'</td>';    
            
        if (isset($game->player_id))
            echo '<td> | '.($game->turn_order == 0 ? 'Eliminated' : 'Active').'</td>';
                echo '<td> | '.anchor('game/view/'.$game->game_id,'VIEW').'</td>';
        echo '</tr>';
    }

?>
    </tbody>
</table>
</p>
