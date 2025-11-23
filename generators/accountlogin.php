<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_accountlogin(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $signin_signup = 'no';
    $billing = 'no';
    $shipping = 'no';
    if( isset($block['create-account']) && $block['create-account'] == 'signin-signup' ) {
        $signin_signup = 'yes';
        $billing = 'no';
        $shipping = 'no';
    } elseif( isset($block['create-account']) && $block['create-account'] == 'signin-signup-billing' ) {
        $signin_signup = 'yes';
        $billing = 'yes';
        $shipping = 'no';
    } elseif( isset($block['create-account']) && $block['create-account'] == 'signin-signup-billing-shipping' ) {
        $signin_signup = 'yes';
        $billing = 'yes';
        $shipping = 'yes';
    }

    //
    // Setup restricted countries
    //
    if( $billing == 'yes' ) {
        $required_account_fields['address1'] = 'Billing Address';
        $required_account_fields['city'] = 'Billing City';
        $required_account_fields['province'] = 'Billing State/Province'; 
        $required_account_fields['postal'] = 'Billing ZIP/Postal Code'; 
        $required_account_fields['country'] = 'Billing Country';
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x10000040) ) {
            $required_account_fields['shipaddress1'] = 'Shipping Address';
            $required_account_fields['shipcity'] = 'Shipping City';
            $required_account_fields['shipprovince'] = 'Shipping State/Province';
            $required_account_fields['shippostal'] = 'Shipping Zip/Postal Code';
            $required_account_fields['shipcountry'] = 'Shipping Country';
            $required_account_fields['shipphone'] = 'Phone at Shipping Address';
        }
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
        // Setup the address fields
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'countryCodes');
        $rc = ciniki_core_countryCodes($ciniki);
        $country_codes = $rc['countries'];
        if( isset($request['site']['settings']['account-allowed-countries']) 
            && trim($request['site']['settings']['account-allowed-countries']) != '' 
            ) { 
            if( ($allowed_codes = preg_split("/,\s*/", trim($request['site']['settings']['account-allowed-countries']))) !== false ) {
               foreach($country_codes as $code => $country) {
                    if( !in_array($code, $allowed_codes) ) {
                        unset($country_codes[$code]);
                    }
                }
            }
        }
        $province_codes = $rc['provinces'];
    }


    $content .= "<div class='block-accountlogin"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . ($signin_signup == 'yes' ? ' signin-signup' : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    $startform = 'login';
    if( isset($block['startform']) ) {
        if( $block['startform'] == 'forgot' ) {
            $startform = 'forgot';
        } elseif( $block['startform'] == 'signup' ) {
            $startform = 'signup';
        } else {
            $startform = 'login';
        }
    } 

    $js = '';

    $email = isset($block['email']) ? $block['email'] : '';

    $dt = new DateTime('now', new DateTimezone('UTC'));
    $return_url_field = '';
    if( isset($request['session']['login-return-url']) ) {
        $return_url_field = "<input type='hidden' name='login-return-url' value='" . urlencode($request['session']['login-return-url']) . "' />";
    }

    //
    // Display the login form
    //
    $content .= "<div id='signin-form' class='signin-form"
        . ($startform == 'login' ? '' : ' hidden')
        . "'"
//        . " style='display: ". ($startform == 'login' ? 'block;' : 'none;') . "'"
        . ">";
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    } else {
        $content .= "<h2>Sign In</h2>";
    }
    $content .= "<form method='POST' action=''>"
        . "<input type='hidden' name='action' value='signin'>"
        . "<input type='hidden' name='fdt' value='" . $dt->format('U') . "' />"
        . $return_url_field
        . "<div class='input first-field'>"
            . "<label for='email'>Email</label>"
            . "<input id='email' type='email' class='text' maxlength='250' name='email' value='$email' />"
        . "</div>\n" 
        . "<div class='input last-field'>"
            . "<label for='password'>Password</label>"
            . "<input id='password' type='password' class='text' maxlength='100' name='password' value='' />"
        . "</div>\n"
        . "<div class='submit'>"
            . "<input type='submit' class='button' value='Sign In' />"
        . "</div>\n"
        . "</form>";
    if( isset($block['forgot']) && $block['forgot'] == 'yes' ) {
        $content .= "<div class='forgot-link'><p>"
            . "<a class='link' href='javscript:void(0);' onclick='swapLoginForm(\"forgotpassword\");return false;'>";
        if( isset($block['forgot-link-text']) && $block['forgot-link-text'] != '' ) {
            $content .= $block['forgot-link-text'];
        } else {
            $content .= "Forgot password?";
        }
        $content .= "</a></p></div>\n";
    }
    if( isset($block['create-account']) 
        && ($block['create-account'] == 'simple' || $block['create-account'] == 'phone-billing') 
        ) {
        $content .= "<div class='create-link'><p>"
            . "<a class='link' href='javscript:void(0);' onclick='swapCreateForm(\"signup\");return false;'>";
        if( isset($block['create-account-text']) && $block['create-account-text'] != '' ) {
            $content .= $block['create-account-text'];
        } else {
            $content .= "Sign Up Now";
        }
        $content .= "</a></p></div>\n";
    }
    $content .= "</div>\n";
        
    //
    // Forgot password form
    //
    $js = '';
    if( isset($block['forgot']) && $block['forgot'] == 'yes' ) {
        //
        // The forgot reset form
        //
        $content .= "<div id='forgotpassword-form' class='forgotpassword-form"
            . ($startform == 'forgot' ? '' : ' hidden')
            . "'"
//            . " style='display:" . ($startform == 'forgot' ? 'block;' : 'none;') . "'"
            . ">";
        if( isset($block['forgot-title']) && $block['forgot-title'] != '' ) {
            $content .= "<h2>" . $block['forgot-title'] . "</h2>";
        } else {
            $content .= "<h2>Forgot Password</h2>";
        }
        $content .= "<p>Please enter your email address and you will receive a link to create a new password.</p>"
            . "<form method='POST' action=''>"
            . "<input type='hidden' name='action' value='forgot'>\n"
            . "<input type='hidden' name='fdt' value='" . $dt->format('U') . "' />"
            . $return_url_field
            . "<div class='input first-field last-field'>"
                . "<label for='forgotemail'>Email</label>"
                . "<input id='forgotemail' type='email' class='text' maxlength='250' name='email' value='$email' />"
            . "</div>\n" 
            . "<div class='submit'>"
                . "<input type='submit' class='button' value='Get New Password' />"
            . "</div>\n"
            . "</form>"
            . "<div class='forgot-link'><p>"
                . "<a class='link' href='javascript:void();' onclick='swapLoginForm(\"signin\"); return false;'>"
                . "Sign In"
                . "</a></p></div>\n"
            . "</div>\n";

        //
        // Javascript to switch login/forgot password forms
        //
        $js .= ""
            . " function swapLoginForm(l) {\n"
            . "     if( l == 'forgotpassword' ) {\n"
            . "         C.gE('signin-form').style.display = 'none';\n"
            . "         C.gE('forgotpassword-form').style.display = 'block';\n"
            . "         C.gE('forgotemail').value = C.gE('email').value;\n"
            . "     } else {\n"
            . "         C.gE('signin-form').style.display = 'block';\n"
            . "         C.gE('forgotpassword-form').style.display = 'none';\n"
            . "     }\n"
            . "     return true;\n"
            . " }\n"
            . "";
    }

    //
    // The simple account create form
    //
    if( isset($block['create-account']) 
        && ($block['create-account'] == 'simple' || $signin_signup == 'yes') 
        ) {
        //
        // The forgot reset form
        //
        $content .= "<div id='signup-form' class='signup-form simple"
            . ($startform == 'signup' || $signin_signup == 'yes' ? '' : ' hidden')
            . "'"
//            . " style='display:" . ($startform == 'signup' ? 'block;' : 'none;') . "'"
            . ">";
        if( isset($block['create-account-text']) && $block['create-account-text'] != '' ) {
            $content .= "<h2>" . $block['create-account-text'] . "</h2>";
        } else {
            $content .= "<h2>Create Account</h2>";
        }
        $content .= ""
            . "<form method='POST' action=''>"
            . "<input type='hidden' name='action' value='signup'>\n"
            . "<input type='hidden' name='fdt' value='" . $dt->format('U') . "' />"
            . $return_url_field
            . "<div class='input first-field required'>"
                . "<label for='first'>First Name</label>"
                . "<input id='first' type='text' class='text' maxlength='150' name='first' value='"
                    . (isset($block['first']) ? $block['first'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='input required'>"
                . "<label for='last'>Last Name</label>"
                . "<input id='last' type='text' class='text' maxlength='150' name='last' value='"
                    . (isset($block['last']) ? $block['last'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='input required'>"
                . "<label for='signupemail'>Email</label>"
                . "<input id='signupemail' type='email' class='text' maxlength='250' name='signupemail' value='$email' />"
            . "</div>\n" 
            . "<div class='input required emailagain'>"
                . "<label for='signupemail'>Email Again</label>"
                . "<input id='signupemail2' type='email' class='text' maxlength='250' name='signupemail2' value='' />"
            . "</div>\n" 
            . "<div class='input"
                . (isset($block['signup-confirm-password']) && $block['signup-confirm-password'] == 'yes' ? '' : ' last-field')
                . " required'>"
                . "<label for='signuppassword'>Password</label>"
                . "<input id='signuppassword' type='password' class='text' maxlength='100' name='signuppassword' value='"
                    . (isset($block['password']) ? $block['password'] : '')
                    . "' />"
            . "</div>\n";
        if( isset($block['signup-confirm-password']) && $block['signup-confirm-password'] == 'yes' ) {
            $content .= "<div class='input last-field required'>"
                    . "<label for='sconfirmpassword'>Confirm Password</label>"
                    . "<input id='sconfirmassword' type='password' class='text' maxlength='100' name='sconfirmpassword' value='"
                        . (isset($block['confirmpassword']) ? $block['confirmpassword'] : '')
                        . "' />"
                . "</div>\n";
        }
        if( $billing == 'yes' ) {
            $address = array(
                'address1'=>(isset($_POST['address1'])?$_POST['address1']:''),
                'address2'=>(isset($_POST['address2'])?$_POST['address2']:''),
                'city'=>(isset($_POST['city'])?$_POST['city']:''),
                'province'=>(isset($args['province'])?$args['province']:''),
                'postal'=>(isset($_POST['postal'])?$_POST['postal']:''),
                'country'=>(isset($_POST['country'])?$_POST['country']:'Canada'),
                );
            $content .= "<h2>Billing Address</h2>";
            $content .= "<div class='input country first-field'>"
                . "<label for='country'>Country" . (array_key_exists('country', $required_account_fields)?' *':'') . "</label>"
                . "<select id='country_code' type='select' class='select' name='country' onchange='updateProvince()'>";
            if( !isset($request['site']['settings']['account-allowed-countries']) 
                || trim($request['site']['settings']['account-allowed-countries']) == '' 
                ) {
                $content .= "<option value=''></option>";
            }
            $selected_country = '';
            foreach($country_codes as $country_code => $country_name) {
                $content .= "<option value='" . $country_code . "' " 
                    . (($country_code == $address['country'] || $country_name == $address['country'])?' selected':'')
                    . ">" . $country_name . "</option>";
                if( $country_code == $address['country'] || $country_name == $address['country'] ) {
                    $selected_country = $country_code;
                }
            }
            $content .= "</select></div>";
            $content .= "<div class='input address1'>"
                . "<label for='address1'>Address" . (array_key_exists('address1', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='address1' value='" . $address['address1'] . "' autocomplete='billing address-line1'>"
                . "</div>";
            $content .= "<div class='input address2'>"
                . "<label for='address2'>" . (array_key_exists('address2', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='address2' value='" . $address['address2'] . "' autocomplete='billing address-line2'>"
                . "</div>";
            $content .= "<div class='input city'>"
                . "<label for='city'>City" . (array_key_exists('city', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='city' value='" . $address['city'] . "' autocomplete='billing address-level2'>"
                . "</div>";
            $content .= "<div class='input province'>"
                . "<label for='province'>State/Province" . (array_key_exists('province', $required_account_fields)?' *':'') . "</label>"
                . "<input id='province_text' type='text' class='text' name='province' "
                    . ((isset($province_codes[$selected_country]) && $province_codes[$selected_country])?" style='display:none;'":"")
                    . "value='" . $address['province'] . "' autocomplete='billing address-level1'>";
            $formjs = '';
            foreach($province_codes as $country_code => $provinces) {
                $content .= "<select id='province_code_{$country_code}' type='select' class='select' "
                    . (($country_code != $selected_country)?" style='display:none;'":"")
                    . " name='province_code_{$country_code}' autocomplete='billing address-level1'>"
                    . "<option value=''></option>";
                $formjs .= "document.getElementById('province_code_" . $country_code . "').style.display='none';";
                foreach($provinces as $province_code => $province_name) {
                    $content .= "<option value='" . $province_code . "'" 
                        . (($province_code == (isset($_POST["province_code_{$country_code}"])?$_POST["province_code_{$country_code}"]:'') || $province_name == (isset($_POST["province_code_{$country_code}"])?$_POST["province_code_{$country_code}"]:''))?' selected':'')
                        . ">" . $province_name . "</option>";
                }
                $content .= "</select>";
            }
            $content .= "</div>";
            $content .= "<div class='input postal last-field'>"
                . "<label for='postal'>ZIP/Postal Code" . (array_key_exists('postal', $required_account_fields)?' *':'') . "</label>"
                . "<input type='text' class='text' name='postal' value='" . $address['postal'] . "' autocomplete='address postal-code'>"
                . "</div>";
            $js .= ""
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
                . "}";

            if( $shipping == 'yes' ) {
                $address = array(
                    'address1'=>(isset($_POST['shipping_address1'])?$_POST['shipping_address1']:''),
                    'address2'=>(isset($_POST['shipping_address2'])?$_POST['shipping_address2']:''),
                    'city'=>(isset($_POST['shipping_city'])?$_POST['shipping_city']:''),
                    'province'=>(isset($args['shipping_province'])?$args['shipping_province']:''),
                    'postal'=>(isset($_POST['shipping_postal'])?$_POST['shipping_postal']:''),
                    'country'=>(isset($_POST['shipping_country'])?$_POST['shipping_country']:'Canada'),
                    );
                $content .= "<h2>Shipping Address</h2>";
                $content .= "<div class='input country first-field'>"
                    . "<label for='shipping_country'>Country" . (array_key_exists('shipping_country', $required_account_fields)?' *':'') . "</label>"
                    . "<select id='shipping_country_code' type='select' class='select' name='shipping_country' onchange='updateShippingProvince()'>";
                if( !isset($request['site']['settings']['account-allowed-countries']) 
                    || trim($request['site']['settings']['account-allowed-countries']) == '' 
                    ) {
                    $content .= "<option value=''></option>";
                }
                $selected_country = '';
                foreach($country_codes as $country_code => $country_name) {
                    $content .= "<option value='" . $country_code . "' " 
                        . (($country_code == $address['country'] || $country_name == $address['country'])?' selected':'')
                        . ">" . $country_name . "</option>";
                    if( $country_code == $address['country'] || $country_name == $address['country'] ) {
                        $selected_country = $country_code;
                    }
                }
                $content .= "</select></div>";
                $content .= "<div class='input address1'>"
                    . "<label for='shipping_address1'>Address" . (array_key_exists('shipping_address1', $required_account_fields)?' *':'') . "</label>"
                    . "<input type='text' class='text' name='shipping_address1' value='" . $address['address1'] . "' autocomplete='shipping address-line1'>"
                    . "</div>";
                $content .= "<div class='input address2'>"
                    . "<label for='shipping_address2'>" . (array_key_exists('shipping_address2', $required_account_fields)?' *':'') . "</label>"
                    . "<input type='text' class='text' name='shipping_address2' value='" . $address['address2'] . "' autocomplete='billing address-line2'>"
                    . "</div>";
                $content .= "<div class='input city'>"
                    . "<label for='shipping_city'>City" . (array_key_exists('shipping_city', $required_account_fields)?' *':'') . "</label>"
                    . "<input type='text' class='text' name='shipping_city' value='" . $address['city'] . "' autocomplete='billing address-level2'>"
                    . "</div>";
                $content .= "<div class='input province'>"
                    . "<label for='shipping_province'>State/Province" . (array_key_exists('shipping_province', $required_account_fields)?' *':'') . "</label>"
                    . "<input id='shipping_province_text' type='text' class='text' name='shipping_province' "
                        . ((isset($province_codes[$selected_country]) && $province_codes[$selected_country])?" style='display:none;'":"")
                        . "value='" . $address['province'] . "' autocomplete='billing address-level1'>";
                $formjs = '';
                foreach($province_codes as $country_code => $provinces) {
                    $content .= "<select id='shipping_province_code_{$country_code}' type='select' class='select' "
                        . (($country_code != $selected_country)?" style='display:none;'":"")
                        . " name='shipping_province_code_{$country_code}' autocomplete='billing address-level1'>"
                        . "<option value=''></option>";
                    $formjs .= "document.getElementById('shipping_province_code_" . $country_code . "').style.display='none';";
                    foreach($provinces as $province_code => $province_name) {
                        $content .= "<option value='" . $province_code . "'" 
                            . (($province_code == (isset($_POST["shipping_province_code_{$country_code}"])?$_POST["shipping_province_code_{$country_code}"]:'') || $province_name == (isset($_POST["shipping_province_code_{$country_code}"])?$_POST["shipping_province_code_{$country_code}"]:''))?' selected':'')
                            . ">" . $province_name . "</option>";
                    }
                    $content .= "</select>";
                }
                $content .= "</div>";
                $content .= "<div class='input postal last-field'>"
                    . "<label for='shipping_postal'>ZIP/Postal Code" . (array_key_exists('shipping_postal', $required_account_fields)?' *':'') . "</label>"
                    . "<input type='text' class='text' name='shipping_postal' value='" . $address['postal'] . "' autocomplete='address postal-code'>"
                    . "</div>";
                $js .= ""
                    . "function updateShippingProvince() {"
                        . "var cc = C.gE('shipping_country_code');"
                        . "var pr = C.gE('shipping_province_text');"
                        . "var pc = C.gE('shipping_province_code_'+cc.value);"
                        . $formjs
                        . "if( pc != null ) {"
                            . "pc.style.display='';"
                            . "pr.style.display='none';"
                        . "}else{"
                            . "pr.style.display='';"
                        . "}"
                    . "}";
            }
        }
        $content .= "<div class='submit'>"
                . "<input type='submit' class='button' value='Create Account' />"
            . "</div>\n"
            . "</form>";
        if( $signin_signup == 'no' ) {
            $content .= "<div class='create-link'><p>"
                . "<a class='link' href='javascript:void();' onclick='swapCreateForm(\"signin\"); return false;'>"
                . "Sign In"
                . "</a></p></div>";
        }
        $content .= "</div>\n";

        //
        // Javascript to switch login/forgot password forms
        //
        $js .= ""
            . " function swapCreateForm(l) {\n"
            . "     if( l == 'signup' ) {\n"
            . "         C.gE('signin-form').style.display = 'none';\n"
            . "         C.gE('signup-form').style.display = 'block';\n"
            . "         C.gE('signupemail').value = C.gE('email').value;\n"
            . "     } else {\n"
            . "         C.gE('signin-form').style.display = 'block';\n"
            . "         C.gE('signup-form').style.display = 'none';\n"
            . "     }\n"
            . "     return true;\n"
            . " }\n"
            . "";

    }

    //
    // The account signup with phone and billing information required
    //
    elseif( isset($block['create-account']) && $block['create-account'] == 'phone-billing' ) {
        //
        // The forgot reset form
        //
        $content .= "<div id='signup-form' class='signup-form phone-billing' style='display:"
            . ($startform == 'signup' ? 'block;' : 'none;')
            . "'>";
        if( isset($block['create-account-text']) && $block['create-account-text'] != '' ) {
            $content .= "<h2>" . $block['create-account-text'] . "</h2>";
        } else {
            $content .= "<h2>Create Account</h2>";
        }
        // Start a form, to be displayed the same way as the
        $content .= "<div class='block-form sectioned'>"
            . "<div class='wrap'>"
            . "<div class='content'>"
            . "<div class='form'>"
            . "<div class='form-sections-fields'>"
            . "<form method='POST' action=''>"
            . "<input type='hidden' name='action' value='signup'>\n"
            . "<input type='hidden' name='fdt' value='" . $dt->format('U') . "' />"
            . $return_url_field
            // Name/email/phone section
            . "<div class='form-section'><div class='fields'>"
            . "<div class='field field-text required size-medium'>"
                . "<label for='first'>First Name</label>"
                . "<input id='first' type='text' class='text' maxlength='150' name='first' value='"
                    . (isset($block['first']) ? $block['first'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='field field-text required size-medium'>"
                . "<label for='last'>Last Name</label>"
                . "<input id='last' type='text' class='text' maxlength='150' name='last' value='"
                    . (isset($block['last']) ? $block['last'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='field field-text required size-medium'>"
                . "<label for='signupemail'>Email</label>"
                . "<input id='signupemail' type='email' class='text' maxlength='250' name='signupemail' value='$email' />"
            . "</div>\n";
        if( isset($block['signup-confirm-password']) && $block['signup-confirm-password'] == 'yes' ) {
            $content .= "<div class='newline'></div>";
            $content .= "<div class='field field-text required size-medium'>"
                    . "<label for='signuppassword'>Password</label>"
                    . "<input id='signuppassword' type='password' class='text' maxlength='100' name='signuppassword' value='"
                        . (isset($block['password']) ? $block['password'] : '')
                        . "' />"
                . "</div>\n";
            $content .= "<div class='field field-text required size-medium'>"
                    . "<label for='sconfirmpassword'>Confirm Password</label>"
                    . "<input id='sconfirmpassword' type='password' class='text' maxlength='100' name='sconfirmpassword' value='"
                        . (isset($block['confirmpassword']) ? $block['confirmpassword'] : '')
                        . "' />"
                . "</div>\n";
        } else {
            $content .= "<div class='field field-text required size-medium'>"
                    . "<label for='signuppassword'>Password</label>"
                    . "<input id='signuppassword' type='password' class='text' maxlength='100' name='signuppassword' value='"
                        . (isset($block['password']) ? $block['password'] : '')
                        . "' />"
                . "</div>\n";
        }
        $content .= "<div class='field field-phone-type-number required size-small'>"
                . "<label for='phone_number_1'>Phone Number</label>"
                . "<label for='phone_label_1' class='hidden'>Phone Type</label>"
                . "<div class='joined-fields'>"
                . "<select id='phone_label_1' class='select' name='phone_label_1'>"
                    . "<option value='Cell'" 
                        . (isset($block['phone_label_1']) && $block['phone_label_1'] == 'Cell' ? ' selected': '')
                    . ">Cell</option>"
                    . "<option value='Home'" 
                        . (isset($block['phone_label_1']) && $block['phone_label_1'] == 'Home' ? ' selected': '')
                    . ">Home</option>"
                    . "<option value='Work'" 
                        . (isset($block['phone_label_1']) && $block['phone_label_1'] == 'Work' ? ' selected': '')
                    . ">Work</option>"
                . "</select>"
//            . "</div>\n" 
//            . "<div class='field field-text required size-small'>"
                . "<input id='phone_number_1' type='text' class='text' maxlength='50' name='phone_number_1' value='"
                    . (isset($block['phone_number_1']) ? $block['phone_number_1'] : '')
                    . "' />"
                . "</div>"
            . "</div>\n" 
            . "<div class='field field-phone-type-number size-small'>"
                . "<label for='phone_label_2' class='hidden'>Alternative Phone Type</label>"
                . "<label for='phone_number_2'>Alternative Number</label>"
                . "<div class='joined-fields'>"
                . "<select id='phone_label_2' class='select' name='phone_label_2'>"
                    . "<option value='Cell'" 
                        . (isset($block['phone_label_2']) && $block['phone_label_2'] == 'Cell' ? ' selected': '')
                    . ">Cell</option>"
                    . "<option value='Home'" 
                        . (!isset($block['phone_label_2']) || $block['phone_label_2'] == '' || $block['phone_label_2'] == 'Home' ? ' selected': '')
                    . ">Home</option>"
                    . "<option value='Work'" 
                        . (isset($block['phone_label_2']) && $block['phone_label_2'] == 'Work' ? ' selected': '')
                    . ">Work</option>"
                . "</select>"
//            . "</div>\n" 
//            . "<div class='field field-text size-small'>"
                . "<input id='phone_number_2' type='text' class='text' maxlength='50' name='phone_number_2' value='"
                    . (isset($block['phone_number_2']) ? $block['phone_number_2'] : '')
                    . "' />"
                . "</div>"
            . "</div>\n" 
            . "</div></div>\n"
            // Address section
            . "<div class='form-section'><h2>Billing Address</h2><div class='fields'>"
            . "<div class='field field-text required size-medium'>"
                . "<label for='address1'>Address Line 1</label>"
                . "<input id='address1' type='text' class='text' maxlength='250' name='address1' value='"
                    . (isset($block['address1']) ? $block['address1'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='field field-text size-medium'>"
                . "<label for='address2'>Address Line 2</label>"
                . "<input id='address2' type='text' class='text' maxlength='250' name='address2' value='"
                    . (isset($block['address2']) ? $block['address2'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='field field-text required size-small-medium'>"
                . "<label for='city'>City</label>"
                . "<input id='city' type='text' class='text' maxlength='250' name='city' value='"
                    . (isset($block['city']) ? $block['city'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='field field-text required size-small'>"
                . "<label for='province'>Province/State</label>"
                . "<input id='province' type='text' class='text' maxlength='250' name='province' value='"
                    . (isset($block['province']) ? $block['province'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='field field-text required size-small'>"
                . "<label for='postal'>Postal/Zip</label>"
                . "<input id='postal' type='text' class='text' maxlength='250' name='postal' value='"
                    . (isset($block['postal']) ? $block['postal'] : '')
                    . "' />"
            . "</div>\n" 
            . "<div class='field field-select required size-small'>"
                . "<label for='country'>Country</label>"
                . "<select id='country' type='text' class='select' name='country'>"
                    . "<option value='Canada' "
                        . ((!isset($block['country']) || $block['country'] == 'CA' || $block['country'] == '' || $block['country'] == 'Canada') ? 'selected' : '')
                        . ">Canada</option>"
                    . "<option value='US' "
                        . ((isset($block['country']) && ($block['country'] == 'US' || $block['country'] == 'USA' || $block['country'] == 'United States')) ? 'selected' : '')
                        . ">United States</option>"
                . "</select>"
            . "</div>\n" 
            . "</div></div>\n"

            . "<div class='submit'>"
                . "<input type='submit' class='button' value='Create Account' />"
            . "</div>\n"
            . "</form>"
            . "<div class='create-link'><p>"
                . "<a class='link' href='javascript:void();' onclick='swapCreateForm(\"signin\"); return false;'>"
                . "Sign In"
                . "</a></p></div>\n"
            . "</div>\n"
            . "</div></div>"
            . "</div></div></div>"
            . "";

        //
        // Javascript to switch login/forgot password forms
        //
        $js .= ""
            . " function swapCreateForm(l) {\n"
            . "     if( l == 'signup' ) {\n"
            . "         C.gE('signin-form').style.display = 'none';\n"
            . "         C.gE('signup-form').style.display = 'block';\n"
            . "         C.gE('signupemail').value = C.gE('email').value;\n"
            . "     } else {\n"
            . "         C.gE('signin-form').style.display = 'block';\n"
            . "         C.gE('signup-form').style.display = 'none';\n"
            . "     }\n"
            . "     return true;\n"
            . " }\n"
            . "";

    }


    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';



    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
