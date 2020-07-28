<?php

class ps_payping extends ps_payment_gateway{

   public $tokencode;

   public $testMode = false;

   public function __construct() {
	  self::load_nusoap();
   }

   public function send($callback, $price, $username, $email, $order_id){
		/* Create Pay */
		$pay_data = array(
			'payerName' => $username,
			'Amount' => $price,
			'payerIdentity'=> $email ,
			'returnUrl' => $callback,
			'Description' => $email,
			'clientRefId' => $order_id
		);
	   $pay_args = array(
			'body' => json_encode( $pay_data ),
			'timeout' => '45',
			'redirection' => '5',
			'httpsversion' => '1.0',
			'blocking' => true,  
			'headers' => array(   
				'Authorization' => 'Bearer ' . $this->$tokencode,  
				'Content-Type'  => 'application/json',    
				'Accept' => 'application/json'  ),
			'cookies' => array()
		);
	   $pay_url = 'https://api.payping.ir/v2/pay';
		$pay_response = wp_remote_post( $pay_url, $pay_args );
		$PAY_XPP_ID = $pay_response["headers"]["x-paypingrequest-id"];
		if( is_wp_error( $pay_response ) ){
			$Status = 'failed';
			$Fault = $pay_response->get_error_message();
			$Message = 'خطا در ارتباط به پی‌پینگ : شرح خطا '.$pay_response->get_error_message();
		}else{
			$code = wp_remote_retrieve_response_code( $pay_response );
			if( $code === 200 ){
				if( isset( $pay_response["body"] ) and $pay_response["body"] != '' ){
					$code_pay = wp_remote_retrieve_body( $pay_response );
					$code_pay =  json_decode( $code_pay, true );
					$_x['transid'] = $code_pay["code"];
					$wpdb->update( $table_name, $_x, array( 'id' => $clientrefid ), $_y, array( '%d' ) );
					wp_redirect( sprintf( 'https://api.payping.ir/v2/pay/gotoipg/%s', $code_pay["code"] ) );
					exit;
				}else{
					$Message = ' تراکنش ناموفق بود- کد خطا : '.$PAY_XPP_ID;
					echo '<pre>';
					print_r($Message);
					echo '</pre>';
				}
			}elseif( $code == 400){
				$Message = wp_remote_retrieve_body( $pay_response ).'<br /> کد خطا: '.$PAY_XPP_ID;
				echo '<pre>';
				print_r($Message);
				echo '</pre>';
			}else{
				$Message = wp_remote_retrieve_body( $pay_response ).'<br /> کد خطا: '.$PAY_XPP_ID;
				echo '<pre>';
				print_r($Message);
				echo '</pre>';
			}
		}
   }

   public function verify($price, $post_id, $order_id, $course_id = 0) {
	$refId = $_POST['refid'];
    $clientrefid = $_POST['clientrefid'];
	/* Verify Pay */
    $varify_data = array( 'refId' => $refId, 'amount' => $price );
    $varify_args = array(
        'body' => json_encode( $varify_data ),
        'timeout' => '45',
        'redirection' => '5',
        'httpsversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Authorization' => 'Bearer ' . $this->$tokencode,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ),
        'cookies' => array()
    );

	$verify_url = 'https://api.payping.ir/v2/pay/verify';
	$verify_response = wp_remote_post( $verify_url, $varify_args );

	$VERIFY_XPP_ID = wp_remote_retrieve_headers( $verify_response )['x-paypingrequest-id'];
	if( is_wp_error( $verify_response ) ){
		$Message = 'خطا در ارتباط به پی‌پینگ : شرح خطا '.$verify_response->get_error_message();
		$this->danger_alert('خطا در پردازش عملیات پرداخت ، نتیجه پرداخت : '. $Message);
	}else{
		$code = wp_remote_retrieve_response_code( $verify_response );
		if( $code === 200 ){
			$this->success_payment($result['RefID'], $order_id, $price, $post_id, $course_id);
		}elseif( $code == 400) {
			$Message = wp_remote_retrieve_body( $verify_response ).'<br /> شماره خطا: '.$VERIFY_XPP_ID;
			$this->danger_alert('خطا در پردازش عملیات پرداخت ، نتیجه پرداخت : '. $Message);
		}else{
			$Message = wp_remote_retrieve_body( $verify_response ).'<br /> شماره خطا: '.$VERIFY_XPP_ID;
		   $this->danger_alert('خطا در پردازش عملیات پرداخت ، نتیجه پرداخت : '. $Message);
		}
	}
   }
}