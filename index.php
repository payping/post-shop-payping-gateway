<?php
/*
Plugin Name: پرداخت پی‌پینگ - فروش پست‌ها
Version: 1.0.0
Description:  درگاه پرداخت پی‌پینگ برای افزونه Post Shop
Plugin URI: https://payping.ir
Author: Mahdi Sarani
Author URI: https://mahdisarani.ir
*/

if( ! defined( 'ABSPATH' ) ){
	exit;
}

function ps_load_payping_payment(){
	function ps_add_payping_payment( $list ){
		$list['payping'] = array(
			'name'       => 'پی‌پینگ',
			'class_name' => 'ps_payping',
			'settings'   => array(
				'tokencode' => array( 'name' => 'توکن پی‌پینگ' )
			)
		);

		return $list;
	}

	function ps_load_payping_class(){
		include_once plugin_dir_path( __FILE__ ) . '/ps_payping.php';
	}

	if( class_exists( 'ps_payment_gateway' ) && ! class_exists('ps_sms_newsms') ){
		add_filter( 'ps_payment_list', 'ps_add_payping_payment' );
		add_action( 'ps_load_payment_class', 'ps_load_payping_class' );
	}
}
add_action( 'plugins_loaded', 'ps_load_payping_payment', 0 );


add_action( 'admin_notices', 'ps_payping_check_requirement' );
function ps_payping_check_requirement(){
	if( current_user_can( 'activate_plugins' ) ){
		if( ! class_exists( 'ps_payment_gateway' ) ){
			echo '<div class="notice notice-warning is-dismissible">';
			echo 'برای استفاده از این درگاه پرداخت نیاز به افزونه فروش پست ها است،لطفا این پلاگین رو خریداری کنید و نصب فعال کنید.';
			echo '<br><a href="http://behnam-rasouli.ir/p/post-shop?source=pay_plugin">اطلاعات بیشتر ...</a>';
			echo '</div>';
		}elseif( version_compare( PS_VERSION, '5.5.0', '<' ) ){
			echo '<div class="notice notice-warning is-dismissible">';
			echo 'برای استفاده از این پلاگین ورژن افزونه فروش پست ها باید حداقل 5.5 باشد!';
			echo '<br><a href="http://behnam-rasouli.ir/p/post-shop?source=pay_plugin">اطلاعات بیشتر ...</a>';
			echo '</div>';
		}
	}
}