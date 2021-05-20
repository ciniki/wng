<?php
//
// Description
// -----------
//
// This is the main starting point for all website generation in the Web Next Generation module.
//
// This funciton is called from scripts/index.php.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_siteRequestProcess(&$ciniki, $tnid, $request) {

    //
    // Load everything about the site needed for header, menu, breadcrumbs and footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
    $rc = ciniki_wng_siteLoad($ciniki, $tnid, $request['site_id'], 'yes');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.9', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
    }
    $request['site'] = $rc['site'];

    //
    // Check for a homepage
    //
    if( !isset($request['site']['homepage_id']) || $request['site']['homepage_id'] == 0 || !isset($request['site']['pages'][$request['site']['homepage_id']]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.11', 'msg'=>'Unable to load site'));
    }

    $request['breadcrumbs'] = array();
    $request['cur_uri_pos'] = -1;   // Start at -1 because home does not have a position

    //
    // Setup response details
    //
    $request['response'] = array(
        // The blocks that are returned by the sections
        'blocks' => array(),
        // The javascript that are returned by the blocks
        'js' => '',
        // The css that are returned by the blocks
        'css' => '',
        // Setup the array with meta variables required by facebook and others
        'og' => array(
            'url' => '',
            'title' => '',
            'site_name' => '',
            'image' => '',
            'description' => '',
            'type' => '',
            ),
        );

    //
    // Update the cache 
    //
    if( isset($ciniki['config']['ciniki.wng']['cache.rebuild']) 
        && $ciniki['config']['ciniki.wng']['cache.rebuild'] == 'always'
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheRebuild');
        $rc = ciniki_wng_cacheRebuild($ciniki, $tnid, $request['site']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.66', 'msg'=>'Unable to rebuild cache', 'err'=>$rc['err']));
        }
    }

    //
    // Start/Load the session
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sessionStart');
    $rc = ciniki_wng_sessionStart($ciniki, $tnid, $request);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.10', 'msg'=>'Site not setup', 'err'=>$rc['err']));
    }

    //
    // Process the page, generate appropriate error page if required
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageRequestProcess');
    $rc = ciniki_wng_pageRequestProcess($ciniki, $tnid, $request, $request['site']['homepage_id']);
    if( $rc['stat'] == 'ok' && isset($rc['json']) && $rc['json'] == 'yes' ) {
        header("Content-Type: text/plain; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");
        unset($rc['json']);
        $rc['content'] = json_encode($rc);
    }
    elseif( $rc['stat'] == 'ok' ) {
        //
        // Page request processes successfully, now generate HTML content
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageGenerate');
        $rc = ciniki_wng_pageGenerate($ciniki, $tnid, $request);
    }
    if( $rc['stat'] == '503' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'page503Generate');
        $rc = ciniki_wng_page503Generate($ciniki, $tnid, $request, $rc);
    }
    elseif( $rc['stat'] == '404' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'page404Generate');
        $rc = ciniki_wng_page404Generate($ciniki, $tnid, $request, $rc);
    }
    elseif( $rc['stat'] != 'ok' && $rc['stat'] != 'exit' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'page500Generate');
        $rc = ciniki_wng_page500Generate($ciniki, $tnid, $request, $rc);
    } 
    elseif( !isset($rc['content']) && $rc['stat'] != 'exit' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'page404Generate');
        $rc = ciniki_wng_page404Generate($ciniki, $tnid, $request, null);
    }

    $exit = isset($rc['stat']) && $rc['stat'] == 'exit' ? 'yes' : 'no';

    // 
    // Final content to be sent back
    //
    if( isset($rc['content']) ) {
        $content = $rc['content'];
    }

    //
    // Save module session information
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sessionSave');
    $rc = ciniki_wng_sessionSave($ciniki, $tnid, $request);
    if( $rc['stat'] != 'ok' ) {
        error_log('ciniki.wng: Unable to save session');
    }

    if( $exit == 'yes' ) {
        return array('stat'=>'exit');
    }

    //
    // Check for emailqueue
    //
    if( (isset($ciniki['emailqueue']) && count($ciniki['emailqueue']) > 0) || (isset($ciniki['smsqueue']) && count($ciniki['smsqueue']) > 0) ) {
        ob_start();
        if( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false ) {
            ob_start("ob_gzhandler");
            print $content;
            ob_end_flush();
        } elseif( isset($content) && $content != '' ) {
            print $content;
        }
        $contentlength = ob_get_length();
        header("Content-Length: $contentlength");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        flush();
        session_write_close();
        while(ob_get_level() > 0) {
            ob_end_clean();
        }
        if( isset($ciniki['emailqueue']) && count($ciniki['emailqueue']) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'emailQueueProcess');
            ciniki_core_emailQueueProcess($ciniki);
        }
        if( isset($ciniki['smsqueue']) && count($ciniki['smsqueue']) > 0 ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'smsQueueProcess');
            ciniki_core_smsQueueProcess($ciniki);
        }
    } 

    elseif( isset($content) && $content != '' ) {
        //
        // Output the page contents
        // FIXME: Add caching in here
        //
        print $content;
    }


    return array('stat'=>'ok');
}
?>
