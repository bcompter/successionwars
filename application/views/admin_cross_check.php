<h3>Cross Check</h3>

<table>
    <tr>
        <th>Id</th>
        <th>Game Id</th>
        <th>Name</th>
        <th>Owner Id</th>
        <th>Location Id</th>
    </tr>
<?php foreach ($units as $u): ?>
    <tr>
        <td><?php echo $u->combatunit_id; ?></td>
        <td><?php echo $u->game_id; ?></td>
        <td><?php echo $u->name; ?></td>
        <td><?php echo $u->owner_id; ?></td>
        <td><?php echo $u->location_id; ?></td>
        <td><?php echo anchor('admin/delete_unit/'.$u->combatunit_id, 'DELETE'); ?></td>
    </tr>
<?php endforeach; ?>
</table>
