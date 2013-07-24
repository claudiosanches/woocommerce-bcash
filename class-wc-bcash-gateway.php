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
        global $woocommerce;

        $this->id             = 'bcash';
        $this->icon           = apply_filters( 'woocommerce_bcash_icon', plugins_url( 'images/bcash.png', __FILE__ ) );
        $this->has_fields     = false;
        $this->method_title   = __( 'Bcash', 'wcbcash' );

        // API Urls.
        $this->payment_url    = 'https://www.bcash.com.br/checkout/pay/';
        $this->ipn_url        = 'https://www.bcash.com.br/checkout/verify/';

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->title          = $this->settings['title'];
        $this->description    = $this->settings['description'];
        $this->email          = $this->settings['email'];
        $this->token          = $this->settings['token'];
        $this->invoice_prefix = ! empty( $this->settings['invoice_prefix'] ) ? $this->settings['invoice_prefix'] : 'WC-';
        $this->debug          = $this->settings['debug'];

        // Actions.
        add_action( 'woocommerce_api_wc_bcash_gateway', array( &$this, 'check_ipn_response' ) );
        add_action( 'valid_bcash_ipn_request', array( &$this, 'successful_request' ) );
        add_action( 'woocommerce_receipt_bcash', array( &$this, 'receipt_page' ) );
        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
        else
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );

        // Valid for use.
        $this->enabled = ( 'yes' == $this->settings['enabled'] ) && ! empty( $this->email ) && ! empty( $this->token ) && $this->is_valid_for_use();

        // Checks if email is not empty.
        if ( empty( $this->email ) )
            add_action( 'admin_notices', array( &$this, 'mail_missing_message' ) );

        // Checks if token is not empty.
        if ( empty( $this->token ) )
            add_action( 'admin_notices', array( &$this, 'token_missing_message' ) );

        // Active logs.
        if ( 'yes' == $this->debug )
            $this->log = $woocommerce->logger();
    }

    /**
     * Check if this gateway is enabled and available in the user's country.
     *
     * @return bool
     */
    public function is_valid_for_use() {
        if ( ! in_array( get_woocommerce_currency(), array( 'BRL' ) ) )
            return false;

        return true;
    }

    /**
     * Admin Panel Options.
     */
    public function admin_options() {

        echo '<h3>' . __( 'Bcash standard', 'wcbcash' ) . '</h3>';
        echo '<p>' . __( 'Bcash standard works by sending the user to Bcash to enter their payment information.', 'wcbcash' ) . '</p>';

        if ( ! $this->is_valid_for_use() ) {

            // Valid currency.
            echo '<div class="inline error"><p><strong>' . __( 'Gateway Disabled', 'wcbcash' ) . '</strong>: ' . __( 'Bcash does not support your store currency.', 'wcbcash' ) . '</p></div>';

        } else {

            // Generate the HTML For the settings form.
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }
    }

    /**
     * Initialise Gateway Settings Form Fields.
     *
     * @return void
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'wcbcash' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Bcash standard', 'wcbcash' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Title', 'wcbcash' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'wcbcash' ),
                'desc_tip' => true,
                'default' => __( 'Bcash', 'wcbcash' )
            ),
            'description' => array(
                'title' => __( 'Description', 'wcbcash' ),
                'type' => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'wcbcash' ),
                'default' => __( 'Pay via Bcash', 'wcbcash' )
            ),
            'email' => array(
                'title' => __( 'Bcash Email', 'wcbcash' ),
                'type' => 'text',
                'description' => __( 'Please enter your Bcash email address; this is needed in order to take payment.', 'wcbcash' ),
                'desc_tip' => true,
                'default' => ''
            ),
            'token' => array(
                'title' => __( 'Bcash Token', 'wcbcash' ),
                'type' => 'text',
                'description' => __( 'Please enter your Bcash token; is necessary to process the payment and notifications.', 'wcbcash' ),
                'desc_tip' => true,
                'default' => ''
            ),
            'invoice_prefix' => array(
                'title' => __( 'Invoice Prefix', 'wcbcash' ),
                'type' => 'text',
                'description' => __( 'Please enter a prefix for your invoice numbers. If you use your Bcash account for multiple stores ensure this prefix is unqiue as Bcash will not allow orders with the same invoice number.', 'wcbcash' ),
                'desc_tip' => true,
                'default' => 'WC-'
            ),
            'testing' => array(
                'title' => __( 'Gateway Testing', 'wcbcash' ),
                'type' => 'title',
                'description' => '',
            ),
            'debug' => array(
                'title' => __( 'Debug Log', 'wcbcash' ),
                'type' => 'checkbox',
                'label' => __( 'Enable logging', 'wcbcash' ),
                'default' => 'no',
                'description' => sprintf( __( 'Log Bcash events, such as API requests, inside %s', 'wcbcash' ), '<code>woocommerce/logs/moip' . sanitize_file_name( wp_hash( 'bcash' ) ) . '.txt</code>' ),
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
            'frete'            => number_format( $order->get_shipping(), 2, '.', '' ),
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
                    if ( $meta = $item_meta->display( true, true ) )
                        $item_name .= ' (' . $meta . ')';

                    $args['produto_codigo_' . $item_loop]    = $item_loop;
                    $args['produto_descricao_' . $item_loop] = sanitize_text_field( $item_name );
                    $args['produto_qtde_' . $item_loop]      = $item['qty'];
                    $args['produto_valor_' . $item_loop]     = $order->get_item_total( $item, false );

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
        global $woocommerce;

        $order = new WC_Order( $order_id );

        $args = $this->get_form_args( $order );

        if ( 'yes' == $this->debug )
            $this->log->add( 'bcash', 'Payment arguments for order ' . $order->get_order_number() . ': ' . print_r( $args, true ) );

        $args_array = array();

        foreach ( $args as $key => $value )
            $args_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';

        if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
            $woocommerce->get_helper( 'inline-javascript' )->add_inline_js( '
                jQuery.blockUI({
                        message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Bcash to make payment.', 'wcpagseguro' ) ) . '",
                        baseZ: 99999,
                        overlayCSS:
                        {
                            background: "#fff",
                            opacity: 0.6
                        },
                        css: {
                            padding:        "20px",
                            zIndex:         "9999999",
                            textAlign:      "center",
                            color:          "#555",
                            border:         "3px solid #aaa",
                            backgroundColor:"#fff",
                            cursor:         "wait",
                            lineHeight:     "24px",
                        }
                    });
                jQuery("#submit-payment-form").click();
            ' );
        } else {
            $woocommerce->add_inline_js( '
                jQuery("body").block({
                        message: "<img src=\"' . esc_url( $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif' ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />' . __( 'Thank you for your order. We are now redirecting you to Bcash to make payment.', 'wcpagseguro' ) . '",
                        overlayCSS:
                        {
                            background: "#fff",
                            opacity:    0.6
                        },
                        css: {
                            padding:         20,
                            textAlign:       "center",
                            color:           "#555",
                            border:          "3px solid #aaa",
                            backgroundColor: "#fff",
                            cursor:          "wait",
                            lineHeight:      "32px",
                            zIndex:          "9999"
                        }
                    });
                jQuery("#submit-payment-form").click();
            ' );
        }

        return '<form action="' . esc_url( $this->payment_url ) . '" method="post" id="payment-form" target="_top">
                ' . implode( '', $args_array ) . '
                <input type="submit" class="button alt" id="submit-payment-form" value="' . __( 'Pay via Bcash', 'wcbcash' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'wcbcash' ) . '</a>
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
        global $woocommerce;

        echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Bcash.', 'wcbcash' ) . '</p>';

        echo $this->generate_form( $order );
    }

    /**
     * Check ipn.
     *
     * @return bool
     */
    public function check_ipn_request_is_valid() {

        if ( 'yes' == $this->debug )
            $this->log->add( 'bcash', 'Checking IPN request...' );

        // Get recieved values from post data.
        $received_values = (array) stripslashes_deep( $_POST );

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
            'timeout'       => 30
        );

        // Post back to get a response.
        $response = wp_remote_post( $this->ipn_url, $params );

        if ( 'yes' == $this->debug )
            $this->log->add( 'bcash', 'IPN Response: ' . print_r( $response, true ) );

        // Check to see if the request was valid.
        if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && ( strcmp( $response['body'], 'VERIFICADO' ) == 0 ) ) {

            if ( 'yes' == $this->debug )
                $this->log->add( 'bcash', 'Received valid IPN response from Bcash' );

            return true;
        } else {
            if ( 'yes' == $this->debug )
                $this->log->add( 'bcash', 'Received invalid IPN response from Bcash' );
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
            wp_die( __( 'Bcash Request Failure', 'wcbcash' ) );
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

        if ( ! empty( $posted['id_pedido'] ) ) {
            $order_key = $posted['id_pedido'];
            $order_id = (int) str_replace( $this->invoice_prefix, '', $order_key );

            $order = new WC_Order( $order_id );

            // Checks whether the invoice number matches the order.
            // If true processes the payment.
            if ( $order->id === $order_id ) {

                if ( 'yes' == $this->debug )
                    $this->log->add( 'bcash', 'Payment status from order ' . $order->get_order_number() . ': ' . $posted['status'] );

                switch ( $posted['cod_status'] ) {
                    case '0':
                        $order->update_status( 'on-hold', __( 'Payment under review by Bcash.', 'wcbcash' ) );

                        break;
                    case '1':

                        // Order details.
                        if ( ! empty( $posted['id_transacao'] ) ) {
                            update_post_meta(
                                $order_id,
                                __( 'Bcash Transaction ID', 'wcbcash' ),
                                $posted['id_transacao']
                            );
                        }
                        if ( ! empty( $posted['cliente_email'] ) ) {
                            update_post_meta(
                                $order_id,
                                __( 'Payer email', 'wcbcash' ),
                                $posted['cliente_email']
                            );
                        }
                        if ( ! empty( $posted['cliente_nome'] ) ) {
                            update_post_meta(
                                $order_id,
                                __( 'Payer name', 'wcbcash' ),
                                $posted['cliente_nome']
                            );
                        }
                        if ( ! empty( $posted['tipo_pagamento'] ) ) {
                            update_post_meta(
                                $order_id,
                                __( 'Payment type', 'wcbcash' ),
                                $posted['tipo_pagamento']
                            );
                        }

                        // Payment completed.
                        $order->add_order_note( __( 'Payment completed.', 'wcbcash' ) );
                        $order->payment_complete();

                        break;
                    case '2':
                        $order->update_status( 'cancelled', __( 'Payment canceled by Bcash.', 'wcbcash' ) );

                        break;

                    default:
                        // No action xD.
                        break;
                }
            }
        }
    }

    /**
     * Adds error message when not configured the email.
     *
     * @return string Error Mensage.
     */
    public function mail_missing_message() {
        echo '<div class="error"><p>' . sprintf( __( '<strong>Bcash Disabled</strong> You should inform your email address. %sClick here to configure!%s', 'wcbcash' ), '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_BCash_Gateway">', '</a>' ) . '</p></div>';
    }

    /**
     * Adds error message when not configured the token.
     *
     * @return string Error Mensage.
     */
    public function token_missing_message() {
        echo '<div class="error"><p>' . sprintf( __( '<strong>Bcash Disabled</strong> You should inform your token. %sClick here to configure!%s', 'wcbcash' ), '<a href="' . get_admin_url() . 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_BCash_Gateway">', '</a>' ) . '</p></div>';
    }

}
