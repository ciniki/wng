<?php
//
// Description
// -----------
// Load the list of sections from the other modules
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_sectionsAvailable(&$ciniki, $tnid, $site_id) {

    //
    // Check each module for blocks available
    //
    $sections = array();
    foreach($ciniki['tenant']['modules'] as $module) {
        //
        // Check if the module has the file wng/sections.php
        //
        $rc = ciniki_core_loadMethod($ciniki, $module['package'], $module['module'], 'wng', 'sections');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, array('site_id'=>$site_id));
            if( $rc['stat'] == 'ok' ) {
                $sections = array_merge($sections, $rc['sections']);
            }
        }
    }

    //
    // Sort the blocks
    //
    uasort($sections, function($a, $b) {
        if( $a['module'] == $b['module'] ) {
            return strcasecmp($a['name'], $b['name']);
        }
        return strcasecmp($a['module'], $b['module']);
        });

    return array('stat'=>'ok', 'sections'=>$sections);
}
?>
