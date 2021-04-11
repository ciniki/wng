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
// Returns
// ---------
// 
function ciniki_wng_processors_headermenu(&$ciniki, $tnid, &$request, $section) {

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();
  
    //
    // Setup the account buttons blocks, if requested
    //
    if( ciniki_core_checkModuleActive($ciniki, 'ciniki.customers')
        && isset($s['account-buttons']) && $s['account-buttons'] == 'yes' 
        ) {
        if( isset($ciniki['customer']['id']) && $ciniki['customer']['id'] > 0 ) {
            $block = array(
                'type' => 'accountbuttons',
                'data' => array(
                    array(
                        'label' => (isset($s['account-label']) && $s['account-label'] != '' ? $s['account-label'] : 'Account'),
                        'url' => $request['ssl_domain_base_url'] . '/account',
                        ),
                    array(
                        'label' => (isset($s['logout-label']) && $s['logout-label'] != '' ? $s['logout-label'] : 'Logout'),
                        'url' => $request['ssl_domain_base_url'] . '/account/logout',
                        ),
                    ),
                );
        } else {
            $block = array(
                'type' => 'accountbuttons',
                'data' => array(
                    array(
                        'label' => (isset($s['signin-label']) && $s['signin-label'] != '' ? $s['signin-label'] : 'Sign In'),
                        'url' => $request['ssl_domain_base_url'] . '/account',
                        ),
                    ),
                );
        }
        
        //
        // Check if cart enabled
        //
        if( isset($request['site']['settings']['cart-active']) && $request['site']['settings']['cart-active'] == 'yes' ) {
            $num_items = '';
            if( isset($ciniki['session']['cart']['num_items']) && $ciniki['session']['cart']['num_items'] > 0 ) {
                $num_items = ' (' . $ciniki['session']['cart']['num_items'] . ')';
            }
            array_unshift($block['data'], array(
                'label' => (isset($s['cart-label']) && $s['cart-label'] != '' ? $s['cart-label'] : 'Cart') . $num_items,
                'url' => $request['ssl_domain_base_url'] . '/cart',
                ));
        }
        //
        // Check if signin should be toggled depending on size along with main menu
        if( isset($s['account-toggle-hide']) && $s['account-toggle-hide'] == 'yes' && isset($s['toggle-em']) ) {
            $block['toggle-em'] = isset($s['toggle-em']) ? $s['toggle-em'] : '';
        }
        $blocks[] = $block;
    }

    $mainmenu = array();
    if( isset($request['site']['headermenu']) ) {
        foreach($request['site']['headermenu'] as $page_id) {
            if( isset($request['site']['pages'][$page_id]) ) {
                $page = $request['site']['pages'][$page_id];
                $item = array(
                    'title' => $page['title'],
                    'selected' => 'no',
                    'url' => $request['base_url'] . $page['path'],
                    );
                if( $page_id == $request['site']['homepage_id'] 
                    && (!isset($request['uri_split'][0]) || $request['uri_split'][0] == '')
                    ) {
                    $item['selected'] = 'yes';
                }
                elseif( isset($request['uri_split'][0]) && $request['uri_split'][0] == $page['permalink'] ) {
                    $item['selected'] = 'yes';
                }
                if( $page_id == $request['site']['homepage_id'] && isset($s['hide-home']) && $s['hide-home'] == 'yes' ) {
                    $item['hidden'] = 'yes';
                }
                $mainmenu[] = $item;
            }
        }
    }

    //
    // Generate the dropdown nav for hamburger
    //
    $hamburgermenu = array();
    if( isset($request['site']['headermenu']) ) {
        foreach($request['site']['headermenu'] as $page_id) {
            if( isset($request['site']['pages'][$page_id]) ) {
                $page = $request['site']['pages'][$page_id];
                $item = array(
                    'title' => $page['title'],
                    'selected' => 'no',
                    'url' => $request['base_url'] . $page['path'],
                    );
                if( $page_id == $request['site']['homepage_id'] 
                    && (!isset($request['uri_split'][0]) || $request['uri_split'][0] == '')
                    ) {
                    $item['selected'] = 'yes';
                }
                elseif( isset($request['uri_split'][0]) && $request['uri_split'][0] == $page['permalink'] ) {
                    $item['selected'] = 'yes';
                }
                if( $page_id == $request['site']['homepage_id'] && isset($s['hide-home']) && $s['hide-home'] == 'yes' ) {
                    $item['hidden'] = 'yes';
                }
                $hamburgermenu[] = $item;
            }
        }

        if( ciniki_core_checkModuleActive($ciniki, 'ciniki.customers')
            && isset($s['account-buttons']) && $s['account-buttons'] == 'yes' 
            ) {
            //
            // Check if cart enabled
            //
            if( isset($request['site']['settings']['cart-active']) && $request['site']['settings']['cart-active'] == 'yes' ) {
                $num_items = '';
                if( isset($ciniki['session']['cart']['num_items']) && $ciniki['session']['cart']['num_items'] > 0 ) {
                    $num_items = ' (' . $ciniki['session']['cart']['num_items'] . ')';
                }
                $hamburgermenu[] = array(
                    'title' => (isset($s['cart-label']) && $s['cart-label'] != '' ? $s['cart-label'] : 'Cart') . $num_items,
                    'selected' => (isset($request['uri_split'][0]) && $request['uri_split'][0] == 'cart' ? 'yes' : 'no'),
                    'url' => $request['ssl_domain_base_url'] . '/cart',
                    );
            }
            //
            // Check if customer logged in
            //
            if( isset($ciniki['customer']['id']) && $ciniki['customer']['id'] > 0 ) {
                $hamburgermenu[] = array(
                    'title' => (isset($s['account-label']) && $s['account-label'] != '' ? $s['account-label'] : 'Account'),
                    'selected' => (isset($request['uri_split'][0]) && $request['uri_split'][0] == 'account' ? 'yes' : 'no'),
                    'url' => $request['ssl_domain_base_url'] . '/account',
                    );
                $hamburgermenu[] = array(
                    'title' => (isset($s['logout-label']) && $s['logout-label'] != '' ? $s['logout-label'] : 'Logout'),
                    'selected' => 'no',
                    'url' => $request['ssl_domain_base_url'] . '/account/logout',
                    );
            } else {
                $hamburgermenu[] = array(
                    'title' => (isset($s['signin-label']) && $s['signin-label'] != '' ? $s['signin-label'] : 'Sign In'),
                    'selected' => 'no',
                    'url' => $request['ssl_domain_base_url'] . '/account',
                    );
            }
        }
    }

    $blocks[] = array(
        'type' => 'imagemenu',
        'image-id' => isset($s['image-id']) ? $s['image-id'] : $s['image-id'],
        'main-menu' =>  $mainmenu,
        'toggle-em' => isset($s['toggle-em']) ? $s['toggle-em'] : '',
        'hamburger-menu' =>  $hamburgermenu,
        );
//    $blocks[] = array('type'=>'content', 'content'=>'<pre>' . print_r($request['site'], true) . '</pre>');
    

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
