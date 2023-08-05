<?php
//
// Description
// -----------
// This function will generate the header for the website, to be displayed 
// at the top of the all pages.
//
// Arguments
// --------- 
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
// title:           The title to use for the page.
//
// Returns
// -------
//
function ciniki_wng_pageHeaderGenerate(&$ciniki, $tnid, $request) {

    //
    // Store the header content
    //
    $content = '';

    $headerblocks = array();
    if( isset($request['site']['headersections']) ) {
        foreach($request['site']['headersections'] as $section) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'sectionRequestProcess');
            $rc = ciniki_wng_sectionRequestProcess($ciniki, $tnid, $request, $section);
            if( $rc['stat'] == 'exit' ) {
                return $rc;
            }
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.101', 'msg'=>'Unable to process the section', 'err'=>$rc['err']));
            }

            //
            // Add any resulting blocks to the request['response']['blocks'] array
            //
            if( isset($rc['blocks']) ) {
                foreach($rc['blocks'] as $block) {
                    $headerblocks[] = $block;
                }
            }
        }
    }
   
    //
    // Check for special case browsers, and add their information to the html_class
    //
    $html_class = '';
    if( isset($_SERVER['HTTP_USER_AGENT']) ) {
        if( preg_match('/Mozilla.*compatible; MSIE 9.0; Windows.*Trident/', $_SERVER['HTTP_USER_AGENT']) ) {
            $html_class .= ($html_class != '' ? ' ':'') . 'browser-ie9 no-flexbox';
        }
    }
    if( isset($request['site']['css_classes']) && $request['site']['css_classes'] != '' ) {
        $html_class .= ($html_class != '' ? ' ':'') . $request['site']['css_classes']; 
    }
    elseif( isset($request['site']['permalink']) && $request['site']['permalink'] != '' ) {
        $html_class .= ($html_class != '' ? ' ':'') . 'site-' . $request['site']['permalink'];
    }

    // Generate the head content
    $content .= "<!DOCTYPE html>\n"
        . "<html class='" . $html_class . "'>\n"
        . "<head>\n";
    $title = '';
    if( isset($request['site']['settings']['header-site-title']) 
        && isset($request['site']['settings']['header-site-title']) != ''
        ) {
        $title .= $request['site']['settings']['header-site-title'];
    }
    if( isset($request['page']['seo_title']) && $request['page']['seo_title'] != '' ) {
        $title .= " - " . $request['page']['seo_title'];
    }
    elseif( isset($request['page']['page_title']) && $request['page']['page_title'] != '' ) {
        $title .= " - " . $request['page']['page_title'];
    }
    elseif( isset($request['page']['title']) && $request['page']['title'] != '' ) {
        $title .= " - " . $request['page']['title'];
    }

    $content .= '<title>' . $title . '</title>';
    if( isset($request['page']['seo_desc']) && $request['page']['seo_desc'] != '' ) {
        $content .= '<meta name="description" content="' . str_replace('"', '&quot;', $request['page']['seo_desc']) . '"/>';
    }

    $content .= "<link rel='icon' href='" . $request['site']['cache_url'] . "/theme/favicon.png' type='image/png' />\n";

    //
    // Add CSS and javascript
    //
    $content .= "<script src='" . $request['site']['cache_url'] . "/theme/site.js?ts=" . filemtime($request['site']['cache_dir'] . '/theme/site.js') . "'></script>\n";
    $content .= "<link rel='stylesheet' type='text/css' media='all' href='" . $request['site']['cache_url'] . "/theme/site.css?ts=" . filemtime($request['site']['cache_dir'] . '/theme/site.css') . "'/>\n";

    //
    // Check for head scripts
    //
    if( isset($request['response']['head']['scripts']) ) {
        foreach($request['response']['head']['scripts'] as $s) {
            $content .= "<script "
                . "src='" . (isset($s['src']) ? $s['src'] : '') . "' "
                . "type='" . (isset($s['type']) ? $s['type'] : 'text/javascript') . "' "
                . ">" 
                . "</script>";
        }
    }
    if( isset($request['response']['js']) ) {
        $content .= "<script type='text/javascript'>" 
            . $request['response']['js'] 
            . "</script>";
    }
    //
    // Check for jump to top button
    //
    if( isset($request['site']['settings']['footer-jump-to-top']) && $request['site']['settings']['footer-jump-to-top'] == 'yes' ) {
        $content .= "<script type='text/javascript'>" 
            . 'document.addEventListener("scroll", () => {'
                . 'if(C.sC().scrollTop>500){'
                    . 'C.rC(C.gE("jumptotop"),"hidden");'
                . '}else{'
                    . 'C.aC(C.gE("jumptotop"),"hidden");'
                . '}'
            . '});'
            . "</script>";
    }

    //
    // Check head links
    //

