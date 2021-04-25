<?php
//
// Description
// -----------
// pricelist
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_pricelist(&$ciniki, $tnid, $request, $block) {

    $content = '';

    //
    // Get tenant/user settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    //
    // Build the list of prices
    //
    if( isset($block['prices']) && is_array($block['prices']) && count($block['prices']) > 0 ) {
        $content .= "<div class='block-pricelist"
            . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
            . "'>";
        $content .= "<div class='wrap'>";
        $content .= "<div class='content'>";

        if( isset($block['title']) && $block['title'] != '' ) {
            $content .= "<h2>" . $block['title'] . "</h2>";
        }

        if( isset($block['intro']) && $block['intro'] != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['intro']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.91', 'msg'=>'Unable to process content', 'err'=>$rc['err']));
            }
            if( $rc['content'] != '' ) {
                $content .= "<div class='intro'>" . $rc['content'] . "</div>";
            }
        }
        
        $content .= "<table class='pricelist'>";
        foreach($block['prices'] as $pid => $price) {
            $content .= "<tr class='price'>";
            if( isset($price['name']) && $price['name'] != '' ) {
                $content .= "<td class='label'>" . $price['name'] . "</td>";
            }

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
        
            //
            // Check if display stock level
            //
            // ** Not currentlys supported, can be added back if required in the future **
/*
            if( isset($price['units_inventory']) ) {
                $inv = 'no';
                if( isset($request['settings']['page-cart-inventory-customers-display']) 
                    && $request['settings']['page-cart-inventory-customers-display'] == 'yes' 
                    ) {
                    $inv = 'yes';
                }
                if( isset($request['settings']['page-cart-inventory-members-display']) 
                    && $request['settings']['page-cart-inventory-members-display'] == 'yes' 
                    && isset($request['session']['customer']['member_status'])
                    && $request['session']['customer']['member_status'] == 10
                    ) {
                    $inv = 'yes';
                }
                if( $inv == 'yes' ) {
                    if( $price['units_available'] > 0 ) {
                        $content .= ' (' . $price['units_available'] . ' in stock)';
                    } else {
                        $content .= ' (backordered)';
                    }
                }
            }
*/
            // Check if sold out
            $sold_out = '';
            $content .= "<td class='buttons'>";
            if( isset($price['limited_units']) && isset($price['units_available']) 
                && $price['limited_units'] == 'yes' && $price['units_available'] < 1 
                ) {
                $content .= ' Sold Out';
            }
            //
            // If quantity is limited, and not sold out
            //
            elseif( isset($price['cart']) && $price['cart'] == 'yes' 
                && isset($request['site']['settings']['cart-active']) 
                && $request['site']['settings']['cart-active'] == 'yes'
                && isset($ciniki['tenant']['modules']['ciniki.sapos']) 
                && ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x08)
                ) {
                $content .= "<form action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST'>";
                $content .= "<input type='hidden' name='action' value='add'/>";
                $content .= "<input type='hidden' name='object' value='" . $price['object'] . "'/>";
                $content .= "<input type='hidden' name='object_id' value='" . $price['object_id'] . "'/>";
                if( isset($price['price_id']) ) {
                    $content .= "<input type='hidden' name='price_id' value='" . $price['price_id'] . "'/>";
                }
                $content .= "<input type='hidden' name='final_price' value='" . $final_price . "'/>";
                // Check what time of field the quantity should be based on how many are available
                /*if( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['units_available']) && $price['units_available'] > 1 
                    && $price['units_available'] <= 30 ) {
                    $content .= "<span class='quantity'>"
                        . "<select name='quantity'>";
                    for($i=1;$i<=$price['units_available'];$i++) {
                        $content .= "<option value='$i'>$i</option>";
                    }
                    $content .= "</select></span>";
                }
                else*/if( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['limited_units']) && $price['units_available'] == 1 ) {
                    $content .= "<input type='hidden' name='quantity' value='1'/>"; 
                }
                elseif( isset($price['limited_units']) && $price['limited_units'] == 'yes' 
                    && isset($price['limited_units']) && $price['units_available'] > 1 ) {
                    $content .= "<span class='quantity'><input class='quantity' name='quantity' type='text' value='1' size='2'/></span>";
                }
                elseif( !isset($price['limited_units']) || $price['limited_units'] == 'no' ) {
                    $content .= "<span class='quantity'><input class='quantity' name='quantity' type='text' value='1' size='2'/></span>";
                }
                    
                $content .= "<span class='submit'>"
                    . "<input class='button' type='submit' name='add' value='";
                if( isset($price['add_text']) && $price['add_text'] != '' ) {
                    $content .= $price['add_text'];
                } else {
                    $content .= 'Add to Cart';
                }
                $content .= "'/></span>";
                $content .= "</form>";
            }
            $content .= "</td>";

            $content .= "</tr>";
        }

        $content .= '</table>';

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
