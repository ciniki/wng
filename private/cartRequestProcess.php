<?php
//
// Description
// -----------
// Process the cart page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_cartRequestProcess(&$ciniki, $tnid, &$request) {

    //
    // Check if maintanence mode
    //
    if( isset($ciniki['config']['ciniki.core']['maintenance']) && $ciniki['config']['ciniki.core']['maintenance'] == 'on' ) {
        if( isset($ciniki['config']['ciniki.core']['maintenance.message']) && $ciniki['config']['ciniki.core']['maintenance.message'] != '' ) {
            $msg = $ciniki['config']['ciniki.core']['maintenance.message'];
        } else {
            $msg = "We are currently doing maintenance on the system and will be back soon.";
        }

        return array('stat'=>'503', 'err'=>array('code'=>'maintenance', 'msg'=>$msg));
    }

    //
    // Check if should be forced to SSL
    //
    if( isset($request['site']['settings']['site-ssl-force-cart']) 
        && $request['site']['settings']['site-ssl-force-cart'] == 'yes' 
        ) {
        if( isset($request['site']['settings']['site-ssl-active'])
            && $request['site']['settings']['site-ssl-active'] == 'yes'
            && (!isset($_SERVER['HTTP_CLUSTER_HTTPS']) || $_SERVER['HTTP_CLUSTER_HTTPS'] != 'on')
            && (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')
            && (!isset($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] != '443' ) 
            ) {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $content = '';
    $display_cart = 'yes';
    $display_signup = 'no';
    $display_passwordreset = 'no';
    $cart_err_msg = '';
    $signup_err_msg = '';
    $cart = NULL;
    $cart_edit = 'yes';
    $errors = array();
    $paypal_checkout = 'no';
    $stripe_checkout = 'no';
    $page_title = "Shopping Cart";
    $required_account_fields = array();
    $required_account_fields['first'] = 'First Name';
    $required_account_fields['last'] = 'Last Name';
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.customers', 0x0400) 
        && isset($request['site']['settings']['account-callsign-required']) 
        && $request['site']['settings']['account-callsign-required'] == 'yes'
        ) {
        $required_account_fields['callsign'] = 'Callsign';
    }
    $required_account_fields['email_address'] = 'Email Address';
    $required_account_fields['password'] = 'Password';
    $required_account_fields['address1'] = 'Billing Address';
    $required_account_fields['city'] = 'Billing City';
    $required_account_fields['province'] = 'Billing State/Province'; 
    $required_account_fields['postal'] = 'Billing ZIP/Postal Code'; 
    $required_account_fields['country'] = 'Billing Country';
    if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x40) ) {
        $required_account_fields['shipaddress1'] = 'Shipping Address';
        $required_account_fields['shipcity'] = 'Shipping City';
        $required_account_fields['shipprovince'] = 'Shipping State/Province';
        $required_account_fields['shippostal'] = 'Shipping Zip/Postal Code';
        $required_account_fields['shipcountry'] = 'Shipping Country';
    }

    //
    // Setup restricted countries
    //
    $restricted_countries = array(
        'CU' => array(), // Cuba
        'IR' => array(), // Iran
        'KP' => array(), // North Korea
        'SY' => array(), // Syria
        'SD' => array(), // Sudan
//        '' => array(), // Crimea Region of Ukraine **No 2 letter country code recognized
        'BY' => array(), // Belarus
        'BI' => array(), // Burundi
        'CF' => array(), // Central African Republic
//        '' => array(), // Darfur ** Part of Sudan
        'CD' => array(), // Democratic Republic of the Congo
        'ER' => array(), // Eritrea
        'IQ' => array(), // Iraq
        'LB' => array(), // Lebanon
        'LY' => array(), // Libya
//        'ML' => array(), // Mali ** Canada Only, Asset Freeze
//        'MM' => array(), // Myanmar ** Canada only, arms embargo, asset freeze, arms tech support
        'NI' => array(), // Nicaragua
        'SO' => array(), // Somalia
        'SS' => array(), // South Sudan
        'UA' => array(), // Ukraine
        'RU' => array(), // Russia
//        'VE' => array(), // Venezuela ** Certain individuals only
        'YE' => array(), // Yemen
        'ZW' => array(), // Zimbabwe
        );

    //
    // Required methods
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'cartLoad');

    //
    // Get tenant/user settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $ciniki['request']['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_sapos_settings', 'tnid', $ciniki['request']['tnid'], 'ciniki.sapos', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.web.15', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $sapos_settings = isset($rc['settings']) ? $rc['settings'] : array();
    
    if( isset($sapos_settings['stripe-pk']) && $sapos_settings['stripe-pk'] != '' 
        && isset($sapos_settings['stripe-sk']) && $sapos_settings['stripe-sk'] != '' 
        ) {
        $stripe_checkout = 'yes';
    }

    if( isset($sapos_settings['paypal-ec-clientid']) && $sapos_settings['paypal-ec-clientid'] != '' 
        && isset($sapos_settings['paypal-ec-password']) && $sapos_settings['paypal-ec-password'] != '' 
        && isset($sapos_settings['paypal-ec-signature']) && $sapos_settings['paypal-ec-signature'] != '' 
        ) {
        $paypal_checkout = 'yes';
    }
    
    


    $request['response']['blocks'][] = array(
        'type' => 'content',
        'content' => "<br/></br><center>Cart Page - Not yet implemented</center><br/><br/><br/>",
        );
    
    return array('stat'=>'ok');
}
?>

