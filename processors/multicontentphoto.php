<?php
//
// Description
// -----------
// Process the section that will display a photo and paragraph.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_multicontentphoto(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();

    $s = $section['settings'];

    $image_position = isset($s['image-position']) ? $s['image-position'] : '';
    if( $image_position == '' ) {
        $image_position = 'top-right';
    }
    for($i = 1; $i <= 100; $i++) {
        if( (isset($s["image-{$i}"]) && $s["image-{$i}"] > 0)
            || (isset($s["title-{$i}"]) && $s["title-{$i}"] != '')
            ) {
            $blocks[] = array(
                'type' => 'contentphoto',
                'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
                'image-id' => (isset($s["image-{$i}"]) ? $s["image-{$i}"] : 0),
                'image-size' => (isset($s['image-size']) ? $s['image-size'] : ''),
                'image-position' => $image_position,
                'title' => (isset($s["title-{$i}"]) ? $s["title-{$i}"] : ''),
                'content' => (isset($s["content-{$i}"]) ? $s["content-{$i}"] : ''),
                );
            if( isset($s['image-alternate']) && $s['image-alternate'] == 'yes' ) {
                if( $image_position == 'top-left' ) {
                    $image_position = 'top-right';
                } elseif( $image_position == 'top-right' ) {
                    $image_position = 'top-left';
                } elseif( $image_position == 'center-left' ) {
                    $image_position = 'center-right';
                } elseif( $image_position == 'center-right' ) {
                    $image_position = 'center-left';
                } elseif( $image_position == 'bottom-left' ) {
                    $image_position = 'bottom-right';
                } elseif( $image_position == 'bottom-right' ) {
                    $image_position = 'bottom-left';
                }
            }
        }
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
