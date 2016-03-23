<?php /*
Plugin Name: SUPER_GIGGLE
Description: This plugin use for API.
Version: 1.0.0
Author: SUPER_GIGGLE
*/
include(ABSPATH . 'wp-config.php');
global $wpdb;
global $charset_collate;
function my_login_redirect( $url, $request, $user ){
    if( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
        if( $user->has_cap( 'administrator' ) ) {
		   $url = site_url().'/my-account/';
        } else {
            $url = home_url('/my-account/');
        }
    }
    return $url;
}
add_filter('login_redirect', 'my_login_redirect', 10, 3 );
$personalics =  'personalics';
add_action('wp_head','personalicsApiScript'); //use the function in wp-head section for add script in header
add_action( 'wp_head', 'single_product' );
add_action( 'wp_head', 'woocommerceCat' );
add_action( 'wp_footer', 'track_url' );
add_action( 'wp_head', 'cart' );
add_action( 'wp_head', 'thankyou' );
add_action( 'wp_head', 'login' );
add_action( 'wp_head', 'anypage' );
add_action('wp_head', 'search');
add_action('wp_head', 'userRegistrationscript');
add_action( 'user_register', 'userRegistration');

function personalicsApiScript() {
	global $wpdb;
	$api_get= $wpdb->get_results("Select * from personalics where id = '1'");
	$siteid = $api_get[0]->siteid;
	$sitedomain = $api_get[0]->sitedomain;
	$trackurl = $api_get[0]->trackurl;
	$output= "<script type='text/javascript'>
		var _paq = _paq || [];
		(function(){
		_paq.push(['setSiteId','$siteid']);
		_paq.push(['setCookieDomain', '$sitedomain']);
		_paq.push(['setDomains',['$sitedomain']]);
		_paq.push(['setTrackerUrl', '$trackurl']); })();</script>"; /*this function is used for include the script file*/
	
	echo $output;
}
function userRegistrationscript() {
	function userRegistration($user_id)
	{        
	$usrdata = get_user_by( 'ID', $user_id );
	echo $usremail = $usrdata->data->user_email;
			$userRegistration= "<script type='text/javascript'> 
			(function(){
			_paq.push(['setUserId','$usremail']);})();
			</script>"; /*this function is used for include the script file*/
			echo $userRegistration;
			return $usremail;
	}
}
add_action( 'admin_menu', 'personalic_menu_page' );
function personalic_menu_page(){
	$page_title ="personalic";
	$menu_title = "personalic";
	$menu_slug = "personalic";
	add_menu_page($page_title, $menu_title, 'manage_options', $menu_slug, 'personalic_function', '', 6);
}
register_activation_hook( __FILE__, 'plugin_activation' ); /*for create the table after activate the plugin*/
function plugin_activation() {
	$table_name = $wpdb->prefix.'personalics';
	$sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	api_key varchar(255) DEFAULT '' NOT NULL,
	siteid varchar(255) DEFAULT '' NOT NULL,
	sitedomain varchar(255) DEFAULT '' NOT NULL,
	trackurl varchar(255) DEFAULT '' NOT NULL,
	userid int(20) NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
//register_deactivation_hook( __FILE__, 'plugin_deactivation' );
	function single_product(){
		if(is_singular('product')){
			global $post; /* use for get the product detail for ecommerce*/
			$product_id = $post->ID;
			$_product = wc_get_product( $product_id );
			$price = $_product->get_regular_price();
			$saleprice = $_product->get_sale_price();
			if ($_product->is_in_stock() ) 
			{
				$type = 0;
				$name =  $_product->post->post_title;
				$sku = $_product->get_sku();
				$category_name = $wp_query->query_vars['product_cat'];
				$category_object = get_term_by('name', $category_name, 'product_cat');
				$category_id = $category_object->term_id;	
				$term_list = wp_get_post_terms($post->ID,'product_cat',array('fields'=>'all'));
				$catname =  array();
				$description = $_product->post->post_content;
				foreach($term_list as $t)
				{ $catname[]=$t->name; }
				$pcat=json_encode($catname);
				$image = wp_get_attachment_image_src( get_post_thumbnail_id($product_id));
				$javascript= "<script type='text/javascript'>
				(function(){
				_paq.push(['setCustomVariable', 1, 'original_price', '$price', 'page']);
				_paq.push(['setCustomVariable', 6, 'img_url', '$image[0]', 'page']);
				_paq.push(['setCustomVariable', 7, 'type', '$type', 'page']);
				_paq.push(['setCustomVariable', 8, 'description', '$description', 'page']);
				_paq.push(['setEcommerceView', '$sku', '$name', '$pcat','$saleprice']);
				})();</script>";
				echo $javascript;
			}	
			else
			{
				$type = 99;
				$name =  $_product->post->post_title;
				$sku = $_product->get_sku();
				$category_name = $wp_query->query_vars['product_cat'];
				$category_object = get_term_by('name', $category_name, 'product_cat');
				$category_id = $category_object->term_id;	
				$term_list = wp_get_post_terms($post->ID,'product_cat',array('fields'=>'all'));
				$catname =  array();
				$description = $_product->post->post_content;
				foreach($term_list as $t)
				{ $catname[]=$t->name; }
				$pcat=json_encode($catname);
				$image = wp_get_attachment_image_src( get_post_thumbnail_id($product_id));
				$javascript= "<script type='text/javascript'>
				(function(){
				_paq.push(['setRequestMethod', 'POST']);
				_paq.push(['setCustomVariable', 7, 'type', '$type', 'page']);
				_paq.push(['setEcommerceView', '$sku']);
				})();</script>";
				echo $javascript;
			}
		}
	}
	function woocommerceCat(){
		if(is_product_category()){
			$category = get_queried_object();
			$catid =  $category->term_id;
			$subcat= array();
			$subcat[] = $category->name;
			$taxonomies = array( 'product_cat' );
			$args = array(
			'fields' => 'ids',
			'child_of' => $catid
			);
			//let's get an array with ids of all the terms a the branch has  
			$subcategory = get_terms( $taxonomies, $args );
			foreach($subcategory as $hh)
			{ 
			$subcatname= get_term_by('id', $hh, 'product_cat', 'ARRAY_A');
			
			$subcat[] = $subcatname['name'];
			}	
			$categories_array = json_encode($subcat);
			$catproduct= "<script type='text/javascript'> 
			(function(){
				_paq.push(['setEcommerceView', false, false, $categories_array]);	
			})();
			</script>"; /*this function is used for include the script file*/
			echo $catproduct;
		}
	}
	function cart(){
		if( is_cart() ) {
			global $woocommerce;
			$cart_num_products = WC()->cart->cart_contents;
			//echo $totalamount = $woocommerce->cart->total;
			$totalamount = preg_replace("/&#?[a-z0-9]+;/i","",strip_tags($woocommerce->cart->get_cart_total()));
			$cartscript.= "<script type='text/javascript'> 
			(function(){
				_paq.push(['trackEcommerceCartUpdate', $totalamount]);
				";
			foreach($cart_num_products as $cartproducts){
				$quantity = $cartproducts['quantity'];
				$product_id = $cartproducts['product_id'];
				$price = $cartproducts['line_total'];
				$attribute_size = $cartproducts['variation']['attribute_size'];
				$_product = wc_get_product( $product_id );
				$name =  $_product->post->post_title;
				$sku = $_product->get_sku();
				$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'all'));
				$catname =  array();
				foreach($term_list as $t)
					{ $catname[]=$t->name; }
				$cats= json_encode($catname);
				$cartscript.=" _paq.push(['addEcommerceItem', '$sku', '$name', $cats, $price, $quantity]);";
			}
			$cartscript.="})();
			</script>"; /*this function is used for include the script file*/
			echo $cartscript;
		}
	}
	function thankyou(){
		if( is_wc_endpoint_url( 'order-received' ) ) {
			if(isset($_GET['view-order'])) {
			$order_id = $_GET['view-order'];
			}
			//check if on view order-received page and get parameter is available
			else if(isset($_GET['order-received'])) {
			$order_id = $_GET['order-received'];
			}
			//no more get parameters in the url
			else {
				$url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
				$template_name = strpos($url,'/order-received/') === false ? '/view-order/' : '/order-received/';
				if (strpos($url,$template_name) !== false) {
					$start = strpos($url,$template_name);
					$first_part = substr($url, $start+strlen($template_name));
					$order_id = substr($first_part, 0, strpos($first_part, '/'));
				}
			}
			//yes, I can retrieve the order via the order id
			$order = new WC_Order($order_id);
			$total = (float) $order->get_total();
			$shipping = $order->get_items( 'shipping' );
			//echo "<pre>"; print_r($order);echo "</pre>";
			foreach($shipping as $shippingprice)
			{ $shippingPrice = $shippingprice['cost'];}
			$items = $order->get_items(); 
			//echo "<pre>"; print_r($items);echo "</pre>";
			$thankuscript.= "<script type='text/javascript'> 
			(function(){ ";
			foreach($items as $item)
			{
				$name= $item['name'];
				$quantity= $item['qty'];				
				$product_id= $item['product_id'];
				$price= $item['line_total'];				
				$sub_total= $item['line_subtotal'];
				$_product = wc_get_product( $product_id );
				$sku = $_product->get_sku();
				$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'all'));
				$catname =  array();
				foreach($term_list as $t)
					{ $catname[]=$t->name; }
				$cats= json_encode($catname);
				$thankuscript.= " _paq.push(['addEcommerceItem', '$sku', '$name', '$cats', '$price', '$quantity']);";
				$subtotal += ( isset( $item['line_subtotal'] ) ) ? $item['line_subtotal'] : 0;
				$tax += ( isset( $item['line_tax'] ) ) ? $item['line_tax'] : 0;
			}
			$thankuscript.=" _paq.push(['trackEcommerceOrder', $order_id, $total,  $subtotal, $tax, $shippingPrice, $discount]);
			})();
			</script>";
			echo $thankuscript;
		}
	}
	function login(){	
		$current_user = wp_get_current_user();
	//	if ( in_array( $GLOBALS['PHP_SELF'], array( wp_login_url(), 'wp-register.php' ) ) )
		if(is_user_logged_in() && is_account_page())
		{
		$user_email=$current_user->data->user_email;
		$loginoutput= "<script type='text/javascript'> 
		(function(){
		_paq.push(['setUserId','$user_email']);})();
		</script>"; /*this function is used for include the script file*/
		echo $loginoutput;
		}
	}
	function anypage(){	
		if($_GET['psuid']!='')
		{
			$user_email=$_GET['email'];
			$psuid=$_GET['psuid'];
			$anypageoutput= "<script type='text/javascript'> 
			(function(){_paq.push(['setCustomVariable', 10, 'psuid', '$psuid', 'page', 'visit']);})();
			</script>"; /*this function is used for include the script file*/
			echo $anypageoutput;
		}
		if($_GET['email']!='')
		{
			$user_email=$_GET['email'];
			$psuid=$_GET['psuid'];
			$anypageoutput= "<script type='text/javascript'> 
			(function(){ _paq.push(['setUserId','$user_email']);})();
			</script>"; /*this function is used for include the script file*/
			echo $anypageoutput;
		}
	}
	function search(){	
		if($_GET['s']!='')
		{
			$query=$_GET['s'];
			global $wp_query;
			$resfound = $wp_query->found_posts;
			if(is_product_category()){
				$category = get_queried_object();
				$catid =  $category->term_id;
				$subcat= array();
				$catselected = $category->name;
				$subcat[] = $category->name;
				$taxonomies = array( 'product_cat' );
				$args = array(
				'fields' => 'ids',
				'child_of' => $catid
				);
				//let's get an array with ids of all the terms a the branch has  
				$subcategory = get_terms( $taxonomies, $args );
				foreach($subcategory as $hh)
				{ 
				$subcatname= get_term_by('id', $hh, 'product_cat', 'ARRAY_A');
				$subcat[] = $subcatname['name'];
				}	
				$categories_array = json_encode($subcat);
			}
			else{
				$catselected = 'false';
			}
			$anysearch="<script type='text/javascript'>
			(function(){ _paq.push(['trackSiteSearch','$query',$catselected,$resfound]);})();
			</script>";
			echo $anysearch;
		}
	}
	function personalic_function(){
		include('form.php');
	}
	function track_url(){
		if(!$_GET['s'])
		{
		$trackurl="<script type='text/javascript'>
		(function(){
		_paq.push(['trackPageView']);
		var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
		g.type='text/javascript'; g.defer=true; g.async=true;
		g.src='http://scripts.personalics.com/piwikm.js';
		s.parentNode.insertBefore(g,s);})();
		</script>";
		echo $trackurl;
		}
		else{
		$trackurl="<script type='text/javascript'>
		(function(){
		var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
		g.type='text/javascript'; g.defer=true; g.async=true;
		g.src='http://scripts.personalics.com/piwikm.js';
		s.parentNode.insertBefore(g,s);})();
		</script>";
		echo $trackurl;
		}
	}
?>
