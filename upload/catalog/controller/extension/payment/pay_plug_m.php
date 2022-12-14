<?php
class ControllerExtensionPaymentPayPlugM extends Controller {
    /**
     * @throws \Payplug\Exception\ConfigurationNotSetException
     * @throws \Payplug\Exception\ConfigurationException
     */
    public function index() {
		$this->load->language('extension/payment/pay_plug_m');

        if(!isset($this->session->data['order_id'])) {
            return false;
        }

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['order_info'] = print_r($order_info, true);

        $secretkey = $this->config->get('payment_pay_plug_m_secret_key');
        if (empty($secretkey)) {
            $data['payplug_error'] = 'Empty Secret Key';
            return $this->load->view('extension/payment/pay_plug_m', $data);
        }

        $apiVersion = '2019-06-14';

        require_once(DIR_SYSTEM . 'library' . DIRECTORY_SEPARATOR . 'payplug' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'init.php');
        \Payplug\Payplug::init(array('secretKey' => $secretkey, 'apiVersion' => $apiVersion));
        $lang = $order_info['language_code'] ?? 'en';
        if (strlen($lang) > 2) {
            $lang = explode('-', $lang);
            if (is_array($lang) && count($lang) > 1) {
                $lang = $lang[0];
            }
        }
        if (strlen($lang) > 2) {
            $lang = 'en';
        }
        $totalInEuro = $order_info['total'];
        if ($totalInEuro < 0.99) {
            $totalInEuro = 0.99;
        }
        $data['payplug_request'] = array(
            'amount'         => round($totalInEuro * 100),//1, // in cent
            'currency'       => $order_info['currency_code'] ?: 'EUR',// 'EUR',
            'save_card'      => false,
            'billing'          => array(
                'title'        => ' ', // 'mr'
                'first_name'   => $order_info['payment_firstname'],//'John',
                'last_name'    => $order_info['payment_lastname'],//'Watson',
                'email'        => $order_info['email'],//'john.watson@example.net',
                'address1'     => $order_info['payment_address_1'],//'221B Baker Street',
                'postcode'     => $order_info['payment_postcode'],//'NW16XE',
                'city'         => $order_info['payment_city'], // 'London',
                'country'      => $order_info['payment_iso_code_2'],// 'GB',payment_country
                'language'     => $lang,//'en'
              //  'mobile_phone_number' => $order_info['telephone'], // '+33612345678',
            ),
            'shipping'          => array(
                'title'         => ' ', // 'mr'
                'first_name'    => $this->cart->hasShipping() && !empty($order_info['shipping_firstname']) ? $order_info['shipping_firstname'] : $order_info['payment_firstname'], // 'John',
                'last_name'     => $this->cart->hasShipping() && !empty($order_info['shipping_lastname']) ? $order_info['shipping_lastname'] : $order_info['payment_lastname'], // 'Watson',
                'email'         => $order_info['email'],//'john.watson@example.net',
                'address1'      => $this->cart->hasShipping() && !empty($order_info['shipping_address_1']) ? $order_info['shipping_address_1'] : $order_info['payment_address_1'],// '221B Baker Street',
                'postcode'      => $this->cart->hasShipping() && !empty($order_info['shipping_postcode']) ? $order_info['shipping_postcode'] : $order_info['payment_postcode'],// 'NW16XE',
                'city'          => $this->cart->hasShipping() && !empty($order_info['shipping_city']) ? $order_info['shipping_city'] : $order_info['payment_city'],// 'London',
                'country'       => $this->cart->hasShipping() && !empty($order_info['shipping_iso_code_2']) ? $order_info['shipping_iso_code_2'] : $order_info['payment_iso_code_2'], // 'GB',
                'language'      => $lang,//'en',
               // 'mobile_phone_number' => $order_info['telephone'], // '+33612345678',
                'delivery_type' => 'BILLING',
//                'company_name'        => 'SuperCorp'
            ),
            'hosted_payment' => array(
                'return_url' => $this->url->link('extension/payment/pay_plug_m_success_checkout', 'customer_id='.$order_info['customer_id'].'&checkout_from=payplug', true),//'https://example.net/success?id=42',
                'cancel_url' => $this->url->link('checkout/failure'),// 'https://example.net/cancel?id=42'
            ),
            'notification_url' => $this->url->link('extension/payment/pay_plug_m_success_checkout'),//'https://example.net/notifications?id=42',
            'metadata'        => array(
                'customer_id' => $order_info['customer_id'],//42
            )
        );
        $data['payplug_request_text'] = '';//'<pre style="text-align: left;">'. print_r($data['payplug_request'], true). '</pre>';

        try {
            $payment = \Payplug\Payment::create($data['payplug_request']);
            if ($payment) {
                $data['payplug_url'] = htmlentities($payment->hosted_payment->payment_url);
            }
        } catch (\Exception $e) {
            $data['payplug_error'] = htmlentities($e->getMessage());
            $this->log->write('-------------------------------------------');
            $this->log->write('PayPlug ERROR $request: ' . print_r($data['payplug_request'], true));
            $this->log->write('PayPlug ERROR $response exception message: ' . $e->getMessage());
            $this->log->write('PayPlug ERROR $response exception file: ' . $e->getFile());
            $this->log->write('PayPlug ERROR $response exception line: ' . $e->getLine());
            $this->log->write('PayPlug ERROR $response exception class: ' . print_r($e, true));
            $this->log->write('-------------------------------------------');
        }


		return $this->load->view('extension/payment/pay_plug_m', $data);
	}
}
