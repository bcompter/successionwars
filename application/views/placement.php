<h1>Place Units</h1>
<br />
<p>Pick a unit to place</p>

<?php
echo '<h3>HOUSE UNITS</h3>';
if (count($combatunits) > 0)
{
    echo '<ul>';
    foreach($combatunits as $unit)
    {
        if ( !$unit->is_merc )
            echo '<li>'.anchor('game/place/'.$game->game_id.'/1/'.$unit->combatunit_id,$unit->name.', '.$unit->strength, 'class="menu"').'</li>';
    }
    echo '</ul>';
}
else
{
    echo '<p>All house combat units have been placed.</p>';
}

echo '<h3>MERCENARY UNITS</h3>';
if (count($combatunits) > 0)
{
    echo '<ul>';
    foreach($combatunits as $unit)
    {
        if ( $unit->is_merc )
            echo '<li>'.anchor('game/place/'.$game->game_id.'/1/'.$unit->combatunit_id,$unit->name.', '.$unit->strength, 'class="menu"').'</li>';
    }
    echo '</ul>';
}
else
{
    echo '<p>All mercenary combat units have been placed.</p>';
}

echo '<h3>JUMPSHIPS</h3>';
if (count($jumpships) > 0)
{
    echo '<ul>';
    foreach($jumpships as $ship)
    {
        echo '<li>'.anchor('game/place/'.$game->game_id.'/2/'.$ship->jumpship_id,'Jumpship '.$ship->capacity, 'class="menu"').'</li>';
    }
    echo '</ul>';
}
else
{
    echo '<p>All jumpships have been placed.</p>';
}

echo '<h3>LEADERS</h3>';
if (count($leaders) > 0)
{
    echo '<ul>';
    foreach($leaders as $leader)
    {
        echo '<li>'.anchor('game/place/'.$game->game_id.'/3/'.$leader->leader_id,
                $leader->name.', M/C/A/L: '.$leader->military.'/'.$leader->combat.'/'.$leader->admin.'/'.($leader->loyalty == 0 ? '*' : $leader->loyalty), 'class="menu"').'</li>';
    }
    echo '</ul>';
}
else
{
    echo '<p>All leaders have been placed.</p>';
}

?>
