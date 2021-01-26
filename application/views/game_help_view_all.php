<h1>Games Needing Help</h1>
<?php echo anchor('admin', '<< Back to Admin'); ?>
<br />
<table class="striped">
    <tr>
        <th>Id</th>
        <th>Game</th>
        <th>Status</th>
        <th>&nbsp</th>
        <th>GO's Description</th>
        <th>Admin Reply</th>
    </tr>

<?php foreach($games as $game): ?>
    <tr>
        <td><?php echo $game->game_id; ?></td>
        <td><?php echo $game->title; ?></td>
        <?php
            $status[0] = 'Normal';
            $status[1] = 'Help!';
            $status[2] = 'Resolved';
        ?>
        <td><?php echo $status[$game->status]; ?></td>
        <td><?php echo anchor('game/view/'.$game->game_id, 'VIEW'); ?> |
            <?php echo anchor('game/play/'.$game->game_id, 'PLAY/SPECTATE'); ?> |
            <?php echo anchor('game/game_tools/'.$game->game_id, 'GAME TOOLS'); ?> |
            <?php echo anchor('game/view_admin/'.$game->game_id, 'VIEW ADMIN'); ?> </td>
        <td><?php echo $game->help_description; ?></td>
        <td><?php echo $game->reply; ?></td>
    </tr>
<?php endforeach; ?>
    
</table>