<?php
//
// Description
// -----------
// Process the account page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_accountRequestProcess(&$ciniki, $tnid, &$request) {

    //
    // Add breadcrumbs update
    //
    $request['breadcrumbs'][] = array(
        'page_id' => 'account',
        'page-class' => 'page-account',
        'title' => 'Account',
        'url' => $request['ssl_domain_base_url'] . '/account',
        );

    //
    // Set no caching
    //
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
    header("Cache-Control: no-store, no-cache, must-revalidate"); 
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    //
    // Check if maintanence mode
    //
    if( isset($ciniki['config']['ciniki.core']['maintenance']) && $ciniki['config']['ciniki.core']['maintenance'] == 'on' ) {
        if( isset($ciniki['config']['ciniki.core']['maintenance.message']) && $ciniki['config']['ciniki.core']['maintenance.message'] != '' ) {
            $msg = $ciniki['config']['ciniki.core']['maintenance.message'];
        } else {
            $msg = "We are currently doing maintenance on the system and will be back soon.";
        }

        return array('stat'=>'503', 'err'=>array('code'=>'maintenance', 'msg'=>$msg));
    }

    //
    // Check if should be forced to SSL
    //
    if( (!isset($ciniki['config']['ciniki.core']['ssl']) || $ciniki['config']['ciniki.core']['ssl'] != 'off')
        && (!isset($_SERVER['HTTP_CLUSTER_HTTPS']) || $_SERVER['HTTP_CLUSTER_HTTPS'] != 'on')
        && (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')
        && (!isset($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] != '443' ) 
        ) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        return array('stat'=>'exit');
    }


    //
    // Check if logout was requested
    //
    if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'logout' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountLogoutProcess');
        //
        // If required, the following can be added so a timeout can be triggered by javascript setTimeout
        //
        if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'timeout' ) {
            return ciniki_wng_accountLogoutProcess($ciniki, $tnid, $request, 'yes');
        }
        return ciniki_wng_accountLogoutProcess($ciniki, $tnid, $request, 'no');
    }

    //
    // Verify the user is logged in, otherwise the accountLoginProcess will return the
    // login form.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountLoginProcess');
    $rc = ciniki_wng_accountLoginProcess($ciniki, $tnid, $request);
    if( $rc['stat'] != 'authenticated' ) {
        return $rc;
    }

    //
    // NOTE: At this point the customer is considered logged in
    //

    //
    // Check if a timeout is specified
    //
/*    if( isset($settings['account-timeout']) && $settings['account-timeout'] > 0 ) {
        $request['response']['js'] .= 'setInterval(function(){'
            . 'window.location.href="' . $request['ssl_domain_base_url'] . '/account/logout/timeout";'
            . '},' 
            . ($settings['account-timeout']*60000) 
            . ');';
    } */

    //
    // Check if there was a switch of customer (parent switching between child accounts)
    // This is a special case because of the redirects
    //
    if( isset($request['uri_split'][1]) && $request['uri_split'][1] == 'switch'
        && isset($request['uri_split'][2]) && $request['uri_split'][2] != '' 
        && isset($request['session']['customers'])
        && isset($request['session']['customers'][$request['uri_split'][2]])
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'accountSwitchProcess');
        return ciniki_wng_accountSwitchProcess($ciniki, $tnid, $request, $request['uri_split'][2]);
    }

//    $request['response']['blocks'][] = array(
//        'type' => 'html',
//        'html' => "<pre>server" . print_r($_SERVER, true) . "</pre>",
//        );
    $request['response']['blocks'][] = array(
        'type' => 'content',
        'content' => "<br/></br><center>Account Page - Now logged in</center><br/><br/><br/>",
        );
    
    return array('stat'=>'ok');

//    print "<pre>" . print_r($request, true) . "</pre>";
//    print "<pre>" . print_r($ciniki['tenant'], true) . "</pre>";
//    exit;

    //
    // Gather the submodule menu items
    //
