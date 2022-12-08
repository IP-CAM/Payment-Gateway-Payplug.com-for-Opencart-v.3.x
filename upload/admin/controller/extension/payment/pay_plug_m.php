<?php
class ControllerExtensionPaymentPayPlugM extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/pay_plug_m');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_pay_plug_m', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['secret_key'])) {
			$data['error_secret_key'] = $this->error['secret_key'];
		} else {
			$data['error_secret_key'] = '';
		}

		if (isset($this->error['publishable_key'])) {
			$data['error_publishable_key'] = $this->error['publishable_key'];
		} else {
			$data['error_publishable_key'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/pay_plug_m', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/pay_plug_m', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_pay_plug_m_secret_key'])) {
			$data['payment_pay_plug_m_secret_key'] = $this->request->post['payment_pay_plug_m_secret_key'];
		} else {
			$data['payment_pay_plug_m_secret_key'] = $this->config->get('payment_pay_plug_m_secret_key');
		}

		if (isset($this->request->post['payment_pay_plug_m_publishable_key'])) {
			$data['payment_pay_plug_m_publishable_key'] = $this->request->post['payment_pay_plug_m_publishable_key'];
		} else {
			$data['payment_pay_plug_m_publishable_key'] = $this->config->get('payment_pay_plug_m_publishable_key');
		}

		if (isset($this->request->post['payment_pay_plug_m_total'])) {
			$data['payment_pay_plug_m_total'] = $this->request->post['payment_pay_plug_m_total'];
		} else {
			$data['payment_pay_plug_m_total'] = $this->config->get('payment_pay_plug_m_total');
		}

		if (isset($this->request->post['payment_pay_plug_m_order_status_id'])) {
			$data['payment_pay_plug_m_order_status_id'] = $this->request->post['payment_pay_plug_m_order_status_id'];
		} else {
			$data['payment_pay_plug_m_order_status_id'] = $this->config->get('payment_pay_plug_m_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_pay_plug_m_geo_zone_id'])) {
			$data['payment_pay_plug_m_geo_zone_id'] = $this->request->post['payment_pay_plug_m_geo_zone_id'];
		} else {
			$data['payment_pay_plug_m_geo_zone_id'] = $this->config->get('payment_pay_plug_m_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_pay_plug_m_status'])) {
			$data['payment_pay_plug_m_status'] = $this->request->post['payment_pay_plug_m_status'];
		} else {
			$data['payment_pay_plug_m_status'] = $this->config->get('payment_pay_plug_m_status');
		}

		if (isset($this->request->post['payment_pay_plug_m_sort_order'])) {
			$data['payment_pay_plug_m_sort_order'] = $this->request->post['payment_pay_plug_m_sort_order'];
		} else {
			$data['payment_pay_plug_m_sort_order'] = $this->config->get('payment_pay_plug_m_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/pay_plug_m', $data));
	}


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/pay_plug_m')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_pay_plug_m_secret_key']) {
			$this->error['secret_key'] = $this->language->get('error_secret_key');
		}

		if (!$this->request->post['payment_pay_plug_m_publishable_key']) {
			$this->error['publishable_key'] = $this->language->get('error_publishable_key');
		}

		return !$this->error;
	}
}
