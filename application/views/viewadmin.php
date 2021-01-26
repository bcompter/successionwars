<h1>Succession Wars | Admin</h1>
<br />
<p>
<?php
    echo 'Site Operational Status: ';
    if ( $admin->maintenance_mode )
    {
        echo 'Maintenance | '.anchor('admin/exit_maintenance','Exit Maintenance Mode');
    }
    else
    {
        echo 'Normal | '.anchor('admin/enter_maintenance','Enter Maintenance Mode');
    }
?>
</p>
<p>
<?php
    echo 'Registration Status: ';
    if ( !$admin->allow_register )
    {
        echo 'Disabled | '.anchor('admin/enable_registration','Enable Registration');
    }
    else
    {
        echo 'Enabled | '.anchor('admin/disable_registration','Disable Registration');
    }
?>
</p>

<p><?php echo anchor('game/view_games_needing_help', 'View Games Needing Help ('.count($games).')'); ?></p>

<p>
<h3>Dashboard Message:</h3>
    <?php echo $admin->dashboard_message; ?>
<br /><br />

    <?php echo anchor('admin/dashboard_message', 'EDIT'); ?>
</p>

<h3>Game Stats</h3>

<table cellspacing="10">
    <tr>
        <td><?php echo $num_games; ?></td>
        <td>Games</td>
    </tr>
    <tr>
        <td><?php echo $num_inactive_games; ?></td>
        <td>Inactive Games (<?php echo anchor('game/viewinactive','VIEW'); ?>)</td>
    </tr>
    <tr>
        <td><?php echo count($games); ?></td>
        <td>Games Needing Help (<?php echo anchor('game/view_games_needing_help', 'VIEW'); ?>)</td>
    </tr>
    <tr>
        <td><?php echo $num_chats; ?></td>
        <td>Chat Messages</td>
    </tr>
    <tr>
        <td><?php echo $chats_today; ?></td>
        <td>Chat Messages Today</td>
    </tr> 
    <tr>
        <td><?php echo $gamemsgs; ?></td>
        <td>Game Messages Today</td>
    </tr>
    <tr>
        <td><?php echo $num_users; ?></td>
        <td>Users</td>
    </tr>
    <tr>
        <td><?php echo $active_users; ?></td>
        <td>Active Users</td>
    </tr>
</table>