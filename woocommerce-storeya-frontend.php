<?php
/**
 * woocommerce-storeya-frontend.php
 *
 * @package default
 */


class woocommerce_storeya_frontend {

	private $settings = array();

	function __construct() {

		$this->settings = get_option ( 'woocommerce_storeya_config' );
		
		//if ( isset ( $this->settings['product_fields']['disable_feed'] ) ) {
		//	return;
		//}

		add_action ( 'template_redirect', array ( &$this, 'render_product_feed' ), 15 );

		if ( isset ( $_REQUEST['action'] ) && 'woocommerce_storeya' == $_REQUEST['action'] ) {
            add_action ( 'woocommerce_storeya_elements', array ( &$this, 'google_elements' ), 10, 2 );
            add_action ( 'woocommerce_storeya_elements', array ( &$this, 'multiple_images' ), 10, 2 );
			add_action ( 'woocommerce_storeya_elements', array ( &$this, 'categories' ), 10, 2 );
		}

	}



	private function get_the_post_thumbnail_src( $post_id = null, $size = 'post-thumbnail' ) {

		$post_thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $post_thumbnail_id ) {
			return false;
        }

		list( $src ) = wp_get_attachment_image_src( $post_thumbnail_id, $size, false );

		return $src;
	}

	
	function render_product_feed() {

		global $wpdb, $wp_query, $post;
		
		define('DONOTCACHEPAGE', TRUE);
        
        set_time_limit ( 0 );

       
		$siteurl = home_url('/');
		$self = home_url("/index.php?action=woocommerce_storeya");

		header("Content-Type: application/xml; charset=UTF-8");
        
		echo "<?xml version='1.0' encoding='UTF-8' ?>\n\r";
		echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom' xmlns:g='http://base.google.com/ns/1.0'>\n";
		echo "  <channel>\n";
		echo "    <title><![CDATA[".get_option('blogname')." Products]]></title>\n";
		echo "    <link>".$siteurl."</link>\n";
		echo "    <description>StoreYa RSS feed 2.4</description>\n";
		
		echo "    <atom:link href='$self' rel='self' type='application/rss+xml' />\n";

		if ( isset ( $this->settings['product_fields']['disable_feed'] ) ) {
			echo "    <message>Feed was disabled.</message>\n";
			echo "  </channel>\n\r";
			echo "</rss>";

		exit();
		}

        $currency = get_option ( 'woocommerce_currency' );
        $weight_units = get_option ( 'woocommerce_weight_unit' );
        $base_country = get_option ( 'woocommerce_base_country' );

        if ( !empty ( $base_country ) && substr ( $base_country, 0, 2 ) == 'US' ) {
            $US_feed = true;
        } else {
            $US_feed = false;
        }

        
		$chunk_size = apply_filters ( 'woocommerce_storeya_chunk_size', 20 );

		$args['post_type'] = 'product';
		$args['numberposts'] = $chunk_size;
		$args['offset'] = 0;

		$products = get_posts ($args);

		while ( count ( $products ) ) {
			
			foreach ($products as $post) {

				setup_postdata($post);

	           	if (function_exists('get_product')) {
					$woocommerce_product = get_product ( $post->ID );
				} else {
					$woocommerce_product = new woocommerce_product ( $post->ID );
				}

	            if ( $woocommerce_product->visibility == 'hidden' )
	            	continue;
					
	            if ( ! $woocommerce_product->is_in_stock() )
	            	continue;
					
                if ( $US_feed ) {
				    $price = $woocommerce_product->get_price_excluding_tax();
                } else {
				    $price = $woocommerce_product->get_price();
                }

	            if ( count ( $woocommerce_product->children ) ) {

				    $children = $woocommerce_product->children;
					if ( is_array($children) ) {

						foreach ( $children as $child_product ) {
			
							if(isset($child_product->product))
							{							
								if ( $US_feed ) {
									$child_price = $child_product->product->get_price_excluding_tax();
								} else {
									$child_price = $child_product->product->get_price();
								}
				
								if (($price == 0) && ($child_price > 0)) {
									$price = $child_price;
								} else if ( ($child_price > 0) && ($child_price < $price) ) {
										$price = $child_price;
								}
							}
						}
					}

	            }

                if ( empty ( $price ) )
                    continue;

                $price = number_format ( $price, 2, '.', '' );

				$purchase_link = get_permalink($post->ID);

				echo "    <item>\n\r";
				echo "      <title><![CDATA[".get_the_title()."]]></title>\n\r";
				echo "      <link>$purchase_link</link>\n\r";
				
				echo "      <description><![CDATA[".substr(apply_filters ('the_content', get_the_content()),0,10000)."]]></description>\n\r";
				echo" <description_short><![CDATA[".get_post_field( post_excerpt, $post->ID )."]]></description_short>\n\r";
				
				echo "      <guid>woocommerce_storeya_".$post->ID."</guid>\n\r";

				$image_link = $this->get_the_post_thumbnail_src ( $post->ID, 'shop_large' );

				if ( ! empty ( $image_link ) ) {
				    echo "      <g:image_link>$image_link</g:image_link>\n\r";
				}


				echo "      <g:price>$price $currency</g:price>\n\r";

				$google_elements = apply_filters ( 'woocommerce_storeya_elements', array(), $post->ID );

				$done_condition = FALSE;
				$done_weight = FALSE;

				if ( count( $google_elements ) ) {

					foreach ( $google_elements as $element_name => $element_values ) {

						foreach ( $element_values as $element_value ) {

                           
                            if ( 'g:availability' == $element_name ) {
                                if ( ! $woocommerce_product->is_in_stock() ) {
                                    $element_value = 'out of stock';
                                }
                            }

							echo "      <".$element_name.">";
							echo "<![CDATA[".$element_value."]]>";
							echo "</".$element_name.">\n\r";

						}

						if ($element_name == 'g:shipping_weight')
							$done_weight = TRUE;

						if ($element_name == 'g:condition')
							$done_condition = TRUE;

					}

				}

				if (!$done_condition)
					echo "      <g:condition>new</g:condition>\n\r";

				if ( ! $done_weight ) {
					$weight = apply_filters ( 'woocommerce_storeya_shipping_weight', $woocommerce_product->get_weight(), $post->ID );
	                if ( $weight_units == 'lbs' )
	                    $weight_units = 'lb';

					if ( $weight && is_numeric( $weight ) && $weight > 0 ) {
						echo "      <g:shipping_weight>$weight $weight_units</g:shipping_weight>";
					}
				}

				echo "    </item>\n\r";

			}

			$args['offset'] += $chunk_size;
			$products = get_posts ( $args );

		}

		echo "  </channel>\n\r";
		echo "</rss>";

		exit();
	}



	
	function google_elements( $elements, $product_id ) {

		global $woocommerce_storeya_common;

		
		$product_values = $woocommerce_storeya_common->get_values_for_product ( $product_id );

		if ( ! empty ( $product_values ) ) {

			foreach ( $product_values as $key => $value ) {

				$elements['g:'.$key] = array ($value);

			}

		}

		return $elements;
	}

	function categories ( $elements, $product_id ) {	 
	
	  $categories = wp_get_post_terms( $product_id, 'product_cat' );
	  foreach ( $categories as $category ) {                
                
				$array = array($category->name);
                $elements['g:product_type'][] = $array[0];
            }
	  return $elements;
	}


	
	function multiple_images ( $elements, $product_id ) {

		global $woocommerce_storeya_common;

		$main_thumbnail = get_post_meta ( $product_id, '_thumbnail_id', true );

        $images = get_children( array ( 'post_parent' => $product_id,
                                        'post_status' => 'inherit',
                                        'post_type' => 'attachment',
                                        'post_mime_type' => 'image',
                                        'exclude' => isset($main_thumbnail) ? $main_thumbnail : '',
                                        'order' => 'ASC',
                                        'orderby' => 'menu_order' ) );

         
        if ( is_array ( $images ) && count ( $images ) ) {

            foreach ( $images as $image ) {
                
                $full_image_src = wp_get_attachment_image_src( $image->ID, 'original' );

                $elements['g:additional_image_link'][] = $full_image_src[0];

            }

        }

		return $elements;
	}



}


$woocommerce_storeya_frontend = new woocommerce_storeya_frontend();


?>