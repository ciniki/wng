<?php
//
// Description
// -----------
// This function will verify the account login of a customer or present the login form. If the customer submitted
// a forgot password request, this function also handles those requests.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_accountLoginProcess(&$ciniki, $tnid, &$request, $args=array()) {

    //
    // Check if the customer is logged in
    //
    if( isset($request['session']['customer']['id']) && $request['session']['customer']['id'] > 0 ) {
        return array('stat'=>'authenticated');
    }

    $settings = $request['site']['settings'];

    //
    // Check if the login form was submitted
    //
    $article_title = 'Account';
    $display_form = 'login';
    $blocks = array();

    //
    // Check if reset request
    //
    if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'passwordreset' ) {
        $display_form = 'reset';
    }
    if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'create' ) {
        $display_form = 'signup';
    }
    if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'signup' && isset($_GET['k']) ) {

        ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'signupComplete');
        $rc = ciniki_customers_wng_signupComplete($ciniki, $tnid, $request, $_GET['k']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }

        //
        // Return to where the process started from
        //
        if( isset($request['session']['login-return-url']) && $request['session']['login-return-url'] != '' ) {
            //
            // Provide a success message and continue button to redirect to next page
            //
            $blocks[] = array(
                'type' => 'msg',
                'class' => 'limit-width limit-width-40 aligncenter',
                'level' => 'success',
                'content' => 'Your account has been created.',
                );
            $blocks[] = array(
                'type' => 'buttons',
                'class' => 'limit-width limit-width-40 aligncenter',
                'list' => array(
                    array(
                        'text' => 'Continue',
                        'url' => $request['session']['login-return-url'],
                        ),
                    ),
                );
            return array('stat'=>'ok', 'blocks'=>$blocks);

/*          Jun 17, 2022 Removed so they are not auto redirected, but get the above message saying success and then proceed forward */
/*          header("Location: " . $request['session']['login-return-url']);
            unset($request['session']['login-return-url']);
            return array('stat'=>'exit'); */
        }
        return array('stat'=>'ok');
    }

    if( isset($_POST['action']) && $_POST['action'] == 'signin' ) {
        //
        // Check the referrer and that cookies are enabled
        //
        if( !isset($request['session']['loginform']) ) {
            if( isset($_POST['formdt']) && $_POST['formdt'] != '' ) {
                $fdt = new DateTime($_POST['fdt'], new DateTimezone('UTC'));
                $dt = new DateTime('now', new DateTimezone('UTC'));
                $dt->sub(new DateInterval('PT1H'));
                if( $fdt < $dt ) {
                    if( isset($_POST['login-return-url']) && $_POST['login-return-url'] != '' ) {
                        Header("Location: " . $_POST['login-return-url']);
                        return array('stat'=>'exit');
                    }
                }
            }
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error',
                'content' => "It appears that you do not have cookies enabled in your browser.  They are "
                    . "required for you to login.  Please check your browser settings and try again.  "
                    . "<br/><br/>Here is a link to help: "
                    . "<a target='_blank' href='http://support.google.com/accounts/bin/answer.py?hl=en&answer=61416'>How to enable cookies</a>."
                );
            $display_form = 'login';
        }

        elseif( isset($_POST['email']) && trim($_POST['email']) == '' 
            && isset($_POST['password']) && trim($_POST['password']) == '' 
            ) {
            $display_form = 'login';
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error',
                'content' => 'You must enter your email address and password.'
                );
        }
        elseif( isset($_POST['email']) && trim($_POST['email']) != '' 
            && isset($_POST['password']) && trim($_POST['password']) == '' 
            ) {
            $display_form = 'login';
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error',
                'content' => 'You must enter your password.'
                );
        }
        //
        // Verify the customer and create a session
        //
        elseif( isset($_POST['email']) && trim($_POST['email']) != '' 
            && isset($_POST['password']) && trim($_POST['password']) != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'auth');
            $rc = ciniki_customers_wng_auth($ciniki, $tnid, $request, trim($_POST['email']), sha1(trim($_POST['password'])));
            if( $rc['stat'] == 'locked' ) {
                if( isset($settings['account-lock-hours']) && $settings['account-lock-hours'] > 0 ) { 
                    $blocks[] = array(
                        'type' => 'msg', 
                        'level' => 'error', 
                        'content' => "Too many login attempts, your account has been locked for " 
                            . $settings['account-lock-hours'] . " hour" 
                            . ($settings['account-lock-hours'] > 1 ? 's' : '') 
                            . '.',
                        );
                } else {
                    $blocks[] = array(
                        'type' => 'msg', 
                        'level' => 'error', 
                        'msg' => "Too many login attempts, your account has been locked. Please contact us for help.",
                        );
                }
                $display_form = 'login';
            } 
            elseif( $rc['stat'] != 'ok' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "Unable to authenticate, please try again or "
                        . "click Forgot password to get a new one.",
                    );
                $display_form = 'login'; 
            } else {
                $display_form = 'no';

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
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.93', 'msg'=>'Unable to load account information', 'err'=>$rc['err']));
                        }
                    }
                }

                //
                // FIXME: Figure out what to do with email attached to multiple accounts
                //

                //
                // If multiple accounts, setup the redirect upon choosing an account
                //
                if( isset($request['session']['customers']) && count($request['session']['customers']) > 1 
                    && (!isset($settings['account-child-logins']) || $settings['account-child-logins'] == 'yes')
                    ) {
                    if( isset($settings['account-signin-redirect']) && ($settings['account-signin-redirect']) ) {
                        $request['session']['account_chooser_redirect'] = $settings['account-signin-redirect'];
                    } else {
                        $request['session']['account_chooser_redirect'] = '';
                    }
                } 
                
                //
                // Check for a returning url
                //
                elseif( isset($request['session']['login-return-url']) && $request['session']['login-return-url'] != '' ) {
                    if( preg_match("/\?/", $request['session']['login-return-url']) ) {
                        header("Location: " . $request['session']['login-return-url'] . '&auth-success');
                    } else {
                        header("Location: " . $request['session']['login-return-url'] . '?auth-success');
                    }
                    unset($request['session']['login-return-url']);
                    return array('stat'=>'exit');
                }
                //
                // Check for a redirect
                //
                elseif( isset($settings['account-signin-redirect']) ) {
                    if( $settings['account-signin-redirect'] == 'back' 
                        && isset($request['session']['login_referer']) && $request['session']['login_referer'] != '' 
                        ) {
                        header('Location: ' . $request['session']['login_referer']);
                        $request['session']['login_referer'] = '';
                        return array('stat'=>'exit');
                    }
                    if( $settings['account-signin-redirect'] != '' ) {
                        header('Location: ' . $request['ssl_domain_base_url'] . $settings['account-signin-redirect']);
                        return array('stat'=>'exit');
                    }
                }

                // No redirects, return ok for default page to show
                return array('stat'=>'authenticated');
            }
        }
    }
    
    //
    // Check if the create simple or phone-billing account form submitted
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'signup' ) {
        if( !isset($_POST['first']) || trim($_POST['first']) == '' ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter your first name.",
                );
            $display_form = 'signup';
        }
        elseif( !isset($_POST['last']) || trim($_POST['last']) == '' ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter your last name.",
                );
            $display_form = 'signup';
        } 
        elseif( !isset($_POST['signupemail']) || trim($_POST['signupemail']) == '' ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter your email address.",
                );
            $display_form = 'signup';
        } 
        elseif( !preg_match("/.+\@.+\..+/", trim($_POST['signupemail'])) ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid email address.",
                );
            $display_form = 'signup';
        } 
        elseif( $args['create-account'] == 'phone-billing' && (!isset($_POST['phone_number_1']) || trim($_POST['phone_number_1']) == '') ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid phone number.",
                );
            $display_form = 'signup';
        }
        elseif( $args['create-account'] == 'phone-billing' && (!isset($_POST['address1']) || trim($_POST['address1']) == '') ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid address.",
                );
            $display_form = 'signup';
        }
        elseif( $args['create-account'] == 'phone-billing' && (!isset($_POST['city']) || trim($_POST['city']) == '') ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid city.",
                );
            $display_form = 'signup';
        }
        elseif( $args['create-account'] == 'phone-billing' && (!isset($_POST['province']) || trim($_POST['province']) == '') ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid province or state.",
                );
            $display_form = 'signup';
        }
        elseif( $args['create-account'] == 'phone-billing' && (!isset($_POST['postal']) || trim($_POST['postal']) == '' || strlen(trim($_POST['postal'])) < 5) ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid postal or zip code.",
                );
            $display_form = 'signup';
        }
        elseif( $args['create-account'] == 'phone-billing' && (!isset($_POST['country']) || trim($_POST['country']) == '') ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid country.",
                );
            $display_form = 'signup';
        }
        elseif( !isset($_POST['signuppassword']) || trim($_POST['signuppassword']) == '' || strlen(trim($_POST['signuppassword'])) < 8 ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "Your password must contain 8 characters.",
                );
            $display_form = 'signup';
        }
        elseif( isset($settings['account-signup-confirm-password']) && $settings['account-signup-confirm-password'] == 'yes' 
            && (!isset($_POST['sconfirmpassword']) 
                || trim($_POST['sconfirmpassword']) == '' 
                || $_POST['sconfirmpassword'] != $_POST['signuppassword']
                )
            ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "Passwords do not match.",
                );
            $display_form = 'signup';
        }
        else {
            $details = array();
            if( $args['create-account'] == 'phone-billing' ) {
                if( isset($_POST['phone_number_1']) && $_POST['phone_number_1'] != '' ) {
                    $details['phone_number_1'] = trim($_POST['phone_number_1']);
                    $details['phone_label_1'] = trim($_POST['phone_label_1']);
                }
                if( isset($_POST['phone_number_2']) && $_POST['phone_number_2'] != '' ) {
                    $details['phone_number_2'] = trim($_POST['phone_number_2']);
                    $details['phone_label_2'] = trim($_POST['phone_label_2']);
                }
                if( isset($_POST['address1']) && $_POST['address1'] != '' ) {
                    $details['address1'] = trim($_POST['address1']);
                }
                if( isset($_POST['address2']) && $_POST['address2'] != '' ) {
                    $details['address2'] = trim($_POST['address2']);
                }
                if( isset($_POST['city']) && $_POST['city'] != '' ) {
                    $details['city'] = trim($_POST['city']);
                }
                if( isset($_POST['province']) && $_POST['province'] != '' ) {
                    $details['province'] = trim($_POST['province']);
                }
                if( isset($_POST['postal']) && $_POST['postal'] != '' ) {
                    $details['postal'] = trim($_POST['postal']);
                }
                if( isset($_POST['country']) && $_POST['country'] != '' ) {
                    $details['country'] = trim($_POST['country']);
                }
            }
            // Honeypot bots
/*            if( isset($_POST['signupemail2']) && $_POST['signupemail2'] != '' ) {
                error_log('Bot Signup Blocked: ' . $_POST['signupemail2']);
                header("Location: " . $_SERVER['REQUEST_URI'] . "?signup-success");
                return array('stat'=>'exit');
            }
            if( isset($_POST['signupemail']) && ($_POST['signupemail'] == 'estankov@yahoo.com') ) {
                error_log('Bot Signup Blocked: ' . $_POST['signupemail']);
                header("Location: " . $_SERVER['REQUEST_URI'] . "?signup-success");
                return array('stat'=>'exit');
            } */
            $display_form = 'signup';
            $url = $request['ssl_domain_base_url'] . '/account/signup';
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'signupRequestProcess');
//            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'signupRequestProcess');
//            $rc = ciniki_customers_wng_signupRequestProcess($ciniki, $tnid, $request, array(    
            $rc = ciniki_wng_signupRequestProcess($ciniki, $tnid, $request, [    
                'first' => trim($_POST['first']),
                'last' => trim($_POST['last']),
                'email' => trim($_POST['signupemail']),
                'email2' => isset($_POST['signupemail2']) ? $_POST['signupemail2'] : null,
                'password' => trim($_POST['signuppassword']),
                'details' => $details,
                'return-url' => isset($args['return-url']) ? $args['return-url'] : '',
                'url' => $url,
                ]);
            if( $rc['stat'] == 'accountexists' ) {
                //
                // Signing up with existing email, send forgot password instead
                //
                $url = $request['ssl_domain_base_url'] . '/account/passwordreset';
                ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'passwordRequestReset');
                $rc = ciniki_customers_wng_passwordRequestReset($ciniki, $tnid, $request, $_POST['signupemail'], $url);
                if( $rc['stat'] == 'ok' ) {
                    header("Location: " . $_SERVER['REQUEST_URI'] . "?forgot-success");
                    return array('stat'=>'exit');
                }
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "We are unable to create your account at this time, please contact us for assistance.",
                    );
                $display_form = 'no';
            } 
            elseif( $rc['stat'] == 'pending' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "You have a signup pending, please check your email and spam folders.",
                    );
                $display_form = 'no';
            }
            elseif( $rc['stat'] == 'honeypot' ) {
                header("Location: " . $_SERVER['REQUEST_URI'] . "?signup-success");
                return array('stat'=>'exit');
            }
            elseif( $rc['stat'] == 'blocked' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "We are unable to create your account, please contact us for assistance.",
                    );
                $display_form = 'no';
            }
            elseif( $rc['stat'] == 'notactive' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "We are unable to create your account at this time, please contact us for assistance.",
                    );
                $display_form = 'no';
            }
            elseif( $rc['stat'] != 'ok' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "We are unable to create your account at this time, please contact us for assistance.",
                    );
                $display_form = 'signup';
            } else {
                if( preg_match("/\?/", $_SERVER['REQUEST_URI']) ) {
                    header("Location: " . $_SERVER['REQUEST_URI'] . "&signup-success");
                } else {
                    header("Location: " . $_SERVER['REQUEST_URI'] . "?signup-success");
                }
                return array('stat'=>'exit');
            }
        }
    }
    elseif( isset($_GET['signup-success']) ) {
        $blocks[] = array(
            'type' => 'msg', 
            'level' => 'success', 
            'content' => "A verification link has been sent to your email. Please also check your spam/junk folder.",
            );

        $display_form = 'no';
    }
    //
    // Check for a forgot password form submit
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'forgot' ) {
        // Set the forgot password notification
        if( isset($_POST['email']) && $_POST['email'] != '' ) {
            $url = $request['ssl_domain_base_url'] . '/account/passwordreset';
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'passwordRequestReset');
            $rc = ciniki_customers_wng_passwordRequestReset($ciniki, $tnid, $request, $_POST['email'], $url);
            if( $rc['stat'] != 'ok' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "You must enter a valid email address to get a new password.",
                    );
                $display_form = 'forgot';
            } else {
                header("Location: " . $_SERVER['REQUEST_URI'] . "?forgot-success");
                return array('stat'=>'exit');
            }
        } else {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "You must enter a valid email address to get a new password.",
                );
            $display_form = 'forgot';
        }
    }

    elseif( isset($_GET['forgot-success']) ) {
        $blocks[] = array(
            'type' => 'msg', 
            'level' => 'success', 
            'content' => "A link has been sent to your email to get a new password. Please also check your spam/junk folder.",
            );
        $display_form = 'no';
    }

    //
    // Check if a reset password was submitted, from a forgot password link
    //
    elseif( isset($_POST['action']) && $_POST['action'] == 'passwordreset' ) {
        if( !isset($_POST['newpassword']) || strlen($_POST['newpassword']) < 8 ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "Your new password must be at least 8 characters long.",
                );
            $display_form = 'reset';
        } 
        elseif( !isset($_POST['email']) || $_POST['email'] == '' ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "Invalid email address.",
                );
            $display_form = 'reset';
        } 
        elseif( !isset($_POST['temppassword']) || $_POST['temppassword'] == '' ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content' => "Invalid link.",
                );
            $display_form = 'reset';
        } 
        else {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'changeTempPassword');
            $rc = ciniki_customers_wng_changeTempPassword($ciniki, $tnid, $request, 
                $_POST['email'], $_POST['temppassword'], $_POST['newpassword']);
            if( $rc['stat'] != 'ok' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content' => "Invalid password reset link, please try again.",
                    );
                error_log('ERR PWD RESET: ' . print_r($rc['err']['code'], true));
                $display_form = 'reset';
            } else {
                if( isset($request['session']['login-return-url']) && $request['session']['login-return-url'] != '' ) {
                    header("Location: " . $request['session']['login-return-url'] . '?reset-pwd-success');
                    unset($request['session']['login-return-url']);
                    return array('stat'=>'exit');
                }
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'success', 
                    'content' => "Your password has been set, you may now sign in.",
                    );
                $display_form = 'login';
            }
        }
    }
    elseif( isset($_GET['reset-pwd-success']) ) {
        if( isset($request['session']['login-return-url']) ) {
            $request['session']['login-return-url'] = '';
        }
        if( isset($request['session']['loginform']) ) {
            $request['session']['loginform'] = '';
        }

        $blocks[] = array(
            'type' => 'msg', 
            'level' => 'success', 
            'content' => "Your password has been reset, you can now login.",
            );
        $display_form = 'login';
    }

    if( $display_form == 'login' || $display_form == 'forgot' || $display_form == 'signup' ) {
        //
        // Set a session variable, to test for cookies being turned on
        //
        if( isset($settings['account-signin-redirect']) && $settings['account-signin-redirect'] == 'back' ) {
            if( (!isset($request['session']['login_referer']) || $request['session']['login_referer'] == '') 
                && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '' 
                ) {
                $request['session']['login_referer'] = $_SERVER['HTTP_REFERER'];
            }
        }
        if( isset($args['return-url']) && $args['return-url'] != '' ) {
            $request['session']['login-return-url'] = $args['return-url'];
        }
        $request['session']['loginform'] = 'yes';

        if( isset($settings['account-intro-alert']) && $settings['account-intro-alert'] != '' ) {
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'class' => 'aligncenter',
                'content' => $settings['account-intro-alert'],
                );
        }

        $block = array(
            'title' => 'Sign In',
            'type' => 'accountlogin',
            'create-account' => isset($args['create-account']) ? $args['create-account'] : '',
            'first' => isset($_POST['first']) ? trim($_POST['first']) : '',
            'last' => isset($_POST['last']) ? trim($_POST['last']) : '',
            'email' => isset($_POST['email']) ? $_POST['email'] : (isset($_POST['signupemail']) ? $_POST['signupemail'] : ''),
            'password' => isset($_POST['signuppassword']) ? trim($_POST['signuppassword']) : '',
            'confirmpassword' => isset($_POST['sconfirmpassword']) ? trim($_POST['sconfirmpassword']) : '',
            'phone_label_1' => isset($_POST['phone_label_1']) ? trim($_POST['phone_label_1']) : '',
            'phone_number_1' => isset($_POST['phone_number_1']) ? trim($_POST['phone_number_1']) : '',
            'phone_label_2' => isset($_POST['phone_label_2']) ? trim($_POST['phone_label_2']) : '',
            'phone_number_2' => isset($_POST['phone_number_2']) ? trim($_POST['phone_number_2']) : '',
            'address1' => isset($_POST['address1']) ? trim($_POST['address1']) : '',
            'address2' => isset($_POST['address2']) ? trim($_POST['address2']) : '',
            'city' => isset($_POST['city']) ? trim($_POST['city']) : '',
            'province' => isset($_POST['province']) ? trim($_POST['province']) : '',
            'postal' => isset($_POST['postal']) ? trim($_POST['postal']) : '',
            'country' => isset($_POST['country']) ? trim($_POST['country']) : 'Canada',
            'startform' => $display_form,
            );
        //
        // Check if allow change/reset password
        //
        if( !isset($settings['account-password-change']) || $settings['account-password-change'] == 'yes' ) {
            $block['forgot'] = 'yes';
        }

        if( isset($settings['account-forgot-link-text']) && $settings['account-forgot-link-text'] != '' ) {
            $block['forgot-link-text'] = $settings['account-forgot-link-text'];
        }
        if( isset($settings['account-create-account-text']) && $settings['account-create-account-text'] != '' ) {
            $block['create-account-text'] = $settings['account-create-account-text'];
        }
        if( isset($settings['account-signin-text']) && $settings['account-signin-text'] != '' ) {
            $block['title'] = $settings['account-signin-text'];
        }
        if( isset($settings['account-signup-confirm-password']) && $settings['account-signup-confirm-password'] != '' ) {
            $block['signup-confirm-password'] = $settings['account-signup-confirm-password'];
        }

        $blocks[] = $block;
    }

    //
    // Check if this page was directed to from the recovery password email link
    // The second argument should be the customer uuid
    // The third argument should be the temp_password
    //
    elseif( $display_form == 'reset' 
        || (isset($request['uri_split'][0]) 
            && $request['uri_split'][0] == 'passwordreset' 
            && isset($_GET['email']) && $_GET['email'] != ''
            && isset($_GET['pwd']) && $_GET['pwd'] != '' 
            )
        ) {

        $request['breadcrumbs'][] = array(
            'name' => 'Reset Password', 
            'url' => $request['base_url'] . '/account',
            );

        $block = array(
            'type' => 'accountpwdreset',
            'class' => 'limit-width limit-width-30',
            'title' => 'Reset Password',
            'email' => isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : ''),
            'temppassword' => isset($_GET['pwd']) ? $_GET['pwd'] : (isset($_POST['pwd']) ? $_POST['pwd'] : ''),
            'message' => 'Please enter a new password.  It must be at least 8 characters long.',
            'action' => $request['ssl_domain_base_url'] . '/account',
            );

        $blocks[] = $block;
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
