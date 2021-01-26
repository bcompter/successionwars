<div id="contentwrapper">
<div id="contentcolumn">
    <?php
    foreach($posts as $post)
    {
        $inner_post = new stdClass();
        $inner_post->post = $post;
        $this->load->view('blog_post', $inner_post);
    }
    ?>
</div>
</div>

<?php $this->load->view('blog_sidebars_1'); ?>