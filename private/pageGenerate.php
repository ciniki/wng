<?php
//
// Description
// -----------
// Generate the HTML for a page from the blocks created after processing the sections.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_pageGenerate(&$ciniki, $tnid, &$request) {

    $content = '';

    //
    // Generate the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageHeaderGenerate');
    $rc = ciniki_wng_pageHeaderGenerate($ciniki, $tnid, $request);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $content .= isset($rc['content']) ? $rc['content'] : '';
   
    //
    // Start the page content
    //
    $content .= '<div id="page-content">';

    //
    // Generate the blocks
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'blocksGenerate');
    $rc = ciniki_wng_blocksGenerate($ciniki, $tnid, $request, $request['response']['blocks']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $content .= $rc['content'];
   
    //
    // Close the page content
    //
    $content .= '</div>';

    //
    // Generate the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageFooterGenerate');
    $rc = ciniki_wng_pageFooterGenerate($ciniki, $tnid, $request);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $content .= isset($rc['content']) ? $rc['content'] : '';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
