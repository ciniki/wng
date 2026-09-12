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
    $request['response']['og']['title'] = $request['site']['pages'][$page_id]['title'];

    //
    // Check if page is hidden
    //
    if( isset($request['site']['pages'][$page_id]['flags']) 
        && ($request['site']['pages'][$page_id]['flags']&0x07) == 0 
        ) {
        return array('stat'=>'404');
    }


    //
    // Check if page only available to members
    //
    if( isset($request['site']['pages'][$page_id]['flags']) 
        && ($request['site']['pages'][$page_id]['flags']&0x04) == 0x04 
        && (!isset($request['session']['customer']['member_status']) || $request['session']['customer']['member_status'] != 10)
        ) {
        $request['session']['login-return-url'] = $request['ssl_domain_base_url'] . $request['site']['pages'][$page_id]['path'];
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountLoginProcess');
        $rc = ciniki_wng_accountLoginProcess($ciniki, $tnid, $request);
        if( $rc['stat'] != 'authenticated' ) {
            $request['response']['blocks'] = isset($rc['blocks']) ? $rc['blocks'] : array();
            return array('stat'=>'ok');
        }
        if( !isset($request['session']['customer']['member_status']) || $request['session']['customer']['member_status'] != 10 ) {
            return array('stat'=>'404');
        }
    }
    //
    // Check if page is only available to customer 
    //
    if( isset($request['site']['pages'][$page_id]['flags']) 
        && ($request['site']['pages'][$page_id]['flags']&0x02) == 0x02 
        && (!isset($request['session']['customer']['id']) || $request['session']['customer']['id'] <= 0)
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountLoginProcess');
        $rc = ciniki_wng_accountLoginProcess($ciniki, $tnid, $request);
        if( $rc['stat'] != 'authenticated' ) {
            $request['response']['blocks'] = isset($rc['blocks']) ? $rc['blocks'] : array();
            return array('stat'=>'ok');
        }
    }

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
        && in_array($request['uri_split'][0], array('account', 'cart', 'search', 'mail', 'ahk', 'cpi', 'stripehook', 'whk'))
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
        // For unsubscribes
        // Format: http://thevillagewinemaker.ca/mail/subscriptions/unsubscribe?e=veggiefrog%40gmail.com&s=Test&k=003e6c205c5bdfe047dadb9b23612b8b
        elseif( isset($request['uri_split'][2]) 
            && $request['uri_split'][0] == 'mail' 
            && $request['uri_split'][1] == 'subscriptions' 
            && $request['uri_split'][2] == 'unsubscribe' 
            ) {
            $request['cur_uri_pos']+=2;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'wng', 'unsubscribeRequestProcess');
            $rc = ciniki_subscriptions_wng_unsubscribeRequestProcess($ciniki, $tnid, $request);
        }
        // 
        // Handler for the Ciniki API (called cpi to avoid bots testing /api)
        //
        elseif( $request['uri_split'][0] == 'cpi' ) {
            $request['cur_uri_pos']+=2;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'apiRequestProcess');
            $rc = ciniki_wng_apiRequestProcess($ciniki, $tnid, $request);
            $rc['json'] = 'yes';
        } 
        // 
        // Handler for the Ciniki Account Hook
        //
        elseif( $request['uri_split'][0] == 'ahk' ) {
            $request['cur_uri_pos']+=2;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'acthookRequestProcess');
            $rc = ciniki_wng_acthookRequestProcess($ciniki, $tnid, $request);
        } 
        // 
        // Handler for Ciniki Webhooks (called cwh to avoid bots hitting /webhooks
        //
        elseif( $request['uri_split'][0] == 'stripehook' ) {
            $request['cur_uri_pos']++;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'webhookStripeProcess');
            $rc = ciniki_sapos_wng_webhookStripeProcess($ciniki, $tnid, $request);
            $rc['json'] = 'yes';
        } 
        // 
        // Handler for Ciniki Webhooks (called cwh to avoid bots hitting /webhooks
        //
        elseif( $request['uri_split'][0] == 'whk' ) {
            $request['cur_uri_pos']+=2;
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'webhookRequestProcess');
            $rc = ciniki_wng_webhookRequestProcess($ciniki, $tnid, $request);
            $rc['json'] = 'yes';
        } 
        else {
            $rc = array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.185', 'msg'=>'Invalid request'));
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
    if( $request['cur_uri_pos'] == -1 && isset($request['uri_split'][0]) && $request['uri_split'][0] != '' 
        && $request['uri_split'][0] != 'download'   // Except for files located on home page
        ) {
        //
        // No child page found, 404 error
        //
        $url_found = 'no';
    }
//    elseif( $request['cur_uri_pos'] >= 0 && isset($request['uri_split'][($request['cur_uri_pos'])]) ) {
//        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.92', 'msg'=>'Page not found'));
//    }

    //
    // Check if the page is a redirect
    //
    if( isset($request['site']['pages'][$page_id]['ptype']) 
        && $request['site']['pages'][$page_id]['ptype'] == 40
        && isset($request['site']['pages'][$page_id]['redirect_url']) 
        ) {
        header("Location: " . $request['site']['pages'][$page_id]['redirect_url']);
        exit;
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
    // Set the open graph settings
    //
    if( isset($request['page']['og_title']) && $request['page']['og_title'] != '' ) {
        $request['response']['og']['title'] = $request['page']['og_title'];
    }
    $request['response']['og']['url'] = $request['ssl_domain_base_url'] . $request['page']['path'];
    if( isset($request['page']['og_image_id']) && $request['page']['og_image_id'] != '' && $request['page']['og_image_id'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageAdd');
        $rc = ciniki_wng_cacheImageAdd($ciniki, $tnid, $request['site'], [
            'image_id' => $request['page']['og_image_id'],
            'version' => 'original',
            'maxwidth' => 1200,
            ]);
        if( $rc['stat'] == 'ok' ) {
            $request['response']['og']['image'] = $request['cache_domain_base_url'] . $rc['url'];
        }
    }
    if( isset($request['page']['og_description']) && $request['page']['og_description'] != '' ) {
        $request['response']['og']['description'] = $request['page']['og_description'];
    }

    //
    // Process the sections building the request['response']['blocks'] array
    //
    if( isset($request['page']['sections']) ) {
        //
        // Remove hidden sections
        //
        foreach($request['page']['sections'] as $sid => $section) {
            if( ($section['flags']&0x10) == 0x10 ) {
                unset($request['page']['sections'][$sid]);
            }
        }
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
            if( $rc['stat'] == '404' ) {
                return $rc;
            }
            if( $rc['stat'] != 'ok' ) {
                if( !isset($rc['err']) ) {
                    error_log('Unknown Error: ' . print_r($rc,true));
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.236', 'msg'=>'Unable to process the section'));
                }
//                $request['response']['blocks'][] = array(
//                    'type' => 'msg',
//                    'level' => 'error',
//                    'content' => 'There appears to be some content missing, please content us',
//                    );
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.56', 'msg'=>'Unable to process the section', 'err'=>$rc['err']));
            }

            //
            // Check if home page block sub content found to prevent 404 error
            //
            if( isset($rc['url_found']) ) {
                $url_found = $rc['url_found'];
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

    if( isset($url_found) && $url_found == 'no' ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.wng.118', 'msg'=>'Page not found'));
    }

    return array('stat'=>'ok');
}
?>
