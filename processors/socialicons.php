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

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    //
    // Get the social links available
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
            'username' => $request['site']['settings']['social-instagram-username'],
            );
    }
    if( isset($request['site']['settings']['social-twitter-username']) 
        && $request['site']['settings']['social-twitter-username'] != ''
        ) {
        $icons[] = array(
            'type' => 'twitter',
            'username' => $request['site']['settings']['social-twitter-username'],
            );
    }
    
    $block = array(
        'type' => 'socialicons',
        'content' => isset($s['content']) ? $s['content'] : '',
        'toggle-em' => isset($s['toggle-em']) ? $s['toggle-em'] : '',
        'icons' => $icons,
        'class' => '',
        );

    if( isset($s['content']) && $s['content'] != '' ) {
        $block['class'] .= ($block['class'] != '' ? ' ' : '') . 'address';
    }
    if( count($icons) > 0 ) {
        $block['class'] .= ($block['class'] != '' ? ' ' : '') . 'icons';
    }

    $blocks[] = $block;

//    $blocks[] = array('type'=>'content', 'content'=>'<pre>' . print_r($request['site'], true) . '</pre>');
    

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
