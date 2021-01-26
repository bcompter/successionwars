<h3>Combine Combat Units | Battle for <?php echo $territory->name; ?> </h3>
<?php echo anchor('sw/viewcombat/'.$territory->territory_id, '<< Back to the Combat View', 'class="menu"'); ?>
<br /><br />
<?php

$displayed; // Track what units have been displayed so far

// Create a look up table of units
$unitlookup;
foreach($units as $unit)
{
    $unitlookup[$unit->combatunit_id] = $unit;
}

echo '<table>';
foreach($units as $unit)
{
    if (!isset($displayed[$unit->combatunit_id]) && !$unit->die && !$unit->combo_broken)
    {
        $displayed[$unit->combatunit_id] = 1;
        
        $strength = $unit->strength;
        
        echo '<tr><td>'.($unit->is_merc?'*':'').$unit->name;
        if (isset($unit->combine_with) && isset($unitlookup[$unit->combine_with]->name))
        {
            // Find the combined unit
            echo ', <br />'.($unitlookup[$unit->combine_with]->is_merc == TRUE?'*':'').$unitlookup[$unit->combine_with]->name;
            $displayed[$unit->combine_with] = 1;
            $strength += $unitlookup[$unit->combine_with]->strength;
            echo ': Strength '.$strength;
            
            echo  '</td><td> | '.anchor('sw/combine/'.$unit->combatunit_id.'/-2', 'CANCEL', 'class="menu"').'</td>';
        }
        else
        {
            echo ': Strength '.$strength;
            
            if (!$unit->die)
                echo '</td><td> | '.anchor('sw/combine/'.$unit->combatunit_id.'/-1', 'COMBINE', 'class="menu"').'</td>';
            else
                echo '</td><td> | '.'</td>';
        }
        echo '</tr>';
    } 
}
echo '</table>';
?>
<h3>Units with Broken Combinations (Cannot be Combined)</h3>
<table>
    <tr>
        <?php foreach($units as $unit) :?>
        <?php if ($unit->combo_broken): ?>
            <td><?php echo $unit->name; ?></td>
            <td>(<?php echo $unit->strength ?>)</td>
        <?php endif; ?>
        <?php endforeach; ?>
    </tr>
</table>