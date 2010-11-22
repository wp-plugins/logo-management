<?php
/*
Plugin Name:  Logo Management
Plugin URI: http://wordpress.org/extend/plugins/logo-management/
Description: Replace WordPress default login/register page logo with custom one.
Version: 1.0
Author: Rajasekaran M
Author URI: http://wordpress.org/extend/plugins/logo-management/
*/

define( 'LM_VERSION' , '1.0' );
define( 'LM_DIR' , dirname( str_replace( '\\' , '/' , __FILE__ ) ) . "/" ) ;
define( 'LM_FILE' , basename( LM_DIR ) );

define( 'LM_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
define( 'LM_URL', WP_PLUGIN_URL . "/" . LM_FOLDER . "/" );

define( 'LM_LOGO_URL', LM_URL . "logos/" );
define( 'LM_LOGO_DIR', LM_DIR . "logos/" );

/* load plugin text domain for get_text files. */
add_action('init', 'lm_load_textdomain');

function lm_load_textdomain() {
   	$thisDir = dirname( plugin_basename( __FILE__ ) );
   	load_plugin_textdomain( 'lm' , false , $thisDir . '/languages' );
}

require_once( 'includes/class.upload.php');

register_activation_hook( __FILE__ , 'lm_activation' );
register_deactivation_hook( __FILE__ , 'lm_deactivation' );

/* Add options upon plugin activation */
function lm_activation() {
	add_option( 'lm_logos' , array('lm_logo.png') );
	add_option( 'lm_logo_login', 'Yes' );
	add_option( 'lm_logo_admin', 'Yes' );	
	add_option( 'lm_logo', 'lm_logo.png');
	add_option( 'lm_logo_path', LM_LOGO_URL . 'lm_logo.png');
}

/* Delete options upon plugin deactivation */
function lm_deactivation() {
	delete_option( 'lm_logos' );
	delete_option( 'lm_logo_login' );
	delete_option( 'lm_logo_admin' );
	delete_option( 'lm_logo' );
	delete_option( 'lm_logo_path' );
}

add_action ( 'admin_menu', 'lm_menus' );

function lm_menus() {
	add_options_page( __('Logo Management', 'lm' ), __('Logo Management', 'lm' ) , 10, 'logo-management', 'logo_management');
}

if( !empty( $_FILES['lm_logo'] ) ) {
	$handle = new upload($_FILES['lm_logo']);
  	if ($handle->uploaded) {
      	$handle->process( LM_LOGO_DIR );
      	if ($handle->processed) {
      		$logos = get_option( 'lm_logos' );
			array_push($logos, $handle->file_dst_name );
			update_option( 'lm_logos' , $logos );
      		$handle->clean();
      		echo '<div id="message" class="updated fade"><p>'.__( 'Logo uploaded Successfully' , 'lm' ).'. </p></div>';
      	} else {
      		echo '<div id="message" class="error"><p>'.__( 'Error' , 'lm' ).' : '.$handle->error.'.</p></div>';
      	}
  	}
}

if ( !empty ( $_POST['lm_save_changes'] ) ) {
	update_option( 'lm_logo_login' , $_POST['lm_login'] );
	update_option( 'lm_logo_admin' , $_POST['lm_admin'] );
	update_option( 'lm_logo' , $_POST['selected_logo'] );
	update_option( 'lm_logo_path' , LM_LOGO_URL . $_POST['selected_logo'] );
}

//add logo to admin if "yes" selected
if( get_option( 'lm_logo_admin' ) == "Yes" ) {
	if ( is_admin() ) {
		add_action( 'admin_head', 'lm_admin_include' );
		function lm_admin_include() { ?>
			<style>
				#header-logo {
					display: none;
				}
				#LM_Logo {
					padding: 0 0 5px;
				}
				#wphead {
					height: auto;
					float: left;
					width: 100%;
				}
				#wphead h1 a {
					display: none;
				}
			</style>
			<script type="text/javascript">
			/* <![CDATA[ */
			window.onload = addLogo;						
			function addLogo() {
				var myBody = document.getElementById("wphead").getElementsByTagName("a")[0];
				myBody.innerHTML = "";
				var img = document.createElement("img");
				img.setAttribute("src","<?php echo get_option('lm_logo_path'); ?>");
				img.setAttribute("id", "LM_Logo");				
				myBody.appendChild(img);				
				myBody.style.display = "block";
				return false;
			}
			/* ]]> */
		</script>
		<?php
		}
	}
}

