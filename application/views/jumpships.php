<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
        
        echo '<h3>Your Jumpships</h3>';
        if ( count( $jumpships ) == 0 )
        {
            echo 'You do not have any jumpships.<br />';
            echo 'Just a suggestion but you should probably get some.';
        }
        else
        {
            $jumpsallowed = 3;
            if ($player->tech_level > 14)
                $jumpsallowed = 4;
            if ($player->tech_level < -6)
                $jumpsallowed = 2;
            $count=0;
            $total_capacity=0;
            
            echo '<table cellspacing="5">';            
            echo '<tr><th> # </th><th align="center"><span style="border-bottom: dashed thin;" title="Capacity">Cap</span></th><th align="center"><span style="border-bottom: dashed thin;" title="Moves Remaining">Mov</span></th><th><span style="border-bottom: dashed thin;" title="Location">Loc</span></th><th>Jumpships</th></tr>';
            foreach($jumpships as $jumpship)
            {
                $id = $jumpship->name;
                $id = str_replace('.', '', $id);
                $id = str_replace(' ', '', $id);
                
                $count++;
                $total_capacity+=$jumpship->capacity;
                echo '<tr><td> '.$count.': </td>';
                echo '<td align="center">'.$jumpship->capacity.'</td>';
                echo '<td align="center">'.($jumpship->being_built ? 'N/A' : '<span style="border-bottom: dashed thin;" title="(not implemented yet)">'.($jumpsallowed - $jumpship->moves_this_turn).'</span>').'</td>';
                echo '<td>'.anchor('sw/location/'.$jumpship->location_id,$jumpship->name,'class="menu hoverlink" hoverid="'.'#'.$id.'"').'</td>';
                echo '<td> '.anchor('jumpship/view/'.$jumpship->jumpship_id,($jumpship->jumpship_name != "" ? $jumpship->jumpship_name : 'unnamed'),'class="menu hoverlink" hoverid="'.'#'.$id.'"').' </td>';
                echo '</tr>';
            }
            echo '<tr><td></td><td align="center">'.$total_capacity.'</td><td colspan="3">Total Capacity</td></tr>';
            echo '</table>';
        }
        
        echo 'Jumpships have a capacity of 1, 2, 3, or 5 as noted.  Leaders take up no space, \'Mechs take up a single space while Conventional take up two spaces.';
        
        echo '</info>';
    echo "</response>";
?>