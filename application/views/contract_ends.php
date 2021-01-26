<h3>End Mercenary Contract</h3>
<br />
<ul>
<?php
    $count = 0;
    foreach($mercs as $merc)
    {
        if ($merc->owner_id != $player->player_id)
        {
            echo '<li>';
            echo '('.$merc->faction.') '.$merc->name.', '.$merc->strength.' @ '.$merc->territory_name.' <br /> '.anchor('cards/play/'.$card->card_id.'/'.$merc->combatunit_id,'END CONTRACT','class="menu"');
            echo '</li><hr />';
            $count++;
        }
    }
    if ($count == 0)
    {
        echo '<li>There are no Mercenaries to target.</li>';
    }
?>
</ul>
