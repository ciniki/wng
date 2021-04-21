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
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        $content .= "<div class='googlemap' id='googlemap'></div>";
        $zoom = 13;
        if( isset($block['zoom']) && $block['zoom'] > 1 && $block['zoom'] < 32 ) {
            $zoom = $block['zoom'];
        }

        $js = ''
            . 'function gmap_initialize() {'
                . 'var myLatlng = new google.maps.LatLng(' . $block['latitude'] . ',' . $block['longitude'] . ');'
                . 'var mapOptions = {'
                    . 'zoom: ' . $zoom . ','
                    . 'center: myLatlng,'
                    . 'panControl: false,'
                    . 'zoomControl: true,'
                    . 'scaleControl: true,'
                    . 'mapTypeId: google.maps.MapTypeId.ROADMAP'
                . '};'
                . 'var map = new google.maps.Map(document.getElementById("googlemap"), mapOptions);'
                . 'var marker = new google.maps.Marker({'
                    . 'position: myLatlng,'
                    . 'map: map,'
                    . 'title:"",'
                    . '});'
            . '};'
            . 'function loadMap() {'
                . 'var script = document.createElement("script");'
                . 'script.type = "text/javascript";'
                . 'script.src = "' . ($request['ssl']=='yes'?'https':'http') . '://maps.googleapis.com/maps/api/js?key=' . $ciniki['config']['ciniki.web']['google.maps.api.key'] . '&sensor=false&callback=gmap_initialize";'
                . 'document.body.appendChild(script);'
            . '};'
            . 'window.onload = loadMap;';

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
