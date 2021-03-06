<?php
/**
 * Bcash gateway
 *
 * @deprecated 1.13.0
 * @package    WooCommerce_Bcash/Gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_BCash_Gateway class.
 */
class WC_BCash_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'bcash';
		$this->icon               = apply_filters( 'woocommerce_bcash_icon', plugins_url( 'assets/images/bcash.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = false;
		$this->method_title       = __( 'Bcash', 'woocommerce-bcash' );
		$this->method_description = __( 'Accept payments by credit card, bank debit or banking ticket using the Bcash.', 'woocommerce-bcash' );
		$this->order_button_text  = __( 'Checkout on Bcash', 'woocommerce-bcash' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->sandbox        = $this->get_option( 'sandbox', 'no' );
		$this->email          = $this->get_option( 'email' );
		$this->token          = $this->get_option( 'token' );
		$this->sandbox_email  = $this->get_option( 'sandbox_email' );
		$this->sandbox_token  = $this->get_option( 'sandbox_token' );
		$this->invoice_prefix = $this->get_option( 'invoice_prefix', 'WC-' );
		$this->debug          = $this->get_option( 'debug' );

		// Actions.
		add_action( 'woocommerce_api_wc_bcash_gateway', array( $this, 'ipn_handler' ) );
		add_action( 'woocommerce_receipt_bcash', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			$this->log = wc_get_logger();
		}
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	protected function using_supported_currency() {
		return 'BRL' == get_woocommerce_currency();
	}

	/**
	 * Get email.
	 *
	 * @since 1.13.0
	 *
	 * @return string
	 */
	public function get_email() {
		return 'yes' === $this->sandbox ? $this->sandbox_email : $this->email;
	}

	/**
	 * Get token.
	 *
	 * @since 1.13.0
	 *
	 * @return string
	 */
	public function get_token() {
		return 'yes' === $this->sandbox ? $this->sandbox_token : $this->token;
	}

	/**
	 * Get payment URL.
	 *
	 * @since 1.13.0
	 *
	 * @return string
	 */
	protected function get_payment_url() {
		$prefix = 'yes' === $this->sandbox ? 'sandbox' : 'www';

		return 'https://' . $prefix . '.bcash.com.br/checkout/pay/';
	}

	/**
	 * Get IPN URL.
	 *
	 * @since 1.13.0
	 *
	 * @return string
	 */
	protected function get_ipn_url() {
		$prefix = 'yes' === $this->sandbox ? 'sandbox' : 'www';

		return 'https://' . $prefix . '.bcash.com.br/transacao/consulta/';
	}

	/**
	 * Returns a value indicating the the Gateway is available or not.
	 *
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && '' !== $this->get_email() && '' !== $this->get_token() && $this->using_supported_currency();
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-bcash' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Bcash standard', 'woocommerce-bcash' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-bcash' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-bcash' ),
				'desc_tip'    => true,
				'default'     => __( 'Bcash', 'woocommerce-bcash' )
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-bcash' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-bcash' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay with credit card, bank debit or banking ticket using the Bcash.', 'woocommerce-bcash' )
			),
			'sandbox' => array(
				'title'       => __( 'Bcash Sandbox', 'woocommerce-bcash' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable Sandbox', 'woocommerce-bcash' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Bcash Sandbox can be used to test the payments. You can create your sandbox account %s.', 'woocommerce-bcash' ), '<a href="https://sandbox.bcash.com.br">' . __( 'here', 'woocommerce-bcash' ) . '</a>' ),
			),
			'email' => array(
				'title'       => __( 'Bcash Email', 'woocommerce-bcash' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Bcash email address; this is needed in order to take payment.', 'woocommerce-bcash' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'token' => array(
				'title'       => __( 'Bcash Access Key', 'woocommerce-bcash' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Bcash Access Key; is necessary to process the payment and notifications.', 'woocommerce-bcash' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'sandbox_email' => array(
				'title'       => __( 'Bcash Sandbox Email', 'woocommerce-bcash' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Bcash email address; this is needed in order to take payment.', 'woocommerce-bcash' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'sandbox_token' => array(
				'title'       => __( 'Bcash Sandbox Access Key', 'woocommerce-bcash' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Bcash Access Key; is necessary to process the payment and notifications.', 'woocommerce-bcash' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'invoice_prefix' => array(
				'title'       => __( 'Invoice Prefix', 'woocommerce-bcash' ),
				'type'        => 'text',
				'description' => __( 'Please enter a prefix for your invoice numbers. If you use your Bcash account for multiple stores ensure this prefix is unqiue as Bcash will not allow orders with the same invoice number.', 'woocommerce-bcash' ),
				'desc_tip'    => true,
				'default'     => 'WC-'
			),
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'woocommerce-bcash' ),
				'type'        => 'title',
				'description' => ''
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'woocommerce-bcash' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-bcash' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Bcash events, such as API requests, inside %s', 'woocommerce-bcash' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woocommerce-bcash' ) . '</a>' )
			)
		);
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'bcash-admin', plugins_url( 'assets/js/admin' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Bcash::VERSION, true );

		include dirname( __FILE__ ) . '/views/html-admin-page.php';
	}

	/**
	 * Generate the args to form.
	 *
	 * @param  object $order Order data.
	 *
	 * @return array         Form arguments.
	 */
	public function get_form_args( $order ) {
		$args = array(
			'email_loja'      => $this->get_email(),
			'tipo_integracao' => 'PAD',

			// Sender info.
			'nome'            => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'email'           => $order->get_billing_email(),
			'telefone'        => str_replace( array( '(', '-', ' ', ')' ), '', $order->get_billing_phone() ),
			// 'rg'
			// 'data_emissao_rg'
			// 'orgao_emissor_rg'
			// 'estado_emissor_rg'
			// 'cpf'
			// 'sexo'
			// 'data_nascimento'
			// 'celular'
			// 'cliente_razao_social'
			// 'cliente_cnpj'

			// Address info.
			'endereco'        => $order->get_billing_address_1(),
			'complemento'     => $order->get_billing_address_2(),
			//'bairro'
			'cidade'          => $order->get_billing_city(),
			'estado'          => $order->get_billing_state(),
			'cep'             => $order->get_billing_postcode(),

			// Tax.
			'acrescimo'       => $order->get_total_tax(),

			// Payment Info.
			'id_pedido'       => $this->invoice_prefix . $order->get_id(),

			// Shipping info.
			'frete'           => number_format( $order->get_shipping_total(), 2, '.', '' ),
			'tipo_frete'      => $order->get_shipping_method(),

			// Return.
			'url_retorno'     => $this->get_return_url( $order ),
			'redirect'        => 'true',
			'redirect_time'   => '15',

			// Notification url.
			'url_aviso'       => WC()->api_request_url( 'WC_BCash_Gateway' ),

			// Others fields.
			// 'parcela_maxima'
			// 'meio_pagamento'
			// 'meses_garantia'
			// 'free'
			// 'hash'
		);

		// Cart Contents.
		$item_loop = 0;
		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				if ( $item['qty'] ) {
					$item_loop++;
					$item_name = $item['name'];
					$item_meta = new WC_Order_Item_Meta( $item );

					if ( $meta = $item_meta->display( true, true ) ) {
						$item_name .= ' (' . $meta . ')';
					}

					$args['produto_codigo_' . $item_loop ]    = $item_loop;
					$args['produto_descricao_' . $item_loop ] = sanitize_text_field( $item_name );
					$args['produto_qtde_' . $item_loop ]      = $item['qty'];
					$args['produto_valor_' . $item_loop ]     = $order->get_item_total( $item, false );
				}
			}
		}

		$args = apply_filters( 'woocommerce_bcash_args', $args, $order );

		return $args;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}

	/**
	 * Output for the order received page.
	 *
	 * @param int $order_id Order ID.
	 */
	public function receipt_page( $order_id ) {
		$order     = wc_get_order( $order_id );
		$args      = $this->get_form_args( $order );
		$form_args = array();

		// Sort args.
		ksort( $args );

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, 'Payment arguments for order ' . $order->get_order_number() . ': ' . print_r( $args, true ) );
		}

		foreach ( $args as $key => $value ) {
			$form_args[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		// Bcash hash.
		$form_args[] = '<input type="hidden" name="hash" value="' . md5( http_build_query( $args ) . $this->get_token() ) . '" />';

		wc_enqueue_js( '
			jQuery.blockUI({
				message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Bcash to make payment.', 'woocommerce-bcash' ) ) . '",
				baseZ: 99999,
				overlayCSS: {
					background: "#fff",
					opacity: 0.6
				},
				css: {
					padding:         "20px",
					zindex:          "9999999",
					textAlign:       "center",
					color:           "#555",
					border:          "3px solid #aaa",
					backgroundColor: "#fff",
					cursor:          "wait",
					lineHeight:      "24px",
				}
			});
			jQuery( "#submit-payment-form" ).click();
		' );

		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Bcash.', 'woocommerce-bcash' ) . '</p>';

		echo '<form action="' . esc_url( $this->get_payment_url() ) . '" method="post" id="payment-form" target="_top">';
		echo implode( '', $form_args );
		echo '<input type="submit" class="button alt" id="submit-payment-form" value="' . __( 'Pay via Bcash', 'woocommerce-bcash' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-bcash' ) . '</a>';
		echo '</form>';
	}

	/**
	 * Get Bcash order data.
	 *
	 * @param  array $args
	 *
	 * @return array
	 */
	protected function get_bcash_order_data( $args ) {
		$args           = wp_unslash( $args );
		$transaction_id = '';
		$order_id       = '';

		if ( isset( $args['transacao_id'] ) && isset( $args['pedido'] ) ) {
			$transaction_id = sanitize_text_field( $args['transacao_id'] );
			$order_id       = sanitize_text_field( $args['pedido'] );
		} elseif ( isset( $args['id_transacao'] ) && isset( $args['id_pedido'] ) ) {
			$transaction_id = sanitize_text_field( $args['id_transacao'] );
			$order_id       = sanitize_text_field( $args['id_pedido'] );
		}

		if ( ! $transaction_id && ! $order_id ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Unable to check the Bcash transaction because is missing the IPN data...' );
			}

			return array();
		}

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, sprintf( 'Checking Bcash transaction #%s data for order %s...', $transaction_id, $order_id ) );
		}

		$data = build_query( array(
			'id_transacao' => $transaction_id,
			'id_pedido'    => $order_id,
			'codificacao'  => 1, // UTF-8
			'tipo_retorno' => 2  // JSON
		) );

		$params = array(
			'body'    => $data,
			'timeout' => 60,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->get_email() . ':' . $this->get_token() )
			)
		);

		$response = wp_safe_remote_post( $this->get_ipn_url(), $params );

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, 'Bcash order data response: ' . print_r( $response, true ) );
		}

		// Check to see if the response is valid.
		if ( ! is_wp_error( $response ) && 200 == $response['response']['code'] ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Bcash order data is valid!' );
			}

			return json_decode( $response['body'], true );
		}

		return array();
	}

	/**
	 * IPN handler.
	 */
	public function ipn_handler() {
		@ob_clean();

		$order_data = $this->get_bcash_order_data( $_POST );

		if ( ! empty( $order_data ) ) {
			header( 'HTTP/1.1 200 OK' );
			$this->update_order_status( $order_data );
		} else {
			$message = __( 'Bcash Request Unauthorized', 'woocommerce-bcash' );
			wp_die( $message, $message, array( 'response' => 401 ) );
		}
	}

	/**
	 * Update order status.
	 *
	 * @param array $transaction_data Bcash transaction data.
	 */
	protected function update_order_status( $transaction_data ) {
		$data     = $transaction_data['transacao'];
		$order_id = intval( str_replace( $this->invoice_prefix, '', sanitize_text_field( $data['id_pedido'] ) ) );
		$order    = wc_get_order( $order_id );

		// Checks whether the invoice number matches the order.
		// If true processes the payment.
		if ( $order->get_id() === $order_id ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Payment status from order ' . $order->get_order_number() . ': ' . sanitize_text_field( $data['status'] ) );
			}

			// Save order details.
			$order->set_transaction_id( sanitize_text_field( $data['id_transacao'] ) );
			$order->update_meta_data( __( 'Payer email', 'woocommerce-bcash' ), sanitize_text_field( $data['cliente_email'] ) );
			$order->update_meta_data( __( 'Payer name', 'woocommerce-bcash' ), sanitize_text_field( $data['cliente_nome'] ) );
			$order->update_meta_data( __( 'Payment type', 'woocommerce-bcash' ), sanitize_text_field( $data['meio_pagamento'] ) );

			// Update order status.
			switch ( intval( $data['cod_status'] ) ) {
				case 1 :
					if ( 10 === intval( $data['cod_meio_pagamento'] ) ) {
						$order->update_status( 'on-hold', __( 'Bcash: One bank ticket was printed, awaiting the payment approval.', 'woocommerce-bcash' ) );
					} else {
						$order->update_status( 'on-hold', __( 'Bcash: Payment under review.', 'woocommerce-bcash' ) );
					}
					break;
				case 3 :
					// Payment completed.
					$order->add_order_note( __( 'Bcash: Payment approved.', 'woocommerce-bcash' ) );
					$order->payment_complete();
					break;
				case 4 :
					$order->add_order_note( __( 'Bcash: Payment completed.', 'woocommerce-bcash' ) );
					break;
				case 5 :
					$order->update_status( 'on-hold', __( 'Bcash: Payment came into dispute.', 'woocommerce-bcash' ) );
					break;
				case 6 :
					$order->update_status( 'refunded', __( 'Bcash: Payment refunded.', 'woocommerce-bcash' ) );
					break;
				case 7 :
					$order->update_status( 'cancelled', __( 'Bcash: Payment canceled.', 'woocommerce-bcash' ) );
					break;
				case 8 :
					$order->update_status( 'failed', __( 'Bcash: Payment refused because of a chargeback.', 'woocommerce-bcash' ) );
					break;

				default :
					break;
			}

			$order->save();
		}
	}
}
