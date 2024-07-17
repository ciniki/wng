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
function ciniki_wng_generators_pricelist(&$ciniki, $tnid, &$request, $block) {

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
        
        //
        // **NOTE** Buy now option was moved to contentphoto so it can be more inline with single
        //          button with popup for ticket selection.
        //
        // Check if buy-now option selected, generate the stripe button
        //
/*        if( isset($block['buy-now']) && $block['buy-now'] == 'yes' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'wng', 'stripeCheckoutCreate');
            $rc = ciniki_sapos_wng_stripeCheckoutCreate($ciniki, $tnid, $request, [
                'invoice_id' => 0,
                'buy-now' => 'yes',
                'prices' => $block['prices'],
                'return_url' => $request['ssl_domain_base_url'] . $request['page']['path'],
                ]);
            $buy_now_button = "<button class='button submit' onclick='{$rc['js']}; return false;' name='stripecheckout'>Buy Now</button>";
        } */

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
            $content .= "<tr class='price"
                . (isset($price['user-amount']) && $price['user-amount'] == 'yes' ? ' user-amount' : '')
                . "'>";
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
            if( !isset($price['user-amount']) || $price['user-amount'] != 'yes' ) {
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
            }
        
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
            $cart_html = '';
            if( isset($price['limited_units']) && isset($price['units_available']) 
                && $price['limited_units'] == 'yes' && $price['units_available'] < 1 
                ) {
                if( isset($price['sold-out-msg']) && $price['sold-out-msg'] != '' ) {
                    $cart_html .= ' ' . $price['sold-out-msg'];
                } else {
                    $cart_html .= ' Sold Out';
                }
            }
            elseif( isset($price['inprogress']) && $price['inprogress'] == 'yes' ) {
                $cart_html .= ' In Progress';
            }
            elseif( isset($price['regclosed']) && $price['regclosed'] == 'yes' ) {
                $cart_html .= ' Closed';
            }
            //
            // **Note** See previous note about buy now
            // If buy now has been requested
            //
/*            elseif( isset($block['buy-now']) && $block['buy-now'] == 'yes'
                && count($block['prices']) == 1 
                && isset($price['cart']) && $price['cart'] == 'yes' 
                && isset($request['site']['settings']['cart-active']) 
                && $request['site']['settings']['cart-active'] == 'yes'
                && isset($ciniki['tenant']['modules']['ciniki.sapos']) 
                && ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x08)  // Shopping cart enabled
                ) {
                $cart_html .= $buy_now_button;
            } */
            //
            // If quantity is limited, and not sold out
            //
            elseif( isset($price['cart']) && $price['cart'] == 'yes' 
                && isset($request['site']['settings']['cart-active']) 
                && $request['site']['settings']['cart-active'] == 'yes'
                && isset($ciniki['tenant']['modules']['ciniki.sapos']) 
                && ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x08)  // Shopping cart enabled
                ) {
                $cart_html .= "<form action='" .  $request['ssl_domain_base_url'] . "/cart' method='POST'>";
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
                } */
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
            elseif( isset($price['no-cart-msg']) ) {
                $cart_html .= $price['no-cart-msg'];
            }
            if( $cart_html != '' ) {
                if( isset($price['user-amount']) && $price['user-amount'] == 'yes' ) {
                    $content .= "<td class='buttons' colspan='2'>";
                } else {
                    $content .= "<td class='buttons'>";
                }
                $content .= $cart_html;
                $content .= "</td>";
            }

            if( isset($block['descriptions']) && $block['descriptions'] == 'yes' 
                && isset($price['description']) && $price['description'] != '' 
                ) {
                $content .= "<tr class='description'><td colspan='3'>" . $price['description'] . "</td></tr>";
            }

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
