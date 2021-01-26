<h1>Available Maps</h1>

<table>
    <thead>
        <th>Name</th>
        <th>Designer</th>
        <th>Status</th>
        <th>Created On</th>
        <th>Last Modified On</th>
        <th>&nbsp;</th>
    </thead>
    <tbody>
        <?php foreach($maps as $map): ?>
        <tr>
            <td><?php echo $map->name; ?></td>
            <td><?php echo $map->username; ?></td>
            <td><?php echo ($map->is_draft ? 'Draft' : 'Released'); ?></td>
            <td><?php echo $map->created_on; ?></td>
            <td><?php echo $map->modified_on; ?></td>
            <td><?php echo anchor('map/view/'.$map->world_id, 'VIEW'); ?></td>
        </tr>        
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (count($maps) == 0): ?>
    There don't seem to be any maps yet...
<?php endif; ?>

<br />

<h3>
    <?php echo anchor('map/create', 'Create a New Map'); ?>
<h3>

