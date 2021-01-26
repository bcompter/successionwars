<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/viewoob.js"'; ?>></script>

<h2>Add to Order of Battle</h2>

<?php echo form_open("orderofbattle/add/".$orderofbattle->orderofbattle_id.'/'.$type); ?>

<?php

    if ($type == 0)
    {
        // Add a player
        echo 'Faction Name:';
        echo '<input type="text" name="faction" />';
        echo 'Starting CBills:';
        echo '<input type="text" name="money" />';
        echo 'Tech Level:';
        echo '<input type="text" name="tech" />';
        echo 'Turn Order:';
        echo '<input type="text" name="turnorder" />';
        echo 'Setup Order:';
        echo '<input type="text" name="setuporder" />';
        echo 'Color:';
        echo '<input type="text" name="color" />';
        echo 'Text Color:';
        echo '<input type="text" name="textcolor" />';
        echo 'Free Bribes:';
        echo '<input type="text" name="free_bribes" />';
        echo 'Elemental Name: (Leave blank to disallow)';
        echo '<input type="text" name="elementals" />';
    }
    else if ($type == 1)
    {
        // Add a card
        foreach($cards as $card)
        {
            echo '<p>';
            echo '<h4>'.$card->title.'</h4>'.$card->text.'<br />';
            echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/1/'.$card->type_id,'ADD CARD');
            echo '</p>';
        }
    }
    else if ($type == 2)
    {
        // Add a territory
        
        echo '<select name="faction">';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '<option value="Comstar">Comstar</option>';
        echo '<option value="Neutral">Neutral</option>';
        echo '</select>';
        
        echo '<select name="mapid">';
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select><br />';
        
        echo 'Garrison Name:';
        echo '<input type="text" name="garrison_name" value="">';
        
        echo 'Resources:';
        echo '<input type="text" name="resource" value="1"><br />';
        
        echo 'Regional Capital: <select name="is_regional">';
        echo '<option value="0">No</option>';
        echo '<option value="1">Yes</option>';
        echo '</select><br />';
        
        echo 'House Capital: <select name="is_capital">';
        echo '<option value="0">No</option>';
        echo '<option value="1">Yes</option>';
        echo '</select><br />';
    }
    else if ($type == 3)
    {
        // Add a combat unit
        echo 'Type:';
        echo '<select name="type" class="combat_unit_type">';
        echo '<option value="Mech">Mech</option>';
        echo '<option value="Conventional">Conventional</option>';
        echo '<option value="Elemental">Elemental (faction dependant)</option>';
        echo '</select>';
        
        echo '<br />Name:';
        echo '<input type="text" name="name" />';
        echo 'Prewar Strength:';
        echo '<input type="text" name="strength" class="strength"/>';
        echo '<br />Faction:';
        echo '<select name="faction">';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '<option value="Comstar">Comstar</option>';
        echo '<option value="Neutral">Neutral</option>';
        echo '</select>';
        
        echo '<br />Location:';
        echo '<select name="location">';
        echo '<option value="None">Not Initially Available</option>';
        echo '<option value="Free">Free Set-up</option>';
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select>';
        echo '<p>Is Mercenary?';
        echo '<input type="checkbox" name="is_merc" /></p>';
        echo '<p>Can Be Rebuilt?';
        echo '<input type="checkbox" name="can_rebuild" '.(isset($data) && $data->arg5data == 0 ? '' : 'checked="checked"').' /></p>';
    }
    else if ($type == 4)
    {
        // Add a leader
        echo 'Name: <input type="text" name="name"><br />';
        echo 'Faction: <select name="faction">';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '<option value="Neutral">Neutral</option>';
        echo '</select><br />';
        echo '<select name="location">';
        echo '<option value="Free">Free Set-up</option>';
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select><br />';
        echo 'Military: <input type="text" name="military"><br />';
        echo 'Combat: <input type="text" name="combat"><br />';
        echo 'Admin: <input type="text" name="admin"><br />';
        echo 'Loyalty: <input type="text" name="loyalty"><br />';
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
        // Add a factory
        echo '<select name="location">';
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        echo '</select>';
    }
    else if ($type == 6)
    {
        // Add a jumpship
        echo '<h3>Faction</h3>';
        echo '<select name="faction">';
        foreach($factions as $f)
        {
            echo '<option value="'.$f->faction.'">'.$f->faction.'</option>';
        }
        echo '<option value="Neutral">Neutral</option>';
        echo '</select>';
        echo '<h3>Location</h3>';
        echo '<select name="location">';
        echo '<option value="Free">Free Set-up</option>';
        foreach($maps as $m)
        {
            echo '<option value="'.$m->map_id.'">'.$m->name.'</option>';
        }
        
        echo '</select><br />';
        echo '<h3>Capacity</h3>';
        echo '<input type="text" name="capacity" />';
    }

?>

<br /><br />

<?php 
if ($type != 1)
{
    echo '<input type="submit" name="submit" value="Add"  />';
}
?>

