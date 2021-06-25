<?php
//
// Description
// -----------
// Generic for generator
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_contactform(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    $content = '';

        
    $content .= "<div class='block-contactform"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    //
    // Setup the address information
    //
    if( (isset($block['contact-intro']) && $block['contact-intro'] != '')
        || (isset($block['address']) && $block['address'] != '')
        || (isset($block['phone']) && $block['phone'] != '')
        || (isset($block['email']) && $block['email'] != '')
        || (isset($block['contact-outro']) && $block['contact-outro'] != '')
        ) {
        $content .= "<div class='address-details'>";
        $content .= "<div class='details'>";
        // Intro message
        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h1>" . $block['title'] . "</h1>";
        }
        if( isset($block['contact-intro']) && $block['contact-intro'] != '' ) {
            $content .= "<div class='intro'>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['contact-intro']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.136', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
            $content .= "</div>";
        }

        // Address
        if( isset($block['address']) && $block['address'] != '' ) {
            $content .= "<div class='detail address'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path d="M25.5,6.1c-1.9-3.4-5.5-5.7-9.4-6C12.2-0.2,8.3,1.4,5.8,4.5s-3.3,7.2-2.2,11c0.8,2.6,2.4,4.8,3.9,6.8l6.9,9.3   c0.2,0.3,0.5,0.4,0.8,0.4c0,0,0,0,0,0c0.3,0,0.6-0.2,0.8-0.4C19,27.3,22,23,24.9,18.8l0.2-0.2c0,0,0,0,0,0   C27.5,14.9,27.6,9.9,25.5,6.1z M23.4,17.4l-0.2,0.2c-2.7,3.9-5.4,7.7-8,11.6L9.2,21c-1.4-1.9-2.9-3.9-3.6-6.2   c-0.9-3.1-0.2-6.6,1.8-9.1c1.8-2.3,4.8-3.7,7.8-3.7c0.3,0,0.5,0,0.8,0c3.2,0.3,6.3,2.2,7.8,5C25.5,10.3,25.4,14.4,23.4,17.4z"/><path d="M15.1,7.9C12.8,7.9,11,9.8,11,12s1.9,4.1,4.1,4.1s4.1-1.9,4.1-4.1S17.4,7.9,15.1,7.9z M15.1,14.2   c-1.2,0-2.1-1-2.1-2.1c0-1.2,1-2.1,2.1-2.1c1.2,0,2.1,1,2.1,2.1C17.2,13.2,16.3,14.2,15.1,14.2z"/></g>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['address']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.139', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['address-label']) && $block['address-label'] != '' ? $block['address-label'] : "Location")
                . "</div><div class='value'>" . $rc['content'] . "</div></div>";
            $content .= "</div>";
        }

        // Phone
        if( isset($block['phone']) && $block['phone'] != '' ) {
            $content .= "<div class='detail phone'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path d="M30.8,23l-3.9-3.9c-0.7-0.7-1.5-1.1-2.4-1.1c-0.9,0-1.7,0.4-2.5,1.1l-2.3,2.3c-0.2-0.1-0.4-0.2-0.6-0.3  c-0.3-0.1-0.5-0.3-0.7-0.4c-2.1-1.3-4.1-3.1-5.9-5.4c-0.9-1.1-1.5-2.1-1.9-3.1c0.6-0.5,1.1-1.1,1.7-1.6c0.2-0.2,0.4-0.4,0.6-0.6  c1.5-1.5,1.5-3.5,0-5l-2-2c-0.2-0.2-0.5-0.5-0.7-0.7C9.8,1.9,9.4,1.5,8.9,1.1C8.2,0.4,7.4,0,6.5,0C5.6,0,4.8,0.4,4.1,1.1l0,0  L1.6,3.5c-0.9,0.9-1.4,2-1.6,3.3c-0.2,2.1,0.4,4,0.9,5.3c1.2,3.1,2.9,6,5.5,9.1c3.1,3.7,6.9,6.7,11.2,8.8c1.6,0.8,3.8,1.7,6.3,1.9  c0.2,0,0.3,0,0.5,0c1.7,0,3-0.6,4.1-1.8c0,0,0,0,0,0c0.4-0.5,0.8-0.9,1.3-1.3c0.3-0.3,0.6-0.6,0.9-0.9c0.7-0.7,1.1-1.6,1.1-2.5  C31.9,24.6,31.5,23.7,30.8,23z M29.4,26.6c-0.3,0.3-0.6,0.6-0.9,0.9c-0.5,0.4-0.9,0.9-1.4,1.4c-0.7,0.8-1.6,1.1-2.7,1.1  c-0.1,0-0.2,0-0.3,0c-2.1-0.1-4.1-1-5.6-1.7c-4.1-2-7.6-4.8-10.6-8.3c-2.4-2.9-4.1-5.7-5.2-8.6C2.1,9.7,1.9,8.3,2,7  c0.1-0.8,0.4-1.5,1-2.1l2.4-2.4c0.4-0.3,0.7-0.5,1.1-0.5c0.5,0,0.8,0.3,1,0.5l0,0C8,2.9,8.5,3.3,8.9,3.7C9.1,4,9.3,4.2,9.6,4.4l2,2  c0.8,0.8,0.8,1.5,0,2.2c-0.2,0.2-0.4,0.4-0.6,0.6c-0.6,0.6-1.2,1.2-1.8,1.7c0,0,0,0,0,0c-0.6,0.6-0.5,1.2-0.4,1.6l0,0.1  c0.5,1.2,1.2,2.4,2.3,3.8l0,0c2,2.4,4.1,4.3,6.4,5.8c0.3,0.2,0.6,0.3,0.9,0.5c0.3,0.1,0.5,0.3,0.7,0.4c0,0,0.1,0,0.1,0.1  c0.2,0.1,0.5,0.2,0.7,0.2c0.6,0,1-0.4,1.1-0.5l2.5-2.5c0.2-0.2,0.6-0.5,1.1-0.5c0.4,0,0.8,0.3,1,0.5l4,4  C30.2,25.1,30.2,25.9,29.4,26.6z"/>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['phone']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.140', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['phone-label']) && $block['phone-label'] != '' ? $block['phone-label'] : "Phone")
                . "</div>";
            if( preg_match("/([0-9][0-9][0-9][^0-9][0-9][0-9][0-9][^0-9][0-9][0-9][0-9][0-9])/", $rc['content'], $m) ) {
                $content .= "<div class='value'><a href='tel:{$m[1]}'>" . $rc['content'] . "</a></div>";
            } else {
                $content .= "<div class='value'>" . $rc['content'] . "</div>";
            }
            $content .= "</div></div>";
        }

        // Email
        if( isset($block['email']) && $block['email'] != '' ) {
            $content .= "<div class='detail email'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path d="M32,6c0-0.1,0-0.1,0-0.2c0-0.1-0.1-0.1-0.1-0.2c0,0,0-0.1-0.1-0.1c0,0,0,0,0,0c0-0.1-0.1-0.1-0.2-0.1  c0,0-0.1-0.1-0.1-0.1c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1,0-0.2,0c0,0,0,0,0,0H1c0,0,0,0-0.1,0c-0.1,0-0.1,0-0.2,0c-0.1,0-0.1,0-0.2,0.1  c-0.1,0-0.1,0.1-0.2,0.1c-0.1,0-0.1,0.1-0.2,0.1c0,0,0,0,0,0c0,0,0,0.1-0.1,0.1c0,0.1-0.1,0.1-0.1,0.2C0,5.9,0,6,0,6  c0,0,0,0.1,0,0.1v19.7c0,0.6,0.4,1,1,1h30c0.6,0,1-0.4,1-1V6.2C32,6.1,32,6.1,32,6z M16.5,17.3L3.9,7.2h24.4L16.5,17.3z M2,24.8V8.3  l13.9,11.1c0,0,0.1,0.1,0.1,0.1c0,0,0.1,0,0.1,0.1c0.1,0,0.2,0.1,0.4,0.1l0,0c0,0,0,0,0,0c0.1,0,0.3,0,0.4-0.1c0,0,0.1,0,0.1-0.1  c0.1,0,0.1-0.1,0.2-0.1L30,8.3v16.5H2z"/>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['email']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.141', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['email-label']) && $block['email-label'] != '' ? $block['email-label'] : "Email")
                . "</div><div class='value'>" . $rc['content'] . "</div></div>";
            $content .= "</div>";
        }

        // Outro message
        if( isset($block['contact-outro']) && $block['contact-outro'] != '' ) {
            $content .= "<div class='intro'>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['contact-outro']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.142', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
            $content .= "</div>";
        }

        $content .= "</div>";
        $content .= "</div>";
    }

    //
    // Setup the contact form
    //
    $content .= "<div class='form-details'>";
    
    if( isset($block['status']) && $block['status'] == 'success' ) {
        $content .= "<div class='success-message'>";
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['success-message']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.137', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
        }
        $content .= $rc['content'];
        $content .= "</div>";
    } else {
        if( isset($block['status']) && $block['status'] == 'error' 
            && isset($block['error-message']) && $block['error-message'] == '' 
            ) {
            $content .= "<div class='error-message'>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['error-message']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.138', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= $rc['content'];
            $content .= "</div>";
        }

        $content .= "<div class='form'>";
        $content .= "<form action='' method='POST'>";
        $content .= "<input type='hidden' name='action' value='submit'>";
        
        foreach($block['fields'] as $fid => $field) {
            $field['class'] = isset($field['class']) ? $field['class'] : '';
            if( isset($field['type']) && $field['type'] == 'textarea' ) {
                $content .= "<div class='textarea {$field['class']}'>"
                    . "<label for='{$fid}'>{$field['label']}</label>"
                    . "<textarea name='{$fid}' class='medium' id='{$fid}'>{$field['value']}</textarea>"
                    . "</div>";
            } else {
                $content .= "<div class='input {$field['class']}'>"
                    . "<label for='{$fid}'>{$field['label']}</label>"
                    . "<input type='text' class='text' value='{$field['value']}' name='{$fid}' id='{$fid}'/>"
                    . "</div>";
            }
        }

        $content .= "<div class='submit'>"
            . "<input type='submit' value='Submit' name='submit' id='contact-form-submit' class='button'>"
            . "</div>";
        $content .= "</form>";
        $content .= "</form>";
        $content .= "</div>";
        
    }

    $content .= "</div>";

    


    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';



    return array('stat'=>'ok', 'content'=>$content);
}
?>
