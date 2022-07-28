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

    $settings = isset($request['site']['settings']) ? $request['site']['settings'] : array();

        error_log(print_r($_POST,true));
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
            return array('stat'=>'exit');
        }
    }

    //
    // Store the content created by the page
    // Make sure everything gets generated ok before returning the content
    //
    $blocks = array();
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
    // Check if child add redirect
    //
    if( isset($_GET['regreview']) ) {
        $display_cart = 'regreview';
        $cart_edit = 'no';
    } elseif( isset($_POST['regreview']) && isset($_POST['update']) && $_POST['update'] == 'Update' ) {
        $display_cart = 'regreview';
        $cart_edit = 'no';
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
    ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartLoad');

    //
    // Get tenant/user settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
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
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_sapos_settings', 'tnid', $tnid, 'ciniki.sapos', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.15', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $sapos_settings = isset($rc['settings']) ? $rc['settings'] : array();
    
    if( isset($request['site']['settings']['stripe-pk']) && $request['site']['settings']['stripe-pk'] != '' 
        && isset($request['site']['settings']['stripe-sk']) && $request['site']['settings']['stripe-sk'] != '' 
        ) {
        $stripe_checkout = 'yes';
    }

    if( isset($request['site']['settings']['paypal-ec-clientid']) && $request['site']['settings']['paypal-ec-clientid'] != '' 
        && isset($request['site']['settings']['paypal-ec-password']) && $request['site']['settings']['paypal-ec-password'] != '' 
        && isset($request['site']['settings']['paypal-ec-signature']) && $request['site']['settings']['paypal-ec-signature'] != '' 
        ) {
        $paypal_checkout = 'yes';
    }
    
    //
    // Check if a login occured before loading the cart
    //
    if( isset($_POST['action']) && $_POST['action'] == 'signin' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'auth');
        $rc = ciniki_customers_wng_auth($ciniki, $tnid, $request, $_POST['email'], sha1($_POST['password']));
        if( $rc['stat'] != 'ok' ) {
            $signinerrors = "Unable to authenticate, please try again or click Forgot your password to get a new one.";
            $display_signup = 'yes';
            $display_cart = 'no';
        } else {
            $display_signup = 'no';
            $display_cart = 'yes';
            $cart_edit = 'yes';
//            $display_cart = 'review';
//            $cart_edit = 'no';
            
            //
            // Check for any module information that should be loaded into the session
            //
            foreach($ciniki['tenant']['modules'] as $module => $m) {
                list($pkg, $mod) = explode('.', $module);
                $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountSessionLoad');
                if( $rc['stat'] == 'ok' ) {
                    $fn = $rc['function_call'];
                    $rc = $fn($ciniki, $tnid, $request);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.98', 'msg'=>'Unable to load account information', 'err'=>$rc['err']));
                    }
                }
            }

            if( isset($_POST['next']) && $_POST['next'] == 'edit' ) {
                header("Location: " . $request['ssl_domain_base_url'] . "/cart");
                return array('stat'=>'exit');
            }
        }
    }

    //
    // Check if new password from password reset was submitted
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'passwordreset' ) {
        if( !isset($_POST['newpassword']) || strlen($_POST['newpassword']) < 8 ) {
            $passwordreseterrors = "Your new password must be at least 8 characters long.";
            $display_passwordreset = 'yes';
            $display_cart = 'no';
        } elseif( !isset($_POST['email']) || $_POST['email'] == '' ) {
            $passworderrors = "You need to enter an email address to reset your password.";
            $display_passwordreset = 'yes';
            $display_cart = 'no';
        } elseif( !isset($_POST['temppassword']) || $_POST['temppassword'] == '' ) {
            $signuperrors = "Sorry, but the link was invalid.  Please try again.";
            $display_signup = 'yes';
            $display_cart = 'no';
        } else {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'changeTempPassword');
            $rc = ciniki_customers_wng_changeTempPassword($ciniki, $tnid, $request, $_POST['email'], $_POST['temppassword'], $_POST['newpassword']);
            if( $rc['stat'] != 'ok' ) {
                $signinerrors = "Sorry, we were unable to set your new password.  Please try again or call us for help.";
                $display_signup = 'yes';
                $display_cart = 'no';
            } else {
                $signinmsg = "Your password has been reset, please login to continue.";
                $display_signup = 'yes';
                $display_cart = 'no';
            }
        }
    }

    //
    // Check if create account form was submitted
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'createaccount' && (!isset($_POST['continue']) || $_POST['continue'] != 'Back') ) {
        $signinerrors = '';
        $display_signup = 'yes';
        $display_cart = 'no';

        //
        // Check for required fields
        //
        $args = $_POST;
        if( isset($args['province_code_' . $args['country']]) && $args['province_code_' . $args['country']] != '' ) {
            $args['province'] = $args['province_code_' . $args['country']];
        }
        if( isset($args['shipcountry']) 
            && isset($args['shipprovince_code_' . $args['shipcountry']]) 
            && $args['shipprovince_code_' . $args['shipcountry']] != '' 
            ) {
            $args['shipprovince'] = $args['shipprovince_code_' . $args['shipcountry']];
        }
        $missing_fields = array();
        foreach($required_account_fields as $fid => $fname) {
            if( !isset($args[$fid]) || trim($args[$fid]) == '' ) {
                $missing_fields[] = $fname;
            }
        }
        if( count($missing_fields) > 1 ) {
            $signinerrors = "You must enter " . implode(', ', $missing_fields) . " to create your account.";
        } elseif( count($missing_fields) > 0 ) {
            $signinerrors = "You must enter " . implode(', ', $missing_fields) . " to create your account.";
        }
        if( $signinerrors == '' ) {
            //
            // Check if email address already exists
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'hooks', 'customerLookup');
            $rc = ciniki_customers_hooks_customerLookup($ciniki, $tnid, array('email'=>$_POST['email_address']));
            if( $rc['stat'] != 'noexist' ) {
                $signinerrors = "There is already an account for that email address, please use the Forgot Password link to recover your password.";
            }
        }

        //
        // Check for restricted countries
        //
        if( isset($restricted_countries[$args['country']]) ) {
            $signinerrors = "We're sorry, we are unable to deliver goods or services to your country. Please contact us and we'll determine if we can.";
        }
        if( isset($args['shipcountry']) && isset($restricted_countries[$args['shipcountry']]) ) {
            $signinerrors = "We're sorry, we are unable to deliver goods or services to your country. Please contact us and we'll determine if we can.";
        }

        if( $signinerrors == '' ) {
            //
            // Setup the customer defaults
            //
            $args['phone_label_1'] = 'Home';
            $args['phone_number_1'] = trim($args['phone']);
            unset($args['phone']);

            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'customerAdd');
            $rc = ciniki_customers_wng_customerAdd($ciniki, $tnid, $request, $args);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $customer_id = $rc['id'];

            //
            // Once the account is created, authenticate
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'auth');
            $rc = ciniki_customers_wng_auth($ciniki, $tnid, $request, $args['email_address'], sha1($args['password']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }

            //
            // FIXME: Load module session info
            //

            //
            // Attach to cart
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartCustomerUpdate');
            $rc = ciniki_sapos_wng_cartCustomerUpdate($ciniki, $tnid, $request);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $display_signup = 'no';
            if( isset($_POST['next']) && $_POST['next'] == 'edit' ) {
                header("Location: " . $request['ssl_domain_base_url'] . "/cart");
                return array('stat'=>'exit');
            } else {
                $display_cart = 'yes';
                $cart_edit = 'yes';
//                $display_cart = 'review';
//                $cart_edit = 'no';
            }
        }
    }

    //
    // Check if a cart already exists
    //
    $rc = ciniki_sapos_wng_cartLoad($ciniki, $tnid, $request);
    if( $rc['stat'] == 'noexist' ) {
        $cart = NULL;
        $request['session']['cart']['sapos_id'] = 0;
        $request['session']['cart']['num_items'] = 0;
    } 
    elseif( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.25', 'msg'=>'Error processing shopping cart, please try again.'));
    } 
    else {
        $cart = $rc['cart'];
        $request['session']['cart'] = $rc['cart'];
    }

    //
    // Check if no customer, and create dummy information
    //
    if( !isset($request['session']['customer']) ) {
        $request['session']['customer'] = array(
            'price_flags' => 0x01,
            'pricepoint_id' => 0,
            'first' => '',
            'last' => '',
            'display_name' => '',
            'email' => '',
            );
    }

    // $ct = print_r($rc, true);

    //
    // FIXME: Add check for cookies
    //

    //
    // Check if a item is being added to the cart
    //
    if( isset($_POST['action']) && $_POST['action'] == 'add' && isset($_POST['object']) && isset($_POST['object_id']) ) {
        $item_exists = 'no';
        if( $cart == NULL ) {
            // Create a shopping cart
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartCreate');
            $rc = ciniki_sapos_wng_cartCreate($ciniki, $tnid, $request, array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $request['session']['cart'] = array();
            $request['session']['cart']['sapos_id'] = $rc['sapos_id'];
            $request['session']['cart']['num_items'] = 0;
        } else {
            //
            // Check if item already exists in the cart
            //
            if( isset($cart['items']) ) {
                foreach($cart['items'] as $item) {
                    $item = $item['item'];
                    if( $item['object'] == $_POST['object']
                        && $item['object_id'] == $_POST['object_id'] 
                        && isset($_POST['quantity']) && is_numeric($_POST['quantity'])
                        && ((!isset($_POST['price_id']) && $item['price_id'] == 0) || $item['price_id'] == $_POST['price_id'])
                        && ($item['flags']&0x08) == 0
                        ) {
                        $item_exists = 'yes';
                        //
                        // Update the quantity
                        //
//                      if( $item['quantity'] != $_POST['quantity'] ) {
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemUpdate');
                        $rc = ciniki_sapos_wng_cartItemUpdate($ciniki, $tnid, $request, array(
                            'item_id'=>$item['id'], 
                            'quantity'=>$item['quantity'] + $_POST['quantity'],
                            ));
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
//                      }
                        break;
                    }
                }
            }
        }

        //
        // Add the item to the cart, if they don't already exist
        //
        if( $item_exists == 'no' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemAdd');
            $rc = ciniki_sapos_wng_cartItemAdd($ciniki, $tnid, $request, array(
                'object' => $_POST['object'],
                'object_id' => $_POST['object_id'],
                'price_id' => (isset($_POST['price_id'])?$_POST['price_id']:0),
                'user_amount' => (isset($_POST['user_amount'])?$_POST['user_amount']:0),
                'quantity' => $_POST['quantity'],
                ));
            if( $rc['stat'] != 'ok' ) {
                if( $rc['stat'] == 'soldout' ) {
                    $cart_err_msg .= "<p class='error'>" . $rc['err']['msg'] . "</p>";
                    $display_cart = 'yes';
                } else {
                    return $rc;
                }
            } elseif( isset($rc['error_message']) && $rc['error_message'] != '' ) {
                $cart_err_msg .= "<p class='error'>" . $rc['error_message'] . "</p>";
                $display_cart = 'yes';
            }
        }

        //
        // Redirect to avoid form duplicate submission
        //
        if( $display_cart != 'yes' || $cart_err_msg == '' ) {
            header("Location: " . $request['ssl_domain_base_url'] . "/cart");
            return array('stat'=>'exit');
        }

        //
        // Incase redirect fails, Load the updated cart
        //
        $rc = ciniki_sapos_wng_cartLoad($ciniki, $tnid, $request);
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        $cart = $rc['cart'];
        $request['session']['cart'] = $rc['cart'];
    }

    //
    // Check if multiple prices are being added at once. Used by mapped ticket selector
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'addprices' && isset($_POST['price_ids']) ) {
        if( $cart == NULL ) {
            // Create a shopping cart
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartCreate');
            $rc = ciniki_sapos_wng_cartCreate($ciniki, $tnid, $request, array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $request['session']['cart'] = array();
            $request['session']['cart']['sapos_id'] = $rc['sapos_id'];
            $request['session']['cart']['num_items'] = 0;
        } 

        $price_ids = explode(',', $_POST['price_ids']);
        foreach($price_ids as $price_id) {
            if( $price_id == '' ) {
                continue;
            }
            //
            // Check if item already exists in the cart
            //
            $item_exists = 'no';
            if( isset($cart['items']) ) {
                foreach($cart['items'] as $item) {
                    $item = $item['item'];
                    if( $item['object'] == $_POST['object']
                        && $item['object_id'] == $_POST['object_id'] 
                        && $item['price_id'] == $price_id
                        && ($item['flags']&0x08) == 0
                        ) {
                        $item_exists = 'yes';
                        //
                        // Update the quantity
                        //
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemUpdate');
                        $rc = ciniki_sapos_wng_cartItemUpdate($ciniki, $tnid, $request, array(
                            'item_id' => $item['id'], 
                            'quantity' => $item['quantity'] + $_POST['quantity'],
                            ));
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        break;
                    }
                }
            }
            //
            // Add the item to the cart, if they don't already exist
            //
            if( $item_exists == 'no' ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemAdd');
                $rc = ciniki_sapos_wng_cartItemAdd($ciniki, $tnid, $request, array(
                    'object' => $_POST['object'],
                    'object_id' => $_POST['object_id'],
                    'price_id' => $price_id,
                    'quantity' => $_POST['quantity'],
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }

        //
        // Redirect to avoid form duplicate submission
        //
        header("Location: " . $request['ssl_domain_base_url'] . "/cart");
        return array('stat'=>'exit');

        //
        // Incase redirect fails, Load the updated cart
        //
        $rc = ciniki_sapos_wng_cartLoad($ciniki, $tnid, $request);
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        $cart = $rc['cart'];
        $request['session']['cart'] = $rc['cart'];
    }

    //
    // Check if multiple object ids are passed. Used by the membership products renewal (Renew All Button).
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'addobjectids' && isset($_POST['object_ids']) ) {
        if( $cart == NULL ) {
            // Create a shopping cart
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartCreate');
            $rc = ciniki_sapos_wng_cartCreate($ciniki, $tnid, $request, array());
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $request['session']['cart'] = array();
            $request['session']['cart']['sapos_id'] = $rc['sapos_id'];
            $request['session']['cart']['num_items'] = 0;
        } 

        $object_ids = explode(',', $_POST['object_ids']);
        foreach($object_ids as $object_id) {
            if( $object_id == '' ) {
                continue;
            }
            //
            // Check if item already exists in the cart
            //
            $item_exists = 'no';
            if( isset($cart['items']) ) {
                foreach($cart['items'] as $item) {
                    $item = $item['item'];
                    if( $item['object'] == $_POST['object']
                        && $item['object_id'] == $object_id 
                        && ($item['flags']&0x08) == 0
                        ) {
                        $item_exists = 'yes';
                        //
                        // Update the quantity
                        //
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemUpdate');
                        $rc = ciniki_sapos_wng_cartItemUpdate($ciniki, $tnid, $request, array(
                            'item_id' => $item['id'], 
                            'quantity' => $item['quantity'] + $_POST['quantity'],
                            ));
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        break;
                    }
                }
            }
            //
            // Add the item to the cart, if they don't already exist
            //
            if( $item_exists == 'no' ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wnb', 'cartItemAdd');
                $rc = ciniki_sapos_wng_cartItemAdd($ciniki, $tnid, $request, array(
                    'object' => $_POST['object'],
                    'object_id' => $object_id,
                    'quantity' => $_POST['quantity'],
                    ));
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }

        //
        // Redirect to avoid form duplicate submission
        //
        header("Location: " . $request['ssl_domain_base_url'] . "/cart");
        return array('stat'=>'exit');

        //
        // Incase redirect fails, Load the updated cart
        //
        $rc = ciniki_sapos_wng_cartLoad($ciniki, $tnid, $request);
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        $cart = $rc['cart'];
        $request['session']['cart'] = $rc['cart'];
    }

    //
    // Check if donation being added to cart
    //
    elseif( (isset($_POST['action']) && $_POST['action'] == 'update')
        && isset($_POST['donate']) && $_POST['donate'] != '' 
        ) {
        if( $_POST['donate'] == 'Add' && isset($_POST['amount']) && $_POST['amount'] != '' ) {
            $amount = preg_replace("/[^0-9\.]/", '', $_POST['amount']);
        } else {
            $amount = preg_replace("/[^0-9\.]/", '', $_POST['donate']);
        }
        if( $amount != '' && $amount > 0 ) {
            //
            // Add the donation to the cart
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemAdd');
            $rc = ciniki_sapos_wng_cartItemAdd($ciniki, $tnid, $request, array(
                'object' => 'ciniki.sapos.cartdonation',
                'object_id' => $cart['id'],
                'price_id' => 0,
                'flags' => 0x8000,
                'user_amount' => $amount,
                'quantity' => 1,
                ));
            if( $rc['stat'] != 'ok' ) {
                if( $rc['stat'] == 'soldout' ) {
                    $cart_err_msg .= "<p class='error'>" . $rc['err']['msg'] . "</p>";
                    $display_cart = 'yes';
                } else {
                    return $rc;
                }
            } elseif( isset($rc['error_message']) && $rc['error_message'] != '' ) {
                $cart_err_msg .= "<p class='error'>" . $rc['error_message'] . "</p>";
                $display_cart = 'yes';
            } else {
                header("Location: " . $request['ssl_domain_base_url'] . "/cart");
                return array('stat'=>'exit');
            }
        }
    }
    //
    // Check if cart quantities were updated
    //
    elseif( (isset($_POST['update']) && isset($_POST['action']) && $_POST['action'] == 'update')
        || (isset($_POST['action']) && $_POST['action'] == 'regreview') 
        || (isset($_POST['action']) && $_POST['action'] == 'delete') 
        || (isset($_POST['submitorder']) && $_POST['submitorder'] != '') 
        || (isset($_POST['checkout']) && $_POST['checkout'] != '') 
        ) {
        $update_args = array();
        if( isset($_POST['po_number']) ) {
            $update_args['po_number'] = $_POST['po_number'];
        }
        if( isset($_POST['customer_notes']) ) {
            $update_args['customer_notes'] = $_POST['customer_notes'];
        }
        if( count($update_args) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartUpdate');
            $rc = ciniki_sapos_wng_cartUpdate($ciniki, $tnid, $request, $update_args);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
        }
        if( isset($cart['items']) ) {
            foreach($cart['items'] as $item) {
                $item = $item['item'];
                if( isset($_POST['quantity_' . $item['id']]) && $_POST['quantity_' . $item['id']] != $item['quantity'] ) {
                    $new_quantity = intval($_POST['quantity_' . $item['id']]);
                    if( $new_quantity <= 0 ) {
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemDelete');
                        $rc = ciniki_sapos_wng_cartItemDelete($ciniki, $tnid, $request, array('item_id'=>$item['id']));
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                    } else {
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemUpdate');
                        $rc = ciniki_sapos_wng_cartItemUpdate($ciniki, $tnid, $request, array(
                            'item_id' => $item['id'], 
                            'quantity' => $new_quantity,
                            ));
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                    }
                }
                if( isset($_POST['student_' . $item['id']]) 
                    && $_POST['student_' . $item['id']] != $item['student_id'] 
                    && (!isset($new_quantity) || $new_quantity > 0)
                    ) {
                    $new_student_id = intval($_POST['student_' . $item['id']]);
                    if( $new_student_id != $item['student_id'] ) {
                        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemUpdate');
                        $rc = ciniki_sapos_wng_cartItemUpdate($ciniki, $tnid, $request, array(
                            'item_id' => $item['id'], 
                            'student_id' => $new_student_id,
                            ));
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                    }
                }
            }
        }

        //
        // Redirect to avoid form duplicate submission
        //
        if( !isset($_POST['submitorder']) && !isset($_POST['checkout']) ) {
            header("Location: " . $request['ssl_domain_base_url'] . "/cart");
            return array('stat'=>'exit');
        }

        //
        // Incase redirect fails, or submiting an order, Load the updated cart
        //
        $rc = ciniki_sapos_wng_cartLoad($ciniki, $tnid, $request);
        if( $rc['stat'] != 'ok' ) { 
            return $rc;
        }
        $cart = $rc['cart'];
        $request['session']['cart'] = $rc['cart'];
    } 

    //
    // Check if dealer is submitting an order
    //
/*    if( isset($_POST['submitorder']) && $_POST['submitorder'] != ''
        && isset($request['session']['customer']['dealer_status']) 
        && $request['session']['customer']['dealer_status'] > 0 
        && $request['session']['customer']['dealer_status'] < 60 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'web', 'checkOrder');
        $rc = ciniki_sapos_web_checkOrder($ciniki, $settings, $tnid, $cart);
        if( $rc['stat'] == 'warn' ) {
            $cart_err_msg .= "<p class='error'>" . $rc['err']['msg'] . "</p>";
            $display_cart = 'yes';
        } elseif( $rc['stat'] != 'ok' ) {
            return $rc;
        } else {
            $display_cart = 'confirm';
            $cart_edit = 'no';
        }
    }
    //
    // Check if dealer has confirmed the order
    //
    elseif( isset($_POST['confirmorder']) && $_POST['confirmorder'] != ''
        && isset($request['session']['customer']['dealer_status']) 
        && $request['session']['customer']['dealer_status'] > 0 
        && $request['session']['customer']['dealer_status'] < 60 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'submitOrder');
        $rc = ciniki_sapos_wng_submitOrder($ciniki, $tnid, $request, $cart);
        if( $rc['stat'] == 'warn' ) {
            $cart_err_msg .= "<p class='error'>" . $rc['err']['msg'] . "</p>";
            $display_cart = 'yes';
        } elseif( $rc['stat'] != 'ok' ) {
            return $rc;
        } else {
            $content .= "<p>Your order has been submitted.</p>";
            //
            // Email the receipt to the dealer
            //
            if( isset($settings['cart-dealersubmit-email-template']) 
                && $settings['cart-dealersubmit-email-template'] != '' 
                && $settings['cart-dealersubmit-email-template'] != 'none' 
                && isset($cart['customer']['emails'][0]['email']['address'])
                ) {
                //
                // Load tenant details
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'tenantDetails');
                $rc = ciniki_tenants_tenantDetails($ciniki, $tnid);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $tenant_details = array();
                if( isset($rc['details']) && is_array($rc['details']) ) {   
                    $tenant_details = $rc['details'];
                }

                //
                // Load the invoice settings
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
                $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_sapos_settings', 'tnid', $tnid,
                    'ciniki.sapos', 'settings', 'invoice');
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $sapos_settings = array();
                if( isset($rc['settings']) ) {
                    $sapos_settings = $rc['settings'];
                }
                
                //
                // Create the pdf
                //
                $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'templates', $settings['cart-dealersubmit-email-template']);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $fn = $rc['function_call'];
                $rc = $fn($ciniki, $tnid, $cart['id'], $tenant_details, $sapos_settings, 'email');
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }

                //
                // Email the pdf to the customer
                //
                $filename = $rc['filename'];
                $invoice = $rc['invoice'];
                $pdf = $rc['pdf'];


                $subject = "Order #" . $invoice['invoice_number'];
                $textmsg = "Thank you for your order, please find the order summary attached.";
                if( isset($settings['cart-dealersubmit-email-textmsg']) 
                    && $settings['cart-dealersubmit-email-textmsg'] != '' 
                    ) {
                    $textmsg = $settings['cart-dealersubmit-email-textmsg'];
                }   
                $ciniki['emailqueue'][] = array('to'=>$invoice['customer']['emails'][0]['email']['address'],
                    'to_name'=>(isset($invoice['customer']['display_name'])?$invoice['customer']['display_name']:''),
                    'tnid'=>$tnid,
                    'subject'=>$subject,
                    'textmsg'=>$textmsg,
                    'attachments'=>array(array('string'=>$pdf->Output('invoice', 'S'), 'filename'=>$filename)),
                    );
            }

            $display_cart = 'no';
            $cart = NULL;
            unset($_SESSION['cart']);
            unset($request['session']['cart']);
        }
    }

    //
    // Check if action is forgot
    //
    else */
    if( isset($_POST['action']) && $_POST['action'] == 'forgot' ) {
        $url = $request['ssl_domain_base_url'] . '/cart/passwordreset';
        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'passwordRequestReset');
        $rc = ciniki_customers_wng_passwordRequestReset($ciniki, $tnid, $request, $_POST['email'], $url);
        if( $rc['stat'] != 'ok' ) {
            $signinerrors = "You must enter a valid email address to get a new password.";
        } else {
            $signinmsg = "A link has been sent to your email to get a new password.";
        }
        $request['session']['passwordreset_referer'] = $request['ssl_domain_base_url'] . "/cart";
        $display_signup = 'yes';
        $display_cart = 'no';
    }

    //
    // Check if action is forgot
    //
    elseif( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'passwordreset' ) {
        $display_signup = 'no';
        $display_cart = 'no';
        $display_passwordreset = 'yes';
    }

    //
    // Check if checkout
    //
    elseif( isset($_POST['checkout']) && $_POST['checkout'] != '' && $cart != NULL ) {
        error_log('checkout');
        //
        // Check the items in the cart before checkout to make sure still available
        //
        $unavailable = '';
        $student_forms = array();
        foreach($cart['items'] as $iid => $item) {
            if( isset($item['item']['form_id']) && $item['item']['form_id'] > 0 
                && isset($item['item']['student_id']) && $item['item']['student_id'] > 0
                && !isset($student_forms["{$item['item']['student_id']}-{$item['item']['form_id']}"]) 
                ) {
                $student_forms["{$item['item']['student_id']}-{$item['item']['form_id']}"] = $item['item'];
            }
            list($pkg, $mod, $f) = explode('.', $item['item']['object']);
            $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'sapos', 'cartItemCheck');
            if( $rc['stat'] == 'ok' ) {
                $fn = $rc['function_call'];
                $rc = $fn($ciniki, $tnid, $request['session']['customer'], $item['item']);
                if( $rc['stat'] == 'unavailable' ) {
                    //
                    // Remove item from cart
                    //
                    $unavailable = ($unavailable != '' ? ', ' : '') . $item['item']['description'];
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemDelete');
                    $rc = ciniki_sapos_wng_cartItemDelete($ciniki, $tnid, $request, array('item_id'=>$item['item']['id']));
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    unset($cart['items'][$iid]);
                }
                elseif( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.119', 'msg'=>'Unable to confirm availability', 'err'=>$rc['err']));
                }
            }
        }
        if( $unavailable != '' ) {
            $carterrors = "We're sorry, the following items are no longer available and have been removed from your cart: " . $unavailable;
            $cart_edit = 'yes';
            $display_cart = 'yes';
        }
        elseif( isset($cart['customer_id']) && $cart['customer_id'] > 0 ) {
            $display_cart = 'review';
            $cart_edit = 'no';
            $page_title = 'Checkout - Review';
        } else {
            $display_signup = 'yes';
            $display_cart = 'no';
        }
        if( count($student_forms) > 0 && ciniki_core_checkModuleActive($ciniki, 'ciniki.forms') ) {
            if( !isset($_POST['regreviewed']) || $_POST['regreviewed'] != 'yes' ) {
                $display_cart = 'regreview';
                $cart_edit = 'no';
                $page_title = 'Checkout - Review Registrations';
            } else {
                $display_cart = 'review';
                foreach($student_forms as $item) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartItemFormCheck');
                    $rc = ciniki_sapos_wng_cartItemFormCheck($ciniki, $tnid, $request, $item);
                    if( isset($rc['blocks']) ) {
                        return $rc;
                    } elseif( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.177', 'msg'=>'Unable to check required form', 'err'=>$rc['err']));
                    }
                }
            }
        }
    }

    //
    // Check if create account was clicked
    //
    elseif( isset($_POST['createaccount']) && $_POST['createaccount'] != '' && $cart != NULL ) {
        $display_signup = 'createaccount';
        $display_cart = 'no';
    }

    //
    // Check if checkout via paypal
    //
    elseif( isset($_POST['paypalexpresscheckout']) && $_POST['paypalexpresscheckout'] != '' && $cart != NULL 
        && isset($cart['customer_id']) && $cart['customer_id'] > 0 
        ) {
        //
        // Load paypal settings
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'paypalExpressCheckoutSet');
        $rc = ciniki_sapos_wng_paypalExpressCheckoutSet($ciniki, $tnid, $request, array(
            'amount' => $cart['total_amount'],
            'type' => 'Sale',
            'returnurl' => $request['ssl_domain_base_url'] . '/cart/pesuccess',
            'cancelurl' => $request['ssl_domain_base_url'] . '/cart/pecancel',
            'currency' => $intl_currency,
            'shipping' => ($cart['shipping_status'] > 0 ? 'yes' : 'no'),
            ));
        if( $rc['stat'] != 'ok' ) {
            $carterrors = $rc['err']['msg'];
            $display_cart = 'yes';
        } else {
            $display_cart = 'review';
        }
        $page_title = 'Checkout - Review';
    }

    //
    // When the cart total is $0.00, then no charge checkout
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'update' 
        && isset($_POST['nocharge_checkout']) 
        && isset($cart['items']) && count($cart['items']) > 0 && $cart['total_amount'] == 0 
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartPaymentReceived');
        $rc = ciniki_sapos_wng_cartPaymentReceived($ciniki, $tnid, $request, $cart);
        if( $rc['stat'] != 'ok' ) {
            $carterrors = "We have received your payment, thank you. There was a problem processing your order, so we have notified the approriate people to look into it. Please do not submit payment again. ";
            error_log('ERR-CART: ' . print_r($rc['err'], true));
            $ciniki['emailqueue'][] = array('to'=>$ciniki['config']['ciniki.core']['alerts.notify'],
                'subject'=>'Web Cart ERR 500',
                'textmsg'=>$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n"
                    . $carterrors . "\n"
                    . "Customer: \n" 
                    . print_r($request['session']['customer'], true) 
                    . "\n"
                    . print_r($rc, true)
                    . "\n",
                );
        } else {
            //
            // Checkout success
            //
            $display_success = 'yes';
            $display_cart = 'checkout_success';
            $cart = NULL;
            $request['session']['cart']['sapos_id'] = 0;
            $request['session']['cart']['num_items'] = 0;
        }
    }

    //
    // Check if checkout via stripe
    //
    elseif( $stripe_checkout == 'yes' 
        && isset($_POST['stripe-token']) && $_POST['stripe-token'] != '' 
        && isset($_POST['stripe-email']) && $_POST['stripe-email'] != '' 
        && $cart != NULL 
        && isset($cart['customer_id']) && $cart['customer_id'] > 0 
        ) {

        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'stripeCustomerCharge');
        $rc = ciniki_sapos_wng_stripeCustomerCharge($ciniki, $tnid, $request, array(
            'invoice_id' => $cart['id'],
            'invoice_number' => $cart['invoice_number'],
            'stripe-token' => $_POST['stripe-token'],
            'stripe-email' => $_POST['stripe-email'],
            'charge-amount' => $cart['total_amount'],
            ));
        if( $rc['stat'] != 'ok' ) {
            $carterrors = "Oops, we seem to have a problem with your payment. Please try again or contact us for help.";
        }
         
        if( !isset($carterrors) || $carterrors == '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartPaymentReceived');
            $rc = ciniki_sapos_wng_cartPaymentReceived($ciniki, $tnid, $request, $cart);
            if( $rc['stat'] != 'ok' ) {
                $carterrors = "We have received your payment, thank you. There was a problem processing your order, so we have notified the approriate people to look into it.";
                error_log('ERR-CART: ' . print_r($rc['err'], true));
                $ciniki['emailqueue'][] = array('to'=>$ciniki['config']['ciniki.core']['alerts.notify'],
                    'subject'=>'Web Cart ERR 500',
                    'textmsg'=>$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n"
                        . $carterrors . "\n"
                        . "Customer: \n" 
                        . print_r($request['session']['customer'], true) 
                        . "\n"
                        . print_r($rc, true)
                        . "\n",
                    );
            } else {
                //
                // Checkout success
                //
                $display_success = 'yes';
                $display_cart = 'checkout_success';
                $cart = NULL;
                $request['session']['cart']['sapos_id'] = 0;
                $request['session']['cart']['num_items'] = 0;
            }
        }
    }

    //
    // Check if checkout was paypal express success
    //
    elseif( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'pesuccess'
        && isset($_GET['token']) && $_GET['token'] != '' 
        ) {

        //
        // Get the paypal payment information
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'paypalExpressCheckoutGet');
        $rc = ciniki_sapos_wng_paypalExpressCheckoutGet($ciniki, $tnid, $request, array(
            'token'=>$_GET['token'],
            ));
        if( $rc['stat'] != 'ok' ) {
            $carterrors = "Oops, we seem to have a problem with your payment. Please try again or contact us for help.";
            error_log('ERR-CART: Paypal DoExpressCheckout: [' . $rc['err']['code'] . '] ' . $rc['err']['msg']);
            return $rc;
        }

        $display_cart = 'paypalexpresscheckoutconfirm';
        $cart_edit = 'no';
        $page_title = 'Checkout - Confirm Payment';
    }

    //
    // Check if checkout was paypal express success
    //
    elseif( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'pecancel') {
        $carterrors = "You cancelled the transaction at Paypal, your purchase was not completed.";
    }

    elseif( isset($_POST['paypalexpresscheckoutdo']) && $_POST['paypalexpresscheckoutdo'] != '' && $cart != NULL ) {
        //
        // Get the paypal payment information
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'paypalExpressCheckoutDo');
        $rc = ciniki_sapos_wng_paypalExpressCheckoutDo($ciniki, $tnid, $request, array(
            'invoice_id' => $cart['id'],
            'type' => 'Sale',
            'amount' => $cart['total_amount'],
            'currency' => $intl_currency,
            ));
        if( $rc['stat'] != 'ok' ) {
            $carterrors = "Oops, we seem to have a problem with your payment. Please try again or contact us for help.";
            error_log('ERR-CART: Paypal DoExpressCheckout: [' . $rc['err']['code'] . '] ' . $rc['err']['msg']);
            $display_cart = 'paypalexpresscheckoutconfirm';
            $cart_edit = 'no';
        }

        if( !isset($carterrors) || $carterrors == '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'cartPaymentReceived');
            $rc = ciniki_sapos_wng_cartPaymentReceived($ciniki, $tnid, $request, $cart);
            if( $rc['stat'] != 'ok' ) {
                $carterrors = "We have received your payment, thank you. "
                    . "There was a problem processing your order, so have notified the approriate people to look into it.";
                error_log('ERR-CART: ' . print_r($rc['err'], true));
                $ciniki['emailqueue'][] = array('to'=>$ciniki['config']['ciniki.core']['alerts.notify'],
                    'subject'=>'Web Cart ERR 500',
                    'textmsg'=>$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n"
                        . "carterrors:\n"
                        . $carterrors . "\n"
                        . "Customer: \n" 
                        . print_r($request['session']['customer'], true) 
                        . "\n"
                        . print_r($rc, true)
                        . "\n",
                    );
            } else {
                //
                // Checkout success
                //
                $display_success = 'yes';
                $display_cart = 'checkout_success';
                $cart = NULL;
                $request['session']['cart']['sapos_id'] = 0;
                $request['session']['cart']['num_items'] = 0;
            }
        }
    }
    
    //
    // Display the forgot password link
    //
    if( $display_passwordreset == 'yes' ) {
        $request['breadcrumbs'][] = array(
            'name' => 'Reset Password', 
            'page-class' => 'page-cart',
            'url' => $request['base_url'] . '/cart',
            );

        if( isset($passwordreseterrors) && $passwordreseterrors != '' ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error',
                'content' => $passwordreseterrors,
                );
        }

        $block = array(
            'type' => 'accountpwdreset',
            'title' => 'Reset Password',
            'email' => isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : ''),
            'temppassword' => isset($_GET['pwd']) ? $_GET['pwd'] : (isset($_POST['temppassword']) ? $_POST['temppassword'] : ''),
            'message' => 'Please enter a new password.  It must be at least 8 characters long.',
            'action' => $request['ssl_domain_base_url'] . '/cart',
            );
        $blocks[] = $block;
    }

    //
    // Display the signup/login form
    //
    if( $display_signup == 'yes' || $display_signup == 'forgot' || $display_signup == 'createaccount' ) {
        error_log('signup/login');
        $post_email = '';
        if( isset($_POST['email']) ) {
            $post_email = $_POST['email'];
        }
        if( isset($signinmsg) && $signinmsg != '' ) {
            $blocks[] = array(  
                'type' => 'msg',
                'level' => 'success',
                'content' => $signinmsg,
                );
        }
        if( isset($signinerrors) && $signinerrors != '' ) {
            $blocks[] = array(  
                'type' => 'msg',
                'level' => 'error',
                'content' => $signinerrors,
                );
        }

        $content = '';
        $content .= "<div class='block-cartsignup'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";
        $content .= "<aside>";
        // Javascript to switch forms   
        $js = " function swapLoginForm(l) {"
                . "if(l=='forgotpassword'){"
                    . "C.gE('signin-form').style.display = 'none';"
                    . "C.gE('forgotpassword-form').style.display = 'block';"
                    . "C.gE('forgotemail').value = document.getElementById('email').value;"
                . "} else {"
                    . "C.gE('signin-form').style.display = 'block';"
                    . "C.gE('forgotpassword-form').style.display = 'none';"
                . "}"
            . "return true;"
            . "}";
        $content .= "<div id='signin-form' style='display:" . ($display_signup=='yes' || $display_signup == 'createaccount' ?'block':'none') . ";'>";
        $content .= "<h2>Existing Account</h2>";
        $content .= "<p>Joined us or purchased previously? Please sign in to your account.</p>";
//        $content .= "<p>Bought something here before? Please sign in to your account:</p>";
        $content .= "<form action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST'>";
        if( $display_signup == 'createaccount' ) {
            $content .= "<input type='hidden' name='next' value='edit'>";
        }
        if( $signup_err_msg != '' ) {
            $content .= "<p class='formerror'>$signup_err_msg</p>";
        }
        $content .="<input type='hidden' name='action' value='signin'>\n"
            . "<div class='input first-field'><label for='email'>Email</label>"
                . "<input id='email' type='email' class='text' maxlength='250' name='email' value='$post_email' />"
            . "</div>" 
            . "<div class='input last-field'><label for='password'>Password</label>"
                . "<input id='password' type='password' class='text' maxlength='100' name='password' value='' />"
            . "</div>"
            . "<div class='submit'><input type='submit' class='button' value='Sign In' /></div>"
            . "</form>"
            . "<br/>";
        if( !isset($settings['page-account-password-change']) 
            || $settings['page-account-password-change'] == 'yes' ) {
            $content .= "<div id='forgot-link'><p>"
                . "<a class='color' href='javascript:void();' onclick='swapLoginForm(\"forgotpassword\"); return false;'>"
                    . "Forgot your password?"
                . "</a>"
                . "</p>"
                . "</div>";
        }
        $content .= "</div>";

        // Forgot password form
        $content .= "<div id='forgotpassword-form' style='display:" . ($display_signup=='forgot'?'block':'none') . ";'>";
        $content .= "<h2>Forgot Password</h2>";
        $content .= "<p>Please enter your email address and you will receive a link to create a new password.</p>";
        $content .= "<form action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST'>";
        if( $signup_err_msg != '' ) {
            $content .= "<p class='formerror'>$signup_err_msg</p>\n";
        }
        $content .= "<input type='hidden' name='action' value='forgot'>\n"
            . "<input type='hidden' name='redirect' value='" . $request['ssl_domain_base_url'] . "/cart' />"
            . "<div class='input first-field last-field'><label for='forgotemail'>Email </label>"
                . "<input id='forgotemail' type='email' class='text' maxlength='250' name='email' value='$post_email' />"
            . "</div>\n" 
            . "<div class='submit'><input type='submit' class='button' value='Get New Password' /></div>\n"
            . "</form>"
            . "<br/>"
            . "<div class='forgot-link'><p>"
                . "<a class='color' href='javascript:void();' onclick='swapLoginForm(\"signin\"); return false;'>"
                . "Sign In</a></p></div>\n"
            . "</div>\n";

        $content .= "</aside>";

        //
        // Signup for a new account form
        //
        $content .= "<div class='signupform'>";
        $content .= "<h2>Create a new account</h2>";
        $content .= "<form action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST'>";
        $content .= "<input type='hidden' name='action' value='createaccount'>";
        if( $display_signup == 'createaccount' ) {
            $content .= "<input type='hidden' name='next' value='edit'>";
        }
        $fields = array();
        //
        // Check if callsign enabled
        //
        $fields['first'] = array('name'=>'First Name', 'type'=>'text', 'class'=>'text', 
            'value'=>(isset($_POST['first'])?$_POST['first']:''), 
            'autocomplete'=>'given-name',
            );
        $fields['last'] = array('name'=>'Last Name', 'type'=>'text', 'class'=>'text', 
            'value'=>(isset($_POST['last'])?$_POST['last']:''), 
            'autocomplete'=>'family-name',
            );
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.customers', 0x0400) ) {
            $fields['callsign'] = array('name'=>'Callsign', 'type'=>'text', 'class'=>'text', 
                'value'=>(isset($_POST['callsign'])?$_POST['callsign']:''),
                );
        }
        $fields['email_address'] = array('name'=>'Email Address', 'type'=>'email', 'class'=>'text', 
            'value'=>(isset($_POST['email_address'])?$_POST['email_address']:''), 
            'autocomplete'=>'email',
            );
        $fields['password'] = array('name'=>'Password', 'type'=>'password', 'class'=>'text', 
            'value'=>(isset($_POST['password'])?$_POST['password']:''),
            );
        $fields['phone'] = array('name'=>'Phone Number', 'type'=>'text', 'class'=>'text', 
            'value'=>(isset($_POST['phone'])?$_POST['phone']:''), 
            'autocomplete'=>'tel',
            );
        $cname = ' first-field';
        foreach($fields as $fid => $field) {
            if( $fid == 'phone' ) {
                $cname = ' last-field';
            }
            $content .= "<div class='input{$cname}'><label for='$fid'>" 
                . $field['name'] . (array_key_exists($fid, $required_account_fields)?' *':'') . "</label>"
                . "<input type='" . $field['type'] . "' class='" . $field['class'] . "' name='$fid' value='" . $field['value'] . "'"
                . (isset($field['autocomplete']) && $field['autocomplete'] != '' ? " autocomplete='" . $field['autocomplete'] . "'" : '')
                . ">";
            $cname = '';
            if( isset($errors[$fid]) && $errors[$fid] != '' ) {
                $content .= "<p class='formerror'>" . $errors[$fid] . "</p>";
            }
            $content .= "</div>";
        }


        //
        // Setup the address fields
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'countryCodes');
        $rc = ciniki_core_countryCodes($ciniki);
        $country_codes = $rc['countries'];
        $province_codes = $rc['provinces'];
        $address = array(
            'address1'=>(isset($_POST['address1'])?$_POST['address1']:''),
            'address2'=>(isset($_POST['address2'])?$_POST['address2']:''),
            'city'=>(isset($_POST['city'])?$_POST['city']:''),
            'province'=>(isset($args['province'])?$args['province']:''),
            'postal'=>(isset($_POST['postal'])?$_POST['postal']:''),
            'country'=>(isset($_POST['country'])?$_POST['country']:'Canada'),
            );
        $form = '';
        $form .= "<h2>Billing Address</h2>";
        $form .= "<div class='input country first-field'>"
            . "<label for='country'>Country" . (array_key_exists('country', $required_account_fields)?' *':'') . "</label>"
            . "<select id='country_code' type='select' class='select' name='country' onchange='updateProvince()'>"
            . "<option value=''></option>";
        $selected_country = '';
        foreach($country_codes as $country_code => $country_name) {
            $form .= "<option value='" . $country_code . "' " 
                . (($country_code == $address['country'] || $country_name == $address['country'])?' selected':'')
                . ">" . $country_name . "</option>";
            if( $country_code == $address['country'] || $country_name == $address['country'] ) {
                $selected_country = $country_code;
            }
        }
        $form .= "</select></div>";
        $form .= "<div class='input address1'>"
            . "<label for='address1'>Address" . (array_key_exists('address1', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='address1' value='" . $address['address1'] . "' autocomplete='billing address-line1'>"
            . "</div>";
        $form .= "<div class='input address2'>"
            . "<label for='address2'>" . (array_key_exists('address2', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='address2' value='" . $address['address2'] . "' autocomplete='billing address-line2'>"
            . "</div>";
        $form .= "<div class='input city'>"
            . "<label for='city'>City" . (array_key_exists('city', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='city' value='" . $address['city'] . "' autocomplete='billing address-level2'>"
            . "</div>";
        $form .= "<div class='input province'>"
            . "<label for='province'>State/Province" . (array_key_exists('province', $required_account_fields)?' *':'') . "</label>"
            . "<input id='province_text' type='text' class='text' name='province' "
                . ((isset($province_codes[$selected_country]) && $province_codes[$selected_country])?" style='display:none;'":"")
                . "value='" . $address['province'] . "' autocomplete='billing address-level1'>";
        $formjs = '';
        foreach($province_codes as $country_code => $provinces) {
            $form .= "<select id='province_code_{$country_code}' type='select' class='select' "
                . (($country_code != $selected_country)?" style='display:none;'":"")
                . " name='province_code_{$country_code}' autocomplete='billing address-level1'>"
                . "<option value=''></option>";
            $formjs .= "document.getElementById('province_code_" . $country_code . "').style.display='none';";
            foreach($provinces as $province_code => $province_name) {
                $form .= "<option value='" . $province_code . "'" 
                    . (($province_code == (isset($_POST["province_code_{$country_code}"])?$_POST["province_code_{$country_code}"]:'') || $province_name == (isset($_POST["province_code_{$country_code}"])?$_POST["province_code_{$country_code}"]:''))?' selected':'')
                    . ">" . $province_name . "</option>";
            }
            $form .= "</select>";
        }
        $form .= "</div>";
        $form .= "<div class='input postal last-field'>"
            . "<label for='postal'>ZIP/Postal Code" . (array_key_exists('postal', $required_account_fields)?' *':'') . "</label>"
            . "<input type='text' class='text' name='postal' value='" . $address['postal'] . "' autocomplete='address postal-code'>"
            . "</div>";
        $form .= "<script type='text/javascript'>"
            . "function updateProvince() {"
                . "var cc = C.gE('country_code');"
                . "var pr = C.gE('province_text');"
                . "var pc = C.gE('province_code_'+cc.value);"
                . $formjs
                . "if( pc != null ) {"
                    . "pc.style.display='';"
                    . "pr.style.display='none';"
                . "}else{"
                    . "pr.style.display='';"
                . "}"
            . "}"
            . "</script>";
        $content .= $form;

        //
        // Check if shipping enabled and then display shipping address
        //
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x40) ) {
            $address = array(
                'shipaddress1'=>(isset($_POST['shipaddress1'])?$_POST['shipaddress1']:''),
                'shipaddress2'=>(isset($_POST['shipaddress2'])?$_POST['shipaddress2']:''),
                'shipcity'=>(isset($_POST['shipcity'])?$_POST['shipcity']:''),
                'shipprovince'=>(isset($args['shipprovince'])?$args['shipprovince']:''),
                'shippostal'=>(isset($_POST['shippostal'])?$_POST['shippostal']:''),
                'shipcountry'=>(isset($_POST['shipcountry'])?$_POST['shipcountry']:'Canada'),
                );
            $form = '';
            $form .= "<h2>Shipping Address</h2>";
            $form .= "<div class='input shipcountry'>"
                . "<label for='shipcountry'>Country" . (array_key_exists('shipcountry', $required_account_fields)?' *':'') . "</label>"
                . "<select id='shipcountry_code' type='select' class='select' name='shipcountry' onchange='updateShipProvince()' autocomplete='shipping country'>"
                . "<option value=''></option>";
            $selected_country = '';
            foreach($country_codes as $country_code => $country_name) {
                $form .= "<option value='" . $country_code . "' " 
                    . (($country_code == $address['shipcountry'] || $country_name == $address['shipcountry'])?' selected':'')
                    . ">" . $country_name . "</option>";
                if( $country_code == $address['shipcountry'] || $country_name == $address['shipcountry'] ) {
                    $selected_country = $country_code;
                }
            }
            $form .= "</select></div>";
            $form .= "<div class='input shipaddress1'>"
                . "<label for='shipaddress1'>Address" . (array_key_exists('shipaddress1', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='shipaddress1' value='" . $address['shipaddress1'] . "' autocomplete='shipping address-line1'>"
                . "</div>";
            $form .= "<div class='input shipaddress2'>"
                . "<label for='shipaddress2'>" . (array_key_exists('shipaddress2', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='shipaddress2' value='" . $address['shipaddress2'] . "' autocomplete='shipping address-line2'>"
                . "</div>";
            $form .= "<div class='input shipcity'>"
                . "<label for='shipcity'>City" . (array_key_exists('shipcity', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='shipcity' value='" . $address['shipcity'] . "' autocomplete='shipping address-level2'>"
                . "</div>";
            $form .= "<div class='input shipprovince'>"
                . "<label for='shipprovince'>State/Province" . (array_key_exists('shipprovince', $required_account_fields)?' *':'') . "</label>"
                . "<input id='shipprovince_text' type='text' class='text' name='shipprovince' "
                    . ((isset($province_codes[$selected_country]) && $province_codes[$selected_country])?" style='display:none;'":"")
                    . "value='" . $address['shipprovince'] . "' autocomplete='shipping address-level1'>";
            $formjs = '';
            foreach($province_codes as $country_code => $provinces) {
                $form .= "<select id='shipprovince_code_{$country_code}' type='select' class='select' "
                    . (($country_code != $selected_country)?" style='display:none;'":"")
                    . " name='shipprovince_code_{$country_code}'  autocomplete='shipping address-level1'>"
                    . "<option value=''></option>";
                $formjs .= "document.getElementById('shipprovince_code_" . $country_code . "').style.display='none';";
                foreach($provinces as $province_code => $province_name) {
                    $form .= "<option value='" . $province_code . "'" 
                        . (($province_code == (isset($_POST["shipprovince_code_{$country_code}"])?$_POST["shipprovince_code_{$country_code}"]:'') || $province_name == (isset($_POST["shipprovince_code_{$country_code}"])?$_POST["shipprovince_code_{$country_code}"]:''))?' selected':'')
                        . ">" . $province_name . "</option>";
                }
                $form .= "</select>";
            }
            $form .= "</div>";
            $form .= "<div class='input shippostal'>"
                . "<label for='shippostal'>ZIP/Postal Code" . (array_key_exists('shippostal', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='shippostal' value='" . $address['shippostal'] . "' autocomplete='shipping postal-code'>"
                . "</div>";
            $form .= "<script type='text/javascript'>"
                . "function updateShipProvince() {"
                    . "var cc = document.getElementById('shipcountry_code');"
                    . "var pr = document.getElementById('shipprovince_text');"
                    . "var pc = document.getElementById('shipprovince_code_'+cc.value);"
                    . $formjs
                    . "if( pc != null ) {"
                        . "pc.style.display='';"
                        . "pr.style.display='none';"
                    . "}else{"
                        . "pr.style.display='';"
                    . "}"
                . "}"
                . "</script>";
            $content .= $form;
        }

        $content .= "<div class='submit'><input type='submit' name='continue' class='button' value='Back' />";
        $content .= "<input type='submit' name='continue' class='button' value='Next' /></div>\n";
        $content .= "</form>";

        $content .= "</div>\n";
        $content .= "</div>\n";
        $content .= "</div>\n";

        $blocks[] = array(
            'type' => 'html',
            'html' => $content,
            'js' => $js,
            );
    }

    //
    // Display the contents of the shopping cart
    //
    if( $display_cart == 'yes' 
        || $display_cart == 'confirm' 
        || $display_cart == 'review' 
        || $display_cart == 'regreview' 
        || $display_cart == 'paypalexpresscheckoutconfirm' 
        ) {
        $request['breadcrumbs'][] = array(
            'name' => 'Cart', 
            'page-class' => 'page-cart',
            'url' => $request['base_url'] . '/cart',
            );

        if( $display_cart == 'review' && (!isset($carterrors) || $carterrors == '') ) {
            $block = array(
                'type' => 'msg',
                'level' => 'success',
                'content' => 'Please review your order.',
                );

            if( isset($settings['cart-checkout-message']) && $settings['cart-checkout-message'] != '' ) {
                $block['content'] = $settings['cart-checkout-message'];
            }
            $blocks[] = $block;
        }
        elseif( $display_cart == 'paypalexpresscheckoutconfirm' && (!isset($carterrors) || $carterrors == '') ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'success',
                'content' => 'To complete your order, please click on Pay Now below.',
                );
        }

        if( isset($carterrors) && $carterrors != '' ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error',
                'content' => $carterrors,
                );
        }



        $content = '';
        $js = '';
        $content .= "<div class='block-cart'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";
        $content .= "<h1>$page_title</h1>";
        
/*        if( isset($settings['cart-product-search']) 
            && $settings['cart-product-search'] == 'yes' 
            ) {
            $content .= "<div class='search-input'>";
        
            if( $cart_edit == 'yes' ) {
                $content .= "<form id='search-form' action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST'>"
                    . "<input type='hidden' name='action' value='add'/>"
                    . "<input id='cart-search-form-object' type='hidden' name='object' value='' />"
                    . "<input id='cart-search-form-object_id' type='hidden' name='object_id' value='' />"
                    . "<input id='cart-search-form-price_id' type='hidden' name='price_id' value='' />"
                    . "<input id='cart-search-form-final_price' type='hidden' name='final_price' value='' />"
                    . "<input id='cart-search-form-quantity' type='hidden' name='quantity' value='1' />"
                    . "<label for='search_str'></label>"
                    . "<input id='cart-search-str' class='input' type='text' autofocus placeholder='Search' "
                        . "name='search_str' "
                        . "value='" . (isset($_POST['search_str'])?$_POST['search_str']:'') . "' "
                        . "onkeyup='return update_cart_search();' "
                        . "onsearch='return update_cart_search();' "
                        . "onsubmit='return false;' autocomplete='off' />"
                    . "</form>";
            }
            $content .= "</div>";
        } */


        $content .= "<div class='cart'>";

        //
        // Check if we should display inventory
        //
        $inv = 'no';
        $codes = 'no';
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x0400) ) {
            $codes = 'yes';
        }
// FIXME: No inventory display possible in cart
/*        if( isset($settings['cart-inventory-customers-display']) && $settings['cart-inventory-customers-display'] == 'yes' ) {
            $inv = 'yes';
        }
        elseif( isset($settings['cart-inventory-dealers-display']) 
            && $settings['cart-inventory-dealers-display'] == 'yes' 
            && isset($request['session']['customer']['dealer_status'])
            && $request['session']['customer']['dealer_status'] > 0 
            && $request['session']['customer']['dealer_status'] < 60 
            ) {
            $inv = 'yes';
        } */

        //
        // Check if we should display the search box
        //
/*        if( isset($settings['cart-product-search']) 
            && $settings['cart-product-search'] == 'yes' 
            && $cart_edit == 'yes' 
            ) {
            $limit = 11;
            $request['ciniki_api'] = 'yes';
            $request['inline_javascript'] .= "<script type='text/javascript'>\n"
                . "var prev_cart_search_str = '';\n"
                . "function update_cart_search() {\n"
                    . "var str = document.getElementById('cart-search-str').value;\n"
                    . "if( prev_cart_search_str != str ) {\n"
                        . "var t = document.getElementById('cart-search-result');\n"
                        . "if( str == '' ) { t.style.display = 'none'; }\n"
                        . "else if( str != prev_cart_search_str ) {\n"
                            . "C.getBg('cart/search/'+encodeURIComponent(str),{'limit':$limit},update_search_results);\n"
                            . "t.style.display = 'block';\n"
                        . "}\n"
                        . "prev_cart_search_str = str;\n"
                    . "}\n"
                    . "return false;"
                . "};"
                . "function cart_add_search_result(o,i,p,f,q) {"
                    . "C.gE('cart-search-form-object').value=o;"
                    . "C.gE('cart-search-form-object_id').value=i;"
                    . "C.gE('cart-search-form-price_id').value=(p!=null&&p!=''?p:0);"
                    . "C.gE('cart-search-form-final_price').value=f;"
                    . "C.gE('cart-search-form-quantity').value=q;"
                    . "C.gE('cart-search-form').submit();"
                . "};"
                . "function update_search_results(rsp) {"
                    . "var d = document.getElementById('cart-search-results');"
                    . "C.clr(d);"
                    . "if(rsp.products!=null&&rsp.products.length>0) {"
                        . "var ct=0;"
                        . "for(i in rsp.products) {"
                            . "var p=rsp.products[i].product;"
                            . "ct++;"
                            . "var tr=C.aE('tr',null,(i%2==0?'item-even':'item-odd'));"
                            . "if(ct>=$limit){"
                                . "var c=C.aE('td',null,'aligncenter','. . .');"
                                . "c.colSpan=" . ($inv=='yes'?5:4) . ";"
                                . "tr.appendChild(c);"
                            . "}else{"
                                . "tr.appendChild(C.aE('td',null,null," . ($codes=='yes' ? "(p.code!='' ? p.code + ' - ' : '')" : '') . " + p.name));"
                                . "if(p.cart!=null&&p.cart=='yes'"
                                    // Check if inventory available or backorder available
                                    . "&&(p.inventory_available>0||(p.inventory_flags&0x02)>0)){"
                                    . "tr.appendChild(C.aE('td',null,'alignright','<span class=\"cart-quantity\"><input "
                                    . "class=\"quantity\" id=\"quantity_'+i+'\" name=\"quantity_'+i+'\" "
                                    . "value=\"1\" size=\"2\"/></span>'));"
                                . "}else{"
                                    . "tr.appendChild(C.aE('td'));"
                                . "}"
                                // Check if inventory is being tracked,
                                // and if the item is backordered, decide if sold out or backorder should display
                                . ($inv=='yes'?"tr.appendChild(C.aE('td',null,'alignright',("
                                . "(p.inventory_flags&0x01)==1?("
                                    . "(p.inventory_available>0?p.inventory_available:"
                                        . "((p.inventory_flags&0x02)==2?'Backordered':'Sold out'))"
                                    . "):''))"
                                . ");":"")
                                . "tr.appendChild(C.aE('td',null,'alignright',p.price));"
                                . "if(p.cart!=null&&p.cart=='yes'"
                                    // Check if inventory available or backorder available
                                    . "&&(p.inventory_available>0||(p.inventory_flags&0x02)>0)){"
                                    . "var e = C.aE('td',null,'aligncenter');"
                                    . "var b = C.aE('input',null,'cart-submit');"
                                    . "b.type='submit';"
                                    . "b.value='Add';"
                                    . "b.setAttribute('onclick', 'cart_add_search_result(\"ciniki.products.product\","
                                        . "\"'+p.id+'\","
                                        . "\"'+(p.price_id!=null?p.price_id:0)+'\","
                                        . "\"'+p.unit_amount+'\","
                                        . "C.gE(\"quantity_'+i+'\").value);return false;');"
                                    . "e.appendChild(b);"
                                    . "tr.appendChild(e);"
                                . "}else{"
                                    . "tr.appendChild(C.aE('td',null,null,''));"
                                . "}"
                            . "}"
                            . "d.appendChild(tr);"
                        . "}"
                    . "}else{"
                        . "d.innerHTML='<tr class=\"item-even\"><td class=\"aligncenter\" colspan=\"" . ($inv=='yes'?5:4) . "\">No products found</td></tr>';"
                    . "}"
                . "};"
                . "</script>\n";
            $content .= "<div class='cart-search-items' id='cart-search-result' style='display:none;'>"
//              . "<form action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST'>"
                . "<table class='items'>\n"
                . "<thead><tr>"
                . "<th class='aligncenter'>Item</th>"
                . "<th class='alignright'>Quantity</th>"
                . ($inv=='yes'?"<th class='alignright'>Inventory</th>":"")
                . "<th class='alignright'>Price</th>"
                . "<th>Actions</th>"
                . "</tr></thead>"
                . "<tbody id='cart-search-results'>"
                . "</tbody>\n"
                . "</table>"
//              . "</form>"
                . "</div>\n";
        }

        //
        // Check if there is a review message
        //
        else */
/*        if( $inv == 'yes' ) {
            $item_objects = array();
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'private', 'getReservedQuantities');
            if( isset($cart['items']) ) {
                foreach($cart['items'] as $item_id => $item) {
                    // Create the object
                    if( !isset($item_objects[$item['item']['object']]) ) {
                        $item_objects[$item['item']['object']] = array();
                    }
                    // Add the item
                    $item_objects[$item['item']['object']][$item['item']['object_id']] = $item_id;
                    $cart['items'][$item_id]['item']['quantity_inventory'] = 0;
                    $cart['items'][$item_id]['item']['quantity_reserved'] = 0;
                }
            }
            foreach($item_objects as $o => $oids) {
                //
                // Get current inventory
                //
                list($pkg, $mod, $obj) = explode('.', $o);
                $object_ids = array_keys($oids);
                $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'sapos', 'cartItemsDetails');
                if( $rc['stat'] == 'ok') {
                    $fn = $pkg . '_' . $mod . '_sapos_cartItemsDetails';
                    $rc = $fn($ciniki, $tnid, array(
                        'object'=>$o, 'object_ids'=>$object_ids));
                    if( isset($rc['details']) ) {
                        foreach($rc['details'] as $detail) {
                            $item_id = $item_objects[$o][$detail['object_id']];
                            $cart['items'][$item_id]['item']['quantity_inventory'] = $detail['quantity_inventory'];
                            $cart['items'][$item_id]['item']['permalink'] = $detail['permalink'];
                        }
                    }
                }

                //
                // Get the number reserved
                //
                $rc = ciniki_sapos_getReservedQuantities($ciniki, $tnid, 
                    $o, $object_ids, $cart['id']);
                if( isset($rc['quantities']) ) {
                    foreach($rc['quantities'] as $quantity) {
                        $item_id = $item_objects[$o][$quantity['object_id']];
                        $cart['items'][$item_id]['item']['quantity_reserved'] = $quantity['quantity_reserved'];
                    }
                }
            }
        } */

        //
        // Check if displaying child select
        //
        $registration_customers = array();
        $display_registration_customer = 'no';
        if( isset($settings['cart-registration-child-select']) && $settings['cart-registration-child-select'] == 'yes' 
            && isset($request['session']['customer']['id']) && $request['session']['customer']['id'] > 0
            && isset($request['session']['customer']['children-allowed']) && $request['session']['customer']['children-allowed'] == 'yes'
            ) {
            $strsql = "SELECT id, display_name "
                . "FROM ciniki_customers "
                . "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $request['session']['customer']['id']) . "' "
                . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "";
            $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.customers', 'child');
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['rows']) ) {
                $registration_customers = $rc['rows'];
                array_unshift($registration_customers, $request['session']['customer']);
                $display_registration_customer = 'yes';
            }
        }

        //
        // Display cart items
        //
        if( $cart != NULL && isset($cart['items']) && count($cart['items']) > 0 ) {
/*            $request['inline_javascript'] .= "<script type='text/javascript'>\n"
                . " function check_cart() {\n";
            if( isset($settings['cart-po-number']) && $settings['cart-po-number'] != 'no' ) {
                $request['inline_javascript'] .= "        if(document.getElementById('po_number').value == '' ) {\n"
                    . "         alert('You need to enter a PO number before you can submit the order.');\n"
                    . "         return false;\n"
                    . "     }\n";
            } 
            $request['inline_javascript'] .= ""
                . "return true;\n"
                . "}\n"
                . "</script>\n"
                . ""; 
*/

            if( $display_cart == 'regreview' 
                && isset($settings['cart-regreview-message'])
                && $settings['cart-regreview-message'] != '' 
                ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $settings['cart-regreview-message']);
                if( $rc['stat'] == 'ok' ) {
                    $content .= "<div class='message regreview-message'>" . $rc['content'] . "</div>";
                }
            }
            
            $content .= "<form id='cart' action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST' >";
            if( $display_cart == 'regreview' ) {
                $content .= "<input type='hidden' id='regreview' name='regreview' value='regreview'/>";
            } else {
                $content .= "<input type='hidden' id='action' name='action' value='update'/>";
            }
            if( $cart_err_msg != '' ) {
                $content .= $cart_err_msg;
            }
            $content .= "<div class='items-wrap'>";
            $content .= "<table class='items'>";
            $content .= "<thead><tr>"
                . "<th class='aligncenter'>Item</th>"
                . ($display_cart != 'regreview' ? "<th class='alignright'>Quantity</th>" : '')
                . ($inv=='yes'?"<th class='alignright'>Inventory</th>":"")
                . ($display_cart != 'regreview' ? "<th class='alignright'>Price</th>" : '')
                . ($display_cart != 'regreview' ? "<th class='alignright'>Total</th>" : '')
                . ($cart_edit=='yes'?"<th class='aligncenter'></th>":"")
                . "</tr></thead>";
            $content .= "<tbody>";
            $count=0;
            foreach($cart['items'] as $item_id => $item) {
                if( $display_cart == 'regreview' && ($item['item']['flags']&0x20) != 0x20 ) {
                    continue;
                }
                $item = $item['item'];
                $content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>"
                    . "<td>";
                if( isset($item['url']) && $item['url'] != '' ) {
                    $content .= "<a href='" . $item['url'] . "'>" . ($codes == 'yes' && $item['code'] != '' ? $item['code'] . ' - ' : '') . $item['description'] . "</a>";
                } else {
                    $content .= ($codes == 'yes' && $item['code'] != '' ? $item['code'] . ' - ' : '') . $item['description'];
                }
                //
                // Check for registration customer, but not for musicfestivals or events
                //
                if( $display_registration_customer == 'yes' 
                    && $item['object'] == 'ciniki.courses.offering'
                    ) {
                    if( $cart_edit == 'yes' || $display_cart == 'regreview' ) {
                        $content .= " for <select name='student_" . $item['id'] . "' name='student_" . $item['id'] . "'"
                            . ">";
                        foreach($registration_customers as $child) {
                            $content .= $child['display_name'];
                            $content .= "<option value='" . $child['id'] . "' ";
                            if( $child['id'] == $item['student_id'] ) {
                                $content .= " selected";
                            }
                            $content .= ">" . $child['display_name'] . "</option>";
                        }
                        $content .= "</select>";
                    } else {
                        foreach($registration_customers as $child) {
                            if( $child['id'] == $item['student_id'] ) {
                                $content .= " (" . $child['display_name'] . ")";
                            }
                        }
                    }
                }
                if( $display_registration_customer == 'yes' && $item['object'] == 'ciniki.musicfestivals.registration' ) {
                    $content .= " for " . $item['notes'];
                } elseif( $display_registration_customer == 'yes' && $item['object'] == 'ciniki.writingfestivals.registration' ) {
                    $content .= " for " . $item['notes'];
                } elseif( $item['notes'] != '' ) {
                    $content .= "<span class='notes'>" . preg_replace("/\n/", '<br/>', $item['notes']) . "</span>";
                }

                $content .= "</td>";
                if( $display_cart != 'regreview' ) {
                    $content .= "<td class='alignright'>";
                    if( $cart_edit == 'yes' ) {
                        if( ($item['flags']&0x8000) == 0x8000 ) {
                            $content .= "<input id='quantity_" . $item['id'] . "' name='quantity_" . $item['id'] . "' type='hidden' value='" . $item['quantity'] . "'/>";
                        } elseif( ($item['flags']&0x08) == 0 ) {
                            $content .= "<span class='quantity'>"
                                . "<input class='quantity' id='quantity_" . $item['id'] . "' name='quantity_" . $item['id'] . "' type='text' value='" 
                                    . $item['quantity'] . "' size='2'/>"
                                . "</span>";
                         } else {
                            $content .= $item['quantity'];
                            $content .= "<input id='quantity_" . $item['id'] . "' name='quantity_" . $item['id'] . "' type='hidden' value='" . $item['quantity'] . "'/>";
                         }
                    } elseif( $display_cart != 'regreview' ) {
                        $content .= $item['quantity'];
                    }
                    $content .= "</td>";
                }
                if( $inv == 'yes' ) {
                    $content .= "<td class='alignright'>";
                    $quantity_available = $item['quantity_inventory'] - $item['quantity_reserved'];
                    if( $quantity_available > 0 ) {
                        $content .= $quantity_available;
                    } else {
                        $content .= (($item['flags']&0x04)>0?'Backordered':'Sold out');
                    }
                    $content .= "</td>";
                }
                $discount_text = '';
                if( $item['unit_discount_amount'] > 0 ) {
                    $discount_text .= '-' . numfmt_format_currency($intl_currency_fmt, 
                        $item['unit_discount_amount'], $intl_currency)
                        . (($item['quantity']>1)?'x'.$item['quantity']:'');
                }
                if( $item['unit_discount_percentage'] > 0 ) {
                    $discount_text .= ($discount_text!=''?', ':'') . '-' . $item['unit_discount_percentage'] . '%';
                }
                if( $display_cart != 'regreview' ) {
                    $content .= "<td class='alignright'>" 
                            . numfmt_format_currency($intl_currency_fmt, $item['unit_amount'], $intl_currency)
                            . ($discount_text!=''?('<br/>' . $discount_text . ' ('
                                . numfmt_format_currency($intl_currency_fmt, $item['discount_amount'], $intl_currency)) . ')':'')
                            . "</td>";
                    $content .= "<td class='alignright'>" 
                            . numfmt_format_currency($intl_currency_fmt, $item['total_amount'], $intl_currency)
                            . "</td>";
                }
                if( $cart_edit == 'yes' ) {
                    $content .= "<td class='aligncenter'>"
                        . "<span class='submit'>"
                        . "<span class='icon remove clickable' onclick='C.gE(\"quantity_" . $item['id'] . "\").value=0;"    
                            . "C.gE(\"action\").value=\"delete\";C.gE(\"cart\").submit();'>"
                        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M268 416h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12zM432 80h-82.41l-34-56.7A48 48 0 0 0 274.41 0H173.59a48 48 0 0 0-41.16 23.3L98.41 80H16A16 16 0 0 0 0 96v16a16 16 0 0 0 16 16h16v336a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128h16a16 16 0 0 0 16-16V96a16 16 0 0 0-16-16zM171.84 50.91A6 6 0 0 1 177 48h94a6 6 0 0 1 5.15 2.91L293.61 80H154.39zM368 464H80V128h288zm-212-48h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12z"/></svg>'
                        . "</span>"
                        . "</span>"
                        . "</td>";
                }
                $content .= "</tr>";
                $count++;
            }
            $content .= "</tbody>";

            // cart totals
            $num_cols = 3;
            if( $inv == 'yes' ) { $num_cols++; }
            if( $display_cart != 'regreview' ) {
                $content .= "<tfoot>";

                $separator = '';
                $duenow = '';
                if( isset($cart['preorder_subtotal_amount']) && $cart['preorder_subtotal_amount'] > 0 ) {
                    $separator = 'separator ';
                    $duenow = ' (Due Now)';
                    $content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
                    $content .= "<td colspan='$num_cols' class='alignright'>Pre-Order Subtotal:</td>"
                        . "<td class='alignright'>"
                        . numfmt_format_currency($intl_currency_fmt, $cart['preorder_subtotal_amount'], $intl_currency)
                        . "</td>"
                        . ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
                    $count++;
                    $content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
                    $content .= "<td colspan='$num_cols' class='alignright'>Shipping:</td>"
                        . "<td class='alignright'>";
                    if( !ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x04)
                        && !ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x10000000)
                        && ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x20000000)
                        ) {
                        $content .= 'Curbside Pickup';
                    } elseif( $cart['preorder_subtotal_amount'] > 0 && $cart['customer_id'] == 0 ) {
                        $content .= "TBD";
                    } else {
                        $content .= numfmt_format_currency($intl_currency_fmt, $cart['preorder_shipping_amount'], $intl_currency);
                    }
                    $content .= "</td>"
                        . ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
                    $count++;

                    if( isset($cart['preorder_taxes']) ) {
                        foreach($cart['preorder_taxes'] as $tax) {
                            $tax = $tax['tax'];
                            $content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
                            $content .= "<td colspan='$num_cols' class='alignright'>" . $tax['description'] . ":</td>"
                                . "<td class='alignright'>"
                                . numfmt_format_currency($intl_currency_fmt, $tax['amount'], $intl_currency)
                                . "</td>"
                                . ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
                            $count++;
                        }
                    }

                    $content .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
                    $content .= "<td colspan='$num_cols' class='alignright'><b>Pre-Order Total (Due On Shipment):</b></td>"
                        . "<td class='alignright'>"
                        . numfmt_format_currency($intl_currency_fmt, $cart['preorder_total_amount'], $intl_currency)
                        . "</td>"
                        . ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
                    $count++;
                } 

                if( $cart['shipping_status'] > 0 || (isset($cart['taxes']) && count($cart['taxes']) > 0) ) {
                    $content .= "<tr class='{$separator}" . (($count%2)==0?'item-even':'item-odd') . "'>";
                    $content .= "<td colspan='$num_cols' class='alignright'>Subtotal:</td>"
                        . "<td class='alignright'>"
                        . numfmt_format_currency($intl_currency_fmt, $cart['subtotal_amount'], $intl_currency)
                        . "</td>"
                        . ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
                    $count++;
                    $separator = '';
                }
                if( $cart['shipping_status'] > 0 || (isset($cart['shipping_amount']) && $cart['shipping_amount'] > 0) ) {
                    $content .= "<tr class='{$separator}" . (($count%2)==0?'item-even':'item-odd') . "'>";
                    $content .= "<td colspan='$num_cols' class='alignright'>Shipping:</td>";
                    if( !ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x04)
                        && !ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x10000000)
                        && ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x20000000)
                        ) {
                        $content .= "<td class=''"
                            . ($cart_edit == 'yes' ? " colspan='2'" : '')
                            . ">";
                        $content .= 'Curbside Pickup';
                        $content .= "</td>";
                    } elseif( $cart['subtotal_amount'] > 0 && $cart['customer_id'] == 0 ) {
                        $content .= "<td class='alignright'>";
                        $content .= "TBD";
                        $content .= "</td>" . ($cart_edit=='yes'?'<td></td>':'');
                    } else {
                        $content .= "<td class='alignright'>";
                        $content .= numfmt_format_currency($intl_currency_fmt, $cart['shipping_amount'], $intl_currency);
                        $content .= "</td>" . ($cart_edit=='yes'?'<td></td>':'');
                    }
                    $content .= "</tr>";
                    $count++;
                    $separator = '';
                }
                if( isset($cart['taxes']) ) {
                    foreach($cart['taxes'] as $tax) {
                        $tax = $tax['tax'];
                        $content .= "<tr class='{$separator}" . (($count%2)==0?'item-even':'item-odd') . "'>";
                        $content .= "<td colspan='$num_cols' class='alignright'>" . $tax['description'] . ":</td>"
                            . "<td class='alignright'>"
                            . numfmt_format_currency($intl_currency_fmt, $tax['amount'], $intl_currency)
                            . "</td>"
                            . ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
                        $count++;
                        $separator = '';
                    }
                }
                $content .= "<tr class='{$separator}" . (($count%2)==0?'item-even':'item-odd') . "'>";
                $content .= "<td colspan='$num_cols' class='alignright'><b>Total{$duenow}:</b></td>"
                    . "<td class='alignright'>"
                    . numfmt_format_currency($intl_currency_fmt, $cart['total_amount'], $intl_currency)
                    . "</td>"
                    . ($cart_edit=='yes'?'<td></td>':'') . "</tr>";
                $count++;
                $separator = '';

                $content .= "</foot>";
            }
            $content .= "</table>";
            $content .= "</div>";   // close items-wrap

            //
            // Display the bill to and ship to information
            //
            $count = 1;
            $cart_details = '';
