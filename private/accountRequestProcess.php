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
    $settings = isset($request['site']['settings']) ? $request['site']['settings'] : array();

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
    // Check if there was a specific account page requested
    //
    $return_url = $request['ssl_domain_base_url'] . '/account';
    if( isset($request['uri_split'][($request['cur_uri_pos']+1)]) 
        || (isset($request['query_string']) && $request['query_string'] != '') 
        ) {
        $return_url = $request['ssl_domain_base_url'] . '/' . implode('/', $request['uri_split']);
        if( isset($request['query_string']) && $request['query_string'] != '' ) {
            $return_url .= '?'. $request['query_string'];
        }
    }

    //
    // Verify the user is logged in, otherwise the accountLoginProcess will return the
    // login form.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountLoginProcess');
    $rc = ciniki_wng_accountLoginProcess($ciniki, $tnid, $request, array(
        'return-url' => $return_url,
        'create-account' => isset($settings['account-create-type']) ? $settings['account-create-type'] : '',
        ));
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
        if( isset($settings["account-menu-{$pkg}-{$mod}"]) && $settings["account-menu-{$pkg}-{$mod}"] == 'off' ) {
            continue;
        }
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountMenuItems');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, $request, array(
                'base_url' => $request['base_url'] . '/account',
                'selected' => (isset($request['uri_split'][1]) ? $request['uri_split'][1] : '')
                    . (isset($request['uri_split'][2]) ? '/' . $request['uri_split'][2] : ''),
                ));
            if( $rc['stat'] == 'ok' && isset($rc['items']) ) {
                //
                // Check the returned items to see if any override other items
                // This allows customer modules to override module account menu items to display custom html
                //
                foreach($rc['items'] as $new_id => $new_item) {
                    foreach($items as $iid => $item) {
                        if( $item['title'] == $new_item['title'] ) {
                            if( isset($new_item['override']) && $new_item['override'] == 'yes' ) {
                                $items[$iid] = $new_item;
                                unset($rc['items'][$new_id]);
                            } elseif( isset($item['override']) && $item['override'] == 'yes' ) {
                                // Existing item has override, ignore new item
                                unset($rc['items'][$new_id]);
                            }
                        }
                    }
                }
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
        'menu-label' => 'My Account',
        'menu-id' => 'account',
        'main-menu' => $items,
        'toggle-em' => (isset($settings['account-menu-toggle-em']) && $settings['account-menu-toggle-em'] != '' ? $settings['account-menu-toggle-em'] : '40'),
        'dropdown' => 'both',
        'hamburger-menu' =>  $items,
        );
 
    //
    // Find the menu item to handle the request
    //
    if( isset($request['uri_split'][1]) ) {
        $item_permalink = $request['base_url'] . '/account/' . $request['uri_split'][1];
        for($i = 2; $i < count($request['uri_split']); $i++) {
            if( isset($request['uri_split'][$i]) ) {
                $item_permalink .= '/' . $request['uri_split'][$i];
            }
        }

        $found = 'no';
        foreach($items as $item) {
            //
            // Check sub/dropdown menu items
            //
            if( isset($item['items']) ) {
                foreach($item['items'] as $itm) {
                    if( isset($itm['url']) 
                        && strncmp($item_permalink, $itm['url'], strlen($itm['url'])) == 0 
                        && isset($itm['ref']) 
                        ) {
                        list($pkg, $mod, $method) = explode('.', $itm['ref']);
                        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'accountRequestProcess');
                        if( $rc['stat'] == 'ok' ) {
                            $fn = $rc['function_call'];
                            $rc = $fn($ciniki, $tnid, $request, $itm);
                            if( $rc['stat'] == 'ok' && isset($rc['blocks']) ) {
                                foreach($rc['blocks'] as $block) {
                                    $blocks[] = $block;
                                }
                            }
                        }
                        //
                        // Found the item to be processed, ignore the remaining menu items
                        //
                        $found = 'yes';
                        break;
                    }
                }
            }
            //
            // Check main item
            //
            if( $found == 'no' 
                && isset($item['url']) 
                && strncmp($item_permalink, $item['url'], strlen($item['url'])) == 0 
                && isset($item['ref']) 
                ) {
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
//        $blocks[] = array(
//            'type' => 'text',
//            'class' => 'limit-width limit-width-40',
//            'content' => "<br/>Choose from the menu above to update your account.<br/><br/><br/>",
//            );
        
        $msg = '';
        if( isset($request['session']['customer']['first']) && $request['session']['customer']['first'] != '' ) {
            $msg .= 'Hi ' . $request['session']['customer']['first'] . ', <br/><br/>';
        }
        $msg .= "Choose from the menu above to update your account.";

        $blocks[] = array(
            'type' => 'msg',
            'level' => 'neutral',
            'class' => 'limit-width limit-width-40',
            'content' => $msg,
            );

    }

//    $blocks[] = array(
//        'type' => 'html',
//        'html' => "<pre>" . print_r($items, true) . "</pre>",
//        );
    
    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
