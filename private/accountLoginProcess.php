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

    if( isset($_POST['action']) && $_POST['action'] == 'signin' ) {
        //
        // Check the referrer and that cookies are enabled
        //
        if( !isset($request['session']['loginform']) ) {
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

        //
        // Verify the customer and create a session
        //
        elseif( isset($_POST['email']) && $_POST['email'] != '' 
            && isset($_POST['password']) && $_POST['password'] != '' 
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'wng', 'auth');
            $rc = ciniki_customers_wng_auth($ciniki, $tnid, $request, $_POST['email'], $_POST['password']);
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
                        . "click Forgot your password to get a new one",
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
            'content' => "A link has been sent to your email to get a new password.",
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

    if( $display_form == 'login' || $display_form == 'forgot' ) {
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


        $block = array(
            'title' => 'Sign In',
            'type' => 'accountlogin',
            'email' => isset($_POST['email']) ? $_POST['email'] : '',
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
