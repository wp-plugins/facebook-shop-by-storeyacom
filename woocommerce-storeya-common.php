<?php

class woocommerce_storeya_common {



    private $settings = Array();
    private $category_cache = Array();



	function __construct() {

        $this->settings = get_option ( 'woocommerce_storeya_config' );

	}



   
    private function remove_blanks ( $array ) {

        if ( empty ( $array ) || ! is_array ( $array ) ) {
            return $array;
        }

        foreach ( array_keys ( $array ) as $key ) {

            if ( empty ( $array[$key] ) ) {
                unset ( $array[$key] );
            }
        }

        return $array;

    }




    public function get_values_for_product ( $product_id = null, $defaults_only = false ) {

        if ( ! $product_id ) 
            return false;

        
        $settings = $this->remove_blanks ( $this->settings['product_defaults'] );

        
        $categories = wp_get_object_terms ( $product_id, 'product_cat', array ( 'fields'=>'ids' ) );

        foreach ( $categories as $category_id ) {

            $category_settings = $this->get_values_for_category ( $category_id );
            $category_settings = $this->remove_blanks ( $category_settings );

            if ( $category_settings )
                $settings = array_merge ( $settings, $category_settings );

        }

        if ( $defaults_only )
            return $settings;
        
        
        $product_settings = get_post_meta ( $product_id, '_woocommerce_storeya_data', true );
        if ( $product_settings ) {
            $product_settings = $this->remove_blanks ( $product_settings );
            $settings = array_merge ( $settings, $product_settings );
        }

        return $settings;

    }



    private function get_values_for_category ( $category_id ) {

        if ( ! $category_id ) 
            return false;

        if ( isset ( $this->category_cache[$category_id] ) )
            return $this->category_cache[$category_id];

        $values = get_metadata( 'woocommerce_term', $category_id, '_woocommerce_storeya_data', true );
        $this->category_cache[$category_id] = &$values;

        return $this->category_cache[$category_id];

    }

}

$woocommerce_storeya_common = new woocommerce_storeya_common();



?>
