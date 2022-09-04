<?php
//
// Description
// -----------
// Generate the HTML for a block that display a picture and paragraph. 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_contentvideo(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    $content = '';

    $video_position = 'top';
    if( isset($block['video-position']) && in_array($block['video-position'], ['bottom', 'bottom-left', 'bottom-right']) ) {
        $video_position = 'bottom';
    }

    if( (isset($block['content']) && $block['content'] != '') 
        || (isset($block['list']) && is_array($block['list']) && count($block['list']) > 0)  
        ) {
        $content .= "<div class='block-contentvideo"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . (isset($block['video-position']) && $block['video-position'] != '' ? ' video-' . $block['video-position'] : ' video-top-right')
            . (!isset($block['video-url']) || $block['video-url'] == '' ? ' no-video' : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['video-url']) && $block['video-url'] != '' && $video_position == 'top' ) {
            $content .= "<div class='video-wrap'>";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'videoProcess');
            $rc = ciniki_wng_videoProcess($ciniki, $tnid, $request, $block['video-url']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.178', 'msg'=>'Unable to process video', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
            $content .= '</div>';
        }

        $content .= "<div class='content-wrap'>"; 
        $content .= "<div class='title-wrap'>";
        if( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h1>" . $block['title'] . "</h1>";
        } elseif( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }
        if( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['subtitle']) && $block['subtitle'] != '' ) {
            $content .= "<h2>" . $block['subtitle'] . "</h2>";
        } elseif( isset($block['subtitle']) && $block['subtitle'] != '' ) {
            $content .= "<h3>" . $block['subtitle'] . "</h3>";
        }
        $content .= "</div>";
        if( isset($block['content']) && $block['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.179', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
        } 

        //
        // Check for any buttons
        //
        $buttons = '';
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
        for($i = 1; $i < 10; $i++) {
            if( (!isset($block["button-{$i}-page"]) || $block["button-{$i}-page"] != '')
                && isset($block["button-{$i}-text"]) && $block["button-{$i}-text"] != '' 
                ) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 
                    isset($block["button-{$i}-page"]) ? $block["button-{$i}-page"] : 0,
                    isset($block["button-{$i}-url"]) ? $block["button-{$i}-url"] : ''
                    );
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.110', 'msg'=>'', 'err'=>$rc['err']));
                }
                if( isset($rc['url']) && $rc['url'] != '' ) {
                    $buttons .= "<a "
                        . (isset($block["button-{$i}-target"]) ? " target='" . $block["button-{$i}-target"] . "' " : '')
                        . "class='"
                        . (isset($block['button-class']) && $block['button-class'] != '' ? $block['button-class'] : 'button')
                        . "' href='" . $rc['url'] . "'>" . $block["button-{$i}-text"] . "</a>";
                }
            }
        }
        if( $buttons != '' ) {
            $content .= "<div class='buttons'>" . $buttons . "</div>";
        }

        //
        // Check for any links
        //
        if( isset($block['links']) && count($block['links']) > 0 ) {
            $links = '';
            foreach($block['links'] as $link) {
                $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request, 0, $link['url']);
                if( isset($rc['url']) && $rc['url'] != '' ) {
                    $url = $rc['url'];
                    if( isset($link['description']) && $link['description'] != '' ) {
                        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $link['description']);
                        if( isset($rc['content']) ) {
                            $links .= $rc['content'];
                        }
                    }
                    $links .= "<a target='_blank' "
                        . " class='button' "
                        . " href='" . $url . "'>" . $link["name"] . "</a>";
                }
            }
            if( $links != '' ) {
                $content .= "<div class='links'>" . $links . "</div>";
            }
        }

        $content .= '</div>';

        if( isset($block['video-url']) && $block['video-url'] != '' && $video_position == 'bottom' ) {
            $content .= "<div class='video-wrap'>";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'videoProcess');
            $rc = ciniki_wng_videoProcess($ciniki, $tnid, $request, $block['video-url']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.180', 'msg'=>'Unable to process video', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }



    return array('stat'=>'ok', 'content'=>$content);
}
?>
