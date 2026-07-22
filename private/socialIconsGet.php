<?php
//
// Description
// -----------
// This function will return the list of social media icons for a site
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_socialIconsGet(&$ciniki, $tnid, $request) {

    $icons = array();
    if( isset($request['site']['settings']['social-facebook-url']) 
        && $request['site']['settings']['social-facebook-url'] != ''
        ) {
        $icons[] = array(
            'type' => 'facebook',
            'url' => $request['site']['settings']['social-facebook-url'],
            );
    }
    if( isset($request['site']['settings']['social-instagram-username']) 
        && $request['site']['settings']['social-instagram-username'] != ''
        ) {
        $icons[] = array(
            'type' => 'instagram',
            'url' => 'https://instagram.com/' . $request['site']['settings']['social-instagram-username'],
            );
    }
    if( isset($request['site']['settings']['social-twitter-username']) 
        && $request['site']['settings']['social-twitter-username'] != ''
        ) {
        $icons[] = array(
            'type' => 'twitter',
            'url' => 'https://twitter.com/' . $request['site']['settings']['social-twitter-username'],
            );
    }
    if( isset($request['site']['settings']['social-youtube-url']) 
        && $request['site']['settings']['social-youtube-url'] != ''
        ) {
        $icons[] = array(
            'type' => 'youtube',
            'url' => $request['site']['settings']['social-youtube-url'],
            );
    }
    if( isset($request['site']['settings']['social-linkedin-url']) 
        && $request['site']['settings']['social-linkedin-url'] != ''
        ) {
        $icons[] = array(
            'type' => 'linkedin',
            'url' => $request['site']['settings']['social-linkedin-url'],
            );
    }

    return array('stat'=>'ok', 'icons'=>$icons);
}
?>
