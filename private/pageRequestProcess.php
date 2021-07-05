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
        'page-class' => 'page-' . $request['site']['pages'][$page_id]['permalink'],
        'title' => $request['site']['pages'][$page_id]['title'],
        'url' => $request['site']['pages'][$page_id]['path'],
        );

    //
    // Check first element of uri_split to see if a child page exists for it.
    //
    if( isset($request['uri_split'][($request['cur_uri_pos']+1)]) 
        && $request['uri_split'][($request['cur_uri_pos']+1)] != '' 
        && isset($request['site']['pages'][$page_id]['children']) 
        ) {
        foreach($request['site']['pages'][$page_id]['children'] as $child_id) {
            if( isset($request['site']['pages'][$child_id]['permalink']) 
                && $request['site']['pages'][$child_id]['permalink'] == $request['uri_split'][($request['cur_uri_pos']+1)]
                ) {
                $request['cur_uri_pos']++;
                return ciniki_wng_pageRequestProcess($ciniki, $tnid, $request, $child_id);
            }
        }
    }

    //
    // Check if special pages (Account, cart, search, cpi), must be at top level of site
    //
    if( $request['cur_uri_pos'] == -1 && isset($request['uri_split'][0]) 
        && in_array($request['uri_split'][0], array('account', 'cart', 'search', 'cpi'))
        ) {
        if( $request['uri_split'][0] == 'account' ) {
            $request['cur_uri_pos']++;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountRequestProcess');
            $rc = ciniki_wng_accountRequestProcess($ciniki, $tnid, $request);
        } 
        elseif( $request['uri_split'][0] == 'cart' ) {
            $request['cur_uri_pos']++;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cartRequestProcess');
            $rc = ciniki_wng_cartRequestProcess($ciniki, $tnid, $request);
        }
        elseif( $request['uri_split'][0] == 'search' ) {
            $request['cur_uri_pos']++;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'searchRequestProcess');
            $rc = ciniki_wng_searchRequestProcess($ciniki, $tnid, $request);
        }
        elseif( $request['uri_split'][0] == 'cpi' ) {
            $request['cur_uri_pos']+=2;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'apiRequestProcess');
            $rc = ciniki_wng_apiRequestProcess($ciniki, $tnid, $request);
            $rc['json'] = 'yes';
        }

        //
        // Check if error returned
        //
        if( isset($rc['blocks']) ) {
            foreach($rc['blocks'] as $block) {
                $request['response']['blocks'][] = $block;
            }
        }

        return $rc;
    }

    //
    // If there is a url request and nothing handled it, return a 404 error
    //
    if( $request['cur_uri_pos'] == -1 && isset($request['uri_split'][0]) && $request['uri_split'][0] != '' ) {
        //
        // No child page found, 404 error
        //
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.118', 'msg'=>'Page not found'));
    }
//    elseif( $request['cur_uri_pos'] >= 0 && isset($request['uri_split'][($request['cur_uri_pos'])]) ) {
//        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.92', 'msg'=>'Page not found'));
//    }

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
    // Check if there should be a page title
    //
    if( isset($request['page']['page_title']) && $request['page']['page_title'] != '' ) {
        $request['response']['blocks'][] = array(
            'type' => 'title',
            'title' => $request['page']['page_title'],
            );
    }
    
    //
    // Process the sections building the request['response']['blocks'] array
    //
    if( isset($request['page']['sections']) ) {
        foreach($request['page']['sections'] as $section) {
            //
            // Skip hidden sections
            //
            if( ($section['flags']&0x10) == 0x10 ) {
                continue;
            }
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
                //
                // If a section processes a sub page, it may return clear to remove
                // and previous blocks. This allows for handling of page 2 content from
                // a section.
                //
                if( isset($rc['clear']) && $rc['clear'] == 'yes' ) {
                    $request['response']['blocks'] = $rc['blocks'];
                } else {
                    foreach($rc['blocks'] as $block) {
                        $request['response']['blocks'][] = $block;
                    }
                }
            }
            //
            // A section could return it should be the only section on a page,
            // and don't process any other sections
            //
            if( isset($rc['stop']) && $rc['stop'] == 'yes' ) {
                break;
            }
        }
    }

    return array('stat'=>'ok');
}
?>
