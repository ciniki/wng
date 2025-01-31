<?php
//
// Description
// -----------
// schedule
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_schedule(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    $content = '';

    $content .= "<div class='block-schedule"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . (isset($block['times']) && $block['times'] == 'no' ? ' no-times' : '')
        . (isset($block['subtitle']) && $block['subtitle'] != '' ? ' subtitle' : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }
    if( isset($block['subtitle']) && $block['subtitle'] != '' ) {
        $content .= "<h3>" . $block['subtitle'] . "</h3>";
    }
    if( isset($block['subtitle2']) && $block['subtitle2'] != '' ) {
        $content .= "<h3>" . $block['subtitle2'] . "</h3>";
    }

    //
    // Process the video
    //
    if( isset($block['video-url']) && $block['video-url'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'videoProcess');
        $rc = ciniki_wng_videoProcess($ciniki, $tnid, $request, array(
            'url' => $block['video-url'],
            'title' => $block['title'],
    //        'sequence' => $block['sequence'],
    //        'clickload' => (isset($block['clickload']) && $block['clickload'] == 'no' ? 'no' : 'yes'),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.178', 'msg'=>'Unable to process video', 'err'=>$rc['err']));
        }
        $videocontent = $rc['content'];
        if( !isset($block['js']) ) {
            $block['js'] = $rc['js'];
        } else {
            $block['js'] .= $rc['js'];
        }
    }

    if( isset($block['content']) && $block['content'] != '' ) {
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.246', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
        }
        $textcontent = $rc['content'];
    }

    if( isset($videocontent) && $videocontent != '' && isset($textcontent) && $textcontent != '' ) {
        $content .= "<div class='content-video'>"
            . "<div class='content-wrap'>"
            . $textcontent
            . "</div>"
            . "<div class='video-wrap'>"
            . $videocontent
            . "</div>"
            . "</div>";
    } elseif( isset($videocontent) && $videocontent != '' ) {
        $content .= "<div class='video-wrap'>" . $videocontent . "</div>";
    } elseif( isset($textcontent) && $textcontent != '' ) {
        $content .= "<div class='content-synopsis'>" . $textcontent . "</div>";
    }

    $content .= "<div class='timeslots'>";

    $prev_time = '';
    foreach($block['items'] as $timeslot) {
        $content .= "<div class='timeslot'>";
        $content .= "<div class='timetitle'>";
        if( !isset($block['times']) || $block['times'] != 'no' ) {
            if( $prev_time == $timeslot['time'] ) {
                $content .= "<div class='time'>&nbsp;</div>";
            } else {
                $content .= "<div class='time'>" . str_replace(' ', '&nbsp;', $timeslot['time']) . "</div>";
            }
        }
        if( isset($block['times']) && $block['times'] == 'no' ) {
            $content .= "<h3 class='title'>{$timeslot['title']}</h3>";
        } else {
            $content .= "<div class='title'>{$timeslot['title']}</div>";
        }
        $content .= "</div>";
        $textcontent = '';
        if( isset($timeslot['synopsis']) && $timeslot['synopsis'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $timeslot['synopsis']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.243', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $textcontent = $rc['content'];
        }

        //
        // Process the video
        //
        $videocontent = '';
        if( isset($timeslot['video-url']) && $timeslot['video-url'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'videoProcess');
            $rc = ciniki_wng_videoProcess($ciniki, $tnid, $request, [
                'url' => $timeslot['video-url'],
                ]);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.178', 'msg'=>'Unable to process video', 'err'=>$rc['err']));
            }
            $videocontent = $rc['content'];
            if( !isset($block['js']) ) {
                $block['js'] = $rc['js'];
            } else {
                $block['js'] .= $rc['js'];
            }
        }

        if( isset($videocontent) && $videocontent != '' && isset($textcontent) && $textcontent != '' ) {
            $content .= "<div class='content-video'>"
                . "<div class='timeslot-synopsis content-wrap'>"
                . $textcontent
                . "</div>"
                . "<div class='video-wrap'>"
                . $videocontent
                . "</div>"
                . "</div>";
        } elseif( isset($videocontent) && $videocontent != '' ) {
            $content .= "<div class='video-wrap'>" . $videocontent . "</div>";
        } elseif( isset($textcontent) && $textcontent != '' ) {
            $content .= "<div class='timeslot-synopsis'>" . $textcontent . "</div>";
        }
        
        if( isset($block['details-columns']) && isset($timeslot['items']) && count($timeslot['items']) > 0 ) {
            $content .= "<div class='details-table'><table>";
            if( isset($block['details-headers']) && $block['details-headers'] == 'yes' ) {
                $content .= "<thead><tr>";
                foreach($block['details-columns'] as $col) {
                    $content .= "<th>{$col['label']}</th>";
                }
                $content .= "</tr></thead>";
            }
            $content .= "<tbody>";
            foreach($timeslot['items'] as $item) {
                $content .= "<tr>";
                foreach($block['details-columns'] as $col) {
                    $content .= "<td"
                        . (isset($col['class']) && $col['class'] != '' ? " class='{$col['class']}'" : '')
                        . ">";
                    $cell_content = '';
                    if( isset($col['fold-label']) && $col['fold-label'] != '' ) {
                        $cell_content .= "<span class='fold-label'>" . $col['fold-label'] . "</span><span class='cell-content'>";
                    }
                    if( isset($item[$col['field']]) ) {
                        $cell_content .= $item[$col['field']];
                    }
                    if( isset($col['fold-label']) && $col['fold-label'] != '' ) {
                        $cell_content .= "</span>";
                    }
                    $content .= $cell_content;
                    $content .= "</td>";
                }
                $content .= "</tr>";
            }
            $content .= "</tbody>";
            $content .= "</table></div>";
        }

        $content .= "</div>";
        $prev_time = $timeslot['time'];
    }

    $content .= "</div>";

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    if( isset($block['js']) && $block['js'] != '' ) {
        return array('stat'=>'ok', 'content'=>$content, 'js'=>$block['js']);
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