/*    *** PROBABLY WON"T NEED THESE *** */
/*    if( isset($ciniki['response']['head']['links']) ) {
        foreach($ciniki['response']['head']['links'] as $link) {
            $content .= "<link rel='" . $link['rel'] . "'" . (isset($link['title'])?" title='" . $link['title'] . "'":'') . " href='" . $link['href'] . "'/>\n";
        }
    }

    if( isset($ciniki['response']['web-app']) && $ciniki['response']['web-app'] == 'yes' ) {
        $content .= '<meta name="apple-mobile-web-app-capable" content="yes" />' . "\n";
        $content .= '<meta id="apple_sbarstyle" name="apple-mobile-web-app-status-bar-style" content="black" />' . "\n";
        $content .= '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />' . "\n";
    }
*/    
    //
    // Header to support mobile device resize
    //
    $content .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    $content .= '<meta charset="UTF-8">' . "\n";

    if( isset($request['site']['settings']['meta-google-site-verification']) 
        && $request['site']['settings']['meta-google-site-verification'] != '' 
        ) {
        $content .= '<meta name="google-site-verification" content="' . $request['site']['settings']['meta-google-site-verification'] . '"/>' . "\n";
    }
    if( isset($request['site']['settings']['meta-pinterest-site-verification']) 
        && $request['site']['settings']['meta-pinterest-site-verification'] != '' 
        ) {
        $content .= '<meta name="p:domain_verify" content="' . $request['site']['settings']['meta-pinterest-site-verification'] . '"/>' . "\n";
    }

/*
    if( isset($request['site']['settings']['site-meta-robots']) 
        && $request['site']['settings']['site-meta-robots'] != '' 
        ) {
        $content .= '<meta name="robots" content="' . $request['site']['settings']['site-meta-robots'] . '"/>' . "\n";
    }
*/
    //
    // Check for header Open Graph (Facebook) object information, for better linking into facebook
    //
    if( isset($ciniki['response']['head']['og']) ) {
        $og_site_name = $ciniki['tenant']['name'];
        foreach($ciniki['response']['head']['og'] as $og_type => $og_value) {
            if( $og_value != '' ) {
                if( $og_type == 'description' ) {
                    $content .= "<meta name='$og_type' content='$og_value' />\n";
                }
                if( $og_type == 'site_name' ) {
                    $og_site_name = $og_value;
                }
                $content .= '<meta property="og:' . $og_type . '" content="' . preg_replace('/"/', "'", $og_value) . '"/>' . "\n";
            }
        }
        if( $og_site_name != '' ) {
            $content .= "<meta property=\"og:site_name\" content=\"" . preg_replace('/"/', "\'", $og_site_name) . "\"/>\n";
        }
        if( $ciniki['response']['head']['og']['title'] == '' ) {
            $content .= '<meta property="og:title" content="' . $ciniki['tenant']['details']['name'] . ' - ' . $title . '"/>' . "\n";
        }
    }

    //
    // Include google analytics
    //
    if( isset($request['site']['settings']['meta-google-analytics-account']) && $request['site']['settings']['meta-google-analytics-account'] != '' ) {
        $content .= "<script type='text/javascript'>\n"
            . "var _gaq = _gaq || [];\n"
            . "_gaq.push(['_setAccount', '" . $request['site']['settings']['meta-google-analytics-account'] . "']);\n"
            . "_gaq.push(['_trackPageview']);\n"
            . "(function() {\n"
                . "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n"
                . "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n"
                . "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n"
            . "})();\n"
            . "</script>\n"
            . "";
    }

    //
    // Include latest (nov 2021) version google tag (gtag)
    //
    if( isset($request['site']['settings']['meta-google-tag-manager']) && $request['site']['settings']['meta-google-tag-manager'] != '' ) {
        $content .= '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $request['site']['settings']['meta-google-tag-manager'] . '"></script>';
        $content .= "<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);} "
            . "gtag('js', new Date()); "
            . "gtag('config', '" . $request['site']['settings']['meta-google-tag-manager'] . "');"
            . "</script>";
    }

    //
    // Include google tag manager
    //
    if( isset($request['site']['settings']['google-gtm-code']) && $request['site']['settings']['google-gtm-code'] != '' ) {
        $content .= "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':"
            . "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],"
            . "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src="
            . "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);"
            . "})(window,document,'script','dataLayer','"
                . $request['site']['settings']['google-gtm-code'] 
            . "');</script>"
            . "";
    }

    //
    // Include facebook pixel
    //
    if( isset($request['site']['settings']['meta-facebook-pixel-id']) && $request['site']['settings']['meta-facebook-pixel-id'] != '' ) {
        $content .= "<script>"
            . "!function(f,b,e,v,n,t,s)"
            . "{if(f.fbq)return;n=f.fbq=function(){n.callMethod?"
            . "n.callMethod.apply(n,arguments):n.queue.push(arguments)};"
            . "if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';"
            . "n.queue=[];t=b.createElement(e);t.async=!0;"
            . "t.src=v;s=b.getElementsByTagName(e)[0];"
            . "s.parentNode.insertBefore(t,s)}(window,document,'script',"
            . "'https://connect.facebook.net/en_US/fbevents.js');"
            . "fbq('init', '" . $request['site']['settings']['meta-facebook-pixel-id'] . "');"
            . "fbq('track', 'PageView');"
            . "</script>\n"
            . "";
    }

    //
    // Header to support fathom analytics
    //
    if( isset($request['site']['settings']['meta-fathom-analytics-siteid']) 
        && $request['site']['settings']['meta-fathom-analytics-siteid'] != '' 
        ) {
        $content .= '<script src="https://cdn.usefathom.com/script.js" data-site="'
            . $request['site']['settings']['meta-fathom-analytics-siteid']
            . '" defer></script>';
    }

    //
    // Setup the background image
    //
