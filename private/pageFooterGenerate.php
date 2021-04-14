<?php
//
// Description
// -----------
// This function will generate the footer to be displayed at the bottom
// of every web page.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_pageFooterGenerate(&$ciniki, $tnid, &$request) {
    global $start_time;

    //
    // Check if fullscreen content, and don't display any footers
    //
/*    if( isset($ciniki['response']['fullscreen-content']) && $ciniki['response']['fullscreen-content'] == 'yes' ) {
        return array('stat'=>'ok', 'content'=>"</body></html>");
    } */

    //
    // Generate the blocks for the page
    //
    $footerblocks = array();
    if( isset($request['site']['footersections']) ) {
        foreach($request['site']['footersections'] as $section) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionRequestProcess');
            $rc = ciniki_wng_sectionRequestProcess($ciniki, $tnid, $request, $section);
            if( $rc['stat'] == 'exit' ) {
                return $rc;
            }
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.80', 'msg'=>'Unable to process the section', 'err'=>$rc['err']));
            }

            //
            // Add any resulting blocks to the request['response']['blocks'] array
            //
            if( isset($rc['blocks']) ) {
                foreach($rc['blocks'] as $block) {
                    $footerblocks[] = $block;
                }
            }
        }
    }
   
    //
    // Store the content
    //
    $content = '';
    $s = isset($request['site']['settings']) ? $request['site']['settings'] : array();

    //
    // Start the footer content
    //
    $content .= "<footer id='page-footer'>";

    //
    // Generate the blocks
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'blocksGenerate');
    $rc = ciniki_wng_blocksGenerate($ciniki, $tnid, $request, $footerblocks);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.57', 'msg'=>'', 'err'=>$rc['err']));
    }
    $content .= $rc['content'];

    //
    // Extra information for the bottom of the page, error messages, debug info, etc
    //
    $content .= "<div id='x-info' class='x-info'>";
    //
    // If there was an error page generated, see if we should put the error code in the footer for debug purposes.
    // This keeps it out of the way, but easy to tell people what to look for.
    //
    if( isset($request['error_codes_msg']) && $request['error_codes_msg'] != '' ) {
        $content .= "<br/><span class='error_msg'>" . $request['error_codes_msg'] . "</span>";
    }
    $content .= "<span id='x-stats' class='x-stats' style='display:none;'>"
        . "Execution: " . sprintf("%.4f", ((microtime(true)-$start_time)/60)) . " seconds"
        . "</span>";
    $content .= "</div>";

    //
    // Check for copyright information
    //
    $content .= "<div class='copyright'>";
    if( isset($s['footer-copyright-name']) && $s['footer-copyright-name'] != '' ) {
        $content .= "<span>All content &copy; Copyright " . date('Y') 
            . " by " . $s['footer-copyright-name'] 
            . "</span>";
    } elseif( isset($ciniki['tenant']['name']) && $ciniki['tenant']['name'] != '' ) {
        $content .= "<span>All content &copy; Copyright " . date('Y') 
            . " by " . $ciniki['tenant']['name']
            . "</span>";
    } else {
        $content .= "<span>All content &copy; Copyright " . date('Y') . "</span>";
    }
    if( isset($s['footer-copyright-message']) && $s['footer-copyright-message'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $s['footer-copyright-message']);   
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['content']) && $rc['content'] != '' ) {
            $content .= $rc['content'];
        }
    }
    $content .= "</div>";

    //
    // Close the footer
    //
    $content .= "</footer>";

    //
    // Close the body and html tags
    //
    $content .= "</body>"
        . "</html>"
        . "";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
