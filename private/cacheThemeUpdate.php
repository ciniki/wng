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
function ciniki_wng_cacheThemeUpdate(&$ciniki, $tnid, $site_id) {

    //
    // Load the site
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
    $rc = ciniki_wng_siteLoad($ciniki, $tnid, $site_id, 'yes');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.63', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
    }
    $site = isset($rc['site']) ? $rc['site'] : array();

    //
    // Get the tenant storage directory
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'hooks', 'storageDir');
    $rc = ciniki_tenants_hooks_storageDir($ciniki, $tnid, array());
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $tenant_storage_dir = $rc['storage_dir'];
   
    //
    // Make sure the cache dir exists
    //
    if( !is_dir($site['cache_dir'] . '/theme') ) {
        if( mkdir($site['cache_dir'] . '/theme', 0755, true) === false ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.67', 'msg'=>'Unable to cache theme'));
        }
    }

    // FIXME: Implement ciniki_wng_sites.theme

    //
    // Build the css and javascript for this site
    //
    $css = '';
    $js = '';

    //
    // Apply the theme-css-imports as they must be at the start of the file
    //
    if( isset($site['settings']['theme-css-imports']) ) {
        $css .= $site['settings']['theme-css-imports'];
    }

    //
    // Load the core assets for css and js
    //
    $asset_dir = $ciniki['config']['ciniki.core']['modules_dir'] . '/wng/assets';
    if( file_exists($asset_dir . '/site.css') ) {
        $css .= file_get_contents($asset_dir . '/site.css');
    }
    if( file_exists($asset_dir . '/site.js') ) {
        $js .= file_get_contents($asset_dir . '/site.js');
    }

    //
    // Check for other css files, and copy image files to cache directory
    //
    if( ($dh = opendir($asset_dir)) !== false ) {
        while( ($file = readdir($dh)) !== false ) {
            $theme_filename = $asset_dir . '/' . $file;
            $cache_filename = $site['cache_dir'] . '/theme/' . $file;
            if( preg_match("/^block-.*\.css/", $file) ) {
                $css .= file_get_contents($asset_dir . '/' . $file);
            }
            elseif( preg_match("/^block-.*\.js/", $file) ) {
                $js .= file_get_contents($asset_dir . '/' . $file);
            }
            elseif( preg_match("/\.(jpg|png|svg)$/", $file) 
                && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($theme_filename)) 
                ) {
                copy($theme_filename, $cache_filename);
            }
        }
    }

    //
    // Add check if css and js should be minified. On development systems we don't want the minified
    //

    //
    // Check if there is a theme defined to be applied on top of core theme.
    // **Note**: This is not implemented but a placeholder for the future.
    //
    if( isset($site['theme']) && $site['theme'] != '' ) {
        $theme = $site['theme'];
        $theme_dir = $ciniki['config']['ciniki.core']['modules_dir'] . '/wng/themes/' . $theme;
        $css = '';
        if( file_exists($theme_dir . '/style.css') ) {
            $css .= file_get_contents($theme_dir . '/style.css');
        }
        if( file_exists($theme_dir . '/site.js') ) {
            $css .= file_get_contents($theme_dir . '/site.js');
        }
        if( ($dh = opendir($theme_dir)) !== false ) {
            while( ($file = readdir($dh)) !== false ) {
                $theme_filename = $theme_dir . '/' . $file;
                $cache_filename = $site['cache_dir'] . '/theme/' . $file;
                if( preg_match("/\.(jpg|png|svg)$/", $file) 
                    && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($theme_filename)) 
                    ) {
                    copy($theme_filename, $cache_filename);
                }
            }
        }
    }

    //
    // FIXME: Apply the theme settings
    //

    //
    // Check for any modules with additional css or js files
    //
    foreach($ciniki['tenant']['modules'] as $module) {
        //
        // Check if the module has the file wng/site.csss
        //
        $mod_dir = $ciniki['config']['ciniki.core']['root_dir'] . '/' . $module['package'] . '-mods/' . $module['module']; 
        if( file_exists($mod_dir . '/wng/site.css') ) {
            $css .= file_get_contents($mod_dir . '/wng/site.css');
            //
            // Check for any images that need to be copied
            //
            if( ($dh = opendir($mod_dir . '/wng')) !== false ) {
                while( ($file = readdir($dh)) !== false ) {
                    $mod_filename = $mod_dir . '/wng/' . $file;
                    $cache_filename = $site['cache_dir'] . '/theme/' . $file;
                    if( preg_match("/\.(jpg|png|svg)$/", $file) 
                        && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($theme_filename)) 
                        ) {
                        copy($mod_filename, $cache_filename);
                    }
                }
            }
        }
        if( file_exists($mod_dir . '/wng/site.js') ) {
            $js .= file_get_contents($mod_dir . '/wng/site.js');
        }
    }


    //
    // Apply the theme-css-overrides settings
    //
    if( isset($site['settings']['theme-css-overrides']) ) {
        $css .= $site['settings']['theme-css-overrides'];
    }

    //
    // Remove comments and extra lines
    //
    $css = preg_replace("/\/\*.*\*\//", '', $css);
    $css = preg_replace("/^\s+/m", '', $css);
    $css = str_replace("\n", '', $css);
    $css = str_replace('; ', ';', $css);
    $css = str_replace(': ', ':', $css);
    $css = str_replace(', ', ',', $css);
    $css = str_replace(' {', '{', $css);

    //
    // Check the theme dir exists in cache
    //
    $old_css = '';
    if( file_exists($site['cache_dir'] . '/theme/site.css') ) {
        $old_css = file_get_contents($site['cache_dir'] . '/theme/site.css');
    }
    if( file_exists($site['cache_dir'] . '/theme/site.js') ) {
        $old_js = file_get_contents($site['cache_dir'] . '/theme/site.js');
    }

    //
    // Create the cached css and js files
    //
    if( !file_exists($site['cache_dir'] . '/theme/site.css') || $css != $old_css ) {
        if( file_put_contents($site['cache_dir'] . '/theme/site.css', $css) === false ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.64', 'msg'=>'Unable to save site.css'));
        }
    }
    if( !file_exists($site['cache_dir'] . '/theme/site.js') || $js != $old_js ) {
        if( file_put_contents($site['cache_dir'] . '/theme/site.js', $js) === false ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.76', 'msg'=>'Unable to save site.js'));
        }
    }

    //
    // Check in settings for theme-images and copy those images
    // with their original filenames to cache/theme/filename
    //
    if( isset($site['settings']['theme-images']) && $site['settings']['theme-images'] != '' ) {
        $image_ids = explode(',', $site['settings']['theme-images']);
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
        if( count($image_ids) > 0 ) {
            $strsql = "SELECT images.id, "
                . "images.uuid, "
                . "images.original_filename, "
                . "UNIX_TIMESTAMP(images.last_updated) "
                . "FROM ciniki_images AS images "
                . "WHERE images.id IN (" . ciniki_core_dbQuoteIDs($ciniki, $image_ids) . ") "
                . "AND images.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "";
            $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'image');
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.74', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
            }
            $images = isset($rc['rows']) ? $rc['rows'] : array();
            foreach($images as $image) {
                $cache_filename = $site['cache_dir'] . '/theme/' . $image['original_filename'];
                $storage_filename = $tenant_storage_dir . '/ciniki.images/' . $image['uuid'][0] . '/' . $image['uuid'];
                if( file_exists($storage_filename) 
                    && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($storage_filename)) 
                    ) {
                    copy($storage_filename, $cache_filename);
                }
            }
        }
    }
    
    return array('stat'=>'ok');
}
?>
