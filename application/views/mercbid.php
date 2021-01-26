<h3>Bidding on <?php echo $merc->name ?> </h3>

<br />

<p>
    The highest bid pays their bid amount and gains control of the mercenary force.
</p>

<h3>Your Offer</h3>
<?php
    echo '<input type="text" value="0" url="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$merc->combatunit_id.'/">';
    echo '<br />';
    echo anchor('#','BID' ,'class="textinput"');
?>