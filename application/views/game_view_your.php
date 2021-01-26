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
<h1>Succession Wars | Your Games</h1>

<br />
<h3> Active Games</h3>
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
<h3> Inactive Games</h3>

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