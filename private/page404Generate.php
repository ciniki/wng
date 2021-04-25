<?php
//
// Description
// -----------
// This function will generate the 404 error page
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
//
function ciniki_wng_page404Generate($ciniki, $tnid, $request, $errors) {

    $content = '';

    //
    // Add the header
    //
    header("Status: 404 Not Found", true, 404);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageHeaderGenerate');
    $rc = ciniki_wng_pageHeaderGenerate($ciniki, $tnid, $request, array());
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    //
    // Generate page content
    //
    $content .= "<div class='block-text'>\n";
    $content .= "<div class='wrap'>\n";
    $content .= "<div class='content'>\n";
    $content .= "<h1 class='entry-title'>Unable to find page</h1>";
    if( $errors != null && isset($errors['err']['msg']) ) {
        $content .= "<p>" . $errors['err']['msg'] . "</p>";
        $request['error_codes_msg'] = 'err:' . $errors['err']['code'];
        // Check for nested errors
        if( isset($errors['err']['err']) ) {
            $err = $errors['err'];
            while( isset($err['err']) ) {
                $request['error_codes_msg'] .= ',' . $err['err']['code'];
                $err = $err['err'];
            }
        }
    } else {
        $content .= "<p>Sorry, but we are unable to find the page you requested.</p>";
    }
    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div>";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageFooterGenerate');
    $rc = ciniki_wng_pageFooterGenerate($ciniki, $tnid, $request);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    return array('stat'=>'ok', 'content'=>$content);
}
?>
