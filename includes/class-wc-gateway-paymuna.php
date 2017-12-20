<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Paymuna_Gateway extends WC_Payment_Gateway {
    /**
     * Paymuna REST API Endpoint
     */
    const PAYMUNA_REST_URL = 'http://paymuna.dev/rest';
    // const PAYMUNA_REST_URL = 'http://paymuna.com/rest';

    /**
     * Initialize things
     */
    public function __construct()
    {
        $this->id                 = 'paymuna';
        $this->has_fields         = true;
        $this->order_button_text  = 'Proceed to Paymuna';
        $this->method_title       = 'Paymuna';
        $this->method_description = 'Paymuna Payment Gateway Options.';

        // Load up the form fields
        $this->init_form_fields();

        // Load up the settings
        $this->init_settings();

        // Define user set variables
        $this->title                = $this->get_option( 'title' );
        $this->description          = $this->get_option( 'description' );
        $this->instructions         = $this->get_option( 'instructions' );
        $this->checkout_page_url    = $this->get_option( 'checkout_page_url' );
        $this->client_id            = $this->get_option( 'client_id' );
        $this->client_secret        = $this->get_option( 'client_secret' );
        $this->checkout_reference   = $this->get_option( 'checkout_reference' );

        // Actions
        add_action( 'woocommerce_api_wc_paymuna_gateway', array( $this, 'paymuna_callback' ) );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou', array( $this, 'process_thankyou' ), 1 );
    }

    /**
     * Handles the callback request from Paymuna's REST API.
     */
    public function paymuna_callback()
    {
        // do we have a checkout reference?
        if ( !isset( $_POST['transaction_reference'] )
            || empty( $_POST['transaction_reference'] )) {
            // return an error message
            exit(json_encode(array(
                'error' => true,
                'message' => 'Invalid request.'
            )));
        }

        // get the checkout reference
        $reference = $_POST['transaction_reference'];

        // get the transaction status
        $status = $_POST['transaction_status'];

        // prepare the argument
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => array_keys( wc_get_order_statuses() ),
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_paymuna_reference',
                    'value' => $reference,
                )
            )
        );

        // run it
        $wp_query = new WP_Query( $args );

        // check if there's a result
        if ( $wp_query->have_posts() ) {
            // loop it!
            while ( $wp_query->have_posts() ) {
                $wp_query->the_post();

                // get the order
                $order = new WC_Order( get_the_ID() );

                // TODO: check if the order is still processing

                // check the transaction status
                switch ( strtolower( $status ) ) {
                    case 'pending':
                        $order->update_status( 'pending payment', 'Pending Payment Order via Paymuna' );
                        break;

                    case 'success':
                        $order->update_status( 'processing', 'Payment already processed via Paymuna' );
                        break;
                }
            }

            exit('ok');
        }

        // order not found
        // return an error message
        exit(json_encode(array(
            'error' => true,
            'message' => 'Invalid request.'
        )));
    }

    /**
     * Initialize the Gateway Settings Form Fields
     */
    public function init_form_fields()
    {
        $this->form_fields = include( 'settings-paymuna.php' );
    }

    /**
     * Process the data to be sent to Paymuna.
     *
     * @param $order_id
     * @return JSON
     */
    public function process_payment( $order_id )
    {
        global $woocommerce;
        $order = new WC_Order( $order_id );

        // validate first if the required fields have values
        if (!$this->checkout_page_url
            || !$this->client_id
            || !$this->client_secret
            || !$this->checkout_reference
        ) {
            // return an error message
            return wc_add_notice( 'Paymuna credentials are not set properly.', 'error' );
        }

        // initialize the product array
        $products = array();

        // get the products, format it and store to an array
        foreach ( $order->get_items() as $key => $item ) {
            $product_data = array();

            $product = new WC_Product( $item['product_id'] );

            // product title
            $product_data['item_name'] = $product->get_title();

            // product image
            $product_data['item_image']['file_path'] = ( $product->get_image_id() == 0 ) ?
                null : wp_get_attachment_image_src( $product->get_image_id() )[0];

            // product description
            $product_data['item_description'] = $product->get_description();

            // product price
            $product_data['item_price'] = $product->get_price();

            // product quantity
            $product_data['item_quantity'] = $item['quantity'];

            // product permalink
            $product_data['item_link'] = $product->get_permalink();

            // push to array
            array_push( $products, $product_data );
        }

        // build out the shipping and billing address
        // billing
        $billing = array(
            'address_fullname'  => $order->billing_first_name . ' ' . $order->billing_last_name,
            'address_email'     => $order->billing_email,
            'address_phone'     => $order->billing_phone,
            'address_country'   => $order->billing_country,
            'address_state'     => $this->get_state( $order->billing_country, $order->billing_state ),
            'address_city'      => $order->billing_city,
            'address_street'    => $order->billing_address_1,
            'address_postal'    => $order->billing_postcode
        );

        // shipping
        $shipping = array(
            'address_fullname'  => $order->shipping_first_name . ' ' . $order->shipping_last_name,
            'address_email'     => (isset($order->shipping_email)) ? $order->shipping_email : $order->billing_email,
            'address_phone'     => (isset($order->shipping_phone)) ? $order->shipping_phone : $order->billing_phone,
            'address_country'   => $order->shipping_country,
            'address_state'     => $this->get_state( $order->shipping_country, $order->shipping_state ),
            'address_city'      => $order->shipping_city,
            'address_street'    => $order->shipping_address_1,
            'address_postal'    => $order->shipping_postcode
        );

        // build out the data
        $parameters = array(
            'transaction_callback'  => WC()->api_request_url( strtolower(get_class($this)) ),
            'transaction_redirect'  => $this->generate_redirect_url( $order ),
            'transaction_addresses' => array(
                'billing'   => $billing,
                'shipping'  => $shipping
            ),
            'transaction_items'     => $products,
            'transaction_variables' => $this->generate_transaction_variables( $order )
        );

        // send request to Paymuna
        $response = $this->send_to_paymuna( $parameters );

        // analyze the response from paymuna
        // parse the response body
        $body = json_decode( $response['body'], true );

        if ( ! is_wp_error( $response ) && $body['error'] === false ) {
            // manually update status
            $order->update_status( 'processing', 'Payment to be processed via Paymuna.' );

            // return the redirect url
            return array(
                'result'   => 'success',
                'redirect' => $body['results']['checkout_url']
            );
        }

        // return an error message
        return wc_add_notice( 'Something went wrong while processing your request. Please try again later.', 'error' );
    }

    /**
     * If Paymuna redirected to the "Thank You" page, we need to get the details
     * of the order.
     */
    public function process_thankyou( $order_id )
    {
        // get the order request
        $order = new WC_Order( $order_id );

        // check if the order is already complete
        if ( $order->status == 'completed' ) {
            return;
        }

        // check if there's a reference GET parameter
        if ( isset( $_GET['reference'] ) && !empty( $_GET['reference'] )) {
            // get the transaction details from CMO
            $response = $this->get_transaction_details( $_GET['reference'] );

            // parse the response body
            $body = json_decode( $response['body'], true );

            // if there's no error in fetching the transaction detail from Paymuna's REST,
            // save the checkout reference of this transaction
            if (! is_wp_error( $response )
                || $body['error'] === false
            ) {
                // save it
                update_post_meta( $order_id, '_paymuna_reference', $_GET['reference'] );

                // update some meta
                // $this->update_order_meta( $order_id, $response['results'] );

                // check the transaction status
                switch ( strtolower( $body['results']['transaction_status'] ) ) {
                    case 'pending':
                        $order->update_status( 'pending payment', 'Pending Payment Order via Paymuna' );
                        break;
                    case 'success':
                        // update the order
                        $order->update_status( 'processing', 'Payment Processed via Paymuna' );
                        break;
                }
            }
        }
    }

    /**
     * Generates the redirect URL based on the WP and WooCommerce Settings.
     *
     * @param $order
     * @return string
     */
    private function generate_redirect_url( $order )
    {
        return get_site_url() . '/checkout/order-received/' . $order->id . '/?key=' . $order->order_key;
    }

    /**
     * Generates transaction variables (discount, shipping, and tax) which will
     * be passed to Paymuna.
     *
     * @param array $order
     * @return array
     */
    private function generate_transaction_variables( $order )
    {
        $variables = [];

        // check for discount
        if ($order->discount_total && $order->discount_total > 0) {
            // set the discount
            $variables[] = [
                'variable_name' => 'Discount',
                'variable_type' => 'discount',
                'variable_value' => $order->discount_total
            ];
        }

        // check for shipping cost
        if ($order->shipping_total && $order->shipping_total > 0) {
            // set the shipping
            $variables[] = [
                'variable_name' => 'Shipping',
                'variable_type' => 'shipping',
                'variable_value' => $order->shipping_total
            ];
        }

        // check for total tax cost
        if ($order->total_tax && $order->total_tax > 0) {
            // set the shipping
            $variables[] = [
                'variable_name' => 'Tax',
                'variable_type' => 'tax',
                'variable_value' => $order->total_tax
            ];
        }

        // return whatever we have
        return $variables;
    }

    /**
     * Performs a request to the Paymaya REST to check the transaction details
     * based on the given reference number.
     *
     * @param $referenceCode
     * @return array|WP_Error
     */
    private function get_transaction_details( $referenceCode )
    {
        // prepare the request url
        $requestUrl = self::PAYMUNA_REST_URL . '/transaction/detail/%s?client_id=%s&client_secret=%s';

        // build it out
        $requestUrl = sprintf(
            $requestUrl,
            $referenceCode,
            $this->client_id,
            $this->client_secret
        );

        // perform the request and return whatever is the response
        return wp_remote_get( $requestUrl );
    }

    /**
     * Gets the real value of the state based on the inputs of the customer.
     *
     * @param $cc
     * @param $state
     * @return mixed
     */
    private function get_state( $cc, $state )
    {
        if ( 'US' === $cc ) {
            return $state;
        }

        $states = WC()->countries->get_states( $cc );

        if ( isset( $states[ $state ] ) ) {
            return $states[ $state ];
        }

        return $state;
    }

    /**
     * Performs a HTTP request to the Paymuna REST to create the transaction.
     *
     * @param $body
     * @return WP_REMOTE_GET
     */
    private function send_to_paymuna( $body )
    {
        // build out the url
        $requestUrl = self::PAYMUNA_REST_URL . '/transaction/create?client_id=%s&client_secret=%s&checkout_reference=%s';
        $requestUrl = sprintf(
            $requestUrl,
            $this->client_id,
            $this->client_secret,
            $this->checkout_reference
        );

        // set the parameter
        $params = array(
            'body'        => $body,
            'timeout'     => 60,
            'httpversion' => '1.1',
            'compress'    => false,
            'decompress'  => false,
            'user-agent'  => 'WooCommerce-Paymuna/' . WC()->version
        );

        // send and return the response
        return wp_remote_post( $requestUrl, $params );
    }
}
