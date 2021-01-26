<div id="leftcolumn">
    <h3>Game Dashboard</h3>
    <ul>
        <li class="first"><?php echo anchor('#', 'Succession Wars Home'); ?></li>
        <li><?php echo anchor('http://www.scrapyardarmory.com', 'ScrapYardArmory'); ?></li>
        <li><?php echo anchor('blog', 'Developer\'s Blog'); ?></li>
        <li><?php echo anchor('forums', 'Forums'); ?></li>
        <li><?php echo anchor('user/preferences', 'Your Preferences'); ?></li>
    </ul>
    
    <h3>Game Links</h3>
    
    <ul>
        <li class="first"><?php echo anchor('bugtracker', 'Bug and Feature Tracker'); ?></li>
        <li><?php echo anchor('game/create', 'Create a New Game'); ?></li>
        <li><?php echo anchor('game/viewyour', 'View Your Games'); ?></li>
        <li><?php echo anchor('game/viewopen', 'View Open Games'); ?></li>
        <li><?php echo anchor('game/viewall', 'View All Games'); ?></li>
        <li><?php echo anchor('orderofbattle', 'Orders of Battle'); ?></li>
        <li><?php echo anchor('map/view_maps', 'Maps'); ?></li>
        <li><?php echo anchor('cards/view_all', 'Card List'); ?></li>
    </ul>
    
    
</div>

<div id="rightcolumn">
    <h3>Your Trackers</h3>
    <?php 
        echo '<ul>';
        if (isset($user_bugs) && count($user_bugs)>0)
        {
            foreach($user_bugs as $bug)
            {
                echo '<li>';
                echo anchor('bugtracker/view/'.$bug->bug_id, $bug->title);
                echo '</li>';
            }
            echo '<li>';
            echo anchor('bugtracker/view_user', 'View All of Your Trackers');
            echo '</li>';
        }
        else
        {
            echo '<li>None... so far.</li>';
        }
        echo '</ul>';
    ?>

    
</div>
