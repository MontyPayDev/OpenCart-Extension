<?php
class ControllerExtensionPaymentMontyPay extends Controller {

	public $currencies_3dotexponent = ['BHD', 'JOD', 'KWD', 'OMR', 'TND'];
    public $currencies_noexponent = [
        //'CLP', 
        'VND', 
        'ISK', 
        'UGX', 
        //'KRW', 
        //'JPY'
    ];
	public function index() {

		$this->load->language('extension/payment/montypay');

		// $data['text_testmode'] = $this->language->get('text_testmode');
		// $data['button_confirm'] = $this->language->get('button_confirm');

		// $data['testmode'] = $this->config->get('payment_montypay_test');

		// if (!$this->config->get('payment_montypay_test')) {
		// 	$data['action'] = 'https://www.paypal.com/cgi-bin/webscr&pal=V4T754QB63XXL';
		// } else {
		// 	$data['action'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr&pal=V4T754QB63XXL';
		// }

		// $this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		// if ($order_info) {
		// 	$data['business'] = $this->config->get('payment_montypay_email');
		// 	$data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		// 	$data['products'] = array();

		// 	foreach ($this->cart->getProducts() as $product) {
		// 		$option_data = array();

		// 		foreach ($product['option'] as $option) {
		// 			if ($option['type'] != 'file') {
		// 				$value = $option['value'];
		// 			} else {
		// 				$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						
		// 				if ($upload_info) {
		// 					$value = $upload_info['name'];
		// 				} else {
		// 					$value = '';
		// 				}
		// 			}

		// 			$option_data[] = array(
		// 				'name'  => $option['name'],
		// 				'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
		// 			);
		// 		}

		// 		$data['products'][] = array(
		// 			'name'     => htmlspecialchars($product['name']),
		// 			'model'    => htmlspecialchars($product['model']),
		// 			'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
		// 			'quantity' => $product['quantity'],
		// 			'option'   => $option_data,
		// 			'weight'   => $product['weight']
		// 		);
		// 	}

		// 	$data['discount_amount_cart'] = 0;

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

		// 	if ($total > 0) {
		// 		$data['products'][] = array(
		// 			'name'     => $this->language->get('text_total'),
		// 			'model'    => '',
		// 			'price'    => $total,
		// 			'quantity' => 1,
		// 			'option'   => array(),
		// 			'weight'   => 0
		// 		);
		// 	} else {
		// 		$data['discount_amount_cart'] -= $total;
		// 	}

		// 	$data['currency_code'] = $order_info['currency_code'];
		// 	$data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
		// 	$data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		// 	$data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
		// 	$data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
		// 	$data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
		// 	$data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
		// 	$data['country'] = $order_info['payment_iso_code_2'];
		// 	$data['email'] = $order_info['email'];
		// 	$data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		// 	$data['lc'] = $this->session->data['language'];
		// 	$data['return'] = $this->url->link('checkout/success');
		// 	$data['notify_url'] = $this->url->link('extension/payment/montypay/callback', '', true);
		// 	$data['cancel_return'] = $this->url->link('checkout/checkout', '', true);

		// 	if (!$this->config->get('payment_montypay_transaction')) {
		// 		$data['paymentaction'] = 'authorization';
		// 	} else {
		// 		$data['paymentaction'] = 'sale';
		// 	}

		// 	$data['custom'] = $this->session->data['order_id'];



			########################################MontyPay########################################

			$order_id = $this->session->data['order_id'];
			$first_name = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$last_name = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$country = $order_info['payment_iso_code_2'];
			$city = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$address = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
			$zip = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$description = 'Payment Order # ' . $order_id .' in the store ';
			$currency = $order_info['currency_code'];
			$total = $order_info['total'];

            if (in_array($currency, $this->currencies_noexponent)) {
                $amount = number_format($total, 0, '.', '');
            }elseif (in_array($currency, $this->currencies_3dotexponent)) {
                $amount = number_format($total, 3, '.', '');
            }else{
				$amount = number_format($total, 2, '.', '');
			}
			
			$customer = array(
				'name' => $first_name . ' ' . $last_name,
				'email' => $order_info['email'],
			);

			$billing_address = array(
				'country' => $country ? $country : 'NA',
				'city' => $city ? $city : 'NA',
				'address' => $address ? $address : 'NA',
				'zip' => $zip ? $zip : 'NA',
				// 'phone' => $order->get_billing_phone() ? $order->get_billing_phone() : 'NA',
			);

			$order_json = array(
				'number' => "$order_id",
				'description' => $description,
				'amount' => $amount, //may troubles
				'currency' => $currency,
			);
			
			$methods = $this->config->get('payment_montypay_method');

			$request_data = [
				'merchant_key' => $this->config->get('payment_montypay_merchant_key'),
				'operation'    => 'purchase', //m subs purchase
				'methods'      => $methods,
				'order'        => $order_json,
				'customer'     => $customer,
				'billing_address' => $billing_address,
				'success_url' => HTTPS_SERVER.'/index.php?route=checkout/success',
				'cancel_url'   => HTTPS_SERVER, //
				'hash'         => $this->get_hash('F_hosted', $order_id, $amount, $currency, $customer['email'], $description),
			];

			$fields  = json_encode($request_data);

			$getter = curl_init('https://checkout.montypay.com/api/v1/session'); //init curl
            curl_setopt($getter, CURLOPT_POST, 1); //post
            curl_setopt($getter, CURLOPT_POSTFIELDS, $fields); //json
            curl_setopt($getter, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); //header
            curl_setopt($getter, CURLOPT_RETURNTRANSFER, true);

            $res = curl_exec($getter);
            $err = curl_error($getter);
            $httpcode = curl_getinfo($getter, CURLINFO_HTTP_CODE);

			$json = json_decode((string) $res);

			

			if(isset($json->redirect_url) && $json->redirect_url){
				$redirect_url = $json->redirect_url;
				$data['redirect_url'] = $redirect_url;
				$data['result'] = 'success';
			}else{

				foreach($json->errors as $error){
					$data['errors'][] = array(
						'error_code'     => $error->error_code,
						'error_message'    => $error->error_message,
					);
				}
				
				$data['result'] = 'failed';
				$data['error_message'] = $json->error_message;
			}
			
			$data['httpcode'] = $httpcode;
			$data['json'] = json_decode($res);
			
			$callback_url = $this->url->link('extension/payment/montypay/callback', '', true);
			
			$data['notification_url'] = $callback_url; 

// 			file_put_contents('./log_1.log', json_encode($data), FILE_APPEND);

			return $this->load->view('extension/payment/montypay', $data);
	}

