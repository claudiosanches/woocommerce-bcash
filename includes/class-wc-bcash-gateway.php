<?php
/**
 * WC Bcash Gateway Class.
 *
 * Built the Bcash method.
 */
class WC_BCash_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 *
	 * @return void
	 */
	public function __construct() {

		// Standards
		$this->id             = 'bcash';
		$this->icon           = apply_filters( 'woocommerce_bcash_icon', plugins_url( 'images/bcash.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields     = false;
		$this->method_title   = __( 'Bcash', 'woocommerce-bcash' );

		// API URLs.
		$this->payment_url    = 'https://www.bcash.com.br/checkout/pay/';
		$this->ipn_url        = 'https://www.bcash.com.br/checkout/verify/';
		$this->consulta_url	  = 'https://www.pagamentodigital.com.br/transacao/consulta';

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->email          = $this->get_option( 'email' );
		$this->token          = $this->get_option( 'token' );
		$this->invoice_prefix = $this->get_option( 'invoice_prefix', 'WC-' );
		$this->debug          = $this->get_option( 'debug' );

		// Actions.
		add_action( 'woocommerce_api_wc_bcash_gateway', array( $this, 'check_ipn_response' ) );
		add_action( 'valid_bcash_ipn_request', array( $this, 'successful_request' ) );
		add_action( 'woocommerce_receipt_bcash', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = $this->woocommerce_instance()->logger();
			}
		}

		// Display admin notices.
		$this->admin_notices();
	}

	/**
	 * Backwards compatibility with version prior to 2.1.
	 *
	 * @return object Returns the main instance of WooCommerce class.
	 */
	protected function woocommerce_instance() {
		if ( function_exists( 'WC' ) ) {
			return WC();
		} else {
			global $woocommerce;
			return $woocommerce;
		}
	}

	/**
	 * Displays notifications when the admin has something wrong with the configuration.
	 *
	 * @return void
	 */
	protected function admin_notices() {
		if ( is_admin() ) {
			// Checks if email is not empty.
			if ( empty( $this->email ) ) {
				add_action( 'admin_notices', array( $this, 'mail_missing_message' ) );
			}

			// Checks if token is not empty.
			if ( empty( $this->token ) ) {
				add_action( 'admin_notices', array( $this, 'token_missing_message' ) );
			}

			// Checks that the currency is supported
			if ( ! $this->using_supported_currency() ) {
				add_action( 'admin_notices', array( $this, 'currency_not_supported_message' ) );
			}
		}
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	protected function using_supported_currency() {
		return ( 'BRL' == get_woocommerce_currency() );
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = ( 'yes' == $this->get_option( 'enabled' ) ) &&
					! empty( $this->email ) &&
					! empty( $this->token ) &&
					$this->using_supported_currency();

		return $available;
	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {
		echo '<h3>' . __( 'Bcash standard', 'woocommerce-bcash' ) . '</h3>';
		echo '<p>' . __( 'Bcash standard works by sending the user to Bcash to enter their payment information.', 'woocommerce-bcash' ) . '</p>';

		// Generate the HTML For the settings form.
		echo '<table class="form-table">';
		$this->generate_settings_html();
		echo '</table>';
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'woocommerce-bcash' ),
				'type' => 'checkbox',
				'label' => __( 'Enable Bcash standard', 'woocommerce-bcash' ),
				'default' => 'yes'
			),
			'title' => array(
				'title' => __( 'Title', 'woocommerce-bcash' ),
				'type' => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-bcash' ),
				'desc_tip' => true,
				'default' => __( 'Bcash', 'woocommerce-bcash' )
			),
			'description' => array(
				'title' => __( 'Description', 'woocommerce-bcash' ),
				'type' => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-bcash' ),
				'default' => __( 'Pay via Bcash', 'woocommerce-bcash' )
			),
			'email' => array(
				'title' => __( 'Bcash Email', 'woocommerce-bcash' ),
				'type' => 'text',
				'description' => __( 'Please enter your Bcash email address; this is needed in order to take payment.', 'woocommerce-bcash' ),
				'desc_tip' => true,
				'default' => ''
			),
			'token' => array(
				'title' => __( 'Bcash Access Key', 'woocommerce-bcash' ),
				'type' => 'text',
				'description' => __( 'Please enter your Bcash Access Key; is necessary to process the payment and notifications.', 'woocommerce-bcash' ),
				'desc_tip' => true,
				'default' => ''
			),
			'invoice_prefix' => array(
				'title' => __( 'Invoice Prefix', 'woocommerce-bcash' ),
				'type' => 'text',
				'description' => __( 'Please enter a prefix for your invoice numbers. If you use your Bcash account for multiple stores ensure this prefix is unqiue as Bcash will not allow orders with the same invoice number.', 'woocommerce-bcash' ),
				'desc_tip' => true,
				'default' => 'WC-'
			),
			'testing' => array(
				'title' => __( 'Gateway Testing', 'woocommerce-bcash' ),
				'type' => 'title',
				'description' => ''
			),
			'debug' => array(
				'title' => __( 'Debug Log', 'woocommerce-bcash' ),
				'type' => 'checkbox',
				'label' => __( 'Enable logging', 'woocommerce-bcash' ),
				'default' => 'no',
				'description' => sprintf( __( 'Log Bcash events, such as API requests, inside %s', 'woocommerce-bcash' ), '<code>woocommerce/logs/bcash-' . sanitize_file_name( wp_hash( 'bcash' ) ) . '.txt</code>' )
			)
		);

	}

	/**
	 * Generate the args to form.
	 *
	 * @param  object $order Order data.
	 *
	 * @return array         Form arguments.
	 */
	public function get_form_args( $order ) {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			$shipping_total = $order->get_total_shipping();
		} else {
			$shipping_total = $order->get_shipping();
		}


		// Fixed phone number.
		$order->billing_phone = str_replace( array( '(', '-', ' ', ')' ), '', $order->billing_phone );

		$args = array(
			'email_loja'      => $this->email,
			'tipo_integracao' => 'PAD',

			// Sender info.
			'nome'            => $order->billing_first_name . ' ' . $order->billing_last_name,
			'email'           => $order->billing_email,
			'telefone'        => $order->billing_phone,
			//'rg'
			//'data_emissao_rg'
			//'orgao_emissor_rg'
			//'estado_emissor_rg'
			//'cpf'
			//'sexo'
			//'data_nascimento'
			//'celular'
			//'cliente_razao_social'
			//'cliente_cnpj'

			// Address info.
			'endereco'        => $order->billing_address_1,
			'complemento'     => $order->billing_address_2,
			//'bairro'
			'cidade'          => $order->billing_city,
			'estado'          => $order->billing_state,
			'cep'             => $order->billing_postcode,

			// Tax.
			'acrescimo'        => $order->get_total_tax(),

			// Discount/Coupon.
			'desconto'         => $order->get_order_discount(),

			// Payment Info.
			'id_pedido'        => $this->invoice_prefix . $order->id,

			// Shipping info.
			'frete'            => number_format( $shipping_total, 2, '.', '' ),
			'tipo_frete'       => $order->shipping_method_title,

			// Return.
			'url_retorno'      => $this->get_return_url( $order ),
			'redirect'         => 'true',
			'redirect_time'    => '0',

			// Notification url.
			'url_aviso'        => home_url( '/?wc-api=WC_BCash_Gateway' ),

			// Others fields.
			//'parcela_maxima'
			//'meio_pagamento'
			//'meses_garantia'
			//'free'
			//'hash'
		);

		// Cart Contents.
		$item_loop = 0;
		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				if ( $item['qty'] ) {
					$item_loop++;
					$item_name  = $item['name'];
					$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );

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
	 * Generate the form.
	 *
	 * @param int     $order_id Order ID.
	 *
	 * @return string           Payment form.
	 */
	public function generate_form( $order_id ) {
		$order      = new WC_Order( $order_id );
		$args       = $this->get_form_args( $order );
		$form_args  = array();

		if ( 'yes' == $this->debug ) {
			$this->log->add( 'bcash', 'Payment arguments for order ' . $order->get_order_number() . ': ' . print_r( $args, true ) );
		}

		foreach ( $args as $key => $value ) {
			$form_args[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			wc_enqueue_js( '
				jQuery.blockUI({
					message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Bcash to make payment.', 'woocommerce-bcash' ) ) . '",
					baseZ: 99999,
					overlayCSS: {
						background: "#fff",
						opacity: 0.6
					},
					css: {
						padding:        "20px",
						zindex:         "9999999",
						textAlign:      "center",
						color:          "#555",
						border:         "3px solid #aaa",
						backgroundColor:"#fff",
						cursor:         "wait",
						lineHeight:		"24px",
					}
				});
				jQuery("#submit-payment-form").click();
			' );
		} else {
			$this->woocommerce_instance()->add_inline_js( '
				jQuery("body").block({
					message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Bcash to make payment.', 'woocommerce-bcash' ) ) . '",
					overlayCSS: {
						background: "#fff",
						opacity:    0.6
					},
					css: {
						padding:         "20px",
						zIndex:          "9999999",
						textAlign:       "center",
						color:           "#555",
						border:          "3px solid #aaa",
						backgroundColor: "#fff",
						cursor:          "wait",
						lineHeight:      "24px"
					}
				});
				jQuery("#submit-payment-form").click();
			' );
		}

		return '<form action="' . esc_url( $this->payment_url ) . '" method="post" id="payment-form" target="_top">
				' . implode( '', $form_args ) . '
				<input type="submit" class="button alt" id="submit-payment-form" value="' . __( 'Pay via Bcash', 'woocommerce-bcash' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-bcash' ) . '</a>
			</form>';
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);
		} else {
			return array(
				'result'   => 'success',
				'redirect' => add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) )
			);
		}
	}

	/**
	 * Output for the order received page.
	 *
	 * @return void
	 */
	public function receipt_page( $order ) {
		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Bcash.', 'woocommerce-bcash' ) . '</p>';
		echo $this->generate_form( $order );
	}

	/**
	 * Check IPN.
	 *
	 * @return bool
	 */
	public function check_ipn_request_is_valid() {
		if ( 'yes' == $this->debug ) {
			$this->log->add( 'bcash', 'Checking IPN request...' );
		}

		// Get recieved values from post data.
		$received_values = (array) stripslashes_deep( $_POST );

		/**
		 * When transacao_id is set it's a "URL de Aviso" request, and it can't be checked
		 * When id_transacao is set it's a "URL de Retorno" request, and it can be checked
		 */
		if(isset($received_values['transacao_id'])){
			// We can't decide if it's valid
			return true;

		}else if (isset($received_values['id_transacao'])){
			$postdata  = 'transacao=' . $received_values['id_transacao'];
			$postdata .= '&status=' . $received_values['status'];
			$postdata .= '&cod_status=' . $received_values['cod_status'];
			$postdata .= '&valor_original=' . $received_values['valor_original'];
			$postdata .= '&valor_loja=' . $_POST['valor_loja'];
			$postdata .= '&token=' . $this->token;

			// Send back post vars.
			$params = array(
				'body'          => $postdata,
				'sslverify'     => false,
				'timeout'       => 60
			);

			// Post back to get a response.
			$response = wp_remote_post( $this->ipn_url, $params );

			if ( 'yes' == $this->debug ) {
				$this->log->add( 'bcash', 'IPN Response: ' . print_r( $response, true ) );
			}

			// Check to see if the request was valid.
			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && ( strcmp( $response['body'], 'VERIFICADO' ) == 0 ) ) {

				if ( 'yes' == $this->debug ) {
					$this->log->add( 'bcash', 'Received valid IPN response from Bcash' );
				}

				return true;
			} else {
				if ( 'yes' == $this->debug ) {
					$this->log->add( 'bcash', 'Received invalid IPN response from Bcash' );
				}
			}
		}

		return false;
	}

	/**
	 * Check API Response.
	 *
	 * @return void
	 */
	public function check_ipn_response() {
		@ob_clean();

		if ( ! empty( $_POST ) && ! empty( $this->token ) && $this->check_ipn_request_is_valid() ) {
			header( 'HTTP/1.1 200 OK' );
			do_action( 'valid_bcash_ipn_request', stripslashes_deep( $_POST ) );
		} else {
			wp_die( __( 'Bcash Request Failure', 'woocommerce-bcash' ) );
		}
	}

	/**
	 * Successful Payment!
	 *
	 * @param array $posted Bcash post data.
	 *
	 * @return void
	 */
	public function successful_request( $posted ) {
		// When transacao_id is set we need to retrieve order information
		// from a second secured source (Pagamento Digital url)
		if(isset($posted['transacao_id'])){
			// Post order id to retrieve order information
			$postdata  = 'id_transacao=' . $posted['transacao_id'];
			$postdata .= '&tipo_retorno=2'; //JSON

			$params = array(
				'body' => $postdata,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode($this->email.':'.$this->token)
				),
				'sslverify'     => false,
				'timeout'       => 60
			);
			$response = wp_remote_post( $this->consulta_url, $params );

			if ( 'yes' == $this->debug ) {
				$this->log->add( 'bcash', 'CONSULTA Response: ' . print_r( $response, true ) );
			}

			// Decoding order data to array
			$posted = json_decode($response['body'], true);
			$posted = $posted['transacao'];
			// There are 7 states on "URL de aviso" returns
			$posted['status_url_aviso'] = true;
		}

		if ( ! empty( $posted['id_pedido'] ) ) {
			$order_key = $posted['id_pedido'];
			$order_id = (int) str_replace( $this->invoice_prefix, '', $order_key );

			$order = new WC_Order( $order_id );

			// Checks whether the invoice number matches the order.
			// If true processes the payment.
			if ( $order->id === $order_id ) {

				if ( 'yes' == $this->debug ) {
					$this->log->add( 'bcash', 'Payment status from order ' . $order->get_order_number() . ': ' . $posted['status'] );
				}

				if( isset($posted['status_url_aviso']) && true == $posted['status_url_aviso'] ){
					switch ( $posted['cod_status'] ) {
						case 1 :
							$order->update_status( 'on-hold', __( 'BCash: The buyer initiated the transaction, but so far the BCash not received any payment information.', 'woocommerce-bcash' ) );
							break;
						case 2 :
							$order->update_status( 'on-hold', __( 'BCash: Payment under review.', 'woocommerce-bcash' ) );
							break;
						case 3 :
							$order->add_order_note( __( 'BCash: Payment approved.', 'woocommerce-bcash' ) );
							// For WooCommerce 2.2 or later.
							add_post_meta( $order->id, '_transaction_id', (string) $posted['id_transacao'], true );
							// Changing the order for processing and reduces the stock.
							$order->payment_complete();
							break;
						case 4 :
							$order->add_order_note( __( 'BCash: Payment completed and credited to your account.', 'woocommerce-bcash' ) );
							break;
						case 5 :
							$order->update_status( 'on-hold', __( 'BCash: Payment came into dispute.', 'woocommerce-bcash' ) );
							$this->send_email(
								sprintf( __( 'Payment for order %s came into dispute', 'woocommerce-bcash' ), $order->get_order_number() ),
								__( 'Payment in dispute', 'woocommerce-bcash' ),
								sprintf( __( 'Order %s has been marked as on-hold, because the payment came into dispute in BCash.', 'woocommerce-bcash' ), $order->get_order_number() )
							);
							break;
						case 6 :
							$order->update_status( 'refunded', __( 'BCash: Payment refunded.', 'woocommerce-bcash' ) );
							$this->send_email(
								sprintf( __( 'Payment for order %s refunded', 'woocommerce-bcash' ), $order->get_order_number() ),
								__( 'Payment refunded', 'woocommerce-bcash' ),
								sprintf( __( 'Order %s has been marked as refunded by BCash.', 'woocommerce-bcash' ), $order->get_order_number() )
							);
							break;
						case 7 :
							$order->update_status( 'cancelled', __( 'BCash: Payment canceled.', 'woocommerce-bcash' ) );
							break;

						default:
							// No action xD.
							break;
					}

				}else{
					switch ( $posted['cod_status'] ) {
						case '0':
							$order->update_status( 'on-hold', __( 'Bcash: Payment under review.', 'woocommerce-bcash' ) );

							break;
						case '1':

							// Order details.
							if ( ! empty( $posted['id_transacao'] ) ) {
								update_post_meta(
									$order_id,
									__( 'Bcash Transaction ID', 'woocommerce-bcash' ),
									$posted['id_transacao']
								);
							}
							if ( ! empty( $posted['cliente_email'] ) ) {
								update_post_meta(
									$order_id,
									__( 'Payer email', 'woocommerce-bcash' ),
									$posted['cliente_email']
								);
							}
							if ( ! empty( $posted['cliente_nome'] ) ) {
								update_post_meta(
									$order_id,
									__( 'Payer name', 'woocommerce-bcash' ),
									$posted['cliente_nome']
								);
							}
							if ( ! empty( $posted['tipo_pagamento'] ) ) {
								update_post_meta(
									$order_id,
									__( 'Payment type', 'woocommerce-bcash' ),
									$posted['tipo_pagamento']
								);
							}

							// Payment completed.
							$order->add_order_note( __( 'Bcash: Payment completed.', 'woocommerce-bcash' ) );
							$order->payment_complete();

							break;
						case '2':
							$order->update_status( 'cancelled', __( 'Bcash: Payment canceled.', 'woocommerce-bcash' ) );

							break;

						default:
							// No action xD.
							break;
					}
				}
			}
		}
	}

	/**
	 * Gets the admin url.
	 *
	 * @return string
	 */
	protected function admin_url() {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_bcash_gateway' );
		}

		return admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_BCash_Gateway' );
	}

	/**
	 * Adds error message when not configured the email.
	 *
	 * @return string Error Mensage.
	 */
	public function mail_missing_message() {
		echo '<div class="error"><p><strong>' . __( 'Bcash Disabled', 'woocommerce-bcash' ) . '</strong>: ' . sprintf( __( 'You should inform your email address. %s', 'woocommerce-bcash' ), '<a href="' . $this->admin_url() . '">' . __( 'Click here to configure!', 'woocommerce-bcash' ) . '</a>' ) . '</p></div>';
	}

	/**
	 * Adds error message when not configured the token.
	 *
	 * @return string Error Mensage.
	 */
	public function token_missing_message() {
		echo '<div class="error"><p><strong>' . __( 'Bcash Disabled', 'woocommerce-bcash' ) . '</strong>: ' . sprintf( __( 'You should inform your access key. %s', 'woocommerce-bcash' ), '<a href="' . $this->admin_url() . '">' . __( 'Click here to configure!', 'woocommerce-bcash' ) . '</a>' ) . '</p></div>';
	}

	/**
	 * Adds error message when an unsupported currency is used.
	 *
	 * @return string
	 */
	public function currency_not_supported_message() {
		echo '<div class="error"><p><strong>' . __( 'Bcash Disabled', 'woocommerce-bcash' ) . '</strong>: ' . sprintf( __( 'Currency <code>%s</code> is not supported. Works only with <code>BRL</code> (Brazilian Real).', 'woocommerce-bcash' ), get_woocommerce_currency() ) . '</p></div>';
	}

}
