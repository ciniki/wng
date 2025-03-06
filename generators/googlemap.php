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
function ciniki_wng_generators_googlemap(&$ciniki, $tnid, $request, $block) {

    $content = '';
    $js = '';

    if( isset($block['latitude']) && $block['latitude'] != ''
        && isset($block['longitude']) && $block['longitude'] != '' 
        ) {
        
        $content .= "<div class='block-googlemap"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['map-position']) && $block['map-position'] != '' ? ' map-' . $block['map-position'] : ' map-top-right')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( !isset($block['id']) || $block['id'] == '' ) {
            $block['id'] = 'gmap';
        }
        if( !isset($block['sid']) || $block['sid'] == '' ) {
            $block['sid'] = '1';
        }
        $content .= "<div class='googlemap' id='{$block['id']}'></div>";
        $zoom = 13;
        if( isset($block['zoom']) && $block['zoom'] > 1 && $block['zoom'] < 32 ) {
            $zoom = $block['zoom'];
        }

        $js = ''
            . "function gmap_initialize{$block['sid']}() {"
                . "gmap_show('{$block['id']}',{$block['latitude']},{$block['longitude']});"
            . "};"
            . "function gmap_show(id,lat,long){"
                . 'var myLatlng = new google.maps.LatLng(lat,long);'
                . 'var mapOptions = {'
                    . 'zoom: ' . $zoom . ','
                    . 'center: myLatlng,'
                    . 'panControl: false,'
                    . 'zoomControl: true,'
                    . 'scaleControl: true,'
                    . 'mapTypeId: google.maps.MapTypeId.ROADMAP'
                . '};'
                . "var map = new google.maps.Map(document.getElementById(id), mapOptions);"
                . 'var marker = new google.maps.Marker({'
                    . 'position: myLatlng,'
                    . 'map: map,'
                    . 'title:"",'
                    . '});'
            . '};'
            . "function loadMap{$block['sid']}() {"
                . 'if(C.gE("googlemap")==null){'
                    . 'var script = document.createElement("script");'
                    . 'script.setAttribute("id", "googlemap");'
                    . 'script.type = "text/javascript";'
                    . 'script.src = "' . ($request['ssl']=='yes'?'https':'http') . '://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . "&sensor=false&callback=gmap_initialize{$block['sid']}\";"
                    . 'document.body.appendChild(script);'
                . '}else{'
                    . "setTimeout(function(){"
                        . "gmap_show('{$block['id']}',{$block['latitude']},{$block['longitude']});"
                    . "}, 1000);"
                . '}'
            . '};'
            . "addEventListener('load', (event) => {loadMap{$block['sid']}();});"
            . "";

        if( isset($block['content']) && $block['content'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.242', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='content-wrap'>" 
                . (isset($block['title']) && $block['title'] != '' ? "<div class='title-wrap'><h3>{$block['title']}</h3></div>" : '')
                . (isset($block['subtitle']) && $block['subtitle'] != '' ? "<div class='subtitle-wrap'><h3>{$block['subtitle']}</h3></div>" : '')
                . $rc['content'] 
                . "</div>";
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
