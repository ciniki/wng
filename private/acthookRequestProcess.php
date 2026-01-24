<?php
//
// Description
// -----------
// Process the account or action hook callback. 
// Designed to allow email links to perform simple actions on account with requiring login.
// Developed first for use with musicfestivals provincials management.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_acthookRequestProcess(&$ciniki, $tnid, &$request) {

    //
    // Add breadcrumbs update
    //
    $request['breadcrumbs'][] = [
        'page_id' => 'account',
        'page-class' => 'page-account',
        'title' => 'Account',
        'url' => $request['ssl_domain_base_url'] . '/ahk',
        ];

    $blocks = [];
    $settings = isset($request['site']['settings']) ? $request['site']['settings'] : [];

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
    // Gather the hooks available 
    //
    $allhooks = array();
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        if( isset($settings["account-menu-{$pkg}-{$mod}"]) && $settings["account-menu-{$pkg}-{$mod}"] == 'off' ) {
            continue;
        }
        // 
        // acthooks must return list of hooks available for module in format hooks[ref][hook] = [fn => ].
        //
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'wng', 'acthooks');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, $request, ['base_url' => $request['base_url'] . '/ahk']);
            if( isset($rc['hooks']) ) {
                foreach($rc['hooks'] as $ref => $hooks) {
                    foreach($hooks as $hook => $details) {
                        $details['pkg'] = $pkg;
                        $details['mod'] = $mod;
                        $allhooks[$ref][$hook] = $details;
                    }
                }
            }
        }
    }

    //
    // Process the request
    //
    if( isset($request['uri_split'][2]) ) {
        $ref = $request['uri_split'][1];
        $hook = $request['uri_split'][2];
        if( isset($allhooks[$ref][$hook]) ) {
            $hk = $allhooks[$ref][$hook];
            $rc = ciniki_core_loadMethod($ciniki, $hk['pkg'], $hk['mod'], 'wng', $hk['fn']);
            if( $rc['stat'] == 'ok' ) { 
                $fn = $rc['function_call'];
                $request['cur_uri_pos']+=2;
                $rc = $fn($ciniki, $tnid, $request);
                if( isset($rc['blocks']) ) {
                    foreach($rc['blocks'] as $block) {  
                        $blocks[] = $block;
                    }
                }
            }
        }
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
