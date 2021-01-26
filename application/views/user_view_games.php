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
<h1>VIEWING: <?php echo $user_being_viewed->username; ?></h1><br />
    <?php
if ($user->group_id == 1)   // TODO Develop viewing other player information further, probably on separate pages, # of posts / topics, show the last few posts by user, etc.
{
?>
    <h2><?php echo $user_being_viewed->username; ?>'s Preferences</h2>
<h3> Account Information</h3>
<table cellpadding="5" cellspacing=10>
    <tbody>
        <tr rowspacing="10px">
            <td>Account Type:</td>
            <td><?php echo $user_being_viewed->group_id; ?></td>
        </tr>
        <tr rowspacing="10px">
            <td>Active:</td>
            <td><?php echo $user_being_viewed->active; ?></td>
        </tr>
        <tr rowspacing="10px">
            <td>IP Address:</td>
            <td><?php echo $user_being_viewed->ip_address; ?></td>
        </tr>
        <tr rowspacing="10px">
            <td>Registered:</td>
            <td><?php echo $user_being_viewed->created_on; ?></td>
        </tr>
        <tr rowspacing="10px">
            <td>Last Login:</td>
            <td><?php echo $user_being_viewed->last_login; ?></td>
        </tr>
    </tbody>
</table>    
<h3> Email Settings</h3>
<table cellpadding="5" cellspacing=10>
    <tbody>
        <tr rowspacing="10px">
            <td>Game notifications:</td>
            <td><?php echo $user_being_viewed->send_me_email; ?></td>
        </tr>
        <tr>
            <td>PM notifications:</td>
            <td><?php echo $user_being_viewed->email_on_private_message; ?></td>
        </tr>
    </tbody>
</table> 
<h3> Game Options</h3>
<table cellpadding="5" cellspacing=10>
    <tbody>
        <tr rowspacing="10px">
            <td>Auto kill all: <?php echo $user_being_viewed->auto_kill_all; ?></td>
        </tr>
        <tr rowspacing="10px">
            <td>Auto kill order: <?php echo $user_being_viewed->auto_kill_order; ?></td>
        </tr>
    </tbody>
</table> 
<h3> Forum Options</h3>
<table cellpadding="5" cellspacing=10>
    <tbody>
        <tr rowspacing="10px">
            <td>Auto-subscribe to topics created: <?php echo $user_being_viewed->forum_auto_subscribe_created; ?></td>
        </tr>
        <tr rowspacing="10px">
            <td>Auto-subscribe to topics posted in: <?php echo $user_being_viewed->forum_auto_subscribe_posted; ?></td>
        </tr>
        <tr rowspacing="10px">
            <td>Number of posts to show per page: <?php echo $user_being_viewed->forum_posts_per_page; ?></td>
        </tr>
    </tbody>
</table> 
<?php
}   
?>
    <h2>Games</h2>

<br />
<h3> Active in these games</h3>
<table cellpadding="5" class="sortable tablesorter" cellspacing=10>
    <thead>
    <tr rowspacing="10px">
        <th>Game Name</th>
        <th>Created On</th>
        <th>Privacy Setting</th>
        <th>Game Turn</th>
        <th>Current Player</th>
        <th>Phase</th>
    </tr>
    </thead>
    <tbody>
<?php foreach( $games as $game ): ?>
    
    <?php if ($game->phase != 'Game Over' && $game->turn_order != 0): ?>
    
    <tr rowspacing="10px">
        <td><?php echo $game->title; ?></td>
        <td><?php echo $game->created_on; ?></td>
        <td align="center"><?php echo ($game->password=='' ? 'Public' : 'Private'); ?></td>
        <td align="center"><?php echo $game->turn; ?></td>
        <td><?php echo $game->faction; ?></td>
        <td><?php echo $game->phase; ?></td>
        <?php if (isset($game->player_id) ): ?>
            <td align="center"> <?php echo ($game->turn_order == 0 ? 'Eliminated' : 'Active'); ?> </td>
        <?php endif; ?>
        <td> | <?php echo anchor('game/view/'.$game->game_id,'VIEW'); ?></td>
    </tr>
    
    <?php endif; ?>

<?php endforeach; ?>
    </tbody>
</table>

<br />
<h3> Inactive in these games</h3>

<table cellpadding="5" class="sortable tablesorter" cellspacing=10>
    <thead>
    <tr rowspacing="10px">
        <th>Game Name</th>
        <th>Created On</th>
        <th>Privacy Setting</th>
        <th>Game Turn</th>
        <th>Current Player</th>
        <th>Phase</th>
    </tr>
    </thead>
    <tbody>
<?php foreach( $games as $game ): ?>
    
    <?php if ($game->phase == 'Game Over' || $game->turn_order == 0): ?>
    
    <tr rowspacing="10px">
        <td><?php echo $game->title; ?></td>
        <td><?php echo $game->created_on; ?></td>
        <td align="center"><?php echo ($game->password=='' ? 'Public' : 'Private'); ?></td>
        <td align="center"><?php echo $game->turn; ?></td>
        <td><?php echo $game->faction; ?></td>
        <td><?php echo $game->phase; ?></td>
        <?php if (isset($game->player_id) ): ?>
            <td align="center"> <?php echo ($game->turn_order == 0 ? 'Eliminated' : 'Active'); ?> </td>
        <?php endif; ?>
        <td> | <?php echo anchor('game/view/'.$game->game_id,'VIEW'); ?></td>
    </tr>
    
    <?php endif; ?>

<?php endforeach; ?>
    </tbody>
</table>
</div>
</div>
<?php $this->load->view('dashboard_sidebars'); ?>