<div id="contentwrapper">
<div id="contentcolumn">
    <h1>Blog Dashboard</h1>
    <br />
    
    <div class="box2">
        <h3>Drafts</h3>
        <br />
        <table>
            <?php foreach ($drafts as $draft): ?>
                <tr>
                    <td><?php echo $draft->title; ?></td>
                    <td><?php echo anchor('blog/edit_post/'.$draft->post_id, 'EDIT') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="box3">
        <h3>...</h3>
        <br />
        <p>
            in development
        </p>
        
    </div>

    <div class="box2">
        <h3>...</h3>
        <br />
        <p>
            in development
        </p>
    </div>
    <div class="box3">
        <h3>...</h3>
        <br />
        <p>
            in development
        </p>
        
    </div>
    
</div>
</div>

<?php $this->load->view('blog_sidebars'); ?>
