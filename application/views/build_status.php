<script type="text/javascript">  
$(document).ready(function()
{
    // Go!!!
    build();	
	
});  	// end document ready

function build()
{
    <?php echo 'var $buildurl = \''.$this->config->item('base_url').'index.php/game/new_build/'.$game->game_id.'\';'; ?>

    $.post( $buildurl,  
        function(xml)
        {
            var status = $("status",xml);
            $("#status").html(status);

            var $done = $("isDone",xml).text();
            if ($done == "0")
                setTimeout("build()", 1000);
            else
                $(".hidden").show();

        });
};
    
</script>
<h3>Build Status</h3>
<br /><br />
<p>Please wait while your game is built...</p>
<div id="status">
    
    <?php
    if (isset($notice))
        echo $notice;
    ?>
    
</div>
<br /><br />
<div class="hidden" style="display:none"><?php echo anchor('game/view/'.$game->game_id, 'Back to the Game'); ?></div>
