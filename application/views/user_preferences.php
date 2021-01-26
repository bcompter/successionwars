<div class="center">
<h1>Succession Wars | User Preferences</h1>
<br />

<div class='mainInfo'>
	
    <h3>Hello <?php echo $user->username; ?></h3>
    
    Account Type: <?php echo $user->group; ?>
    <br /><br />
    
    <?php echo form_open("user/change_preferences");?>

    <h3>Email Notifications</h3>
    <p>
      <label for="send_email">Enable email game notifications:</label> <?php echo form_checkbox('send_email', '1', $user->send_me_email);?>
    </p>
    
    <p>
      <label for="email_on_private_message">Enable email notifications when you receive a private message:</label> <?php echo form_checkbox('email_on_private_message', '1', $user->email_on_private_message);?>
    </p>
    
    <h3>Game Options</h3>
    <p>
        <label for="auto_kill_all">If all your units will end up KIA in a region, automatically mark them KIA:</label>
        <?php
        $auto_kill_all_options = array(
                  '0' => 'No',
                  '1' => 'Upon viewing the combat',
                  //'2' => 'When kills are determined', TODO need to turn the code into a function for reuse
                  );
         echo form_dropdown('auto_kill_all',$auto_kill_all_options,$user->auto_kill_all);
         ?>
    </p>
        <?php
        //  TODO: Implement the following
        /*echo '<p>';
        echo '<label for="auto_kill_order">When only a portion of your units will end up KIA in a region, automatically mark them KIA in this order:</label>';
        $auto_kill_order_options = array(
                '0' => 'No',
                '1' => 'Conventional, Merc, Elementals, Non-Merc',
                '2' => 'Conventional, Elementals, Merc, Non-Merc',
                '3' => 'Merc, Conventional, Elementals, Non-Merc',
                '4' => 'Merc, Elementals, Conventional, Non-Merc',
                '5' => 'Elementals, Merc, Conventional, Non-Merc',
                '6' => 'Elementals, Conventional, Merc, Non-Merc',
                '7' => 'Weakest to strongest',
                '8' => 'Mercs, weakest to strongest',
                );
         echo form_dropdown('auto_kill_order',$auto_kill_order_options,$user->auto_kill_order);
         echo '</p>';*/
         ?>
    
        <h3>Forum Options</h3>
        <?php
        //  TODO: Implement the following
        echo '<p>';
        echo '<label for="forum_posts_per_page">Show this number of posts per page in each topic: </label>';
        $posts_per_page_options = array(
                '10' => '10',
                '20' => '20',
                '30' => '30',
                '40' => '40',
                '50' => '50',
                '0' => 'All Posts',
                );
         echo form_dropdown('forum_posts_per_page',$posts_per_page_options,$user->forum_posts_per_page);
         echo '</p>';
         ?>
    
    <p><?php echo form_submit('submit', 'Save Changes');?></p>
    
    <?php echo form_close();?>
    
    <hr>
    
    <h3>Change Your Password</h3>
    <p>
        <?php echo anchor('auth/change_password','Change Your Password'); ?>
    </p>

</div>
</div>