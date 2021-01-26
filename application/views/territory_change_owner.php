<h2>Admin Change Territory Owner for <?php echo $territory->name; ?></h2>

<?php echo anchor('game/view_admin_map/'.$game->game_id, '<< Back to the Admin View Territory');?>
<br /><br />

<table cellspacing=10>
    <thead>
    <tr>
        <th>New Owner</th>
        <th>&nbsp</th>
    </tr>
    </thead>
    <tbody>
<?php foreach( $players as $p ): ?>
        <tr>
            <td><?php echo $p->faction; ?></td>
            <td><?php echo anchor('territory/change_owner/'.$territory->territory_id.'/'.$p->player_id, 'SELECT'); ?></td>
        </tr>
<?php endforeach; ?>
        <tr>
            <td>Comstar</td>
            <td><?php echo anchor('territory/change_owner/'.$territory->territory_id.'/0', 'SELECT'); ?></td>
        </tr>
    
    </tbody>
</table>