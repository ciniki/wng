<?php
//
// Description
// -----------
// textcards
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_pricecards(&$ciniki, $tnid, &$request, $block) {

    $content = '';
   
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'urlProcess');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    numfmt_set_attribute($intl_currency_fmt, NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);
    $intl_currency = $rc['settings']['intl-default-currency'];
    
    //
    // Skip if nothing
    //
    if( !isset($block['items']) || count($block['items']) < 1 ) {
        return array('stat'=>'ok', 'content'=>'');
    }

    $num_items = count($block['items']);
    //
    // Find the quotients with no remainders. These are used to layout the grid evenly.
    //
    $quotient = '';
    for($i = 2;$i <= 10; $i++) {
        if( ($num_items % $i) == 0 ) {
            $quotient .= " q-{$i}";
        }
    }
    if( $quotient == '' ) {
        $quotient = ' q-prime';
    }

    //
    // Use the sequence number to give each carousel a unique id which 
    // allows several carousels on the same page
    //
    $content .= "<div class='block-pricecards"
        . (isset($block['collapsible']) && $block['collapsible'] == 'yes' ? ' collapsible' : '')
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    $content .= "<div class='items items-{$num_items}{$quotient}'>";

    $hlevel = 2;
    if( isset($block['level']) && $block['level'] == 3 ) {
        $hlevel = 3;
    }

    foreach($block['items'] as $iid => $item) {
        if( isset($item['synopsis']) && !isset($item['content']) ) {
            $item['content'] = $item['synopsis'];
        }
        if( isset($item['name']) && !isset($item['title']) ) {
            $item['title'] = $item['name'];
        }
        $content .= "<div class='item'>";
        $url = 'no';
        if( (isset($item['page']) && $item['page'] > 0) || (isset($item['url']) && $item['url'] != '') ) {
            $rc = ciniki_wng_urlProcess($ciniki, $tnid, $request,
                isset($item['page']) ? $item['page'] : 0,
                isset($item['url']) ? $item['url'] : ''
                );
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.252', 'msg'=>'Unable to process url', 'err'=>$rc['err']));
            }
            $content .= "<a target='" . $rc['target'] . "' href='" . $rc['url'] . "' />";
            $url = 'yes';
        }
        $content .= "<div class='item-wrap'>";

        $content .= "<div class='title'><h{$hlevel}>" . $item['title'] . "</h{$hlevel}>";
        if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
            $content .= "<h" . ($hlevel+1) . ">{$item['subtitle']}</h" . ($hlevel+1) . ">";
        }
        $content .= "</div>";

        if( isset($item['image-id']) && $item['image-id'] > 0 && is_numeric($item['image-id']) ) {
            //
            // Copy image to cache
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheImageSizes');
            $rc = ciniki_wng_cacheImageSizes($ciniki, $tnid, $request['site'], array( 
                'image_id' => $item['image-id'],
                'version' => (isset($block['image-version']) ? $block['image-version'] : 'original'),
                'maxwidth' => (isset($block['image-size']) ? $block['image-size'] : '2048'),
                'webp' => 'yes',
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.103', 'msg'=>'', 'err'=>$rc['err']));
            }
            $image = $rc;

            $content .= "<div class='image-wrap'><div class='image ratio-"
                . (isset($item['image-ratio']) && $item['image-ratio'] ? $item['image-ratio'] : '1-1')
                . "' "
                . "style='background:#fff url(" . $image['url'] . ") "
                . (isset($item['image-position']) && $item['image-position'] != '' ? $item['image-position'] : 'center')
                . ";";
            if( isset($block['image-format']) && $block['image-format'] == 'padded' ) {
                $content .= "background-size:contain;background-repeat:no-repeat;";
            } else {
                $content .= "background-size:cover;";
            }
            // Add image set
            if( isset($image['bg_set']) && $image['bg_set'] != '' ) {
                $content .= "background-image: -webkit-image-set("
                    . $image['bg_set']
                    . ");"; 
            }
            $content .= "'>";
            $content .= '</div></div>';
        }

        $content .= "<div class='info'>";
        if( isset($item['intro']) && $item['intro'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['intro']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.248', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='text'>" . $rc['content'] . "</div>";
        }
        if( isset($item['content']) && $item['content'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['content']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.251', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='text'>" . $rc['content'] . "</div>";
        }
        if( isset($item['benefits']) && is_array($item['benefits']) && count($item['benefits']) > 0 ) {
            $benefit_list = '';
            foreach($item['benefits'] as $benefit) {
                if( (isset($benefit['title']) && $benefit['title'] != '')
                    || (isset($benefit['synopsis']) && $benefit['synopsis'] != '')
                    ) {
                    $benefit_list .= '<li>'
                        . (isset($benefit['title']) && $benefit['title'] != '' ? "<b>{$benefit['title']}</b><br/>" : '')
                        . "";
                    if( isset($benefit['synopsis']) && $benefit['synopsis'] != '' ) {
                        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $benefit['synopsis']);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.250', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
                        }
                        $benefit_list .= $rc['content'];
                    }
                    $benefit_list .= '</li>';
                }
            }
            if( $benefit_list != '' ) {
                $content .= "<ul>{$benefit_list}</ul>";
            }
        }
        if( isset($item['ending']) && $item['ending'] != '' ) {
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $item['ending']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.249', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            $content .= "<div class='text'>" . $rc['content'] . "</div>";
        }
        $content .= '</div>';
  
        if( isset($item['prices']) && count($item['prices']) > 0 
            && isset($request['site']['settings']['cart-active']) 
            && $request['site']['settings']['cart-active'] == 'yes'
            && ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x08) 
            ) {
            $cart_html = '';
            foreach($item['prices'] as $price) {
                $final_price = $price['unit_amount'];
                $discount = '';
                if( isset($price['unit_discount_amount']) && $price['unit_discount_amount'] > 0 ) {
                    $discount .= " - " . numfmt_format_currency($intl_currency_fmt, $price['unit_discount_amount'], $intl_currency);
                    $final_price = bcsub($price['unit_amount'], $price['unit_discount_amount'], 4);
                }
                if( isset($price['unit_discount_percentage']) && $price['unit_discount_percentage'] > 0 ) {
                    $discount .= " - " .  sprintf('%0.2f', $price['unit_discount_percentage']) . "%";
                    $percentage = bcdiv($price['unit_discount_percentage'], 100, 4);
                    $final_price = bcsub($final_price, bcmul($final_price, $percentage, 4), 4);
                }

                // Apply the discounts
/*                if( !isset($price['user-amount']) || $price['user-amount'] != 'yes' ) {
                    $content .= "<td class='amount'>";
                    if( $final_price != $price['unit_amount'] ) {
                        $content .= '<del>' . numfmt_format_currency($intl_currency_fmt, $price['unit_amount'], $intl_currency) . '</del>' . $discount . ' ';
                        $content .= numfmt_format_currency($intl_currency_fmt, $final_price, $intl_currency);
                        if( isset($request['settings']['cart-currency-display']) && $request['settings']['cart-currency-display'] == 'yes' ) {
                            $content .= ' ' . $intl_currency;
                        }
                    } else {
                        $content .= numfmt_format_currency($intl_currency_fmt, $price['unit_amount'], $intl_currency);
                        if( isset($request['settings']['cart-currency-display']) && $request['settings']['cart-currency-display'] == 'yes' ) {
                            $content .= ' ' . $intl_currency;
                        }
                    }
                    $content .= "</td>";
                } */
                $cart_html .= "<form action='{$request['ssl_domain_base_url']}/cart' method='post'>";
                $cart_html .= "<input type='hidden' name='action' value='add'/>";
                $cart_html .= "<input type='hidden' name='object' value='" . $price['object'] . "'/>";
                $cart_html .= "<input type='hidden' name='object_id' value='" . $price['object_id'] . "'/>";
                if( isset($price['price_id']) ) {
                    $cart_html .= "<input type='hidden' name='price_id' value='" . $price['price_id'] . "'/>";
                }
                $cart_html .= "<input type='hidden' name='final_price' value='" . $final_price . "'/>";

                if( isset($price['user-amount']) && $price['user-amount'] == 'yes' ) {
                    $cart_html .= "<input type='hidden' name='quantity' value='1'/>"; 
                    $cart_html .= "<span class='user-amount'>"
                        . "<input class='user-amount' name='user_amount' type='text' value='' placeholder='$25' size='8'/>"
                        . "</span>";
                }
                elseif( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['limited_units']) && $price['units_available'] == 1 ) {
                    $cart_html .= "<input type='hidden' name='quantity' value='1'/>"; 
                }
                elseif( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['limited_units']) && $price['units_available'] > 1 ) {
                    $cart_html .= "<span class='quantity'><input class='quantity' name='quantity' type='text' value='1' size='2'/></span>";
                }
                elseif( !isset($price['limited_units']) || $price['limited_units'] == 'no' ) {
                    $cart_html .= "<span class='quantity'><input class='quantity' name='quantity' type='text' value='1' size='2'/></span>";
                }
                    
                $cart_html .= "<span class='submit'>"
                    . "<input class='button' type='submit' name='add' value='";
                if( isset($price['add_text']) && $price['add_text'] != '' ) {
                    $cart_html .= $price['add_text'];
                } else {
                    $cart_html .= 'Add to Cart';
                }
                $cart_html .= "'/></span>";
                $cart_html .= "</form>";
            }
            if( $cart_html != '' ) {
                $content .= "<div class='cart-buttons'>" . $cart_html . "</div>";
            }
        }

        $content .= '</div>';

        if( $url == 'yes' ) {
            $content .= "</a>";
        }

        $content .= '</div>';
    }

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    $js = '';
// Converted to use built in javascript function, dec 26, 2023
/*    if( isset($block['collapsible']) && $block['collapsible'] == 'yes' ) {
        $js = "function tctoggle(i,s){"
            . "var e=C.gE(i);"
            . "C.tC(e,'collapsed');"
            . "if(s!=null){e.scrollIntoView();}"
            . "};";
    } */

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
