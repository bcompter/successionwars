<div id="statusbar">
    <p class="left">The Succession Wars by <?php echo anchor('http://www.scrapyardarmory.com', 'ScrapYardArmory'); ?>
<?php 
    if (isset($user->username))
    {
        echo ' | '.anchor('game', 'Dashboard');
        echo '</p>';
        echo '<p class="right">Logged in as '.$user->username;
        echo ' | '.anchor('auth/logout', 'Log Out').'</p>';
    }
    else
    {
        echo '<p class="right">';
        echo anchor('auth/login', 'Log In').' | '.anchor('auth/register', 'Register').'</p>';
    }

?>
</div>