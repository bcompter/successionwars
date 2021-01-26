<h3>Bidding on <?php echo $merc->name ?> </h3>

<?php
    echo '<input type="text" value="0" url="'.$this->config->item('base_url').'index.php/sw/bid/'.$merc->combatunit_id.'/">';
    echo anchor('#','BID' ,'class="textinput"');
?>

<h4>Current Bid</h4>
<?php
    if (isset($bids[0]->offer_id))
    {
        foreach ($bids as $bid)
            echo $bid->offer.' MM CBills';
    }
    else
        echo 'None';

?>


<p>The highest bid pays their bid amount and gains control of the mercenary force.
</p>

<p>Bid 0 if you do not want to bid on this mercenary.
</p>