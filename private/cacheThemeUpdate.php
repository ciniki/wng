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

    // // Load the site //
    if( is_array($site_id) ) {
        $site = $site_id;
    } else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteLoad');
        $rc = ciniki_wng_siteLoad($ciniki, $tnid, $site_id, 'yes');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.63', 'msg'=>'Unable to load site', 'err'=>$rc['err']));
        }
        $site = isset($rc['site']) ? $rc['site'] : array();
    }

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
//        $css .= $site['settings']['theme-css-imports'];
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
            elseif( $file == 'favicon.png' ) {
                if( isset($site['settings']['favicon-image-id']) && $site['settings']['favicon-image-id'] > 0 ) {
                    // Ignore standard favicon
                } else {
                    copy($theme_filename, $cache_filename);
                    touch($cache_filename, filemtime($theme_filename));
                }
            }
            elseif( preg_match("/\.(jpg|png|svg|eot|ttf|woff|woff2)$/", $file) 
                && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($theme_filename)) 
                ) {
                copy($theme_filename, $cache_filename);
                touch($cache_filename, filemtime($theme_filename));
            }
        }
    }

    //
    // Check for favicon
    //
    if( isset($site['settings']['favicon-image-id']) && $site['settings']['favicon-image-id'] > 0 
        && (!isset($site['settings']['favicon-filename']) || $site['settings']['favicon-filename'] == '')
        ) {
        //
        // Lookup image details
        //
        $strsql = "SELECT images.id, "
            . "images.uuid, "
            . "images.original_filename, "
            . "UNIX_TIMESTAMP(images.last_updated) "
            . "FROM ciniki_images AS images "
            . "WHERE images.id = '" . ciniki_core_dbQuote($ciniki, $site['settings']['favicon-image-id']) . "' "
            . "AND images.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'image');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.267', 'msg'=>'Unable to load image', 'err'=>$rc['err']));
        }
        if( isset($rc['image']) ) {
            $filename = preg_replace("/^.*\.(gif|png|jpg|jpeg|webp|svg)$/", "favicon.$1", $rc['image']['original_filename']);
            $cache_filename = $site['cache_dir'] . '/theme/' . $filename;
            $storage_filename = $tenant_storage_dir . '/ciniki.images/' . $rc['image']['uuid'][0] . '/' . $rc['image']['uuid'];
            if( file_exists($storage_filename) 
                && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($storage_filename)) 
                ) {
                copy($storage_filename, $cache_filename);
            }
            //
            // Lookup the setting
            //
            $strsql = "SELECT settings.id, "
                . "settings.detail_value "
                . "FROM ciniki_wng_settings AS settings "
                . "WHERE settings.site_id = '" . ciniki_core_dbQuote($ciniki, $site['id']) . "' "
                . "AND settings.detail_key = 'favicon-filename' "
                . "AND settings.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . "";
            $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'setting');
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.270', 'msg'=>'Unable to load setting', 'err'=>$rc['err']));
            }
            if( isset($rc['setting']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.setting', $rc['setting']['id'], [
                    'detail_value' => $filename,
                    ], 0x04);
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.268', 'msg'=>'Unable to update the setting', 'err'=>$rc['err']));
                    }
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.269', 'msg'=>'Unable to update favicon', 'err'=>$rc['err']));
                }

            } else {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wng.setting', array(
                    'site_id' => $site['id'],
                    'detail_key' => 'favicon-filename',
                    'detail_value' => $filename,
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.271', 'msg'=>'Unable to add the setting', 'err'=>$rc['err']));
                }
            }
        }
    }


    //
    // Check theme directory specified, if it exists in wng/themes directory
    //
    if( isset($site['theme']) && $site['theme'] != '' ) {
        $themes_dir = $ciniki['config']['ciniki.core']['modules_dir'] . '/wng/themes/' . $site['theme'];
        if( file_exists($themes_dir) && ($dh = opendir($themes_dir)) !== false ) {
            while( ($file = readdir($dh)) !== false ) {
                $theme_filename = $themes_dir . '/' . $file;
                $cache_filename = $site['cache_dir'] . '/theme/' . $file;
                if( $file == 'site.css' ) {
                    $css .= file_get_contents($themes_dir . '/site.css');
                }
                elseif( preg_match("/^block-.*\.css/", $file) ) {
                    $css .= file_get_contents($themes_dir . '/' . $file);
                }
                elseif( preg_match("/^block-.*\.js/", $file) ) {
                    $js .= file_get_contents($themes_dir . '/' . $file);
                }
                elseif( preg_match("/\.(jpg|png|svg|eot|ttf|woff|woff2)$/", $file) 
                    && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($theme_filename)) 
                    ) {
                    copy($theme_filename, $cache_filename);
                    touch($cache_filename, filemtime($theme_filename));
                }
            }
        }
    }

    //
    // Add check if css and js should be minified. On development systems we don't want the minified
    //

    //
    // Check for any modules with additional css or js files
    //
    foreach($ciniki['tenant']['modules'] as $module) {
        //
        // Check if the module has the file wng/site.csss
        //
        $mod_dir = $ciniki['config']['ciniki.core']['root_dir'] . '/' . $module['package'] . '-mods/' . $module['module']; 
        if( !file_exists($mod_dir . '/wng') ) {
            // Skip if no wng directory in module
            continue;
        }
        if( file_exists($mod_dir . '/wng/site.css') ) {
            $css .= file_get_contents($mod_dir . '/wng/site.css');
            //
            // Check for any images that need to be copied
            //
            if( ($dh = opendir($mod_dir . '/wng')) !== false ) {
                while( ($file = readdir($dh)) !== false ) {
                    $mod_filename = $mod_dir . '/wng/' . $file;
                    $cache_filename = $site['cache_dir'] . '/theme/' . $file;
                    if( preg_match("/\.(jpg|png|svg|mp4|mp3|eot|ttf|woff|woff2)$/", $file) 
                        && (!file_exists($cache_filename) || filemtime($cache_filename) < filemtime($mod_filename)) 
                        ) {
                        copy($mod_filename, $cache_filename);
                        touch($cache_filename, filemtime($mod_filename));
                    }
                }
            }
        }
        if( file_exists($mod_dir . '/wng/site.js') ) {
            $js .= file_get_contents($mod_dir . '/wng/site.js');
        }

        //
        // Check if theme directory exists in module
        //
        if( isset($site['theme']) && $site['theme'] != '' && file_exists($mod_dir . '/wng/' . $site['theme']) ) {
            if( file_exists($mod_dir . '/wng/' . $site['theme'] . '/site.css') ) {
                $css .= file_get_contents($mod_dir . '/wng/' . $site['theme'] . '/site.css');
                //
                // Check for any images that need to be copied
                //
                if( ($dh = opendir($mod_dir . '/wng/' . $site['theme'])) !== false ) {
                    while( ($file = readdir($dh)) !== false ) {
                        $mod_filename = $mod_dir . '/wng/' . $site['theme'] . '/' . $file;
                        $cache_filename = $site['cache_dir'] . '/theme/' . $file;
                        if( preg_match("/\.(jpg|png|svg|eot|ttf|woff|woff2)$/", $file) 
                            && (!file_exists($cache_filename) || filesize($cache_filename) != filesize($theme_filename)) 
                            ) {
                            copy($mod_filename, $cache_filename);
                        }
                    }
                }
            }
            if( file_exists($mod_dir . '/wng/' . $site['theme'] . '/site.js') ) {
                $js .= file_get_contents($mod_dir . '/wng/' . $site['theme'] . '/site.js');
            }
        }
    }

    //
    // Check if specific theme file for this website
    //
    

    //
    // Apply the theme-css-overrides settings
    //
    if( isset($site['settings']['theme-css-overrides']) ) {
        $css .= $site['settings']['theme-css-overrides'];
    }

    //
    // Remove comments and extra lines
    //
    $css = preg_replace("/^\s+/m", '', $css);
    $css = preg_replace("/\/\*.*\*\//", '', $css);
    $css = str_replace("\n", '', $css);
    $css = str_replace('; ', ';', $css);
    $css = str_replace(': ', ':', $css);
    $css = str_replace(', ', ',', $css);
    $css = str_replace(' {', '{', $css);

    $js = preg_replace("/^\s+/m", '', $js);
    $js = preg_replace("/^\/\/$/m", '', $js);
    $js = preg_replace("/\/\/ .*/", '', $js);
    $js = preg_replace("/\/\*.*\*\//", '', $js);
    $js = str_replace("\n\n", "\n", $js);
    $js = str_replace("\n\n", "\n", $js);

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
