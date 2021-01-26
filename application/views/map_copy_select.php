<h1>Select an Existing Map to Copy into: <?php echo $world->name; ?></h1>
<br />
<table>
    <?php foreach($otherworlds as $w): ?>
    <?php if ($w->world_id != $world->world_id): ?>
    <tr>
        <td><?php echo $w->name; ?></td>
        <td><?php echo anchor('map/copy/'.$world->world_id.'/'.$w->world_id, ' COPY '); ?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
</table>