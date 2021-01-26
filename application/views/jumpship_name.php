<h1>Update Jumpship Name</h1>
<br />
<p>
    Naming jumpships is purely cosmetic and has no game function other than helping 
    you keep track of them.
</p>

<p>
    Current Name: <?php echo ( $jumpship->name != "" ? $jumpship->name : 'None'); ?>
</p>

<p>
<label for="status">New Name:</label>
<?php 
    $data = array('size'=>'40', 'name'=>'name', 'url'=>$this->config->item('base_url').'index.php/jumpship/name/'.$jumpship->jumpship_id);
    echo form_input($data);
?>
</p>  

<p><?php echo anchor('#','Update Name' ,'class="textin"');?></p>