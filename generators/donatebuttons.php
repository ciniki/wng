<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_generators_donatebuttons(&$ciniki, $tnid, $request, $block) {


    $content = "<div class='block-donatebuttons'>"
        . "<div class='wrap'>"
        . "<div class='content'>";
    
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>{$block['title']}</h2>";
    }
    if( isset($block['content']) && $block['content'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['content']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.261', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
        }
        $content .= $rc['content'];
    }
   
    $content .= "<form action='{$request['base_url']}/cart' method='POST'>";
    $content .= "<input type='hidden' name='action' value='update'>";

    
    $amounts = explode(',', $block['amounts']);
    if( count($amounts) > 0 ) {
        $content .= "<div class='donation-options'>";
        $content .= "<b>Donate</b>: ";
        foreach($amounts as $amount) {
            $amount = preg_replace("/[^0-9\.]/", '', $amount);
            $content .= "<input class='button submit' type='submit' name='donate' value='$" . number_format($amount, 0) . "'/>";
        }
        $content .= "</div>";
    }
    if( isset($block['other']) && $block['other'] == 'yes' ) {
        $content .= "<div class='other-amount'>"
            . "<b>Other Amount:</b>"
            . "<input class='quantity' type='text' name='amount' value=''/>"
            . "<input class='button submit' type='submit' name='donate' value='Add'/>"
            . "</div>";
    }

    $content .= "</form>";
    $content .= "</div>"
        . "</div>"
        . "</div>";


    return array('stat'=>'ok', 'content'=>$content);
}
?>
