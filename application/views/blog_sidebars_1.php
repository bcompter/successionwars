<div id="leftcolumn">
    <h3>Links</h3>
    <ul>
        <li class="first"><?php echo anchor('#', 'Succession Wars Home'); ?></li>
        <li><?php echo anchor('http://www.scrapyardarmory.com', 'ScrapYardArmory'); ?></li>
        <li><?php echo anchor('user/preferences', 'Your Preferences'); ?></li>
    </ul>
    
    <h3>Game Links</h3>
    
    <ul>
        <li class="first"><?php echo anchor('bugtracker', 'Bug and Feature Tracker'); ?></li>
        <li><?php echo anchor('game/create', 'Create a New Game'); ?></li>
        <li><?php echo anchor('game/viewopen', 'View Open Games'); ?></li>
        <li><?php echo anchor('game/viewall', 'View All Games'); ?></li>
    </ul>
    
    
    
</div>

<div id="rightcolumn">
    <h3>Recent Posts</h3>
    <ul>
        <?php foreach($posts as $post): ?>
        
        <li><?php echo anchor('blog/view_post/'.$post->post_id, $post->title); ?></li>
        
        <?php endforeach; ?>
    </ul>
</div>
