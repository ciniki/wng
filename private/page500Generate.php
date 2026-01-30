<?php
//
// Description
// -----------
// This function will generate an error page when there was a problem processing the request.  It should
// appear in the customers website with header/footer.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_page500Generate(&$ciniki, $tnid, $request, $errors) {

    $content = '';

    //
    // Add the header
    //
    header("Status: 500 Internal Server Error", true, 500);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageHeaderGenerate');
    $rc = ciniki_wng_pageHeaderGenerate($ciniki, $tnid, $request);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $content .= $rc['content'];

    $content .= "<div id='page-content'>\n";

    $content .= "<div class='block-title'>\n";
    $content .= "<div class='wrap'>\n";
    $content .= "<div class='content'>\n";
    $content .= "<h1 class='entry-title'>We seem to have hit a snag</h1>";
    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div>";
    $content .= "<div class='block-msg error'>\n";
    $content .= "<div class='wrap'>\n";
    $content .= "<div class='content'>\n";
    $content .= "<div class='msg'><p>I'm sorry, but we seem to be having trouble processing your request.  "
        . "You can continue browsing the site while we fix the problem."
        . "</p></div>";
    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div>";

    $content .= "</div>";

    $err_msg = "Web ERR [500]: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] 
        . ' [' . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'No Agent') . '] ';

    if( isset($errors['err']['code']) ) {
        $request['error_codes_msg'] = 'err:' . $errors['err']['code'];
        $err_msg .= '[' . $errors['err']['code'] . ':' . $errors['err']['msg'] . ']';
    } else {
        $request['error_codes_msg'] = "I'm sorry, we seem to have run into a spot of trouble.";
        $err_msg .= "I'm sorry, we seem to have run into a spot of trouble.";
    }
    // Check for nested errors
    if( isset($errors['err']['err']) ) {
        $err = $errors['err'];
        while( isset($err['err']) ) {
            $request['error_codes_msg'] .= ',' . $err['err']['code'];
            $err_msg .= '[' . $err['err']['code'] . ':' . $err['err']['msg'] . ']';
            $err = $err['err'];
        }
    }
    error_log($err_msg);

    //
    // Email sysadmins there was a problem with a web request
    //
    if( !isset($ciniki['config']['ciniki.web']['email.500.errors']) || $ciniki['config']['ciniki.web']['email.500.errors'] == 'yes' ) {
        $msg = print_r($request['query_string'], true);
        $msg .= print_r($request['args'], true);
        $msg .= print_r($request['uri_split'], true);
        $msg .= print_r($request['domain'], true);
        $msg .= print_r($request['session'], true);
        $msg = preg_replace("/password.*\n/m", 'password removed', $msg);
        $ciniki['emailqueue'][] = array('to'=>$ciniki['config']['ciniki.core']['alerts.notify'],
            'subject'=>'Web ERR 500',
            'textmsg'=>$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n"
                . print_r($errors, true)
                . "\n\n"
                . $msg
            );
    }

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
