<div id="leftcolumn">
    <h3>Dashboard</h3>
    <ul>
        <li><?php echo anchor('blog/dashboard', 'Blog Dashboard'); ?></li>
    </ul>
    
    <h3>Links</h3>
    <ul>
        <li><?php echo anchor('blog', 'View Blog'); ?></li>
        <li><?php echo anchor('blog/create_post', 'Create a New Post'); ?></li>
        
        <li><?php echo anchor('blog/view_drafts', 'View Drafts'); ?></li>
        <li><?php echo anchor('blog/view_posts', 'View Posts'); ?></li>
    </ul>
    
    
    
</div>

<div id="rightcolumn">
    <h3>Local Links</h3>
    <ul>    
    <?php if(isset($post->post_id)): ?>
        <li><?php echo anchor('blog/publish_post/'.$post->post_id, 'Publish Post'); ?></li>
        
        <li><?php echo anchor('blog/delete_post/'.$post->post_id, 'Delete Post'); ?></li>
    <?php endif; ?>
    </ul>
</div>
