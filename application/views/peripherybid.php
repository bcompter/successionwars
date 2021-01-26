<h3><?php echo $periphery->name; ?> | Bid</h3>

<br />

<p>
    The highest bid will assume control over this Periphery nation.  How much is it worth to you?
</p>

<h3>Your Offer</h3>
<?php
    echo '<input type="text" value="0" url="'.$this->config->item('base_url').'index.php/periphery/bid/'.$periphery->territory_id.'/">';
    echo '<br />';
    echo anchor('#','BID' ,'class="textinput"');
?>