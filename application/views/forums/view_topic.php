<h1>Succession Wars Forums</h1>
<h2><?php echo $section->title; ?></h2>
<h3><?php echo $topic->title; ?></h3>
<?php echo anchor('forums/', '<< Index').' | '.anchor('forums/view_section/'.$section->section_id, $section->title); ?>
<table class="forum_table">
    <tr><td colspan="2">Pages:
    <?php 
        $data = new stdClass();
        $data->offset = $offset;
        $data->limit = $posts_per_page;
        $data->number = $num_posts;
        $data->anchor = 'forums/view_topic/'.$topic->topic_id.'/';
        $this->load->view('pagenation', $data); 
    ?>
    <?php echo anchor('forums/create_post/'.$topic->topic_id,'Add A Reply', 'class="float_right"'); ?></td></tr>
    <?php 
        foreach($posts as $post) 
        {
            unset($data);
            $data['post'] = $post;
            if(isset($last_read))
                $data['last_read'] = $last_read;
            $this->load->view('forums/post_partial', $data);
        }
    ?>
    <tr><td colspan="2">Pages:
    <?php 
        $data = new stdClass();
        $data->offset = $offset;
        $data->limit = $posts_per_page;
        $data->number = $num_posts;
        $data->anchor = 'forums/view_topic/'.$topic->topic_id.'/';
        $this->load->view('pagenation', $data); 
    ?>
    <?php echo anchor('forums/create_post/'.$topic->topic_id,'Add A Reply', 'class="float_right"'); ?></td></tr>
</table>