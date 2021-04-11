<?php
//
// Description
// -----------
// This function will return the b
//
// Arguments
// ---------
// ciniki:
// tnid:            The ID of the tenant.
// args:            The possible arguments for.
//
//
// Returns
// -------
//
function ciniki_wng_wng_process(&$ciniki, $tnid, &$request, $section) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.wng']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.60', 'msg'=>"Content not available."));
    }

    //
    // Check to make sure the report is specified
    //
    if( !isset($section['ref']) || !isset($section['settings']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.61', 'msg'=>"No section specified."));
    }
    //
    // Return the list of reports for the tenant
    //
    $s = explode('.', $section['ref']);
    if( isset($s[2]) ) {
        $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'processors', $s[2]);
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            return $fn($ciniki, $tnid, $request, $section);
        }
    } 

    return array('stat'=>'ok');
}
?>