//add logo to login if "yes" is selected
if( get_option( 'lm_logo_login' ) == "Yes" ) {
	add_action( 'login_head', 'lm_login_include' );
	function lm_login_include() { ?>
		<style>
			#LM_Logo {
				padding: 0 0 10px 7px;
				max-width: 314px;
				margin: 0;
			}
			#login h1 {
				display: none;
			}
		</style>
		<script type="text/javascript">
			/* <![CDATA[ */
			window.onload = addLogo;						
			function addLogo() {
				var myBody = document.getElementById("login").getElementsByTagName("h1")[0];
				myBody.innerHTML = "";
				var img = document.createElement("img");
				img.setAttribute("src","<?php echo get_option('lm_logo_path'); ?>");
				img.setAttribute("id", "LM_Logo");				
				myBody.appendChild(img);				
				myBody.style.display = "block";
				return false;
			}
			/* ]]> */
		</script>
    <?php
	}
}

function logo_management() {
	?>
	<script type="text/javascript" src="<?php echo LM_URL; ?>js/jquery-1.3.2.js"></script>
	<script type="text/javascript" src="<?php echo LM_URL; ?>js/thickbox-compressed.js"></script>
	<link rel="stylesheet" href="<?php echo LM_URL; ?>css/thickbox.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo LM_URL; ?>css/lm-styles.css" type="text/css" />
	<script type="text/javascript">
	function delete_logo(val) {
		if( confirm('<?php echo __( "Are you sure want to delete this logo?" , 'lm' ); ?>') ) {
			$.post("<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", {action:"del_logo", val: val, "cookie": encodeURIComponent(document.cookie)}, function(str) {
				document.getElementById('logo_div_'+val).style.display = 'none';
			});
		} else {
			return false;
		}
	}
	function lm_validation() {
		if( $('#lm_logo').val() != '' ) {
			var ext = $('#lm_logo').val().split('.').pop().toLowerCase();
			var allow = new Array('gif','png','jpg','jpeg');
			if(jQuery.inArray(ext, allow) == -1) {
				alert('<?php echo __( 'Please upload .jpg or .jpeg or .png or .gif files only.', 'lm' ); ?>');
				return false;
			} else {
				return true;
			}
		}
	}
	</script>
	<div id="lm_form" class=wrap>
		<div id="icon-edit" class="icon32"><br></div>		
    	<h2><?php echo __( 'Logo Management', 'lm' ); ?></h2>
    	<form name="lm_upload_frm" action="<?php _e($_SERVER["REQUEST_URI"]); ?>" method="post" enctype="multipart/form-data" >
	    	<table class="form-table" >
				<tr valign="top">
					<td width="46%"><label for="lm_logo"><?php echo __( 'Choose a file to upload', 'lm' ); ?></label></td>
					<td><input type="file" name="lm_logo" id="lm_logo" /><img style="padding-left:10px;" src="<?php echo LM_URL; ?>img/refresh.gif" onClick="javascript:$('#lm_logo').val('');" /></td>
				</tr>
				<tr valign="top">
					<td><label for="lm_login"><?php echo __( 'Would you like your logo to appear on the login page?', 'lm' ); ?></label></td>
					<td>
						<label><input name="lm_login" value="Yes" type="radio" <?php if( get_option( 'lm_logo_login' ) == 'Yes' ) echo 'checked = "checked"'; ?> > <?php echo __( 'Yes', 'lm' ); ?></label><br>
						<label><input name="lm_login" value="No"  type="radio" <?php if( get_option( 'lm_logo_login' ) == 'No' ) echo 'checked = "checked"'; ?> > <?php echo __( 'No', 'lm' ); ?></label>
					</td>
				</tr>
				<tr valign="top">
					<td><label for="lm_admin"><?php echo __( 'Would you like your logo to appear on the admin pages?', 'lm' ); ?></label></td>
					<td>
						<label><input name="lm_admin" value="Yes" type="radio" <?php if( get_option( 'lm_logo_admin' ) == 'Yes' ) echo 'checked = "checked"'; ?> > <?php echo __( 'Yes', 'lm' ); ?></label><br>
						<label><input name="lm_admin" value="No" type="radio" <?php if( get_option( 'lm_logo_admin' ) == 'No' ) echo 'checked = "checked"'; ?>> <?php echo __( 'No', 'lm' ); ?></label>
					</td>
				</tr>
	    	</table>
    		
    		<div class="navigation">
				<ul class="thumbs">
					<?php $logos = get_option( 'lm_logos' ); ?>
					<?php $current_logo = get_option( 'lm_logo' ); ?>
					<?php foreach ($logos as $logo) { ?>
					<?php $selected = ( $current_logo == $logo ) ? 'checked = "checked"' : ''; ?>
					<li id="logo_div_<?php echo $logo; ?>" >
						<a href="<?php echo LM_LOGO_URL . $logo ; ?>" class="thickbox" style="padding: 2px; display: block; border: 1px solid #ccc;">					
							<img src="<?php echo LM_URL; ?>includes/timthumb.php?src=/logos/<?php echo $logo; ?>&w=100&zc=0" alt="<?php echo __( 'Logo', 'lm' ); ?>" />
						</a>
						<div class="caption">
							<ul id="options" class="imgmenu">
								<li id="delete" class="delete" onClick="return delete_logo('<?php echo $logo; ?>');" ><img src="<?php echo LM_URL; ?>/img/delete_icon.png" border="0" title="<?php echo __( 'Delete', 'lm' ); ?>" alt="<?php echo __( 'Delete', 'lm' ); ?>" /></li>
								<li id="preview" class="preview"><a href="<?php echo LM_LOGO_URL . $logo ; ?>" class="thickbox"><img src="<?php echo LM_URL; ?>/img/preview-icon.gif" title="<?php echo __( 'Preview', 'lm' ); ?>" alt="<?php echo __( 'Preview', 'lm' ); ?>"  /></a></li>
								<li id="select" class="select"><input type="radio" name="selected_logo" value="<?php echo $logo; ?>" <?php echo $selected; ?> /></li>
							</ul>
						</div>
					</li>
					<?php } ?>
				</ul>
			</div>
			<div style="clear:both;"></div>
			<input type="submit" id="lm_save_changes" name="lm_save_changes" value="<?php _e('Save Changes', 'lm') ?>" class="button-primary action" onClick="return lm_validation();" /><br />
		</form>
    </div>
	<?php 
}

add_action('wp_ajax_del_logo', 'ajaxDeleteLogo');

function ajaxDeleteLogo() {
	$val = $_REQUEST['val'];	
	$logos = get_option( 'lm_logos' );
	if ( !empty( $logos ) ) {
		foreach( $logos as $key => $value ) {
			if ( $value == $val) {
				unset( $logos[$key] );
				update_option( 'lm_logos', $logos );
				$logo_path= LM_LOGO_DIR . $val;
				@unlink($logo_path);
			}
		}
	}	
}

function lm_settings_link( $links ) { 
  $settings_link = '<a href="options-general.php?page=logo-management">'. __( 'Settings', 'lm' ) .'</a>'; 
  array_unshift( $links, $settings_link ); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter( "plugin_action_links_$plugin", 'lm_settings_link' );