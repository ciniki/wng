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
function ciniki_wng_processors_accountbuttons(&$ciniki, $tnid, &$request, $section) {

    $s = isset($section['settings']) ? $section['settings'] : array();

    if( isset($request['session']['customer']['id']) && $request['session']['customer']['id'] > 0 ) {
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
        if( isset($request['session']['cart']['num_items']) && $request['session']['cart']['num_items'] > 0 ) {
            $num_items = ' (' . $request['session']['cart']['num_items'] . ')';
        }
        array_unshift($block['data'], array(
            'label' => (isset($s['cart-label']) && $s['cart-label'] != '' ? $s['cart-label'] : 'Cart') . $num_items,
            'url' => $request['ssl_domain_base_url'] . '/cart',
            ));
    }

    return array('stat'=>'ok', 'blocks'=>array($block));
}
?>
