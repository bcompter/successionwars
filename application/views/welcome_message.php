<h1>Welcome to Succession Wars!</h1>
<br />

<div class="notice">April 19th, 2020, 09:24 AM: Hey look, an updated message!  Things have been running smoothly recently. I hope everyone is staying safe and doing well out there!</div>

<p>The Succession Wars game takes place at the beginning of the 31st century, when
the great dynasties of interstellar civilizations are preparing to go to war to 
determine who will dominate human space.  Originally published by FASA in 1987, The Succession Wars game has long been
    out of print.  The ScrapYard is proud to present an online adaptation of the
    board game for you and your friends to play.
</p>
<p>
    The current code base is in Open Beta.  There are sure to be a few bugs to 
    quash along the way and the more people that play the better the finished game will be.
</p>
<p>
    Please forward all bugs, feature requests, suggestions, and thrown pottery to
    brian@scrapyardarmory.com
</p>

<h3>Browsers!</h3>
<p> A quick word about browsers.  The Succession Wars game works best on Chrome, Firefox, or Safari. <br />
    Sadly, there are know issues with compatibility for certain versions of Internet Explorer.  <br />
    Keep that in mind if you are attempting to play for the first time.  Happy gaming!
</p>
<p>
    <?php echo img('images/chrome_logo.png'); ?>
    <?php echo img('images/firefox_logo.png'); ?>
    <?php echo img('images/safari_logo.png'); ?>
</p>

<p> Support the site by buying me a ko-fi! <br />
<?php echo anchor("https://ko-fi.com/successionwars", "https://ko-fi.com/successionwars"); ?>
</p>

<?php

    if ( !$this->ion_auth->logged_in() )
    {
        // User is not logged in, show a link to either register or sign in
        echo anchor( 'auth/register', 'Register' );
        echo ' | ';
        echo anchor( 'auth/login', 'Log In' );
    }
    else
    {
        // User is logged in, show a link ot the game dashboard
        echo '<p>Welcome back '.$this->ion_auth->get_user()->username.'.</p>';
        echo anchor('game','Game Dashboard');
        echo ' | ';
        echo anchor('bugtracker','Bug and Feature Tracker');
        
        // If the user is an admin, show a link to the admin pages
        if ( $this->ion_auth->is_admin() )
        {
            echo ' | ';
            echo anchor('auth','Manage Users');
            echo ' | ';
            echo anchor('admin','Admin Page');
            echo ' | ';
            echo anchor('blog/dashboard','Blog Dashboard');
        }
        
        echo '<p>'.anchor('auth/logout', 'Log Out').'</p>';
    }

?>

