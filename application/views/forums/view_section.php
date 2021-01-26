<h1>Succession Wars Forums</h1>
<h2><?php echo $section->title; ?></h2>
<?php echo anchor('forums', '<< Index'); ?>
<table class="forum_table">
    <tr><td colspan="4">Pages:
           
    <?php 
        $data = new stdClass();
        $data->offset = $offset;
        $data->limit = 30;
        $data->number = $num_topics;
        $data->anchor = 'forums/view_section/'.$section->section_id.'/';
        $this->load->view('pagenation', $data); 
    ?>

    <?php echo anchor('forums/create_topic/'.$section->section_id,'Create a New Topic', 'class="float_right"'); ?></td></tr>
         <tr>
            <td></td>
            <td>Threads</td>
            <td></td>
            <td>Last Post Date</td>
        </tr>
    <?php 
        foreach($topics as $topic)
        {
            unset($data);
            $data['topic'] = $topic;
//            $data['section'] = 
            $this->load->view('forums/topic_partial', $data);
        }   
    ?>
    
    <?php if (count($topics) == 0): ?>
    <tr><td>No Topics in this section...</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td></tr>
    <?php endif; ?>
    
    <tr><td colspan="4"><?php echo anchor('forums/create_topic/'.$section->section_id,'Create a New Topic', 'class="float_right"'); ?></td></tr>
</table>