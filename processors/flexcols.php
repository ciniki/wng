<?php
//
// Description
// -----------
// Process the flex columns
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_flexcols(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

    $columns = array();
    for($i = 1; $i <= 100; $i++) {
        if( !isset($s["col-{$i}"]) || $s["col-{$i}"] == '' || !isset($s["label-{$i}"]) || $s["col-{$i}"] == '' ) {
            continue;
        }
        $col = $s["col-{$i}"];
        if( !isset($columns[$col]) ) {
            $columns[$col] = [
                'size' => isset($s["column-{$col}-size"]) && $s["column-{$col}-size"] != '' ? $s["column-{$col}-size"] : 'medium',
                'items' => [],
                ];
        }
        if( isset($s["title-{$i}"]) && $s["title-{$i}"] != '' ) {
            $columns[$col]['items'][] = [
                'type' => 'title',
                'title' => $s["title-{$i}"],
                ];
        }
        
        if( isset($s["image-{$i}"]) && $s["image-{$i}"] > 0 && is_numeric($s["image-{$i}"]) ) {
            $columns[$col]['items'][] = array(
                'type' => 'image',
                'image-id' => $s["image-{$i}"],
                'page' => isset($s["image-link-page-{$i}"]) ? $s["link-page-{$i}"] : (isset($s["link-{$i}-page"]) ? $s["link-{$i}-page"] : 0),
                'link-text' => '',
                'url' => isset($s["link-url-{$i}"]) ? $s["link-url-{$i}"] : (isset($s["link-{$i}-url"]) ? $s["link-{$i}-url"] : ''),
                );
        }
        if( isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ) {
            $columns[$col]['items'][] = [
                'type' => 'content',
                'content' => isset($s["content-{$i}"]) && $s["content-{$i}"] != '' ? $s["content-{$i}"] : '',
                ];
        }
        if( isset($s["social-icons-{$i}"]) && $s["social-icons-{$i}"] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'socialIconsGet');
            $rc = ciniki_wng_socialIconsGet($ciniki, $tnid, $request);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $icons = isset($rc['icons']) ? $rc['icons'] : array();
            if( count($icons) > 0 ) {
                $columns[$col]['items'][] = [
                    'type' => 'socialicons',
                    'items' => $icons,
                    ];
            }
        }


        $link_type = isset($s["link-type-{$i}"]) && $s["link-type-{$i}"] != '' ? $s["link-type-{$i}"] : 'link';

        $links = [];
        for($j = 1; $j <= 10; $j++) {
            if( isset($s["link-{$j}-text-{$i}"]) && $s["link-{$j}-text-{$i}"] != '' 
                && ((isset($s["link-{$j}-url-{$i}"]) && $s["link-{$j}-url-{$i}"] != '') 
                    || (isset($s["link-{$j}-page-{$i}"]) && $s["link-{$j}-page-{$i}"] > 0)
                    )
                ) {
                $links[] = array(
                    'text' => $s["link-{$j}-text-{$i}"],
                    'page' => isset($s["link-{$j}-page-{$i}"]) && $s["link-{$j}-page-{$i}"] > 0 ? $s["link-{$j}-page-{$i}"] : 0,
                    'url' => isset($s["link-{$j}-url-{$i}"]) ? $s["link-{$j}-url-{$i}"] : '',
                    );
            }
        }
        if( $link_type == 'button' ) {
            $columns[$col]['items'][] = [
                'type' => 'buttons',
                'items' => $links,
                ];
        } else {
            $columns[$col]['items'][] = [
                'type' => 'links',
                'items' => $links,
                ];
        }
    }

    if( isset($s['title']) && $s['title'] != '' ) {
        $blocks[] = array(
            'type' => 'title',
            'title' => $s['title'],
            );
    }

    if( count($columns) > 0 ) {
        $blocks[] = array(
            'type' => 'flexcols',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            'sequence' => $section['sequence'],
            'columns' => $columns,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
