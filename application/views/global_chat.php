<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/chat_room.js"'; ?>></script>
<script type="text/javascript">                                         
    // Set variables
    <?php echo 'var $loadChatUrl = \''.$this->config->item('base_url').'index.php/chat_global/load_chat/\';'; ?>
    <?php echo 'var $updateUrl = \''.$this->config->item('base_url').'index.php/chat_global/update/\';'; ?>

</script>


<h3>Chat Room</h3>
<div id="globalchatdiv"></div>

<div id="globalwhodiv"></div>

<div class="box1 fluid">
<?php
    $attributes = array('id'=>'public_chat');
    echo form_open('chat_global/chat', $attributes);
    $message_input=array('name'=>'public_message','value'=>'','size'=>'80','maxlength'=>'200');
    echo form_input($message_input);  
    echo form_submit('chat', 'Send'); 
    echo form_close();
?>
</div>

