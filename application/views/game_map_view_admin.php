<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/tablesorter.js"'; ?>></script> 

<script type="text/javascript">
    $(document).ready(function() 
        { 
            $(".sortable").tablesorter(); 
        } 
    ); 
</script>

<h2>Admin View Map for <?php echo $game->title; ?></h2>

<?php echo anchor('game/view_admin/'.$game->game_id, '<< Back to the Admin View');?>
<br /><br />

<table class="sortable tablesorter" cellspacing=10>
    <thead>
    <tr>
        <th>Territory</th>
        <th>ID</th>
        <th>Map ID</th>
        <th>Owner</th>
        <th>Is Contested</th>
    </tr>
    </thead>
    <tbody>
<?php foreach( $territories as $t ): ?>
        <tr>
            <td><?php echo $t->name; ?></td>
            <td><?php echo $t->territory_id; ?></td>
            <td><?php echo $t->map_id; ?></td>
            <td><?php echo anchor('territory/change_owner/'.$t->territory_id, $t->faction); ?></td>
            <td><?php echo anchor('territory/toggle_contested/'.$t->territory_id, ($t->is_contested ? 'YES' : 'NO')); ?></td>
        </tr>
<?php endforeach; ?>
    
    </tbody>
</table>