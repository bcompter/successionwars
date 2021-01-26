<?php

if (isset($message))
    echo $message;

if (isset($thisterritory))
    echo '<h1>Path Tool - One Way: '.$thisterritory->name.'</h1>';

echo '<p>> '.anchor('/map/pathtool2/', 'HOME').( isset($thisterritory->world_id) ? ' > '.anchor('/map/pathtool2/'.$thisterritory->world_id, 'WORLD'.' '.$thisterritory->world_id) : '').'</p>';

echo '<h3>One-way path editing!</h3>';
echo '<p>Swap to standard '.anchor('/map/pathtool/'.( isset($thisterritory->world_id) ? $thisterritory->world_id : ''), 'two-way').' path editing</p>';

echo '<table><td>';


if (isset($allterritories))
{
echo '<table>';
echo '<tr><th>Name</th><th>Territory Id</th><th>World Id</th><th>&nbsp</th></tr>';
foreach ($allterritories as $territory)
{
    if ( !isset($connected) )
    {
        echo '<tr><td>'.anchor('map/pathtool2/'.$territory->world_id.'/'.$territory->map_id.'/', $territory->name).'</td><td>'.$territory->map_id.' </td><td>'.$territory->world_id.' </td><td>'.anchor('map/pathtool2/'.$territory->world_id.'/'.$territory->map_id, 'PICK').'</td></tr>';
    }
    else
        echo '<tr><td>'.anchor('map/pathtool2/'.$territory->world_id.'/'.$territory->map_id.'/', $territory->name).'</td> <td>'.$territory->map_id.' </td><td>'.$territory->world_id.' </td><td>'.anchor('map/pathtool2/'.$territory->world_id.'/'.$thisterritory->map_id.'/'.$territory->map_id, 'CONNECT').'</td></tr>';

    
}
echo '</table></td>';
}

if (isset($connected))
{
    echo '<td valign=top><h3>';
    if (isset($thisterritory))
        echo 'PathTool2: Working with '.$thisterritory->name;
    echo 'Connected To</h3>';
    echo '<table>';
    echo '<tr><th>Name</th><th>Territory Id</th><th>World Id</th><th>&nbsp</th></tr>';
    foreach ($connected as $territory)
    {
        echo '<tr><td>'.$territory->name.' </td><td>'.$territory->map_id.' </td><td>'.$territory->world_id.'</td><td>'.anchor('map/delete_path2/'.$territory->world_id.'/'.$thisterritory->map_id.'/'.$territory->path_id, 'DELETE').'</td></tr>';
    }
    echo '</table></td>';
}
echo '</table>';

?>
