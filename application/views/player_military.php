<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
    echo "<info>";
?>


<h3><?php echo $player->faction ?> Military</h3>
<?php echo anchor('player/view/'.$player->player_id, '<< Back to Player View', 'class="menu"'); ?>

<table class="sortable tablesorter" cellspacing=3>
    <thead>
        <tr>
            <th>Name</th>
            <th>Str</th>
            <th>Pre-War</th>
            <th>Built</th>
            <th>Location</th>
        </tr>
    </thead>
    
    <tbody>
        <?php foreach($combatunits as $c): ?>
            
        <tr>
            <td><?php echo ($c->is_merc ? '* ' : '').$c->name; ?></td>
            <td align='center'><?php echo $c->strength; ?></td>
            <td align='center'><?php echo $c->prewar_strength; ?></td>
            <td align='center'><?php echo ($c->strength > 0 ? 'Yes' : 'No'); ?></td>
            <td align='center'><?php echo ($c->strength > 0 ? $c->territory_name : 'NA'); ?></td>
        </tr>
        
        <?php endforeach; ?>
    </tbody>
    
</table>

<?php
        echo '</info>';
    echo "</response>";
?>