<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
?>

<h3>Create a New Mercenary Unit</h3>

<p>This card does not work the way it is supposed to in the real board game.  So we made this little compromise.  
    Pick any cool 'merc name you like or use those from the actual game.  If you pick a duplicate 'merc name, they will be able to freely combine,  
    if fighting for the same faction.  An end contract card will work on every 'merc unit with the same name as a group no matter what house controls them.</p>

<table><tr><td>Name:</td> <td><input type="text" size="40"></input></td></tr>


<tr><td>Location: </td><td>
<select id="option">
    <?php
    
    foreach($territories as $t)
    {
        echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$t->territory_id.'/">'.$t->name.'</option>';
    }
    
    ?>
</select></td></tr></table>

<?php
    
    echo anchor('#', 'CREATE', 'class="combotext"')

?>

<?php
        echo '</info>';
    echo "</response>";
?>