<?php
defined( 'ABSPATH' ) || exit;
class PT_GSS_Api {

	private $site_id;

	private $api_key;

	private $api_url = "https://api.gosweetspot.com/api/";

	public function __construct()
	{
		// John Doe
		// test@jadecreative.co.nz
		// 287 Durham Street North, Christchurch Central City, Christchurch, 8013
		$gss_settings = get_option('peter_timbs_gss');
		$this->api_key = $gss_settings['gss_api_key'];
		$this->site_id = $gss_settings['gss_site_id'];
	}

	public function create_customer_order($wc_order)
	{
		$order_type = $wc_order->get_meta("_order_type");
		if( $order_type !== "delivery") {
			return true;
		}
		$order_items = $this->get_wc_order_items($wc_order);
		$address_array = $wc_order->get_address();
		$country = $address_array['country'];
		$state = $address_array['state'];
		$full_address  = [
			$address_array['address_1'],
			$address_array['address_2'],
			$address_array['city'],
			WC()->countries->get_states( $country )[$state],
			$address_array['postcode'],
			WC()->countries->countries[$country],
		];
		$full_address = str_replace( ", ,", ",",  implode(", ", $full_address) );
		$full_name = $wc_order->get_billing_first_name() . " " . $wc_order->get_billing_last_name();
		$data = [
			"packingslipno" => $wc_order->get_id(),
			"consignee" => $full_name,
			"address1"	=> $wc_order->get_billing_address_1(),
			"address2"	=> $wc_order->get_billing_address_2(),
			"suburb" 	=> $wc_order->get_billing_state(),
			"city"		=> $wc_order->get_billing_city(),
			"postcode"	=> $wc_order->get_billing_postcode(),
			"country"	=> $wc_order->get_billing_country(),
			"delvref"	=> $wc_order->get_id(),
			"delvinstructions" => $wc_order->get_customer_note(),
			"contactname"		=> $full_name,
			"contactphone"		=> $wc_order->get_billing_phone(),
			"email"				=> $wc_order->get_billing_email(),
			"rawaddress"		=> $full_address,
			"products"		=> $order_items,
			"customField1Value" => "PROCESSING",
			"customField2Value" => ""
		];
		$this->post("customerorders", [$data]);
	}

	private function post($endpoint, $data, $headers = [])
	{
		$url = $this->api_url . $endpoint;
		$headers["Content-Type"] = "application/json";
		$headers["access_key"] = $this->api_key;
		$headers["site_id"] = $this->site_id;
		$args = array(
		    'method' => 'PUT',
		    'body' => json_encode( $data ),
		    'headers' => $headers,
		);

		$response = wp_remote_request($url, $args);
		_dd($response);
	}

	private function get_wc_order_items($wc_order)
	{
		$items = $wc_order->get_items();
		$products = [];
		foreach($items as $item) {
			$i = $item->get_data();
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $i["id"] ), 'single-post-thumbnail' );
			$products[] = [
				"productcode" => $i["id"],
				"description" => $i["name"],
				"units"		  => $i["quantity"],
				"unitvalue"   => $i["total"] / $i["quantity"],
				"countryofManufacture" => "NZ",
				"imageurl" => $image[0],
				"currency" => "NZD",
				"alreadySent"	=> $i["total"],
				"fulfilledQty"	=> $i["quantity"],
				"linetotal"		=> $i["total"],
				"bin"	=> "",
			];
		}
		return $products;
	}

}