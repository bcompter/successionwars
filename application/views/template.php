<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="keywords" content="" />
		<meta name="description" content="" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Succession Wars : ScrapYardArmory.com</title>
		<link href="http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz" rel="stylesheet" type="text/css" />
                <link rel="stylesheet" <?php echo 'href="'.$this->config->item('base_url').'style_ng.css"'; ?> type="text/css" />
                <script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'jquery-3.1.0.min.js"'; ?>></script>
                <script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'jquery-ui/jquery-ui.min.js"'; ?>></script>
                <link rel="icon" href="<?=base_url()?>images/favicon.ico" type="image/ico">
	</head>
	<body>
                <?php $this->load->view('statusbar'); ?>
		<div id="bg">
			<div id="outer">
				<div id="header">
					<div id="logo">
						<h1>
							<a href=<?php echo '"'.$this->config->item('base_url').'"'; ?>>Succession Wars</a>
						</h1>
					</div>
					
				</div>
				<div id="main">
									
					<div id="content">
						<div class="box1">
                                                    <?php 
                                                        if (isset($error))
                                                            echo '<div class="error">'.$error.'</div>';
                                                        if (isset($notice))
                                                            echo '<div class="notice">'.$notice.'</div>';
                                                        if (isset($warning))
                                                            echo '<div class="warning">'.$warning.'</div>';
                                                        
                                                        $flash = $this->session->flashdata('notice');
                                                        if ($flash != '')
                                                            echo '<div class="notice">'.$flash.'</div>';
                           
                                                        $flash = $this->session->flashdata('error');
                                                        if ($flash != '')
                                                            echo '<div class="error">'.$flash.'</div>';
                                                        
                                                        $flash = $this->session->flashdata('warning');
                                                        if ($flash != '')
                                                            echo '<div class="warning">'.$flash.'</div>';
                                                        
                                                        if(isset($content))
                                                            $this->load->view($content); 
                                                        else 
                                                            echo '<div class="error">An Error Occured...  This should not of happened!</div>';

                                                     ?> 
						</div>
						
						
						<br class="clear" />
					</div>
					<br class="clear" />
				</div>
				<div id="footer">
					<br class="clear" />
				</div>
			</div>
			<div id="copyright">
                            <p>MechWarrior, BattleMech, Mech and AeroTech are registered trademarks of Topps, Inc. All Rights Reserved. <br />
                            ScrapYardArmory.com | Design: Nameless Geometry by nodethirtythree<br />
                            Page rendered in {elapsed_time} seconds</p>
                        </div>
                    <script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'touchscroll.js"'; ?>></script>                                            
		</div>
	</body>
</html>