/*            if( isset($settings['cart-po-number']) && $settings['cart-po-number'] != 'no' ) {
                $cart_details .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
                $cart_details .= "<th>PO Number:</td>" . "<td>";
                if( $cart_edit == 'yes' ) {
                    $cart_details .= "<input id='po_number' class='text' type='text' placeholder='PO Number' "
                            . "name='po_number' value='" . $cart['po_number'] . "' />";
                } else {
                    $cart_details .= $cart['po_number'];
                }
                $cart_details .= "</td></tr>";
                $count++;
            } */
            $baddr = '';
            if( isset($cart['billing_name']) && $cart['billing_name'] != '' ) {
                $baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_name'];
            }
            if( isset($cart['billing_address1']) && $cart['billing_address1'] != '' ) {
                $baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_address1'];
            }
            if( isset($cart['billing_address2']) && $cart['billing_address2'] != '' ) {
                $baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_address2'];
            }
            $city = '';
            if( isset($cart['billing_city']) && $cart['billing_city'] != '' ) {
                $city .= ($city!=''?'':'') . $cart['billing_city'];
            }
            if( isset($cart['billing_province']) && $cart['billing_province'] != '' ) {
                $city .= ($city!=''?', ':'') . $cart['billing_province'];
            }
            if( isset($cart['billing_postal']) && $cart['billing_postal'] != '' ) {
                $city .= ($city!=''?'  ':'') . $cart['billing_postal'];
            }
            if( $city != '' ) { 
                $baddr .= ($baddr!=''?'<br/>':'') . $city;
            }
            if( isset($cart['billing_country']) && $cart['billing_country'] != '' ) {
                $baddr .= ($baddr!=''?'<br/>':'') . $cart['billing_country'];
            }
            if( $baddr != '' ) {
                $cart_details .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
                $cart_details .= "<th>Bill To:</th><td>";
                $cart_details .= $baddr;
                $cart_details .= "</td></tr>";
                $count++;
            }
            $saddr = '';
            if( isset($cart['shipping_name']) && $cart['shipping_name'] != '' ) {
                $saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_name'];
            }
            if( isset($cart['shipping_address1']) && $cart['shipping_address1'] != '' ) {
                $saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_address1'];
            }
            if( isset($cart['shipping_address2']) && $cart['shipping_address2'] != '' ) {
                $saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_address2'];
            }
            $city = '';
            if( isset($cart['shipping_city']) && $cart['shipping_city'] != '' ) {
                $city .= ($city!=''?'':'') . $cart['shipping_city'];
            }
            if( isset($cart['shipping_province']) && $cart['shipping_province'] != '' ) {
                $city .= ($city!=''?', ':'') . $cart['shipping_province'];
            }
            if( isset($cart['shipping_postal']) && $cart['shipping_postal'] != '' ) {
                $city .= ($city!=''?'  ':'') . $cart['shipping_postal'];
            }
            if( $city != '' ) { 
                $saddr .= ($saddr!=''?'<br/>':'') . $city;
            }
            if( isset($cart['shipping_country']) && $cart['shipping_country'] != '' ) {
                $saddr .= ($saddr!=''?'<br/>':'') . $cart['shipping_country'];
            }
            $tenant_flags = isset($ciniki['tenant']['modules']['ciniki.sapos']['flags']) ? $ciniki['tenant']['modules']['ciniki.sapos']['flags'] : 0;
            // 
            // Make sure shipping, simple shipping or instore pickup are enabled
            //
            $details_class = 'billto';
            if( ($tenant_flags&0x30000040) > 0 && $saddr != '' && ($cart['shipping_status'] > 0 || ($cart['preorder_total_amount'] > 0)) ) {
                $details_class = 'shipto';
                $cart_details .= "<tr class='" . (($count%2)==0?'item-even':'item-odd') . "'>";
                $cart_details .= "<th>Ship To:</th><td>";
                // 
                // Check if tenant has ONLY instore pickup
                //
/*                if( ($tenant_flags&0x30000040) == 0x20000000 ) {
                    if( isset($settings['cart-instore-address-message']) && $settings['cart-instore-address-message'] != '' ) {
                        $cart_details .= preg_replace("/\n/", "<br/>", $settings['cart-instore-address-message']);
                    } else {
                        $cart_details .= "**IN STORE PICKUP**";
                    }
                } else { */
                    $cart_details .= $saddr;
//                }
                $cart_details .= "</td></tr>";
                $count++;
            }

            if( $cart_details != '' && $display_cart != 'regreview' ) {
                $content .= "<div class='details-wrap " . $details_class . "'>";
                $content .= "<table class='details'>";
                $content .= "<tbody>";
                $content .= $cart_details;
                $content .= "</tbody>";
                $content .= "</table>";
                $content .= "</div>";
            }

            if( isset($settings['cart-customer-notes']) 
                && $settings['cart-customer-notes'] == 'yes' 
                && $display_cart != 'regreview' 
                ) {
                if( $cart_edit == 'yes' ) {
                    $content .= "<div class='customer-notes'>";
                    $content .= "<label for='customer_notes'>Notes</label>"
                        . "<textarea class='' class='notes' id='customer_notes' name='customer_notes'>" 
                        . $cart['customer_notes'] 
                        . "</textarea>"
                        . "";
                    $content .= "</div>";
                } elseif( isset($cart['customer_notes']) && $cart['customer_notes'] != '' ) {
                    $content .= "<div class='customer-notes'>";
                    $content .= "<label for='customer_notes'>Notes</label>"
                        . "<p>" . $cart['customer_notes'] . "</p>";
                    $content .= "</div>";
                }
            }
                    
            // 
            // Check if donation requests should be displayed
            //
            if( $cart_edit == 'yes' 
                && ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x02000000)
                && isset($settings['cart-donation-message']) && $settings['cart-donation-message'] != ''
                && isset($settings['cart-donation-amounts']) && $settings['cart-donation-amounts'] != '' 
                ) {

                //
                // Check if donation already in cart
                // 
                $donations = 'no';
                if( isset($cart['items']) ) {
                    foreach($cart['items'] as $item) {
                        if( ($item['item']['flags']&0x8000) == 0x8000 ) {
                            $donations = 'yes';
                        }
                    }
                }
              
                if( $donations == 'yes' ) {
                    if( isset($settings['cart-donation-thankyou']) && $settings['cart-donation-thankyou'] != '' ) {
                        $content .= "<div class='donations'><div class='message'>" . $settings['cart-donation-thankyou'] . "</div></div>";
                    }

                } else {
                    $content .= "<div class='donations'>";
                    $content .= "<div class='message'>" . $settings['cart-donation-message'] . "</div>";
                    $content .= "<div class='donation-options'>";
                    $content .= "<b>Add Donation:</b>";
                    if( isset($settings['cart-donation-amounts']) 
                        && $settings['cart-donation-amounts'] != '' 
                        ) {
                        $amounts = explode(',', $settings['cart-donation-amounts']);
                        foreach($amounts as $amount) {
                            $amount = preg_replace("/[^0-9\.]/", '', $amount);
                            $content .= "<input class='button submit' type='submit' name='donate' value='$" . number_format($amount, 0) . "'/>";
                        }
                    } else {
                        $content .= "<input class='button submit' type='submit' name='donate' value='$25'/>";
                        $content .= "<input class='button submit' type='submit' name='donate' value='$50'/>";
                        $content .= "<input class='button submit' type='submit' name='donate' value='$75'/>";
                        $content .= "<input class='button submit' type='submit' name='donate' value='$100'/>";
                        $content .= "<input class='button submit' type='submit' name='donate' value='$200'/>";

                    }
                    $content .= "</div>";
                    $content .= "<div class='other-amount'>"
                        . "<b>Other Amount:</b>"
                        . "<input class='quantity' type='text' name='amount' value=''/>"
                        . "<input class='button submit' type='submit' name='donate' value='Add'/>"
                        . "</div>";

                    $content .= "</div>";     
                }
            }

            if( $cart_edit == 'yes' && isset($settings['cart-noaccount-message']) && $settings['cart-noaccount-message'] != '' 
                && (!isset($request['session']['customer']['id']) || $request['session']['customer']['id'] == 0)
                ) {
                $content .= "<div class='noaccount-message'><p class='message noaccount-message'>" . $settings['cart-noaccount-message'] . "</p></div>";
            }

            if( $cart_edit == 'yes' && isset($sapos_settings['invoice-preorder-message']) && $sapos_settings['invoice-preorder-message'] != '' && $sapos_settings['invoice-preorder-message'] != 'null' ) {
                $content .= "<div class='preorder-message'><p class='message'>" . $sapos_settings['invoice-preorder-message'] . "</p></div>";
            }

            if( $cart_edit == 'yes' && isset($settings['cart-bottom-message']) && $settings['cart-bottom-message'] != '' && $settings['cart-bottom-message'] != 'null' ) {
                $content .= "<div class='bottom-message'><p class='message'>" . $settings['cart-bottom-message'] . "</p></div>";
            }

            //
            // cart buttons
            //
            $content .= "<div class='buttons'>";
            if( $cart_edit == 'yes' || $display_cart == 'regreview' ) {
                if( $display_cart == 'regreview' ) {
                    $content .= "<span class='submit'>"
                        . "<input class='button submit' type='submit' name='continue' value='Back'/>"
                        . "</span>";
                }
                $content .= "<span class='submit'>"
                    . "<input class='button submit' type='submit' name='update' value='Update'/>"
                    . "</span>";
                if( isset($settings['cart-account-create-button']) && $settings['cart-account-create-button'] == 'yes' 
                    && (!isset($request['session']['customer']['id']) || $request['session']['customer']['id'] == 0)
                    ) {
                    $content .= "<span class='submit'>"
                        . "<input class='button submit' type='submit' name='createaccount' value='Create Account'/>"
                        . "</span>";
                }
                if( isset($settings['cart-child-create-button']) && $settings['cart-child-create-button'] == 'yes' 
                    && isset($request['session']['customer']['id']) && $request['session']['customer']['id'] > 0
                    && isset($request['session']['customer']['children-allowed']) && $request['session']['customer']['children-allowed'] == 'yes'
                    ) {
                    if( $display_cart == 'regreview' ) {
                        $content .= "<span class='submit'>"
                            . "<input class='button submit' type='submit' name='addchild' value='Add Child' "
                                . "onclick='window.open(\"" . $request['ssl_domain_base_url'] . "/account/children?add=yes&next=regreview" . "\",\"_self\");return false;'"
                                . " />"
                            . "</span>";

                    } else {
                        $content .= "<span class='submit'>"
                            . "<input class='button submit' type='submit' name='addchild' value='Add Child' "
                                . "onclick='window.open(\"" . $request['ssl_domain_base_url'] . "/account/children?add=yes&next=cart" . "\",\"_self\");return false;'"
                                . " />"
                            . "</span>";
                    }
                } 
            }
