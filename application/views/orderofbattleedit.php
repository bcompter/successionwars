<h2>Edit Item in the Order of Battle</h2>

<?php echo form_open("orderofbattle/edit/".$data->data_id);
$type = $data->type;
    echo '<input type="hidden" name="oob_id" value="'.$data->oob_id.'">';
    echo '<input type="hidden" name="type" value="'.$data->type.'">';
    echo '<input type="hidden" name="data_id" value="'.$data->data_id.'">';
    if ($type == 0)
    {
        // Edit a player
        // query for data: in the orderofsuccessiondata table where data_id is the number passed
        echo 'Faction Name:';
        echo '<input type="text" name="faction" value="'.$data->arg0data.'" />';
        echo 'Turn Order:';
        echo '<input type="text" name="turnorder" value="'.$data->arg1data.'" />';
        echo 'Setup Order:';
        echo '<input type="text" name="setuporder" value="'.$data->arg7data.'" />';  
        echo 'Starting CBills:';
        echo '<input type="text" name="money" value="'.$data->arg2data.'" />';
        echo 'Tech Level:';
        echo '<input type="text" name="tech" value="'.$data->arg3data.'" />';
        echo 'Color:';
        echo '<input type="text" name="color" value="'.$data->arg4data.'" />';
        echo 'Text Color:';
        echo '<input type="text" name="textcolor" value="'.$data->arg5data.'" />';
        echo 'Free Bribes:';
        echo '<input type="text" name="free_bribes" value="'.$data->arg6data.'" />';
        echo 'Elemental Name: (Leave blank to disallow)';
        echo '<input type="text" name="elementals" value="'.$data->arg8data.'" />';
    }
    else if ($type == 1)
    {
        // Edit a card
        echo 'Card: <select name="card">';
        echo '<option value="'.$data->arg0data.'">Current: '.$data->arg0data.'</option>';    
        $x=0;
        foreach($cards as $card)
        {
                
                echo '<option value="'.$card->type_id.'">'.++$x.': '.$card->title.'</option>';
        }
        echo '</select><br />'; 
    }
    else if ($type == 2)
    {
        // Edit a territory
        
        echo 'Region Name: <select name="mapid">';
        echo '<option value="'.$data->arg1data.'">'.$region.'</option>';
        echo '</select><br />';       
        
        echo 'Controlling Faction: <select name="faction">';
        echo '<option value="'.$data->arg0data.'">'.$data->arg0data.'</option>';
        echo '<option value="Comstar">Comstar</option>';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '<option value="Neutral">Neutral</option>';
        echo '</select><br />';
        
        echo 'Garrison Name:';
        echo '<input type="text" name="garrison_name" value="'.$data->arg3data.'">';
        
        echo 'Resources:';
        echo '<input type="text" name="resource" value="'.$data->arg4data.'"><br />';
        
        echo 'Regional Capital: <select name="is_regional">';
        echo '<option value="0"'.(!$data->arg5data ? 'selected="selected"' : '').'>No</option>';
        echo '<option value="1"'.($data->arg5data ? 'selected="selected"' : '').'>Yes</option>';
        echo '</select><br />';
        
        echo 'House Capital: <select name="is_capital">';
        echo '<option value="0"'.(!$data->arg6data ? 'selected="selected"' : '').'>No</option>';
        echo '<option value="1"'.($data->arg6data ? 'selected="selected"' : '').'>Yes</option>';
        echo '</select><br />';
        
    }
    else if ($type == 3)
    {
        // Edit a combat unit
        echo 'Name: <input type="text" name="name" value="'.$data->arg0data.'">';
        
        echo 'Pre-war Strength: <select name="strength">';
        echo '<option value="'.$data->arg2data.'">'.$data->arg2data.'</option>';
        for ($x=1;$x<=10;$x++)
        {
            echo '<option value="'.$x.'">'.$x.'</option>';    
        }
        echo '</select><br />';
        
        echo 'Faction: <select name="faction" value="'.$data->arg1data.'">';
        echo '<option value="'.$data->arg1data.'">'.$data->arg1data.'</option>';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '<option value="Comstar">Comstar</option>';
        echo '<option value="Neutral">Neutral</option>';
        echo '</select><br />';
        
        echo 'Location: <select name="location">';
        echo '<option value="'.$data->arg3data.'">'.$region.'</option>';
        echo '<option value="None">Not Initially Available</option>';
        echo '<option value="Free">Free Set-up</option>';
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select>';
        
        echo '<p>Is Mercenary?';
        
        echo '<input type="checkbox" name="is_merc" '.($data->arg4data == 1 ? 'checked="checked"' : '').' /></p>';
        
        echo '<p>Can Be Rebuilt?';
        
        echo '<input type="checkbox" name="can_rebuild" '.($data->arg5data == 1 ? 'checked="checked"' : '').' /></p>';
        
    }
    else if ($type == 4)
    {
        // Edit a leader
        echo 'Name: <input type="text" name="name" value="'.$data->arg0data.'"><br />';
        echo 'Faction: <select name="faction">';
        echo '<option value="'.$data->arg1data.'">'.$data->arg1data.'</option>';
        echo '<option value="Neutral">Neutral</option>';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '</select><br />';
        echo 'Location: <select name="location">';
        if ( isset($region) )
            echo '<option value="'.$data->arg2data.'">'.$region.'</option>';
        echo '<option value="Free">Free Set-up</option>';
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select><br />';
        echo 'Military: <input type="text" name="military" value="'.$data->arg3data.'"><br />';
        echo 'Combat: <input type="text" name="combat" value="'.$data->arg4data.'"><br />';
        echo 'Admin: <input type="text" name="admin" value="'.$data->arg5data.'"><br />';
        echo 'Loyalty: <input type="text" name="loyalty" value="'.$data->arg6data.'"><br />';
        
        echo 'Is Mercenary (Associated Units):<br />';
        echo '<select name="associated_units">';
        echo '<option value="0">Not a Mercenary</option>';
        foreach($mercs as $merc)
        {
            echo '<option value="'.$merc->arg0data.'">'.$merc->arg0data.'</option>';
        }
        echo '</select>';
        
    }
    else if ($type == 5)
    {
        // Edit a factory
        echo 'Factory Location: <select name="location">';
        echo '<option value="'.$data->arg0data.'">current: '.$region.'</option>';        
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select>';
    }
    else if ($type == 6)
    {
        // Edit a jumpship
        echo 'Faction: <select name="faction">';
        echo '<option value="'.$data->arg0data.'">'.$data->arg0data.'</option>';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '</select><br />';
        
        echo 'Location: <select name="location">';
        echo '<option value="Free">Free Set-up</option>';
        if ($data->arg1data == "Free")
        {
            echo '<option value="Free">Free Setup</option>';
        }
        else
        {
            echo '<option value="'.$data->arg1data.'">'.$data->arg1data.'</option>';
        }
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select><br />';
        echo 'Capacity: <select name="capacity">';
        echo '<option value="'.$data->arg2data.'">'.$data->arg2data.'</option>';
        echo '<option value="1">1</option>';
        echo '<option value="2">2</option>';
        echo '<option value="3">3</option>';
        echo '<option value="5">5</option>';
        echo '</select><br />';
    }
?>

<br /><br />

<?php 
    echo '<input type="submit" name="submit" value="Update"  />';
?>