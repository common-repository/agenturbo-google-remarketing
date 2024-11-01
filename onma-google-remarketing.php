<?php
/*
Plugin Name: AdWords AgenTurbo Google Remarketing
Plugin URI: https://adwords-agenturbo.de/wordpress-plug-in/adwords-remarketing-tag/
Description: Include Google Remarketing Ad Codes on a per page or post basis. There is also a spot for a default code.
Author: Adwords Agenturbo 
Version: 1.1
Author URI: https://adwords-agenturbo.de
License: GPL2

	Forked from:
	http://wordpress.org/extend/plugins/adwords-remarketing/

*/

function call_wpGoogleRemarketing() 
{
    return new wpGoogleRemarketing();
}
call_wpGoogleRemarketing();

class wpGoogleRemarketing
{
	private $default_post_types = array( 'page', 'post' );

	public function __construct()
	{
		// ADMIN STUFF
		if ( is_admin() )
		{
			$plugin = plugin_basename(__FILE__); 
			add_filter("plugin_action_links_$plugin", array( &$this, 'googleremarketing_settings_link' ) );		
		
			add_action(	'add_meta_boxes', 	array( &$this, 'add_googleremarketing_meta_box' ));
			add_action(	'save_post', 		array( &$this, 'save_googleremarketing_meta_box_content' ));
			
			add_action(	'admin_menu',		array( &$this, 'googleremarketing_admin_menu' ));
			add_action( 'admin_init', 		array( &$this, 'googleremarketing_register_settings' ) );
		}
		else
		{
			add_action(	'wp_footer', 		array( &$this,'embed_googleremarketing_meta_box_content' ), 100);
		}
	}
	
	
	// SETTINGS LINK ON PLUGIN PAGE
	function googleremarketing_settings_link($links) 
	{ 
	  $settings_link = '<a href="options-general.php?page=googleremarketing">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}
	
	
	// SETTINGS SETUP
	public function googleremarketing_register_settings()
	{
	
		// SETTINGS
		register_setting( 'googleremarketing-group', 'default_google_retracking_code', array( &$this,'googleremarketing_validate_settings' ) );
		register_setting( 'googleremarketing-group', 'google_retracking_post_types', array( &$this,'googleremarketing_validate_post_types_field' ) );
		
		
		// SECTION
		add_settings_section( 'default_settings', 'Einstellungen', array( &$this,'googleremarketing_default_section' ), 'google-remarketing' );
		// SECTION
		add_settings_section( 'default_settings', 'Einstellungen', array( &$this,'googleremarketing_default_section' ), 'google-remarketing' );
		
		// DEFAULT CODE
		add_settings_field( 'default_google_retracking_code', 'Default Retracking Code:', array( &$this,'googleremarketing_default_code_field' ), 'google-remarketing', 'default_settings' );
	
	
		// POST TYPES
		add_settings_field( 'google_retracking_display_post_types', 'Display for Post Types:', array( &$this,'googleremarketing_post_types_field' ), 'google-remarketing', 'default_settings' );
	}
	
	
	// SECTION DESCRIPTION
	function googleremarketing_default_section()
	{
		echo "Sie können in der folgenden Spalte Ihren Trackingcode 1:1 integrieren, den Sie aus Ihrem Google AdWords Konto genieren können. Eine Modifizierung des Codes wird automatisch vorgenommen.";
	}
		
		
	// RETRACKING CODE FIELD
	function googleremarketing_default_code_field() 
	{
	    ?>
	    <input type="text" name="default_google_retracking_code" value="<?php echo get_option('default_google_retracking_code'); ?>"  style="width: 90%;" />
	    <?php
	}
	
	
	// DISPLAY ON POST TYPES FIELD
	function googleremarketing_post_types_field()
	{
		$select_post_types = (array) get_option('google_retracking_post_types');
		if(!$select_post_types) { $select_post_types = $this->default_post_types; } // display on page & post by default
	
		$post_types = get_post_types();
		foreach( $post_types as $post_type)
		{
			$chcked = in_array($post_type, $select_post_types) ? " CHECKED " : "";
		?>
			<input type="checkbox" name="google_retracking_post_types[]" value="<?php echo $post_type ?>" <?php echo $chcked ?>/> <?php echo $post_type ?> &nbsp; 
		<?php 
		}
	}
	
	// SETTINGS PAGE MENU ITEM
	public function googleremarketing_admin_menu() 
	{
	    add_options_page( 'Google Remarketing', 'Google Remarketing', 'manage_options', 'googleremarketing', array( &$this, 'googleremarketing_settings' ) );
	}
	
	
	// DISPLAY SETTINGS
	public function googleremarketing_settings() 
	{
	    if (!current_user_can('manage_options')) 
	    {
	        wp_die('You do not have sufficient permissions to access this page.');
	    }
	?>
		
		<div class="wrap">
        <style type="text/css">
        div.plugintitle{ padding-bottom: 5px;
    overflow: hidden;
    padding-top: 5px;}
		  div.plugintitle h2{color: #528794;
    font-size: 34px;
    margin: 14px 0 0; }
		  div.plugintitle img{ float:right;}
		  .wp-core-ui .button-primary{  background-color: #528794;
    border: 0 none;
    border-radius: 0;
    font-size: 16px;
    height: auto;
    padding: 8px 15px;
    text-shadow: none;
    text-transform: uppercase; box-shadow:none;}
	
	.wp-core-ui .button-primary:hover{ background-color:#496268}
	
body {
    background: #FFFFFF none repeat scroll 0 0;
}
.form-table  input[type="text"],input[type="checkbox"]{box-shadow: 0 0 0 2px transparent, 0 1px 0 rgba(0, 0, 0, 0.08) inset;
    padding: 8px 12px;
    transition: all 0.3s ease 0s;
    color: #4e5663; box-shadow: 1px 1px 4px 0 rgba(0, 0, 0, 0.3);}
	div.updated{ margin-top:50px!important;}
        </style>
			<?php screen_icon(); ?>
			<div class="plugintitle"><?php
echo '<a href="https://adwords-agenturbo.de/" target="_blank"><img src="' . plugins_url( 'agenturbo-google-remarketing/images/logo-adwords-agenturbo.jpg', dirname(__FILE__) ) . '" ></a>';
?><h2 > AdWords AgenTurbo Google Remarketing</h2></div>
			<form method="post" action="options.php">
				<?php settings_fields( 'googleremarketing-group' ); ?>
				<?php do_settings_sections( 'google-remarketing' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>		
	<?php
	}
	

	// VALIDATE DEFAULT CODE FIELD
	public function googleremarketing_validate_settings( $input )
	{
		return $this->googleremarketing_sanatize_code( $input );
	}
	
	
	// VALIDATE POST TYPES FIELD
	public function googleremarketing_validate_post_types_field( $input )
	{
		return (array) $input;
	}
	
	// DISPLAY METABOXES
	public function add_googleremarketing_meta_box()
	{
		$select_post_types = (array) get_option('google_retracking_post_types');
		if(!$select_post_types) { $select_post_types = $this->default_post_types; }
		
		
		foreach($select_post_types as $select_post_type)
		{
			add_meta_box ( 'googleremarketing_meta_box_name', 'Google Remarketing', array(&$this,'render_googleremarketing_meta_box_content'), $select_post_type, 'normal', 'low' );
		
		}
	}
	
	
	// META BOX DISPLAY
	public function render_googleremarketing_meta_box_content($post) 
	{
		$out = '<label for="myplugin_new_field">Remarketing Code or Image URL</label><br />';
		$out .= '<input type="text" id="input_googleremarketing" name="input_googleremarketing" value="' . get_post_meta($post->ID, 'input_googleremarketing', true) . '" size="90" />';
		echo $out;
	}
	
	
	// FIND THE URL 
	public function googleremarketing_sanatize_code( $code )
	{
		$code = stripslashes( $code );
		$code = preg_replace( '^.*<img.*src="^is', '', $code);
		$code = preg_replace( '^["|\'].*^is', '', $code);
		return $code;
	}
	
	
	// SAVE META BOX
	public function save_googleremarketing_meta_box_content($postid) 
	{
		if(defined('DOING_AUTOSAVE')&& DOING_AUTOSAVE) return;
		
		if(isset($_POST['input_googleremarketing']))
		{
			$code = $this->googleremarketing_sanatize_code( $_POST['input_googleremarketing'] );

			add_post_meta( $postid, 'input_googleremarketing', $code, 1);
			update_post_meta( $postid, 'input_googleremarketing', $code);
			return $code;
		}
	}
	
	// CALLED FROM wp_footer, EMBEDS THE IMAGE
	public function embed_googleremarketing_meta_box_content( )
	{
		global $post;
		$code = false;
		
		// CHECK PAGE OR POST FOR CODE
		if(is_page() OR is_single() OR is_singular())
		{
			if( $post->ID )
			{
				$code = get_post_meta( $post->ID, 'input_googleremarketing', true );
			}
		}
	
		// IF NOT CODE, CHECK FOR DEFAULT
		if(!$code AND get_option('default_google_retracking_code'))
		{
			$code = get_option('default_google_retracking_code');
		}
		
		// RETURN THE CODE
		if($code)
		{
			echo "\n\n<!-- Google Remarketing Pixel -->\n" . '<img src="'.$code.'" alt="" height="1" width="1" border="0" style="border:none !important;" />' . "\n\n";
		}
	}
}