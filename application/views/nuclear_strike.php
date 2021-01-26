<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
?>
<h3>Nuclear Strike - Select Target</h3>
<table>
<?php foreach($targets as $t): ?>
    <tr>
        <td><?php echo $t->name; ?></td>
        <td><?php echo anchor('cards/play/'.$card->card_id.'/'.$t->territory_id, 'LAUNCH', 'class="menu"'); ?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php
    echo '</info>';
    echo "</response>";
?>
