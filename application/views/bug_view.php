<script type="text/javascript" <?php echo 'src="'.base_url().'tinymce/js/tinymce/tinymce.min.js"'; ?>></script>

<script type="text/javascript">
tinymce.init({
        selector: 'textarea',
        width: "80%",
        theme: "modern",
        menubar:false,
        statusbar: false
});
</script>

<div id="contentwrapper">
<div id="contentcolumn">
        <h1><?php echo $bug->title; ?></h1>
        <br />
        <p>
            <?php echo $bug->description; ?>
        </p>
        <p>
            <?php echo 'Submitted by: '.$bug_owner->username; ?>
        </p>
        <p>
            <?php echo 'Created: '.$bug->created_on; ?>
        </p>
        <p>
            <?php echo 'Last Modified: '.$bug->modified_on; ?>
        </p>
        <p>
            <?php echo 'Status: '.$bug->status; ?>
        </p>
        <p>
            <?php if (!isset($bug->karma)) $bug->karma = 0; ?>
            <?php echo 'Karma: '.($bug->karma > -1 ? '+'.$bug->karma : $bug->karma). ' of '.$bug->number_of_votes; ?>
        </p>
		
        <?php
            // Show bug vote links if needed
            if (!isset($karma->bug_karma_id) && $bug->status != 'Completed')
            {
                    echo '<p>';
                    echo anchor('bugtracker/vote/'.$bug->bug_id.'/1', img('images/glyphicons/glyphicons_343_thumbs_up.png')).' | '.
                            anchor('bugtracker/vote/'.$bug->bug_id.'/-1', img('images/glyphicons/glyphicons_344_thumbs_down.png'));
                    echo '</p>';
            }
            
            // Show delete if admin
            if ($this->ion_auth->is_admin())
            {
                echo '<p>';
                echo anchor('bugtracker/update_status/'.$bug->bug_id, 'UPDATE');
                echo ' | ';
                echo anchor('bugtracker/delete/'.$bug->bug_id, 'DELETE');
                echo '</p>';
            }
        ?>
		
        <?php
            if (isset($comments))
            {
                if(count($comments) > 0)
                {
                    foreach($comments as $comment)
                    {
                        
                        echo '<h3>'.$comment->username.' ('.$comment->created_on.')</h3>';
                        echo '<p>'.$comment->text.'</p>';
                    }
                }
                else 
                {
                    echo 'No comments yet.';
                }

            }
            
            // New comment form
            echo form_open("bugtracker/add_comment/".$bug->bug_id);

            echo '<p><label for="comment">Add a Comment:</label></p>';
?>
            <textarea name="comment" rows="15">
            </textarea>
        <?php
            echo form_submit('submit', 'Submit Comment');
            echo form_close();
 
        ?>
            
    </div>

</div>

<?php $this->load->view('bug_sidebars'); ?>