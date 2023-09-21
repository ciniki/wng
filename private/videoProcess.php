<?php
//
// Description
// -----------
// This function will process raw text content into HTML.
//
// Arguments
// ---------
// ciniki:
// unprocessed_content:     The unprocessed text content that needs to be turned into html.
//
// Returns
// -------
//
function ciniki_wng_videoProcess($ciniki, $tnid, $request, $args) {

    if( !isset($args['url']) || $args['url'] == '' ) {
        return array('stat'=>'ok', 'content'=>'');
    }
    $video_url = $args['url'];

    $content = ''; 
    $js = '';
    if( preg_match("/youtu.be\/([^\/]+)/", $video_url, $m) ) {
        $youtube_id = $m[1];
    } elseif( preg_match("/youtube.*v=([^\/\&]+)/", $video_url, $m) ) {
        $youtube_id = $m[1];
    } elseif( preg_match("/youtube.*\/embed\/([^\/\&]+)/", $video_url, $m) ) {
        $youtube_id = $m[1];
    } elseif( preg_match("/youtube.*\/shorts\/([^\/\&]+)/", $video_url, $m) ) {
        $youtube_id = $m[1];
    } elseif( preg_match("/vimeo.*\/([^\/\&]+)/", $video_url, $m) ) {
        $vimeo_id = $m[1];
    }


    //
    // Check if the video should be delayed load until user clicks on image
    //
    if( isset($args['clickload']) && $args['clickload'] == 'yes' ) {
        $img_title = (isset($args['title']) && $args['title'] != '' ? $args['title'] : '');
        $img_title = preg_replace("/\"/", '&quot;', $img_title);
        if( isset($youtube_id) ) {
            $content = "<div id='video-{$args['sequence']}' class='video video-clickload' onclick='playVideo{$args['sequence']}();'>"
                . "<div class='playbtn'>"
                . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 311.69 311.69"><path d="M155.84,0A155.85,155.85,0,1,0,311.69,155.84,155.84,155.84,0,0,0,155.84,0Zm0,296.42A140.58,140.58,0,1,1,296.42,155.84,140.58,140.58,0,0,1,155.84,296.42Z"></path><polygon points="218.79 155.84 119.22 94.34 119.22 217.34 218.79 155.84"></polygon></svg>'
                . "</div>"
                . "<img alt=\"{$img_title}\" src='https://img.youtube.com/vi/{$youtube_id}/maxresdefault.jpg' "
//                . "srcset='https://img.youtube.com/vi/{$youtube_id}/mqdefault.jpg 1100w,"
//                    . "https://img.youtube.com/vi/{$youtube_id}/maxresdefault.jpg 1200w"
//                    . "' "
//                . "sizes='100vmin'"
                . "/></div>";
            $js = "function playVideo{$args['sequence']}() {"
                . "var e=C.gE('video-{$args['sequence']}');"
                . "e.innerHTML=\"<iframe src='https://www.youtube.com/embed/{$youtube_id}?autoplay=1&rel=0' title='YouTube Video player' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe></div>\";"
                . "};";
        }
        elseif( isset($vimeo_id) ) {
            $content = "<div class='video'><iframe src='https://player.vimeo.com/video/{$vimeo_id}' allow='autoplay; fullscreen; picture-in-picture' allowfullscreen></iframe></div>";
        }
    } else {
        if( isset($youtube_id) ) {
            $content = "<div class='video'><iframe src='https://www.youtube.com/embed/{$youtube_id}' title='YouTube Video player' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe></div>";
        }
        elseif( isset($vimeo_id) ) {
            $content = "<div class='video'><iframe src='https://player.vimeo.com/video/{$vimeo_id}' allow='autoplay; fullscreen; picture-in-picture' allowfullscreen></iframe></div>";
        }
    }

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
