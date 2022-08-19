<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_socialicons(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    //
    // Get the social links available
    // ** Note ** Any changes need to also be in processors_headermenu
    //
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
    
    $block = array(
        'type' => 'socialicons',
        'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
        'content' => isset($s['content']) ? $s['content'] : '',
        'toggle-em' => isset($s['toggle-em']) ? $s['toggle-em'] : '',
        'icons' => $icons,
        );

    if( isset($s['content']) && $s['content'] != '' ) {
        $block['class'] .= ($block['class'] != '' ? ' ' : '') . 'address';
    }
    if( count($icons) > 0 ) {
        $block['class'] .= ($block['class'] != '' ? ' ' : '') . 'icons';
    }

    $blocks[] = $block;

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
