<?php 
/**
 * Card Class.
 *
 * @link       https://FeatherTechlabs.com
 * @since      1.0.0
 *
 * @package    Petertimbs
 * @subpackage Petertimbs/public
 * @author     FeatherTechlabs <dev@FeatherTechlabs.com>
 */
use \Firebase\JWT\JWT;

class Petertimbs_Card {

	public function index($request) {
		$this->user = pt_validate_request($request);

		global $wpdb;

		$sql = "SELECT pt.token_id, pt.token, pt.type, ptm.meta_key, ptm.meta_value FROM {$wpdb->prefix}woocommerce_payment_tokens as pt 
			INNER JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta as ptm 
				ON pt.token_id = ptm.payment_token_id
			WHERE pt.user_id = ".$this->user->user_id;
		$data = $wpdb->get_results($sql, ARRAY_A);
		$cardsData = [];
		foreach($data as $d) {
			$cardsData[$d['token_id']]["token"] = $d['token'];
			$cardsData[$d['token_id']]["token_id"] = $d['token_id'];
			$cardsData[$d['token_id']][$d['meta_key']] = $d['meta_value'];
		}
		$cards = [];
		foreach ($cardsData as $c) {
			$cards[] = $c;
		}
		$response = [
			"success" => true,
			"cardList"   => $cards
		];
        wp_send_json($response, 200);
        wp_die();
	}

	public function create($request) {
		$this->user = pt_validate_request($request);
		$post = $request->get_json_params();

		$customer_id = get_user_meta( $this->user->user_id, "wp__stripe_customer_id", true );
		if(empty($customer_id)) {
			$customer_id = $this->_generate_stripe_customer($post['token']);
		} else {
			$this->_attach_card_to_customer($customer_id, $post['token']);
		}

		global $wpdb;
		$token = [
			"user_id" => $this->user->user_id,
			"gateway_id" => "stripe",
			"token" => $post['token'],
			"type"  => "CC",
			"is_default" => 0
		];
		$wpdb->insert($wpdb->prefix."woocommerce_payment_tokens", $token);
		$token_id = $wpdb->insert_id;

		$meta = [
			"payment_token_id" => $token_id,
			"meta_key" => "last4",
			"meta_value" => $post['last4']
		];
		$wpdb->insert($wpdb->prefix."woocommerce_payment_tokenmeta", $meta);

		$meta = [
			"payment_token_id" => $token_id,
			"meta_key" => "expiry_year",
			"meta_value" => $post['expiry_year']
		];
		$wpdb->insert($wpdb->prefix."woocommerce_payment_tokenmeta", $meta);

		$meta = [
			"payment_token_id" => $token_id,
			"meta_key" => "expiry_month",
			"meta_value" => $post['expiry_month']
		];
		$wpdb->insert($wpdb->prefix."woocommerce_payment_tokenmeta", $meta);

		$meta = [
			"payment_token_id" => $token_id,
			"meta_key" => "card_type",
			"meta_value" => $post['card_type']
		];
		$wpdb->insert($wpdb->prefix."woocommerce_payment_tokenmeta", $meta);

		$response = [
			"success" => true,
			"token_id" => $token_id
		];
        wp_send_json($response, 200);
        wp_die();
	}

	public function delete($request) {
		$this->user = pt_validate_request($request);
		$post = $request->get_json_params();

		global $wpdb;
		$where = [
			"user_id" => $this->user->user_id,
			"token_id" => $post['token_id']
		];
		$deleted = $wpdb->delete($wpdb->prefix."woocommerce_payment_tokens", $where);
		if($deleted) {
			$where = [
				"payment_token_id" => $post['token_id']
			];
			$deleted = $wpdb->delete($wpdb->prefix."woocommerce_payment_tokenmeta", $where);
		}
		$response = [
			"success" => true,
			"message" => "Card removed"
		];
        wp_send_json($response, 200);
        wp_die();
	}

	private function _generate_stripe_customer($source) {
		$fields = [
			"email" => $this->user->email,
			"name"  => $this->user->first_name." ".$this->user->last_name,
			"source" => $source
		];
		$response = WC_Stripe_API::request( $fields, 'customers' );
		$customer_id = update_user_meta( $this->user->user_id, "wp__stripe_customer_id", $response->id );
		return $response->id;
	}

	private function _attach_card_to_customer($customer_id, $source) {
		$fields = [
			"source" => $source
		];
		$response = WC_Stripe_API::request( $fields, 'customers/'.$customer_id );
	}
}
