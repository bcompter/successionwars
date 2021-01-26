<div id="contentwrapper">
<div id="contentcolumn">
    <h1><?php echo (isset($title) ? $title : 'No Title Provided'); ?></h1>
    
    <br />
    
    <table>
        <tr>
            <th>Title</th><th>Status</th><th>Author</th><th>Last Modified</th><th>Published Date</th><th>&nbsp</th>
        </tr>
        <?php foreach($posts as $post):?>
            <tr>
                <td><?php echo $post->title; ?></td>
                <td><?php echo $post->status; ?></td>
                <td><?php echo $post->username; ?></td>
                <td><?php echo $post->last_edit; ?></td>
                <td><?php echo $post->published_on; ?></td>
                <td>
                    <?php echo anchor('blog/edit_post/'.$post->post_id, 'EDIT'); ?> 
                    <?php echo anchor('blog/preview/'.$post->post_id, 'PREVIEW'); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</div>

<?php $this->load->view('blog_sidebars'); ?>