/*    $submenu = array();
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'accountSubMenuItems');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $settings, $tnid);
            if( $rc['stat'] == 'ok' && isset($rc['submenu']) ) {
                $submenu = array_merge($submenu, $rc['submenu']);
            }
        }
    }

    //
    // Sort the menu items by priority
    //
    usort($submenu, function($a, $b) {
        if( $a['priority'] == $b['priority'] ) {
            return 0;
        }
        // Sort so largest priority is top of list or first menu item
        return ($a['priority'] < $b['priority'])?1:-1;
    });
*/
    //
    // Check for a module to process the request
    //
    $requested_item = null;
    $base_url = $request['base_url'] . '/account';
    if( isset($request['uri_split'][0]) && $request['uri_split'][0] != '' ) {
        $requested_page_url = $base_url . '/' . $request['uri_split'][0];
        foreach($submenu as $item) {
            if( strncmp($requested_page_url, $item['url'], strlen($requested_page_url)) == 0 ) {
                $requested_item = $item;
                break;
            }
        }
    } 
    //
    // Nothing requested, default to the first item in the submenu
    //
    elseif( isset($submenu[0]) ) {
        $requested_item = $submenu[0];
        if( !isset($request['uri_split'][0]) ) {
            $request['uri_split'] = explode('/', preg_replace('#' . $base_url . '#', '', $requested_item['url'], 1));
            if( isset($request['uri_split'][0]) && $request['uri_split'][0] == '' ) {
                array_shift($request['uri_split']);
            }
        }
    } 

    if( $requested_item == null ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.13', 'msg'=>'Requested page not found.'));
    }

    //
    // Process the request
    //
    $content = '';
    $rc = ciniki_core_loadMethod($ciniki, $requested_item['package'], $requested_item['module'], 'wng', 'accountProcessRequest');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.web.14', 'msg'=>'Requested page not found.', 'err'=>$rc['err']));
    }
    $fn = $rc['function_call'];
    $rc = $fn($ciniki, $tnid, $request, array(
        'page_title'=>'Account', 
        'breadcrumbs'=>$breadcrumbs,
        'base_url'=>$base_url,
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $page = $rc['page'];
    if( isset($page['breadcrumbs']) ) {
        $breadcrumbs = $page['breadcrumbs'];
    }

/*    //
    // Check if a container class was set
    //
    if( isset($page['container-class']) && $page['container-class'] != '' ) {
        if( !isset($ciniki['request']['page-container-class']) ) { 
            $ciniki['request']['page-container-class'] = $page['container-class'];
        } else {
            $ciniki['request']['page-container-class'] .= ' ' . $page['container-class'];
        }
    } */

    //
    // Process the blocks of content before header incase require includes in header
    //
/*    $block_content = "<div class='entry-content'>\n";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlocks');
    if( isset($page['blocks']) ) {
        $rc = ciniki_web_processBlocks($ciniki, $settings, $ciniki['request']['tnid'], $page['blocks']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $block_content .= $rc['content'];
    }
    $block_content .= "</div>";
    $block_content .= "</article>";

    //
    // Add the header
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageHeader');
    $rc = ciniki_web_generatePageHeader($ciniki, $settings, 'Account', $submenu);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $page_content = $rc['content'];
    
    //
    // Check if article title and breadcrumbs should be displayed above content
    //
    if( (isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes')
        || (isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes')
        ) {
        $page_content .= "<div class='page-header'>";
        if( isset($settings['theme']['header-article-title']) && $settings['theme']['header-article-title'] == 'yes' ) {
            $page_content .= "<h1 class='page-header-title'>" . $page['title'] . "</h1>";
        }
        if( isset($settings['theme']['header-breadcrumbs']) && $settings['theme']['header-breadcrumbs'] == 'yes' && isset($breadcrumbs) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBreadcrumbs');
            $rc = ciniki_web_processBreadcrumbs($ciniki, $settings, $ciniki['request']['tnid'], $breadcrumbs);
            if( $rc['stat'] == 'ok' ) {
                $page_content .= $rc['content'];
            }
        }
        $page_content .= "</div>";
    }

    $page_content .= "<div id='content'>";

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'left' ) {
        //
        // Add the sidebar content
        //
        $page_content .= "<div class='sidebar-menu-toggle'>"
            . "<button type='button' id='sidebar-menu-toggle' class='sidebar-menu-toggle'><i class='fa fa-bars'></i></button>"
            . "</div>";
        $page_content .= "<aside id='sidebar-menu' class='col-left-narrow sidebar-menu'>";
        $page_content .= "<div class='aside-content sidebar-menu'>";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockMenu');
        $rc = ciniki_web_processBlockMenu($ciniki, $settings, $ciniki['request']['tnid'], array('title'=>'', 'menu'=>$sidebar_menu));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
        $page_content .= "</div>";
        $page_content .= "</aside>";

        $page_content .= "<article class='page col-right-wide'>\n";
    } elseif( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'right' ) {
        $page_content .= "<article class='page col-left-wide'>\n";
    } else {
        $page_content .= "<article class='page'>\n";
    }

    $page_content .= "<header class='entry-title'><h1 id='entry-title' class='entry-title'>$article_title</h1></header>";

    $page_content .= $block_content;

    if( isset($settings['page-account-sidebar']) && $settings['page-account-sidebar'] == 'right' ) {
        //
        // Add the sidebar content
        //
        $page_content .= "<div class='sidebar-menu-toggle'>"
            . "<button type='button' id='sidebar-menu-toggle' class='sidebar-menu-toggle'><i class='fa fa-bars'></i></button>"
            . "</div>";
        $page_content .= "<aside id='sidebar-menu' class='col-right-narrow sidebar-menu'>";
        $page_content .= "<div class='aside-content sidebar-menu'>";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processBlockMenu');
        $rc = ciniki_web_processBlockMenu($ciniki, $settings, $ciniki['request']['tnid'], array('title'=>'', 'menu'=>$sidebar_menu));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page_content .= $rc['content'];
        $page_content .= "</div>";
        $page_content .= "</aside>";
    }

    $page_content .= "</div>";

    //
    // Add the footer
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'generatePageFooter');
    $rc = ciniki_web_generatePageFooter($ciniki, $settings);
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $page_content .= $rc['content'];

    $request['response']['blocks'][] = array(
        'type' => 'content',
        'content' => "<br/></br><center>Account Page - Not yet implemented</center><br/><br/><br/>",
        );
   */

    return array('stat'=>'ok');
}
?>
