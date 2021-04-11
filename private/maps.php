<?php
//
// Description
// -----------
// This function returns the int to text mappings for the module.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wng_maps(&$ciniki) {
    //
    // Build the maps object
    //
    $maps = array();
    $maps['site'] = array(
        'status' => array(
            '5' => 'Development',
            '10' => 'Active',
            '90' => 'Archive',
        ),
    );
    //
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
