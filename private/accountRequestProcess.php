<?php
//
// Description
// -----------
// Process the account page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_accountRequestProcess(&$ciniki, $tnid, &$request) {

    //
    // Add breadcrumbs update
    //
    $request['breadcrumbs'][] = array(
        'page_id' => 'account',
        'page-class' => 'page-account',
        'title' => 'Account',
        'url' => $request['ssl_domain_base_url'] . '/account',
        );

    $blocks = array();

    //
    // Set no caching
    //
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
    header("Cache-Control: no-store, no-cache, must-revalidate"); 
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

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
    if( (!isset($ciniki['config']['ciniki.core']['ssl']) || $ciniki['config']['ciniki.core']['ssl'] != 'off')
        && (!isset($_SERVER['HTTP_CLUSTER_HTTPS']) || $_SERVER['HTTP_CLUSTER_HTTPS'] != 'on')
        && (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')
        && (!isset($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] != '443' ) 
        ) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        return array('stat'=>'exit');
    }


    //
    // Check if logout was requested
    //
    if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'logout' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountLogoutProcess');
        //
        // If required, the following can be added so a timeout can be triggered by javascript setTimeout
        //
        if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'timeout' ) {
            return ciniki_wng_accountLogoutProcess($ciniki, $tnid, $request, 'yes');
        }
        return ciniki_wng_accountLogoutProcess($ciniki, $tnid, $request, 'no');
    }

    //
    // Verify the user is logged in, otherwise the accountLoginProcess will return the
    // login form.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountLoginProcess');
    $rc = ciniki_wng_accountLoginProcess($ciniki, $tnid, $request);
    if( $rc['stat'] != 'authenticated' ) {
        return $rc;
    }

    //
    // NOTE: At this point the customer is considered logged in
    //

    //
    // Check if a timeout is specified
    //
/*    if( isset($settings['account-timeout']) && $settings['account-timeout'] > 0 ) {
        $request['response']['js'] .= 'setInterval(function(){'
            . 'window.location.href="' . $request['ssl_domain_base_url'] . '/account/logout/timeout";'
            . '},' 
            . ($settings['account-timeout']*60000) 
            . ');';
    } */

    //
    // Check if there was a switch of customer (parent switching between child accounts)
    // This is a special case because of the redirects
    //
    if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'switch'
        && isset($request['uri_split'][2]) && $request['uri_split'][2] != '' 
        && isset($request['session']['customers'])
        && isset($request['session']['customers'][$request['uri_split'][2]])
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountSwitchProcess');
        return ciniki_wng_accountSwitchProcess($ciniki, $tnid, $request, $request['uri_split'][2]);
    }

//    print "<pre>" . print_r($request, true) . "</pre>";
//    print "<pre>" . print_r($ciniki['tenant'], true) . "</pre>";
//    exit;

    //
    // Gather the account menu items
    //
    $items = array();
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountMenuItems');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, $request, array(
                'base_url' => $request['base_url'] . '/account',
                ));
            if( $rc['stat'] == 'ok' && isset($rc['items']) ) {
                $items = array_merge($items, $rc['items']);
            }
        }
    }

    //
    // Sort the menu items by priority
    //
    usort($items, function($a, $b) {
        if( $a['priority'] == $b['priority'] ) {
            return 0;
        }
        // Sort so largest priority is top of list or first menu item
        return ($a['priority'] < $b['priority'])?1:-1;
    });

    $blocks[] = array(
        'type' => 'imagemenu',
        'class' => 'account-menu',
        'main-menu' => $items,
        'toggle-em' => 'custom',
        );

    //
    // Find the menu item to handle the request
    //
    if( isset($request['uri_split'][1]) ) {
        $item_permalink = $request['base_url'] . '/account/' . $request['uri_split'][1];
        foreach($items as $item) {
            if( strncmp($item_permalink, $item['url'], strlen($item_permalink)) == 0 && isset($item['ref']) ) {
                list($pkg, $mod, $method) = explode('.', $item['ref']);
                $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountRequestProcess');
                if( $rc['stat'] == 'ok' ) {
                    $fn = $rc['function_call'];
                    $rc = $fn($ciniki, $tnid, $request, $item);
                    if( $rc['stat'] == 'ok' && isset($rc['blocks']) ) {
                        foreach($rc['blocks'] as $block) {
                            $blocks[] = $block;
                        }
                    }
                }
                break;
            }
        }
    } 

    //
    // Display default account page
    //
    else {
        $blocks[] = array(
            'type' => 'content',
            'content' => "</br><center>Choose from the menu above to update your account.</center><br/><br/><br/>",
            );

    }

//    $blocks[] = array(
//        'type' => 'html',
//        'html' => "<pre>" . print_r($items, true) . "</pre>",
//        );
    
    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
