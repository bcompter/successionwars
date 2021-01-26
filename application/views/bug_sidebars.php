<div id="leftcolumn">
    <h3>Bug Home</h3>
    <ul>
        <li><?php echo anchor('bugtracker', 'Bug Tracker Home'); ?></li>
    </ul>
    
    <h3>Top Bugs</h3>
    
    <?php 
        echo '<ul>';
        if (count($top_bugs)>0)
        {
            foreach($top_bugs as $bug)
            {
                echo '<li>';
                echo anchor('bugtracker/view/'.$bug->bug_id, $bug->title);
                echo '</li>';
            }
        }
        else
        {
            echo '<li>None... so far.</li>';
        }
        echo '</ul>';
    ?>
    
    <h3>Top Features</h3>
    <?php 
        echo '<ul>';
        if (count($top_features)>0)
        {
            foreach($top_features as $bug)
            {
                echo '<li>';
                echo anchor('bugtracker/view/'.$bug->bug_id, $bug->title);
                echo '</li>';
            }
        }
        else
        {
            echo '<li>None... so far.</li>';
        }
        echo '</ul>';
    ?>
</div>

<div id="rightcolumn">
    <h3>Your Trackers</h3>
    <?php 
        echo '<ul>';
        if (count($user_bugs)>0)
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
    
    <h3>Links</h3>
    <ul>
        <li><?php echo anchor('bugtracker/view_all', 'View All Trackers'); ?></li>
        <li><?php echo anchor('bugtracker/view_pending', 'Pending Trackers'); ?></li>
        <li><?php echo anchor('bugtracker/view_inprogress', 'In Progress Trackers'); ?></li>
        <li><?php echo anchor('bugtracker/view_completed', 'Completed Trackers'); ?></li>
    </ul>
    
</div>