/*    if( isset($settings['site-background-image']) && $settings['site-background-image'] > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'getScaledImageURL');
        $rc = ciniki_web_getScaledImageURL($ciniki, $settings['site-background-image'], 'original', 0, 0, 90);
        if( $rc['stat'] == 'ok' ) {
            $content .= "<style>"
                . "html {"
                    . "background: url('" . $rc['url'] . "'); "
                    . "background-repeat: repeat-y; "
                    . "background-size: 100%; "
                    . "background-attachment: fixed; "
                    . "background-position-x: " . (isset($settings['site-background-position-x']) && $settings['site-background-position-x'] != '' ? $settings['site-background-position-x'] : '0') . ";"
                    . "background-position-y: " . (isset($settings['site-background-position-y']) && $settings['site-background-position-y'] != '' ? $settings['site-background-position-y'] : '0') . ";"
                . "}"
                . "</style>"
                . "";
        }
    } */

    $content .= "</head>\n";

    //
    // Generate header of the page
    //
    $content .= "<body>\n";

    //
    // Include google tag manager
    //
    if( isset($settings['site-google-gtm-code']) && $settings['site-google-gtm-code'] != '' ) {
        $content .= '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='
            . $settings['site-google-gtm-code'] 
            . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>'
            . '';
    }

    //
    // Add the breadcrumb page-class to page class
    //
    $page_classes = (isset($request['response']['page-container-class']) ? $request['response']['page-container-class'] : '');
    if( isset($request['breadcrumbs']) ) {
        foreach($request['breadcrumbs'] as $crumb) {
            // Skip home page when in sub pages
            if( $crumb['url'] == '/' && count($request['breadcrumbs']) > 1 ) {
                continue;
            }
            if( isset($crumb['page-class']) && $crumb['page-class'] != '' ) {
                $page_classes .= ($page_classes != '' ? ' ' : '') . $crumb['page-class'];
            }
        }
    }

    $content .= "<div id='page-container' class='" . $page_classes . "'>";
    $content .= "<header id='page-header'>";

    //
    // Generate the blocks
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'blocksGenerate');
    $rc = ciniki_wng_blocksGenerate($ciniki, $tnid, $request, $headerblocks);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.57', 'msg'=>'', 'err'=>$rc['err']));
    }
    $content .= $rc['content'];

    $content .= "</header>";

    return array('stat'=>'ok', 'content'=>$content);
}
?>
