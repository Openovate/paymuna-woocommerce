<?php //-->

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'enabled' => array(
        'title' => __( 'Enable/Disable', 'paymuna' ),
        'type' => 'checkbox',
        'label' => __( 'Enable Paymuna Payment Gateway', 'paymuna' ),
        'default' => 'no'
    ),
    'title' => array(
        'title' => __( 'Title', 'paymuna' ),
        'type' => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'paymuna' ),
        'desc_tip' => true,
        'default' => __( 'Paymuna', 'paymuna' )
    ),
    'description' => array(
        'title' => __( 'Description', 'paymuna' ),
        'type' => 'textarea',
        'description' => __( 'This controls the description which the user sees during checkout.', 'paymuna' ),
        'default' => __( 'Descriptions for Paymuna.', 'paymuna' )
    ),
    'instructions' => array(
        'title' => __( 'Instructions', 'paymuna' ),
        'type' => 'textarea',
        'description' => __( 'Instructions that will be added to the thank you page.', 'paymuna' ),
        'default' => __( 'Instructions for Paymuna.', 'paymuna' )
    ),
    'api_details' => array(
        'title'       => __( 'Credentials', 'paymuna' ),
        'type'        => 'title',
        'description' => __( 'Enter your Paymuna App credentials to process checkout and payments via Paymuna.', 'paymuna' ),
    ),
    'checkout_page_url' => array(
        'title' => __( 'Checkout Page URL', 'paymuna' ),
        'type' => 'text',
        'description' => __( 'Checkout Template Reference', 'paymuna' )
    ),
    'client_id' => array(
        'title' => __( 'API Token', 'paymuna' ),
        'type' => 'text',
        'description' => __( 'API Token generated from Paymuna', 'paymuna' )
    ),
    'client_secret' => array(
        'title' => __( 'API Secret', 'paymuna' ),
        'type' => 'text',
        'description' => __( 'API Secret generated from Paymuna', 'paymuna' )
    ),
    'checkout_reference' => array(
        'title' => __( 'Checkout Reference', 'paymuna' ),
        'type' => 'text',
        'description' => __( 'Checkout Reference', 'paymuna' )
    ),
);
