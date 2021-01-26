<h1>Place Units</h1>
<br />
<p>Pick a location for 
<?php

if (isset($combatunit))
{
    $name = $combatunit->name.', '.$combatunit->strength;
    echo '<em>'.$name.'</em>';
    $type = 1;
    $id = $combatunit->combatunit_id;
}
else if (isset($jumpship))
{
    $name = 'Jumpship '.$jumpship->capacity;
    echo $name;
    $type = 2;
    $id = $jumpship->jumpship_id;
}
else if (isset($leader))
{
    $name = $leader->name.', M/C/A/L: '.$leader->military.'/'.$leader->combat.'/'.$leader->admin.'/'.($leader->loyalty == 0 ? '*' : $leader->loyalty);
    echo $name;
    $type = 3;
    $id = $leader->leader_id;
}
else
{
    echo 'Something horrible has happened...';
    $type = 0;
    $id = 0;
}

?>
</p>

<?php
echo '<h4>TERRITORIES</h4>';
if (count($locations) > 0)
{
    echo '<ul>';
    foreach($locations as $location)
    {
        $hoverid = $location->name;
        $hoverid = str_replace('.', '', $hoverid);
        $hoverid = str_replace(' ', '', $hoverid);
        echo '<li>'.anchor('game/place/'.$game->game_id.'/'.$type.'/'.$id.'/'.$location->territory_id, $location->name, 'class="menu hoverlink" hoverid="'.'#'.$hoverid.'"').'</li>';
    }
    echo '</ul>';
}
else
{
    echo '<p>Something horrible has happened...</p>';
}

?>
