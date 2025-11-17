<?php

require_once dirname(dirname(__FILE__)) . "/inc/class-gss-api.php";

class PT_GSS_Public {

	public function __construct()
	{
		add_action('woocommerce_thankyou', [$this, 'place_css_order']);
	}

	public function place_css_order($order_id)
	{
		$gss = new PT_GSS_Api();
		$order = wc_get_order($order_id);
		$gss_order = $gss->create_customer_order($order);
	}
}

new PT_GSS_Public();