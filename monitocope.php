<?php
/*
Plugin Name: Monitoscope
Description: Get alerted when your website go down with Monitoscope.
Author: <a href="http://www.eralion.com" target="_blank">ERALION.com</a>
Text Domain: monitoscope
Domain Path: /languages
Version: 1.0
*/
add_action( 'plugins_loaded', 'monitoscope_init' );
function monitoscope_init()
{
    load_plugin_textdomain( 'monitoscope', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function monitoscope_menu() {
        add_menu_page('Monitoscope', 'Monitoscope', 8, 'monitoscope_panel','monitoscope_panel', 'dashicons-welcome-view-site');
}
add_action("admin_menu", "monitoscope_menu");

function monitoscope_panel() {	
	$monitoscope_api_key=get_option('monitoscope_api_key');
	
	if (isset($_GET['addwebsite'])) {
		$response = wp_remote_get('https://www.monitoscope.com/?domain='.$_SERVER['HTTP_HOST'].'&api_key='.$monitoscope_api_key.'&add=1&adddomain='.(isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST']);
		$monitoscope_add_status = wp_remote_retrieve_body( $response );
	}
	
	if (isset($_POST['monitoscope_api_key'])) {
		$monitoscope_api_key=sanitize_text_field($_POST['monitoscope_api_key']);
		update_option('monitoscope_api_key',$monitoscope_api_key);
	}
	
	$monitoscope_status=0;
	if (strlen($monitoscope_api_key)>10) {
		$response = wp_remote_get('https://www.monitoscope.com/?domain='.$_SERVER['HTTP_HOST'].'&api_key='.$monitoscope_api_key);
		$monitoscope_status = wp_remote_retrieve_body( $response );
	}
	
	$notifmailalert='';
	if ($monitoscope_status==1) {
		$response = wp_remote_get('https://www.monitoscope.com/?domain='.$_SERVER['HTTP_HOST'].'&api_key='.$monitoscope_api_key.'&getmail=1');
		$notifmailalert = wp_remote_retrieve_body( $response );
	}
	
	echo '
	<div class="wrap">
		<h2>Monitoscope</h2>
		<div>
			<form action="" method="POST">
				'.($monitoscope_status!=1?'<p>'.__( 'Instructions to get Monitoscope working' , 'monitoscope' ).': <ul><li>1. '.__( 'You need to subscribe to <a href="https://www.monitoscope.com" target="_blank">https://www.monitoscope.com</a> to get an API key' , 'monitoscope' ).'</li><li>2. '.__( 'Enter your API key in the box on this page a little bit below' , 'monitoscope' ).'</li><li>3. '.__( 'Click on the link to add your site that appears' , 'monitoscope' ).'</li></ul></p><br>':'').'
				<p><b>'.__( 'Your API key' , 'monitoscope' ).':</b> <input type="text" name="monitoscope_api_key" value="'.$monitoscope_api_key.'" style="width:350px;"></p>
				<p><b>'.__( 'Status' , 'monitoscope' ).':</b> ';
				if ($monitoscope_status==1) {
					echo '<span style="color:green">'.__( 'ENABLED' , 'monitoscope' ).'</span>';
				}
				else {
					if ($monitoscope_status==2 && !isset($monitoscope_add_status)) {
						echo '<span style="color:orange">API key is valid but '.$_SERVER['HTTP_HOST'].' '.__('isn\'t added to your monitored websites on Monitoscope. <a href="admin.php?page=monitoscope_panel&addwebsite=1">Click here to add it !</a>' , 'monitoscope' ).'</span>';
					}
					elseif (isset($monitoscope_add_status) && $monitoscope_add_status!=1) {
						echo '<p style="color:red;">'.$monitoscope_add_status.'</p>';
					}
					else {
						echo '<span style="color:red">'.__( 'DISABLED' , 'monitoscope' ).'</span>';
					}
				}
				echo '</p>';
				if (strlen($notifmailalert)>3) {
					echo '<p><em>'.__('You will be notified by mail on this e-mail if your website goes down:' , 'monitoscope' ).' <b>'.$notifmailalert.'</b> (go on Monitoscope.com to edit)</em></p>';
				}
				echo '
				<br>
				<p><input type="submit" value="'.__( 'Update' , 'monitoscope' ).'"></p>
			</form>
		</div>
	</div>
	';
}

function monitoscope_sonde_footer() {
    echo '<span style="display:none !important;">sonde monitoscope</span>';
}
add_action( 'wp_footer', 'monitoscope_sonde_footer' );