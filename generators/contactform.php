<?php
//
// Description
// -----------
// Generic for generator
//
// Icons from iconfinder.com (free ones, or purchased)
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
    // Setup the contact form
    //
    $content .= "<div class='form-details"
        . (isset($block['form-title']) && $block['form-title'] != '' ? '' : ' no-title')
        . "'>";
    if( isset($block['form-title']) && $block['form-title'] != '' ) { 
        $content .= "<h2>" . $block['form-title'] . "</h2>";
    }
    
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

        if( isset($block['form-intro']) && $block['form-intro'] != '' ) { 
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['form-intro']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.159', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='form-intro'>" . $rc['content'] . "</div>";
        }
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

    //
    // Setup the address information
    //
    if( (isset($block['contact-intro']) && $block['contact-intro'] != '')
        || (isset($block['address']) && $block['address'] != '')
        || (isset($block['phone']) && $block['phone'] != '')
        || (isset($block['tollfree']) && $block['tollfree'] != '')
        || (isset($block['email']) && $block['email'] != '')
        || (isset($block['mailing']) && $block['mailing'] != '')
        || (isset($block['contact-outro']) && $block['contact-outro'] != '')
        || (isset($block['staff']) && count($block['staff']) > 0)
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

        // Mailing Address
        if( isset($block['mailing']) && $block['mailing'] != '' ) {
            $content .= "<div class='detail mailing'>";
            $content .= "<div class='icon'><svg class='stroke' style='enable-background:new 0 0 32 32;' version='1.1' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'><style type='text/css'>.st0{fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}</style><path class='st0' d='M16,25H2v-8c0-3.9,3.1-7,7-7h0c3.9,0,7,3.1,7,7V25z'/><path class='st0' d='M23,10L23,10c3.9,0,7,3.1,7,7v8H16'/><line class='st0' x1='9' x2='23' y1='10' y2='10'/><rect class='st0' height='6' width='6' x='13' y='25'/><polyline class='st0' points='22,18 22,4.9 22,1 30,1 30,5 22,5'/>"
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['mailing']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.160', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['mailing-label']) && $block['mailing-label'] != '' ? $block['mailing-label'] : "Mailing Address")
                . "</div><div class='value'>" . $rc['content'] . "</div></div>";
            $content .= "</div>";
        }

        // Phone
        if( (isset($block['phone']) && $block['phone'] != '')
            || (isset($block['tollfree']) && $block['tollfree'] != '')
            ) {
            $content .= "<div class='detail phone'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path d="M30.8,23l-3.9-3.9c-0.7-0.7-1.5-1.1-2.4-1.1c-0.9,0-1.7,0.4-2.5,1.1l-2.3,2.3c-0.2-0.1-0.4-0.2-0.6-0.3  c-0.3-0.1-0.5-0.3-0.7-0.4c-2.1-1.3-4.1-3.1-5.9-5.4c-0.9-1.1-1.5-2.1-1.9-3.1c0.6-0.5,1.1-1.1,1.7-1.6c0.2-0.2,0.4-0.4,0.6-0.6  c1.5-1.5,1.5-3.5,0-5l-2-2c-0.2-0.2-0.5-0.5-0.7-0.7C9.8,1.9,9.4,1.5,8.9,1.1C8.2,0.4,7.4,0,6.5,0C5.6,0,4.8,0.4,4.1,1.1l0,0  L1.6,3.5c-0.9,0.9-1.4,2-1.6,3.3c-0.2,2.1,0.4,4,0.9,5.3c1.2,3.1,2.9,6,5.5,9.1c3.1,3.7,6.9,6.7,11.2,8.8c1.6,0.8,3.8,1.7,6.3,1.9  c0.2,0,0.3,0,0.5,0c1.7,0,3-0.6,4.1-1.8c0,0,0,0,0,0c0.4-0.5,0.8-0.9,1.3-1.3c0.3-0.3,0.6-0.6,0.9-0.9c0.7-0.7,1.1-1.6,1.1-2.5  C31.9,24.6,31.5,23.7,30.8,23z M29.4,26.6c-0.3,0.3-0.6,0.6-0.9,0.9c-0.5,0.4-0.9,0.9-1.4,1.4c-0.7,0.8-1.6,1.1-2.7,1.1  c-0.1,0-0.2,0-0.3,0c-2.1-0.1-4.1-1-5.6-1.7c-4.1-2-7.6-4.8-10.6-8.3c-2.4-2.9-4.1-5.7-5.2-8.6C2.1,9.7,1.9,8.3,2,7  c0.1-0.8,0.4-1.5,1-2.1l2.4-2.4c0.4-0.3,0.7-0.5,1.1-0.5c0.5,0,0.8,0.3,1,0.5l0,0C8,2.9,8.5,3.3,8.9,3.7C9.1,4,9.3,4.2,9.6,4.4l2,2  c0.8,0.8,0.8,1.5,0,2.2c-0.2,0.2-0.4,0.4-0.6,0.6c-0.6,0.6-1.2,1.2-1.8,1.7c0,0,0,0,0,0c-0.6,0.6-0.5,1.2-0.4,1.6l0,0.1  c0.5,1.2,1.2,2.4,2.3,3.8l0,0c2,2.4,4.1,4.3,6.4,5.8c0.3,0.2,0.6,0.3,0.9,0.5c0.3,0.1,0.5,0.3,0.7,0.4c0,0,0.1,0,0.1,0.1  c0.2,0.1,0.5,0.2,0.7,0.2c0.6,0,1-0.4,1.1-0.5l2.5-2.5c0.2-0.2,0.6-0.5,1.1-0.5c0.4,0,0.8,0.3,1,0.5l4,4  C30.2,25.1,30.2,25.9,29.4,26.6z"/>'
                . "</svg></div>";
            $content .= "<div class='item'>";
            if( isset($block['phone']) && $block['phone'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['phone']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.161', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                }
                $content .= "<div class='label'>"
                    . (isset($block['phone-label']) && $block['phone-label'] != '' ? $block['phone-label'] : "Phone")
                    . "</div>";
                if( preg_match("/([0-9][0-9][0-9][^0-9][0-9][0-9][0-9][^0-9][0-9][0-9][0-9][0-9])/", $rc['content'], $m) ) {
                    $content .= "<div class='value'><a href='tel:{$m[1]}'>" . $rc['content'] . "</a></div>";
                } else {
                    $content .= "<div class='value'>" . $rc['content'] . "</div>";
                }
            }
            if( isset($block['tollfree']) && $block['tollfree'] != '' ) {
                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['tollfree']);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.266', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                }
                $content .= "<div class='label'>"
                    . (isset($block['tollfree-label']) && $block['tollfree-label'] != '' ? $block['tollfree-label'] : "Toll Free")
                    . "</div>";
                if( preg_match("/([0-9][0-9][0-9][^0-9][0-9][0-9][0-9][^0-9][0-9][0-9][0-9][0-9])/", $rc['content'], $m) ) {
                    $content .= "<div class='value'><a href='tel:{$m[1]}'>" . $rc['content'] . "</a></div>";
                } else {
                    $content .= "<div class='value'>" . $rc['content'] . "</div>";
                }
            }
            $content .= "</div></div>";
        }

        // Fax
        if( isset($block['fax']) && $block['fax'] != '' ) {
            $content .= "<div class='detail fax'>";
            $content .= "<div class='icon'><svg viewbox='0 0 12 12'>"
                . '<path d="M10.5,3.125H9.875V0.5c0-0.2070313-0.1679688-0.375-0.375-0.375h-7   c-0.2070313,0-0.375,0.1679688-0.375,0.375v2.625H1.5c-0.7583008,0-1.375,0.6171875-1.375,1.375v4   c0,0.7578125,0.6166992,1.375,1.375,1.375h0.625V11.5c0,0.2070313,0.1679688,0.375,0.375,0.375h7   c0.2070313,0,0.375-0.1679688,0.375-0.375V9.875H10.5c0.7583008,0,1.375-0.6171875,1.375-1.375v-4   C11.875,3.7421875,11.2583008,3.125,10.5,3.125z M2.875,0.875h6.25v2.25h-6.25V0.875z M9.125,11.125h-6.25v-3.25h6.25V11.125z    M11.125,8.5c0,0.3447266-0.2802734,0.625-0.625,0.625H9.875V7.5c0-0.2070313-0.1679688-0.375-0.375-0.375h-7   c-0.2070313,0-0.375,0.1679688-0.375,0.375v1.625H1.5c-0.3447266,0-0.625-0.2802734-0.625-0.625v-4   c0-0.3447266,0.2802734-0.625,0.625-0.625h9c0.3447266,0,0.625,0.2802734,0.625,0.625V8.5z" /><circle cx="9.5000305" cy="5.4999695" r="0.3750031"/>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['fax']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.162', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['fax-label']) && $block['fax-label'] != '' ? $block['fax-label'] : "Fax")
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
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.163', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['email-label']) && $block['email-label'] != '' ? $block['email-label'] : "Email")
                . "</div><div class='value'>" . $rc['content'] . "</div></div>";
            $content .= "</div>";
        }

        // Staff
        if( isset($block['staff']) && count($block['staff']) > 0 ) {
            $content .= "<div class='detail staff'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path d="M19.7,15c2.1-1.5,3.6-4,3.6-6.8C23.2,3.7,19.5,0,15,0S6.8,3.7,6.8,8.2c0,2.8,1.4,5.3,3.6,6.8  C4.8,16.3,1,20,1,24.6v3.3C1,30.2,2.8,32,5.1,32h19.8c2.3,0,4.1-1.8,4.1-4.1v-3.3C29,20,25.2,16.3,19.7,15z M8.8,8.2  C8.8,4.8,11.6,2,15,2s6.2,2.8,6.2,6.2s-2.8,6.2-6.2,6.2S8.8,11.7,8.8,8.2z M27,27.9c0,1.2-0.9,2.1-2.1,2.1H5.1C3.9,30,3,29.1,3,27.9  v-3.3c0-4.6,5.3-8.1,12-8.1s12,3.6,12,8.1V27.9z"/>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['email']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.164', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'>";
            foreach($block['staff'] as $staff) {
                $content .= "<div class='label'>" . $staff['name'] . "</div>";
                if( $staff['phone'] != '' ) {
                    $content .= "<div class='value'>" . $staff['phone'] . "</div>";
                }
                if( $staff['email'] != '' ) {
                    $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $staff['email']);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.165', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                    }
                    $content .= "<div class='value'>" . $rc['content'] . "</div>";
                }
            }
            $content .= "</div>";
            $content .= "</div>";
        }

        // Hours
        if( isset($block['hours-tuesday']) && $block['hours-tuesday'] != '' ) {
            $content .= "<div class='detail hours'>";
            $content .= "<div class='icon'><svg viewbox='0 0 32 32'>"
                . '<path class="st0" d="M16,0C7.2,0,0,7.2,0,16s7.2,16,16,16c8.7,0,15.7-6.8,16-15.4c0-0.2,0-0.4,0-0.6C32,7.2,24.8,0,16,0z M30,16.5   C29.7,24.1,23.6,30,16,30C8.3,30,2,23.7,2,16S8.3,2,16,2s14,6.3,14,14C30,16.2,30,16.3,30,16.5z"/><path class="st0" d="M17,15.5v-8c0-0.6-0.4-1-1-1s-1,0.4-1,1v9c0,0.6,0.4,1,1,1h9.5c0.6,0,1-0.4,1-1s-0.4-1-1-1H17z"/>'
                . "</svg></div>";
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['hours-label']) && $block['hours-label'] != '' ? $block['hours-label'] : "Hours")
                . "</div><div class='value'>";
            $content .= "<table><tbody>";
            if( isset($block['hours-monday']) && $block['hours-monday'] != '' ) {
                $content .= "<tr><td>Monday</td><td>" . $block['hours-monday'] . "</td></tr>";
            }
            if( isset($block['hours-tuesday']) && $block['hours-tuesday'] != '' ) {
                $content .= "<tr><td>Tuesday</td><td>" . $block['hours-tuesday'] . "</td></tr>";
            }
            if( isset($block['hours-wednesday']) && $block['hours-wednesday'] != '' ) {
                $content .= "<tr><td>Wednesday</td><td>" . $block['hours-wednesday'] . "</td></tr>";
            }
            if( isset($block['hours-thursday']) && $block['hours-thursday'] != '' ) {
                $content .= "<tr><td>Thursday</td><td>" . $block['hours-thursday'] . "</td></tr>";
            }
            if( isset($block['hours-friday']) && $block['hours-friday'] != '' ) {
                $content .= "<tr><td>Friday</td><td>" . $block['hours-friday'] . "</td></tr>";
            }
            if( isset($block['hours-saturday']) && $block['hours-saturday'] != '' ) {
                $content .= "<tr><td>Saturday</td><td>" . $block['hours-saturday'] . "</td></tr>";
            }
            if( isset($block['hours-sunday']) && $block['hours-sunday'] != '' ) {
                $content .= "<tr><td>Sunday</td><td>" . $block['hours-sunday'] . "</td></tr>";
            }

            $content .= "</tbody></table></div></div>";
            $content .= "</div>";
        }

        // Directions
        if( isset($block['directions']) && $block['directions'] != '' ) {
            $content .= "<div class='detail directions'>";
            $content .= "<div class='icon'><svg viewbox='0 0 512 512'>"
                . '<path d="M255.9,512c-68.4,0-132.7-26.6-181.1-75C-25,337.2-25,174.8,74.9,75C123.2,26.6,187.5,0,255.9,0   S388.6,26.6,437,75c48.4,48.4,75,112.6,75,181c0,68.4-26.6,132.7-75,181C388.6,485.4,324.3,512,255.9,512z M255.9,32   c-59.8,0-116.1,23.3-158.4,65.6c-87.3,87.3-87.3,229.5,0,316.8c42.3,42.3,98.6,65.6,158.4,65.6s116.1-23.3,158.4-65.6   C456.7,372.1,480,315.8,480,256c0-59.8-23.3-116.1-65.6-158.4C372,55.3,315.8,32,255.9,32z"/><path d="M391.7,407.8c-2.8,0-5.6-0.7-8.1-2.2L212.8,304.7c-2.3-1.4-4.3-3.3-5.6-5.6L106.4,128.4   c-3.7-6.3-2.7-14.3,2.5-19.5c5.2-5.2,13.2-6.2,19.5-2.5L299,207.3c2.3,1.4,4.3,3.3,5.6,5.6l100.9,170.7c3.7,6.3,2.7,14.3-2.5,19.4   C400,406.2,395.9,407.8,391.7,407.8z M232.7,279.3l113.7,67.1l-67.1-113.6l-113.7-67.1L232.7,279.3z"/><path d="M272.9,239c9.4,9.4,9.4,24.6,0,33.9c-9.4,9.4-24.6,9.4-33.9,0c-9.4-9.4-9.4-24.6,0-33.9   C248.3,229.7,263.5,229.7,272.9,239z"/>'
                . "</svg></div>";
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['directions']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.166', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='item'><div class='label'>"
                . (isset($block['directions-label']) && $block['directions-label'] != '' ? $block['directions-label'] : "Directions")
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

    


    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';



    return array('stat'=>'ok', 'content'=>$content);
}
?>
