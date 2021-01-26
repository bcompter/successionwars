<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/tablesorter.js"'; ?>></script> 

<script type="text/javascript">
    $(document).ready(function() 
        { 
            $(".sortable").tablesorter(); 
        } 
    ); 
</script>

<h1>Orders of Battle</h1>
<br />

<?php 
    if (isset($user->id) && $user->group_id != 2) echo anchor('orderofbattle/create','Create a New Order of Battle'); 
?>
<br /><br />

<table class="sortable tablesorter tablenew">
    <thead>
        <tr><th>Order of Battle</th><th>Description</th><th>Status</th><th>&nbsp;</th></tr>
        
    </thead>
    <?php foreach($orderofbattles as $oob): ?>
    <tr>
        <td><?php echo $oob->name; ?></td>
        <td><?php echo $oob->description; ?></td>
        <td align="center"><?php echo ($oob->draft ? 'Draft' : 'Released'); ?></td>
        <td><?php echo anchor('orderofbattle/view/'.$oob->orderofbattle_id,'VIEW') ?></td>
    </tr>
    <?php endforeach; ?>
</table>