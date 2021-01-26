<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/tablesorter.js"'; ?>></script> 

<script type="text/javascript">
    $(document).ready(function() 
        { 
            $(".sortable").tablesorter(); 
        } 
    ); 
</script>

<div id="contentwrapper">
<div id="contentcolumn">
        <h3><?php echo $title; ?></h3>
        <br />
        <table class="sortable tablesorter" cellspacing=10>
            <thead>
                <tr>
                    <th>Karma</th><th>Title</th><th>Bug/Feature</th><th>Status</th><th>Last Modified</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($bugs as $bug): ?>
                <?php if (!isset($bug->karma)) $bug->karma = 0; ?>
                <tr>
                    <td><?php echo ($bug->karma > 0 ? '+'.$bug->karma : $bug->karma); ?></td>
                    <td><?php echo $bug->title; ?></td>
                    <td><?php echo ($bug->is_bug ? 'Bug' : 'Feature' ); ?></td>
                    <td><?php echo $bug->status; ?></td>
                    <td><?php echo $bug->modified_on; ?></td>
                    <td><?php echo anchor('bugtracker/view/'.$bug->bug_id, 'VIEW'); ?></td>
                </tr>            
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php
            if(count($bugs) == 0)
                echo 'No records found.';  
        ?>
    </div>
</div>

<?php $this->load->view('bug_sidebars'); ?>