<?php
//
// Description
// -----------
// Process the carousel images
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_images(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $titles = 'no';
    $items = array();
    $requested_image = 0;
    $cur_item_num = 1;
    for($i = 1; $i <= 100; $i++) {
        if( isset($s["image-{$i}"]) && $s["image-{$i}"] > 0 ) {
            $caption = '';
            if( isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ) {
                $caption = $s["title-{$i}"];
            } elseif( isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ) {
                $caption = $s["content-{$i}"];
            }
            $items[$cur_item_num] = array(
                'image-id' => $s["image-{$i}"],
                'caption' => $caption,
                'aria-label' => $caption,
                'url' => $request['page']['path'] . ($request['page']['path'] != '/' ? '/' : '') . $i,
                'permalink' => $cur_item_num,
                );
            if( isset($request['uri_split'][($request['cur_uri_pos']+1)])
                && $request['uri_split'][($request['cur_uri_pos']+1)] == $i
                ) {
                $requested_image = $cur_item_num;
            }
            $cur_item_num++;
        }
    }
    if( $requested_image > 0 ) {
        $title = '';
        if( isset($s['title']) && $s['title'] != '' ) {
            $title = $s['title'];
        }
        if( isset($s["title-{$requested_image}"]) && $s["title-{$requested_image}"] != '' ) {
            $title .= ($title != '' ? ' - ' : '') . $s["title-{$requested_image}"];
        }
        $blocks[] = array(
            'type' => 'image',
            'title' => $title,
            'class' => 'image-caption',
            'content' => isset($s["content-{$requested_image}"]) && $s["content-{$requested_image}"] != '' ? $s["content-{$requested_image}"] : '',
            'image-id' => $s["image-{$requested_image}"],
            'image-permalink' => $requested_image,
            'image-list' => $items,
            'base-url' => $request['page']['path'],
            );
        return array('stat'=>'ok', 'stop'=>'yes', 'clear'=>'yes', 'blocks'=>$blocks);
    }

    if( isset($s['title']) && $s['title'] != '' ) {
        $blocks[] = array(
            'type' => 'title',
            'title' => $s['title'],
            'content' => (isset($s['content']) ? $s['content'] : ''),
            );
    } elseif( isset($s['content']) && $s['content'] != '' ) {
        $blocks[] = array(
            'type' => 'text',
            'content' => (isset($s['content']) ? $s['content'] : ''),
            );
    }

    $blocks[] = array(
        'type' => 'gallery',
        'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
        'sequence' => $section['sequence'],
        'layout' => 'originals',
        'items' => $items,
        );

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
