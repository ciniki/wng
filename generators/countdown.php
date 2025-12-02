<?php
//
// Description
// -----------
// This function will create a countdown timer on a webpage
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_generators_countdown(&$ciniki, $tnid, $request, $block) {

   
    if( !isset($block['end-dt']) || !is_object($block['end-dt']) ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $dt = new DateTime('now', new DateTimezone('UTC'));
    $remaining = $dt->diff($block['end-dt']);

    $state = 'running';
    if( $dt > $block['end-dt'] ) {
        $state = 'finished';
    }

    $content = "<div "
        . (isset($block['id']) && $block['id'] != '' ? "id='{$block['id']}' " : '')
        . "class='block-countdown {$state}"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    $content .= "<div id='countdown-running' class='running" . ($state != 'running' ? ' hidden' : '') . "'>";
    if( isset($block['running-title']) && $block['running-title'] != '' ) {
        $content .= "<div class='title'>";
        $content .= "<h2>{$block['running-title']}</h2>";
        $content .= "</div>";
    } 
    $content .= "<div class='countdown'>";
    $content .= "<div class='days'><div id='countdown-d' class='number'>" 
        . $remaining->format('%a') . "</div><div class='label'>days</div></div>";
    $content .= "<div class='hours'><div id='countdown-h' class='number'>" 
        . ($remaining->format('%H') > 0 ? ltrim($remaining->format('%H'),0) : 0) . "</div><div class='label'>hours</div></div>";
    $content .= "<div class='minutes'><div id='countdown-m' class='number'>" 
        . ($remaining->format('%I') > 0 ? ltrim($remaining->format('%I'),0) : 0) . "</div><div class='label'>minutes</div></div>";
    $content .= "<div class='seconds'><div id='countdown-s' class='number'>" 
        . ($remaining->format('%S') > 0 ? ltrim($remaining->format('%S'),0) : 0) . "</div><div class='label'>seconds</div></div>";
    $content .= "</div>";
    $content .= "</div>";

    $content .= "<div id='countdown-finished' class='finished" . ($state != 'finished' ? ' hidden' : '') . "'>";
    if( isset($block['finished-title']) && $block['finished-title'] != '' ) {
        $content .= "<div class='title'>";
        $content .= "<h2>{$block['finished-title']}</h2>";
        $content .= "</div>";
    } 
    if( isset($block['finished-content']) && $block['finished-content'] != '' ) {
        $content .= "<div class='message'>";
        $content .= "<h2>{$block['finished-content']}</h2>";
        $content .= "</div>";
    } 
    $content .= "</div>";

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    $js = "function countdown() {"
            . "const end=" . $block['end-dt']->getTimestamp() . ";"
            . "var dt = new Date();"
            . "const cur=Date.UTC(dt.getUTCFullYear(),dt.getUTCMonth(),dt.getUTCDate(),dt.getUTCHours(),dt.getUTCMinutes(),dt.getUTCSeconds());"
            . "const ts=end-(cur/1000);"
            . "if( ts <= 0 ) {"
                . "C.aC(C.gE('countdown-running'), 'hidden');"
                . "C.rC(C.gE('countdown-finished'), 'hidden');"
                . "clearInterval(countdown_timer);"
            . "}"
            . "const d=Math.floor(ts/3600/24);"
            . "const h=Math.floor(ts/3600)%24;"
            . "const m=Math.floor(ts/60)%60;"
            . "const s=Math.floor(ts)%60;"
            . "C.gE('countdown-d').innerHTML=d;"
            . "C.gE('countdown-h').innerHTML=h;"
            . "C.gE('countdown-m').innerHTML=m;"
            . "C.gE('countdown-s').innerHTML=s;"
        . "}"
        . "var countdown_timer = setInterval(countdown, 1000);"
        . "";

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
