<h3>Edit Mercenary Offer</h3>
<?php echo anchor('game/view_admin/'.$game->game_id, '<< Back to game admin view'); ?>
<br />
<p>
    Entering a blank value for the offer will result in the offer being changed to NULL.
</p>
<?php echo form_open("game/edit_merc_offer/".$offer->offer_id.'/'.$game->game_id); ?>
<br />
<table>
    <tr>
        <td>Mercenary Id</td>
        <td><input type="text" name="merc_id" value="<?php echo $offer->merc_id; ?>"></td>
    </tr>
    <tr>
        <td>Player Id</td>
        <td><input type="text" name="player_id" value="<?php echo $offer->player_id; ?>"></td>
    </tr>
    <tr>
        <td>Offer</td>
        <td><input type="text" name="offer" value="<?php echo $offer->offer; ?>"></td>
    </tr>
</table>



<input type="submit" name="submit" value="Update"  />