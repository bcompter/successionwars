<tr>
    <td class="forum_user"><h4><?php

    if(!isset($last_read) || $last_read < max($post->created_on,$post->modified_on))
        echo img('images/glyphicons/unread.png');
    else
        echo img('images/glyphicons/glyphicons_036_file.png');    
    echo "<br><br>".$post->username;
   
    
    ?></h4></td>
    <td class="forum_post">
        <h4><?php echo $post->created_on; ?></h4>
        <?php echo $post->text; ?>
        <?php if ($post->modified_on != '0000-00-00 00:00:00'): ?>
        <div class="forum_modified_on">Last Modified on <?php echo $post->modified_on; ?> </div>
        <?php endif; ?>
        <?php echo($show_links || $post->show_links ? '<br />'
                .anchor('forums/edit_post/'.$post->post_id, 'EDIT').' | '
                .anchor('forums/delete_post/'.$post->post_id, 'DELETE') : ''); ?>
    </td>
</tr>
