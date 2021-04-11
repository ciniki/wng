<?php
//
// Description
// -----------
// This function will call the section processor in the sections module.
//
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_sectionRequestProcess(&$ciniki, $tnid, &$request, $section) {

    //
    // Call the processor for this sections
    //
    $s = explode('.', $section['ref']);
    if( isset($s[1]) ) {
        $rc = ciniki_core_loadMethod($ciniki, $s[0], $s[1], 'wng', 'process');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            return $fn($ciniki, $tnid, $request, $section);
        }
    }

    return array('stat'=>'ok');
}
?>
