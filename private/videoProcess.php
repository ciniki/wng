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
function ciniki_wng_videoProcess($ciniki, $tnid, $request, $video_url) {

    $content = ''; 
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

    if( isset($youtube_id) ) {
        $content = "<div class='video'><iframe src='https://www.youtube.com/embed/{$youtube_id}' title='YouTube Video player' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe></div>";
    }
    elseif( isset($vimeo_id) ) {
        $content = "<div class='video'><iframe src='https://player.vimeo.com/video/{$vimeo_id}' allow='autoplay; fullscreen; picture-in-picture' allowfullscreen></iframe></div>";
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