/*            if( isset($request['session']['customer']['dealer_status']) 
                && $request['session']['customer']['dealer_status'] > 0 
                && $request['session']['customer']['dealer_status'] < 60 
                ) {
                if( $display_cart == 'confirm' ) {
                    $content .= "<span class='cart-submit'>"
                        . "<input class='button submit' type='submit' name='confirmorder' value='Confirm Order'/>"
                        . "</span>";
                } else {
                    $content .= "<span class='cart-submit'>"
                        . "<input class='button submit' type='submit' name='submitorder' value='Submit Order' onclick='return check_cart();'/>"
                        . "</span>";
                }
            } else { */
                if( $display_cart == 'review' ) {
                    $content .= "<span class='submit'>"
                        . "<input class='button submit' type='submit' name='continue' value='Back'/></span>";
                    if( $stripe_checkout == 'yes' && $cart['total_amount'] == 0 && $cart['preorder_total_amount'] == 0 ) {
                        $content .= "<button class='button submit' onclick='' type='submit' name='nocharge_checkout'>Confirm</button>";
                    }
                    elseif( $stripe_checkout == 'yes' ) {
                        if( !isset($request['response']['head']['scripts']) ) {
                            $request['response']['head']['scripts'] = array();
                        }
                        $request['response']['head']['scripts'][] = array(
                            'src'=>'https://checkout.stripe.com/checkout.js', 
                            'type'=>'text/javascript',
                            ); 
                        $js .= "var stripeCheckout = StripeCheckout.configure({"
                                . 'key: "' . $request['site']['settings']['stripe-pk'] . '", '
                                . 'image: "' . $request['site']['cache_url'] . '/theme/stripe_checkout.jpg", '
                                . 'locale: "auto", '
                                . 'name: "' . $request['site']['settings']['header-site-title'] . '", '
                                . 'description: "", '
                                . 'amount: ' . number_format($cart['total_amount'] * 100, 0, '', '') . ', '
                                . 'zipCode: true, '
                                . 'allowRememberMe: false, '
                                . 'currency: "' . $intl_currency . '", '
                                . 'token: function(token) {'
                                    . 'document.getElementById("stripe-token").value=token.id;'
                                    . 'document.getElementById("stripe-email").value=token.email;'
                                    . 'document.getElementById("cart").submit();'
                                . '},'
                                . 'opened: function() {'
                                . '},'
                                . 'closed: function(e) {'
                                . '},'
                                . '});';
                        $content .= "<input id='stripe-token' type='hidden' name='stripe-token' value=''/>";
                        $content .= "<input id='stripe-email' type='hidden' name='stripe-email' value=''/>";
                        $content .= "<button class='button submit' onclick='stripeCheckout.open(); return false;' type='submit' name='stripecheckout'>Pay Now</button>";
                    }
                    if( $paypal_checkout == 'yes' && $cart['total_amount'] == 0 && $cart['preorder_total_amount'] == 0 ) {
                        $content .= "<button class='button submit' onclick='' type='submit' name='nocharge_checkout'>Confirm</button>";
                    }
                    elseif( $paypal_checkout == 'yes' ) {
                        $content .= "<input class='button submit' type='submit' name='paypalexpresscheckout' value='Checkout with a Credit Card'/>";
                    }
                    $content .= "</span>";
                } elseif( $display_cart == 'paypalexpresscheckoutconfirm' ) {
                    $content .= "<span class='cart-submit'>"
                        . "<input class='button submit' type='submit' name='paypalexpresscheckoutdo' value='Pay Now'/>"
                        . "</span>";
                } elseif( $display_cart == 'regreview' ) {
                    $content .= "<span class='cart-submit'>"
                        . "<input type='hidden' name='regreviewed' value='yes'/>"
                        . "<input class='button submit' type='submit' name='checkout' value='Checkout'/>"
                        . "</span>";
                } else {
                    $content .= "<span class='cart-submit'>"
                        . "<input class='button submit' type='submit' name='checkout' value='Checkout'/>"
                        . "</span>";
                }
/*            } */
            $content .= "</div>";   // Close buttons

            $content .= "</form>";
        } else {
            $content .= "<p>Your shopping cart is empty.</p>";
        }

        $content .= "</div>";    // Close cart

        $content .= "</div>";
        $content .= "</div>";
        $content .= "</div>";

        $blocks[] = array(
            'type' => 'html',
            'html' => $content,
            'js' => $js,
            );
    }

    if( $display_cart == 'checkout_success' ) {
        $block = array(
            'type' => 'msg',
            'level' => 'success',
            'content' => 'Thank you for your order, we have emailed you a receipt.'
            );
        if( isset($settings['cart-payment-success-message']) && $settings['cart-payment-success-message'] != '' ) {
            $block['content'] = $settings['cart-payment-success-message'];
        } 
        $blocks[] = $block;

        //
        // Check for a redirect 
        //
        if( isset($request['session']['cart-redirect-success']) 
            && $request['session']['cart-redirect-success'] != ''
            ) {
            $request['session']['cart-payment-success'] = 'yes';
            header('Location: ' . $request['session']['cart-redirect-success']);
            return array('stat'=>'exit');
        }
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
