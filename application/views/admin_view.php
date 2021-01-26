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

<p>
<h3>Dashboard Message:</h3>
    <?php echo $admin->dashboard_message; ?>
<br /><br />

    <?php echo anchor('admin/dashboard_message', 'EDIT'); ?>
</p>