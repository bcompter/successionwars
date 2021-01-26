<h3>Periphery Nations</h3>

<?php
    
    if (count($periphery) == 0)
    {
        echo 'There are no neutral Periphery Nations.';
    }
    else
    {
        echo '<table>';
        echo '<tr><th>Nation</th><th>Status</th><th></th>';
        $index = 0;
        foreach($periphery as $p)
        {
            $id = $p->name;
            $id = str_replace('.', '', $id);
            $id = str_replace(' ', '', $id);
            
            // Is this periphery open for bidding?
            $open = $isopen[$index];
            
            echo '<tr>';
            echo '<td>'.$p->name.'</td>';
            if ($open)
            {
                echo '<td>Open for Bidding</td>';
                echo '<td>'.anchor('periphery/bid/'.$p->territory_id,'BID', 'class="menu hoverlink" hoverid="'.'#'.$id.'"').'</td>';
            }
            else
            {
                echo '<td>Neutral</td>';
                echo '<td> | '.($has_open_nations ? '' : anchor('periphery/bid/'.$p->territory_id,'OPEN FOR BIDDING', 'class="menu hoverlink" hoverid="'.'#'.$id.'"')).'</td>';
            }
                
            echo '</tr>';
            $index++;
        }
        echo '</table>';
    }
    

?>
