<div class="center">
<h1>Succession Wars | Login</h1>

<div class='mainInfo'>
<br />

    <div class="pageTitleBorder"></div>
	<p>Please login with your email address and password below.</p>
	
	<div id="infoMessage"><?php echo (isset($message) ? $message : '') ;?></div>
	
    <?php echo form_open("auth/login");?>
    	
      <p>
      	<label for="email">Email:</label>
      	<?php echo form_input($email);?>
      </p>
      
      <p>
      	<label for="password">Password:</label>
      	<?php echo form_input($password);?>
      </p>
      
      <p>
	      <label for="remember">Remember Me:</label>
	      <?php echo form_checkbox('remember', '1', FALSE);?>
	  </p>
      
      
      <p><?php echo form_submit('submit', 'Login');?></p>

      
    <?php echo form_close();?>
      
    <p><?php echo anchor('auth/forgot_password','Password Reset'); ?>
    </p>

</div>
</div>