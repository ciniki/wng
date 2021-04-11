<?php
//
// Description
// -----------
// This function will add an image to the list of images available to the theme.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_themeImageAdd(&$ciniki, $tnid, $site_id, $image_id) {
    
    //
    // Get the settings for the site
    //
    $images = array();
    $strsql = "SELECT id, detail_key, detail_value "
        . "FROM ciniki_wng_settings "
        . "WHERE site_id = '" . ciniki_core_dbQuote($ciniki, $site_id) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND detail_key = 'theme-images' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'setting');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.78', 'msg'=>'Unable to load images', 'err'=>$rc['err']));
    }
    $setting = isset($rc['setting']) ? $rc['setting'] : array();
    if( isset($setting['detail_value']) && $setting['detail_value'] != '' ) {
        $images = explode(',', $setting['detail_value']);
    }

    //
    // Add the image_id if not already in array
    //
    if( !in_array($image_id, $images) ) {
        $images[] = $image_id;
        sort($images);
        $detail_value = join(',', $images);

        //
        // Update or add the setting
        //
        if( isset($setting['id']) && $setting['id'] > 0 ) {
            if( $detail_value != $setting['detail_value'] ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.setting', $setting['id'], array(
                    'detail_value' => $detail_value,
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.79', 'msg'=>'Unable to update the setting', 'err'=>$rc['err']));
                }
            }
        } elseif( $detail_value != '' ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
            $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wng.setting', array(
                'site_id' => $site_id,
                'detail_key' => 'theme-images',
                'detail_value' => $detail_value,
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.69', 'msg'=>'Unable to update the setting', 'err'=>$rc['err']));
            }
        }

        //
        // Update the cached theme so images are copied
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheThemeUpdate');
        $rc = ciniki_wng_cacheThemeUpdate($ciniki, $tnid, $site_id);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
    }

    return array('stat'=>'ok');
}
?>
