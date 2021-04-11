<?php
//
// Description
// -----------
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
function ciniki_wng_pageRequestProcess(&$ciniki, $tnid, &$request, $page_id) {

    //
    // Add to breadcrumbs
    //
    $request['breadcrumbs'][] = array(
        'page_id' => $page_id,
        'title' => $request['site']['pages'][$page_id]['title'],
        'url' => $request['site']['pages'][$page_id]['path'],
        );

    //
    // Check first element of uri_split to see if a child page exists for it.
    //
    if( isset($request['uri_split'][$request['cur_uri_pos']]) && $request['uri_split'][$request['cur_uri_pos']] != '' && isset($request['site']['pages'][$page_id]['children']) ) {
        foreach($request['site']['pages'][$page_id]['children'] as $child_id) {
            if( isset($request['site']['pages'][$child_id]['permalink']) 
                && $request['site']['pages'][$child_id]['permalink'] == $request['uri_split'][$request['cur_uri_pos']]
                ) {
                //
                // Shift the path array, and return results from the child page
                //
                //array_shift($request['uri_split']);
                $request['cur_uri_pos']++;
                return ciniki_wng_pageRequestProcess($ciniki, $tnid, $request, $child_id);
            }
        }
    }

    //
    // Check if special pages (Account, cart, search), must be at top level of site
    //
    if( $request['cur_uri_pos'] == 0 && isset($request['uri_split'][0]) ) {
        if( $request['uri_split'][0] == 'account' ) {
            $request['cur_uri_pos']++;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountRequestProcess');
            return ciniki_wng_accountRequestProcess($ciniki, $tnid, $request);
        } 
        elseif( $request['uri_split'][0] == 'cart' ) {
            $request['cur_uri_pos']++;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cartRequestProcess');
            return ciniki_wng_cartRequestProcess($ciniki, $tnid, $request);
        }
        elseif( $request['uri_split'][0] == 'search' ) {
            $request['cur_uri_pos']++;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'searchRequestProcess');
            return ciniki_wng_searchRequestProcess($ciniki, $tnid, $request);
        }
    }

    //
    // Load the page details and sections
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'pageLoad');
    $rc = ciniki_wng_pageLoad($ciniki, $tnid, $request, $page_id);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.21', 'msg'=>'Unable to load page', 'err'=>$rc['err']));
    }
    $request['page'] = $rc['page'];
    
    //
    // Process the sections building the request['response']['blocks'] array
    //
    if( isset($request['page']['sections']) ) {
        foreach($request['page']['sections'] as $section) {
            error_log('process: ' . $section['ref']);
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionRequestProcess');
            $rc = ciniki_wng_sectionRequestProcess($ciniki, $tnid, $request, $section);
            if( $rc['stat'] == 'exit' ) {
                return $rc;
            }
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.56', 'msg'=>'Unable to process the section', 'err'=>$rc['err']));
            }

            //
            // Add any resulting blocks to the request['response']['blocks'] array
            //
            if( isset($rc['blocks']) ) {
                foreach($rc['blocks'] as $block) {
                    $request['response']['blocks'][] = $block;
                }
            }
        }
    }

    return array('stat'=>'ok');
}
?>
