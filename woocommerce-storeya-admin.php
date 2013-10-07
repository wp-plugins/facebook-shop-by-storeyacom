<?php

class woocommerce_storeya_admin {

	private $settings = array();
	private $product_fields = array ();



	function __construct() {

		$this->settings = get_option ( 'woocommerce_storeya_config' );
		
		$this->product_fields = array (										
									'disable_feed' => array (
										'desc' => __( 'Disable feed', 'woocommerce_storeya' ),
										'full_desc' => __ ( '', 'woocommerce_storeya' ) ),
		);

		add_action ( 'init', array ( &$this, 'init' ) );		

		add_filter ( 'woocommerce_settings_tabs_array', array ( &$this, 'add_woocommerce_settings_tab' ) );
		add_action ( 'woocommerce_settings_tabs_storeya', array ( &$this, 'config_page' ) );
		add_action ( 'woocommerce_update_options_storeya', array ( &$this, 'save_settings' ) );

	}


	function init() {		
	

		$this->product_fields = apply_filters ( 'woocommerce_wpf_product_fields', $this->product_fields );	

	}

	function add_woocommerce_settings_tab( $tabs ) {

		$tabs['storeya'] = __('StoreYa Facebook Store', 'woocommerce_storeya');
		return $tabs;

	}


	function config_page() {

		if ( ! empty ( $_POST['woocommerce_storeya_config'] ) ) {
			$this->save_settings();
		}

?>
        <h3><?php _e ( 'Congratulations!', 'woocommerce_storeya' ); ?></h3>
		
		<p><?php _e ( "You have successfully generated a Products Feed for your store!", 'woocommerce_storeya'); ?></p>
        </p>		
        <p><?php _e ( "1. Please go to ", 'woocommerce_storeya'); ?><a href="<?php esc_attr_e ( "http://www.StoreYa.com/" ); ?>" target="_blank">www.StoreYa.com</a>.</p>
        </p>
		<p><?php _e ( "2. If you are not logged in, please click on the &quot;Get started now - Connect with Facebook&quot; button, and choose WP as your store's solution.", 'woocommerce_storeya'); ?></p>
        </p>
		<p><?php _e ( "3. Type in your Store's URL and click on the &quot;Continue&quot; button and then on the &quot;Activate&quot; button.", 'woocommerce_storeya'); ?></p>
        </p>
		<p><?php _e ( "4. Connect your store to your Facebook fan page.", 'woocommerce_storeya'); ?></p>
        </p>
		<p><?php _e ( "5. Once you are happy with your Facebook store's customization, have it published!", 'woocommerce_storeya'); ?></p>
        </p>
		<br/>
		<p>
		<b><?php _e ( "Settings:", 'woocommerce_storeya'); ?></b></p>
        </p>
		
		<table class="form-table">
        <?php		

		foreach ( $this->product_fields as $key => $info ) {
			echo '<tr valign="top">';
			echo '  <th scope="row" class="titledesc">'.esc_html ( $info['desc'] ). '</th>';
			echo '  <td class="forminp">';
			echo '    <div class="woocommerce_storeya_field_selector_group">';
			echo '    <input type="checkbox" class="woocommerce_storeya_field_selector" name="woocommerce_storeya_config[product_fields]['.$key.']" ';
		
			if ( isset ( $this->settings['product_fields'][$key] ) ) {		 
				 echo 'checked="checked"';         
				 
			}	
			 
			
			echo '><label for="woocommerce_storeya_config[product_fields]['.$key.']">'.esc_html ( $info['full_desc'] ) . '</label>';
			if ( isset ( $this->product_fields[$key]['can_default'] ) ) {

				echo '<div class="woocommerce_storeya_config_'.$key.'"';
				if ( ! isset ( $this->settings['product_fields'][$key] ) ) {
					echo ' style="display:none;"';

				}
				echo '>'.__( 'Store default: ', 'woocommerce_storeya' );
				if ( ! isset ( $this->{"product_fields"}[$key]['callback'] ) || ! is_callable( $this->{"product_fields"}[$key]['callback'] ) ) {

					echo '<input type="textbox" name="_woocommerce_storeya_data['.$key.']" ';
					if ( !empty ( $this->settings['product_defaults'][$key] ) )
						echo ' value="'.esc_attr($this->settings['product_defaults'][$key]).'"';
					echo '>';

				} else {

					if ( isset ( $this->settings['product_defaults'][$key] ) ) {
						call_user_func( $this->{"product_fields"}[$key]['callback'], $key, $this->settings['product_defaults'][$key] );
					} else {
						call_user_func( $this->{"product_fields"}[$key]['callback'], $key );
					}

				}
				echo "</div></div></td>";
			}
			echo '</tr>';
		}
?>
        </table>
	    <script type="text/javascript">
		    jQuery(document).ready(function(){
  			    jQuery('.woocommerce_storeya_field_selector').change(function(){
  			    	group = jQuery(this).parent('.woocommerce_storeya_field_selector_group');
                    defspan = group.children('div');
                    defspan.slideToggle('fast');
                });
            });
        </script>
		<?php
	}

	
	function save_settings() {

		
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-settings' ) ) die( __( 'Save action failed. Please refresh the page and retry.', 'woothemes' ) );

		if ( ! $this->settings ) {
			$this->settings = array();
			add_option ( 'woocommerce_storeya_config', $this->settings, '', 'yes' );
		}

		foreach ( $_POST['_woocommerce_storeya_data'] as $key => $value ) {
			$_POST['_woocommerce_storeya_data'][$key] = stripslashes($value);
		}

		
		if ( isset ( $_POST['_woocommerce_storeya_data'] ) ) {
			$_POST['woocommerce_storeya_config']['product_defaults'] = $_POST['_woocommerce_storeya_data'];
			unset ( $_POST['_woocommerce_storeya_data'] );
		}

		$this->settings = $_POST['woocommerce_storeya_config'];
		update_option ( 'woocommerce_storeya_config', $this->settings );

		echo '<div id="message" class="updated"><p>'.__('Settings saved.').'</p></div>';

	}

}

$woocommerce_storeya_admin = new woocommerce_storeya_admin();

?>
