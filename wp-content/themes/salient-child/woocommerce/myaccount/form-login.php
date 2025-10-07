<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php $nectar_account_disable_reg_class = ( get_option( 'woocommerce_enable_myaccount_registration' ) !== 'yes' ) ? 'n-disable-reg' : ''; ?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

<div class="u-columns col2-set" id="customer_login">

	<div class="u-column1 col-1">

<?php endif; ?>

		<h2 class="<?php echo esc_attr( $nectar_account_disable_reg_class ); ?>"><?php esc_html_e( 'Login', 'woocommerce' ); ?></h2>

		<form class="woocommerce-form woocommerce-form-login login <?php echo esc_attr( $nectar_account_disable_reg_class ); ?>" method="post">

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				
				<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> 
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline" for="rememberme">
					<span></span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?>
				</label>
				<button type="submit" class="woocommerce-Button button" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
			</p>
			<p class="woocommerce-LostPassword lost_password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	</div>

	<div class="u-column2 col-2">

		<h2><?php esc_html_e( 'Register', 'woocommerce' ); ?></h2>

		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
			<p>Register for an account below to see if you are eligible for a Peter Timbs Delivery.</p>
			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
				<label for="first_name"><?php esc_html_e( 'First Name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="first_name" value="<?php echo ( !empty( $_POST['first_name'] ) ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
				<label for="last_name"><?php esc_html_e( 'Last Name', 'woocommerce' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="last_name" value="<?php echo ( !empty( $_POST['last_name'] ) ) ? esc_attr( wp_unslash( $_POST['last_name'] ) ) : ''; ?>" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="billing_company"><?php esc_html_e( 'Company Name', 'woocommerce' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_company" id="billing_company" value="<?php echo ( !empty( $_POST['billing_company'] ) ) ? esc_attr( wp_unslash( $_POST['billing_company'] ) ) : ''; ?>" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="loyalty_card_number"><?php esc_html_e( 'Loyalty Card Number', 'woocommerce' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="loyalty_card_number" id="loyalty_card_number" value="<?php echo ( !empty( $_POST['loyalty_card_number'] ) ) ? esc_attr( wp_unslash( $_POST['loyalty_card_number'] ) ) : ''; ?>" />
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
				</p>

			<?php endif; ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
				</p>
				
			<?php else : ?>

			<p><?php esc_html_e( 'A password will be sent to your email address.', 'woocommerce' ); ?></p>

			<?php endif; ?>
			<p class="form-row form-row-wide address-field" id="billing_country_field">
				<label for="billing_country" class="">Country</label>
				<span class="woocommerce-input-wrapper"><strong>New Zealand</strong>
					<input type="hidden" name="billing_country" id="billing_country" value="NZ" autocomplete="country" class="country_to_state" readonly="readonly">
				</span>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="billing_address_1"><?php esc_html_e( 'Street Address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text delivery_field" name="billing_address_1" id="billing_address_1" value="<?php echo ( !empty( $_POST['billing_address_1'] ) ) ? esc_attr( wp_unslash( $_POST['billing_address_1'] ) ) : ''; ?>" placeholder="House number and street name" />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text delivery_field" name="billing_address_2" id="billing_address_2" value="<?php echo ( ! empty( $_POST['billing_address_2'] ) ) ? esc_attr( wp_unslash( $_POST['billing_address_2'] ) ) : ''; ?>" placeholder="Apartment, suite, unit etc. (optional)"/>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="billing_city"><?php esc_html_e( 'Town/City', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text delivery_field" name="billing_city" id="billing_city" value="<?php echo ( ! empty( $_POST['billing_city'] ) ) ? esc_attr( wp_unslash( $_POST['billing_city'] ) ) : ''; ?>" />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="billing_state" class="billing_state_label"><?php esc_html_e( 'Region', 'woocommerce' ); ?></label>
				<?php 
					$country_obj = new WC_Countries; 
					$states = $country_obj->get_states("NZ");
				?>
				<select id="billing_state" name="billing_state" class="woocommerce-Input woocommerce-Input--select2 input-select2">
					<?php foreach ($states as $code => $name ) : ?>
						<option value="<?php echo $code; ?>"><?php echo $name; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="billing_postcode"><?php esc_html_e( 'Postcode', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text delivery_field" name="billing_postcode" id="billing_postcode" value="<?php echo ( ! empty( $_POST['billing_postcode'] ) ) ? esc_attr( wp_unslash( $_POST['billing_postcode'] ) ) : ''; ?>" />
			</p>
			<div id="delivery-note" class="woocommerce-notices-wrapper hide">
				<ul class="woocommerce-error" role="alert">
					<li></li>
				</ul>
			</div>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="billing_phone"><?php esc_html_e( 'Phone', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone" id="billing_phone" value="<?php echo ( ! empty( $_POST['billing_phone'] ) ) ? esc_attr( wp_unslash( $_POST['billing_phone'] ) ) : ''; ?>" />
			</p>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="woocommerce-FormRow form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>
			<input id="is_under_range" type="hidden" name="is_under_range" value="0">
		</form>

	</div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
