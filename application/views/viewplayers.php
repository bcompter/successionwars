<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
    echo "<info>";
    ?>

<h3>Players</h3>

<?php
echo '<table>';
    foreach($players as $player)
    {
        echo '<tr><td>'.$player->faction.'</td><td>| '.anchor('player/view/'.$player->player_id,'VIEW', 'class="menu"').'</td></tr>';
    }
    echo '</table>';
?>
<?php
        echo '</info>';
    echo "</response>";
?>