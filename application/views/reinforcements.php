<h3>Select Unit to Reinforce</h3>
<br />
Pay local forces 5 CBills to immediately reinforce a 'Mech unit to combat strength of 4.
<br /><br />
<table class="sortable tablesorter" cellspacing=1>
    <thead>
        <tr>
            <th>Unit</th><th>Str</th><th>Location</th><th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($targets as $unit) : ?>
        <tr>
            <td><?php echo $unit->name; ?></td>
            <td align="center"><?php echo $unit->strength; ?></td>
            <td><?php //echo $unit->location_name;
            $id = $unit->location_name;
            $id = str_replace('.', '', $id);
            $id = str_replace(' ', '', $id);            
            echo '<td>'.anchor('sw/location/'.$unit->location_id,$unit->location_name,'class="menu hoverlink" hoverid="'.'#'.$id.'"').'</td>';
            ?></td>
            <td><?php echo anchor('cards/play/'.$card->card_id.'/'.$unit->combatunit_id, 'REINFORCE', 'class="menu"'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>