	function get_hash($formula, $order_id, $amount, $currency, $payer_email, $description){

		$merchant_password = $this->config->get('payment_montypay_merchant_password');
		
		$str_to_hash = $order_id . $amount . $currency . $description . $merchant_password;
		$hash = sha1(md5(strtoupper($str_to_hash)));    
		
		return $hash;
	}
	

	public function callback() {
	    
	    file_put_contents('./log_callback.log', json_encode($_POST), FILE_APPEND);

		if (isset($this->request->post['order_number'])) {
			$order_id = $this->request->post['order_number'];
		} else {
			$order_id = 0;
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {

				$order_status_id = $this->config->get('config_order_status_id');

				switch($this->request->post['status']) {
					case 'Canceled_Reversal':
						$order_status_id = $this->config->get('payment_montypay_canceled_reversal_status_id');
						break;
					case 'success':
						$total_paid_match = ((float)$this->request->post['order_amount'] == $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false));

						if ($total_paid_match) {
							$order_status_id = $this->config->get('payment_montypay_processed_status_id');
						}
						
						if (!$total_paid_match) {
							$this->log->write('montypay :: TOTAL PAID MISMATCH! ' . $this->request->post['order_amount']);
						}
						break;
					case 'Denied':
						$order_status_id = $this->config->get('payment_montypay_denied_status_id');
						break;
					case 'Expired':
						$order_status_id = $this->config->get('payment_montypay_expired_status_id');
						break;
					case 'failed':
						$order_status_id = $this->config->get('payment_montypay_failed_status_id');
						break;
					case 'Pending':
						$order_status_id = $this->config->get('payment_montypay_pending_status_id');
						break;
					case 'Processed':
						$order_status_id = $this->config->get('payment_montypay_processed_status_id');
						break;
					case 'Refunded':
						$order_status_id = $this->config->get('payment_montypay_refunded_status_id');
						break;
					case 'Reversed':
						$order_status_id = $this->config->get('payment_montypay_reversed_status_id');
						break;
					case 'Voided':
						$order_status_id = $this->config->get('payment_montypay_voided_status_id');
						break;
				}

				$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
		}
	}
}