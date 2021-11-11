<?php
//
// Description
// -----------
// This function returns the settings for the module and the main menu items and settings menu items
//
// Arguments
// ---------
// ciniki:
// tnid:
// args: The arguments for the hook
//
// Returns
// -------
//
function ciniki_wng_hooks_uiSettings(&$ciniki, $tnid, $args) {
    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'menu_items'=>array(), 'settings_menu_items'=>array());

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['tenant']['modules']['ciniki.wng'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        //
        // Get the list of active sites
        //
        $strsql = "SELECT id, name "
            . "FROM ciniki_wng_sites "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND status < 60 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'site');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.7', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
        }
        if( isset($rc['site']) ) { 
            $menu_item = array(
                'priority'=>900,
                'label'=>$rc['site']['name'],
                'edit'=>array('app'=>'ciniki.wng.main', 'args'=>array('site_id'=>$rc['site']['id'])),
                );
            $rsp['menu_items'][] = $menu_item;
        } else {
            foreach($rc['rows'] as $site) {
                $menu_item = array(
                    'priority'=>900,
                    'label'=>$site['name'],
                    'edit'=>array('app'=>'ciniki.wng.main', 'args'=>array('site_id'=>$site['id'])),
                    );
                $rsp['menu_items'][] = $menu_item;
            }
        }
    }

    return $rsp;
}
?>
