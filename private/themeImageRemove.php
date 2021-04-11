<?php
//
// Description
// -----------
// This function will remove an image to the list of images available to the theme.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_themeImageRemove(&$ciniki, $tnid, $site_id, $image_id) {
    
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
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.65', 'msg'=>'Unable to load images', 'err'=>$rc['err']));
    }
    $setting = isset($rc['setting']) ? $rc['setting'] : array();
    if( isset($setting['detail_value']) && $setting['detail_value'] != '' ) {
        $images = explode(',', $setting['detail_value']);
    }

    //
    // Add the image_id if not already in array
    //
    if( ($key = array_search($image_id, $images)) !== false ) {

        unset($images[$key]);
        $detail_value = join(',', $images);

        //
        // Update or add the setting
        //
        if( $detail_value != $setting['detail_value'] ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
            $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.setting', $setting['id'], array(
                'detail_value' => $detail_value,
                ), 0x04);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.68', 'msg'=>'Unable to update the setting', 'err'=>$rc['err']));
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
