<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_headermenu(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();
 
    //
    // Check if social icons requested
    // ** NOTE ** Any changes also need to be processors_socialicons
    //
    if( isset($s['social-icons']) && $s['social-icons'] == 'yes' ) {
        $icons = array();
        if( isset($request['site']['settings']['social-facebook-url']) 
            && $request['site']['settings']['social-facebook-url'] != ''
            ) {
            $icons[] = array(
                'type' => 'facebook',
                'name' => 'Facebook',
                'url' => $request['site']['settings']['social-facebook-url'],
                );
        }
        if( isset($request['site']['settings']['social-instagram-username']) 
            && $request['site']['settings']['social-instagram-username'] != ''
            ) {
            $icons[] = array(
                'type' => 'instagram',
                'name' => 'Instagram',
                'url' => 'https://instagram.com/' . $request['site']['settings']['social-instagram-username'],
                );
        }
        if( isset($request['site']['settings']['social-twitter-username']) 
            && $request['site']['settings']['social-twitter-username'] != ''
            ) {
            $icons[] = array(
                'type' => 'twitter',
                'name' => 'Twitter',
                'url' => 'https://twitter.com/' . $request['site']['settings']['social-twitter-username'],
                );
        }
        if( isset($request['site']['settings']['social-youtube-url']) 
            && $request['site']['settings']['social-youtube-url'] != ''
            ) {
            $icons[] = array(
                'type' => 'youtube',
                'name' => 'YouTube',
                'url' => $request['site']['settings']['social-youtube-url'],
                );
        }
    }

    //
    // Setup the account buttons blocks, if requested
    //
    if( ciniki_core_checkModuleActive($ciniki, 'ciniki.customers')
        && isset($s['account-buttons']) && $s['account-buttons'] == 'yes' 
        ) {
        if( isset($request['session']['customer']['id']) && $request['session']['customer']['id'] > 0 ) {
            $block = array(
                'type' => 'accountbuttons',
                'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
                'data' => array(
                    array(
                        'label' => (isset($s['account-label']) && $s['account-label'] != '' ? $s['account-label'] : 'Account'),
                        'url' => $request['ssl_domain_base_url'] . '/account',
                        ),
                    array(
                        'label' => (isset($s['logout-label']) && $s['logout-label'] != '' ? $s['logout-label'] : 'Logout'),
                        'url' => $request['ssl_domain_base_url'] . '/account/logout',
                        ),
                    ),
                );
        } else {
            $block = array(
                'type' => 'accountbuttons',
                'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
                'data' => array(
                    array(
                        'label' => (isset($s['signin-label']) && $s['signin-label'] != '' ? $s['signin-label'] : 'Sign In'),
                        'url' => $request['ssl_domain_base_url'] . '/account',
                        ),
                    ),
                );
        }
        
        //
        // Check if cart enabled
        //
        if( isset($request['site']['settings']['cart-active']) && $request['site']['settings']['cart-active'] == 'yes' ) {
            $num_items = '';
            if( isset($request['session']['cart']['num_items']) && $request['session']['cart']['num_items'] > 0 ) {
                $num_items = ' (' . $request['session']['cart']['num_items'] . ')';
            }
            array_unshift($block['data'], array(
                'label' => (isset($s['cart-label']) && $s['cart-label'] != '' ? $s['cart-label'] : 'Cart') . $num_items,
                'url' => $request['ssl_domain_base_url'] . '/cart',
                ));
        }
        //
        // Check if signin should be toggled depending on size along with main menu
        if( isset($s['account-toggle-hide']) && $s['account-toggle-hide'] == 'yes' && isset($s['toggle-em']) ) {
            $block['toggle-em'] = isset($s['toggle-em']) ? $s['toggle-em'] : '';
        }

        //
        // Check if social icons should be included
        //
        if( isset($s['social-icons']) && $s['social-icons'] == 'yes' && isset($icons) ) {
            $block['social-icons'] = $icons;
        }

        $blocks[] = $block;
    }

    $mainmenu = array();
    if( isset($request['site']['headermenu']) ) {
        $page_num = 0;
        foreach($request['site']['headermenu'] as $page_id) {
            if( isset($request['site']['pages'][$page_id]) ) {
                if( isset($s['image-position']) && $s['image-position'] == 'center' 
                    && $page_num == ceil(count($request['site']['headermenu'])/2) 
                    && isset($s['image-id']) && $s['image-id'] > 0 
                    ) {
                    $page = $request['site']['pages'][$request['site']['homepage_id']];
                    $item = array(
                        'title' => $page['title'],
                        'selected' => 'no',
                        'url' => $request['base_url'] . $page['path'],
                        'image-id' => $s['image-id'],
                        );
                    if( !isset($request['uri_split'][0]) || $request['uri_split'][0] == '' ) {
                        $item['selected'] == 'yes';
                    }
                    $mainmenu[] = $item;
                    $page_num++;
                }
                $page = $request['site']['pages'][$page_id];
                //
                // Skip hidden pages
                //
                if( (isset($page['flags']) && ($page['flags']&0x01) == 0) 
                    && (($page['flags']&0x02) == 0 || (!isset($request['session']['customer']['id']) || $request['session']['customer']['id'] <= 0))
                    && (($page['flags']&0x04) == 0 || (!isset($request['session']['customer']['id']) || $request['session']['customer']['id'] <= 0))
                    ) {
                    continue;
                }
                $item = array(
                    'title' => $page['title'],
                    'selected' => 'no',
                    'url' => $request['base_url'] . $page['path'],
                    );
                if( $page_id == $request['site']['homepage_id'] 
                    && (!isset($request['uri_split'][0]) || $request['uri_split'][0] == '')
                    ) {
                    $item['selected'] = 'yes';
                }
                elseif( isset($request['uri_split'][0]) && $request['uri_split'][0] == $page['permalink'] ) {
                    $item['selected'] = 'yes';
                }
                if( $page_id == $request['site']['homepage_id'] && isset($s['hide-home']) && $s['hide-home'] == 'yes' ) {
                    $item['hidden'] = 'yes';
                }
                if( $page['ptype'] == 40 && $page['redirect_url'] != '' ) {
                    // Only redirect to new tab if redirect is to a different site
                    if( preg_match("/https?:\/\//", $page['redirect_url']) ) {
                        $item['target'] = '_blank';
                    }
                    $item['url'] = $page['redirect_url'];
                }
                //
                // Check for submenu items
                //
                if( isset($page['children']) && isset($s['dropdown']) 
                    && $page_id != $request['site']['homepage_id'] 
                    && ($s['dropdown'] == 'full' || $s['dropdown'] == 'both') 
                    ) {
                    foreach($page['children'] as $child) {
                        if( isset($request['site']['pages'][$child]) ) {
                            $subpage = $request['site']['pages'][$child];
                            //
                            // Skip hidden pages
                            //
                            if( (isset($subpage['flags']) && ($subpage['flags']&0x01) == 0) 
                                && (($subpage['flags']&0x02) == 0 || (!isset($request['session']['customer']['id']) && $request['session']['customer']['id'] <= 0))
                                && (($subpage['flags']&0x04) == 0 || (!isset($request['session']['customer']['id']) && $request['session']['customer']['id'] <= 0))
                                ) {
                                continue;
                            }
                            $subitem = array(
                                'title' => $subpage['title'],
                                'selected' => 'no',
                                'url' => $request['base_url'] . $subpage['path'],
                                );
                            if( isset($request['uri_split'][1]) && $request['uri_split'][1] == $subpage['permalink'] ) {
                                $subitem['selected'] = 'yes';
                            }
                            if( !isset($item['items']) ) {
                                $item['items'] = array();
                            }
                            if( isset($subpage['flags']) && ($subpage['flags']&0x01) == 0 ) {
                                continue;
                            }
                            $item['items'][] = $subitem;
                        }
                    }
                }
                $mainmenu[] = $item;
                $page_num++;
            }
        }
    }

    //
    // Generate the dropdown nav for hamburger
    //
    $hamburgermenu = array();
    if( isset($request['site']['headermenu']) ) {
        foreach($request['site']['headermenu'] as $page_id) {
            if( isset($request['site']['pages'][$page_id]) ) {
                $page = $request['site']['pages'][$page_id];
                //
                // Check if hidden or private or membersonly page
                //
                if( (isset($page['flags']) && ($page['flags']&0x01) == 0) 
                    && (($page['flags']&0x02) == 0 || (!isset($request['session']['customer']['id']) || $request['session']['customer']['id'] <= 0))
                    && (($page['flags']&0x04) == 0 || (!isset($request['session']['customer']['id']) || $request['session']['customer']['id'] <= 0))
                    ) {
                    continue;
                }
                $item = array(
                    'title' => $page['title'],
                    'selected' => 'no',
                    'url' => $request['base_url'] . $page['path'],
                    );
                if( $page_id == $request['site']['homepage_id'] 
                    && (!isset($request['uri_split'][0]) || $request['uri_split'][0] == '')
                    ) {
                    $item['selected'] = 'yes';
                }
                elseif( isset($request['uri_split'][0]) && $request['uri_split'][0] == $page['permalink'] ) {
                    $item['selected'] = 'yes';
                }
                if( $page_id == $request['site']['homepage_id'] && isset($s['hide-home']) && $s['hide-home'] == 'yes' ) {
                    $item['hidden'] = 'yes';
                }
                if( $page['ptype'] == 40 && $page['redirect_url'] != '' ) {
                    // Only redirect to new tab if redirect is to a different site
                    if( preg_match("/https?:\/\//", $page['redirect_url']) ) {
                        $item['target'] = '_blank';
                    }
                    $item['url'] = $page['redirect_url'];
                }
                //
                // Check for submenu items
                //
                if( isset($page['children']) && isset($s['dropdown']) 
                    && $page_id != $request['site']['homepage_id'] 
                    && ($s['dropdown'] == 'hamburger' || $s['dropdown'] == 'both') 
                    ) {
                    foreach($page['children'] as $child) {
                        if( isset($request['site']['pages'][$child]) ) {
                            $subpage = $request['site']['pages'][$child];
                            //
                            // Skip hidden pages
                            //
                            if( (isset($subpage['flags']) && ($subpage['flags']&0x01) == 0) 
                                && (($subpage['flags']&0x02) == 0 || (!isset($request['session']['customer']['id']) && $request['session']['customer']['id'] <= 0))
                                && (($subpage['flags']&0x04) == 0 || (!isset($request['session']['customer']['id']) && $request['session']['customer']['id'] <= 0))
                                ) {
                                continue;
                            }
                            $subitem = array(
                                'title' => $subpage['title'],
                                'selected' => 'no',
                                'url' => $request['base_url'] . $subpage['path'],
                                );
                            if( isset($request['uri_split'][1]) && $request['uri_split'][1] == $subpage['permalink'] ) {
                                $subitem['selected'] = 'yes';
                            }
                            if( !isset($item['items']) ) {
                                $item['items'] = array();
                            }
                            if( isset($subpage['flags']) && ($subpage['flags']&0x01) == 0 ) {
                                continue;
                            }
                            $item['items'][] = $subitem;
                        }
                    }
                }
                $hamburgermenu[] = $item;
            }
        }

        if( !isset($s['account-toggle-hide']) || $s['account-toggle-hide'] != 'no' ) {
            if( ciniki_core_checkModuleActive($ciniki, 'ciniki.customers')
                && isset($s['account-buttons']) && $s['account-buttons'] == 'yes' 
                ) {
                //
                // Check if cart enabled
                //
                if( isset($request['site']['settings']['cart-active']) && $request['site']['settings']['cart-active'] == 'yes' ) {
                    $num_items = '';
                    if( isset($request['session']['cart']['num_items']) && $request['session']['cart']['num_items'] > 0 ) {
                        $num_items = ' (' . $request['session']['cart']['num_items'] . ')';
                    }
                    $hamburgermenu[] = array(
                        'title' => (isset($s['cart-label']) && $s['cart-label'] != '' ? $s['cart-label'] : 'Cart') . $num_items,
                        'selected' => (isset($request['uri_split'][0]) && $request['uri_split'][0] == 'cart' ? 'yes' : 'no'),
                        'url' => $request['ssl_domain_base_url'] . '/cart',
                        );
                }
                //
                // Check if customer logged in
                //
                if( isset($request['session']['customer']['id']) && $request['session']['customer']['id'] > 0 ) {
                    $hamburgermenu[] = array(
                        'title' => (isset($s['account-label']) && $s['account-label'] != '' ? $s['account-label'] : 'Account'),
                        'selected' => (isset($request['uri_split'][0]) && $request['uri_split'][0] == 'account' ? 'yes' : 'no'),
                        'url' => $request['ssl_domain_base_url'] . '/account',
                        );
                    $hamburgermenu[] = array(
                        'title' => (isset($s['logout-label']) && $s['logout-label'] != '' ? $s['logout-label'] : 'Logout'),
                        'selected' => 'no',
                        'url' => $request['ssl_domain_base_url'] . '/account/logout',
                        );
                } else {
                    $hamburgermenu[] = array(
                        'title' => (isset($s['signin-label']) && $s['signin-label'] != '' ? $s['signin-label'] : 'Sign In'),
                        'selected' => 'no',
                        'url' => $request['ssl_domain_base_url'] . '/account',
                        );
                }
            }
        }
    }
    $block = array(
        'type' => 'imagemenu',
        'image-id' => isset($s['image-id']) ? $s['image-id'] : 0,
        'main-menu' =>  $mainmenu,
        'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']) . ' header-menu',
        'dropdown' => isset($s['dropdown']) ? $s['dropdown'] : '',
        'toggle-em' => isset($s['toggle-em']) ? $s['toggle-em'] : '',
        'hamburger-menu' =>  $hamburgermenu,
        );
    if( isset($s['title']) && $s['title'] != '' ) {
        $block['title'] = $s['title'];
    }
    if( isset($s['image-position']) && $s['image-position'] == 'center' ) {
        $block['image-toggle-em'] = isset($s['toggle-em']) ? $s['toggle-em'] : '';
        $block['class'] .= ' center-logo';
    }
    if( isset($s['dropdown']) && $s['dropdown'] != '' && $s['dropdown'] != 'off' ) {
        $block['dropdown'] = $s['dropdown'];
    }

    $blocks[] = $block;

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
