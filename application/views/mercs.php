<h3>Mercenaries for Hire</h3>

<ul>
<?php
    foreach($mercs as $merc)
    {
        echo '<li>';
        echo $merc->name.' | '.anchor('sw/bid/'.$merc->combatunit_id,'BID', 'class="menu"');
        echo '</li>';
        break;
    }
    if (count($mercs) == 0)
    {
        echo '<li>There are no Mercenaries for hire.</li>';
    }
    if (count($mercs) > 0)
    {
        echo '<br />';
        echo '<h4>Includes the following units</h4>';
        foreach($mercs as $merc)
        {
            echo '<li>';
            echo $merc->name.', '.$merc->strength;
            echo '<br />Location: '.(isset($merc->territory_name) ? $merc->territory_name : 'Free Placement');
            echo '</li>';
        }
        
        echo '<h4>Current Bid</h4>';
        if (isset($bids[0]->offer_id))
        {
            foreach ($bids as $bid)
                echo $bid->offer.' MM CBills';
        }
        else
            echo 'None';

        if (count($leaders) > 0)
        {
            echo '<h4>Led By:</h4>';
            echo '<table>';
            echo '<tr><th>&nbsp</th><th>Military</th><th>Combat</th><th>Admin</th><th>Loyalty</th></tr>';
            
            
            foreach($leaders as $leader)   
            {
                echo '<tr><td>'.$leader->name.'</td><td align="center">'.
                    $leader->military.'</td><td align="center">'.
                    $leader->combat.'</td><td align="center">'.
                    $leader->admin.'</td><td align="center">'.
                    ($leader->loyalty == 0 ? '*' : $leader->loyalty).'</td></tr>';
                
            }  
            echo '</table>';             
        }
    }
    
    if (count($mercs_to_place) > 0 || count($leaders) > 0)
    {
        echo '<h3>Mercenaries to Place</h3>';
        foreach($mercs_to_place as $merc)
        {
            echo '<li>';
            echo $merc->name.', '.$merc->strength;
            if ($merc->owner_id == $player->player_id)
                echo ' | '.anchor('game/place/'.$game->game_id.'/1/'.$merc->combatunit_id,'PLACE UNIT', 'class="menu"');
            echo '</li>';

        }
        if (count($leaders) > 0 && $game->phase == 'Mercenary Phase')
        {
            foreach($leaders as $leader)   
            {
                echo $leader->name.' | '.anchor('game/place/'.$game->game_id.'/3/'.$leader->leader_id,'PLACE LEADER', 'class="menu"');
            }              
        }
    }
    
?>
</ul>
