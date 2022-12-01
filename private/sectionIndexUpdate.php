<?php
//
// Description
// -----------
// Update the index for the website page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_sectionIndexUpdate(&$ciniki, $tnid, &$site, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'objectIndexUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'objectIndexDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'objectImageIndexUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'objectImageIndexDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makeKeywords');

    //
    // Check if section has already been indexed
    //
    if( in_array($section['id'], $site['indexed_sections']) ) {
        return array('stat'=>'ok');
    }

    //
    // Load the existing objects
    //
    $strsql = "SELECT CONCAT_WS('.', object, object_id) AS oid, " 
        . "id, "
        . "uuid, "
        . "UNIX_TIMESTAMP(last_updated) AS last_updated_ts "
        . "FROM ciniki_wng_index "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $site['id']) . "' "
        . "AND section_id = '" . ciniki_core_dbQuote($ciniki, $section['id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'objects', 'fname'=>'oid', 
            'fields'=>array('oid', 'id', 'uuid', 'last_updated_ts'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.197', 'msg'=>'Unable to load items', 'err'=>$rc['err']));
    }
    $indexed_objects = isset($rc['objects']) ? $rc['objects'] : array();

    //
    // Load the indexable objects for this section and their last updated date
    //
    $s = explode('.', $section['ref']);
    if( isset($s[1]) ) {
        $rc = ciniki_core_loadMethod($ciniki, $s[0], $s[1], 'wng', 'indexObjects');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, $site, $section);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.198', 'msg'=>'Unable to get index objects', 'err'=>$rc['err']));
            }
            if( isset($rc['objects']) && count($rc['objects']) > 0 ) {
                $indexable_objects = $rc['objects'];
            }
        }
    }

    //
    // Compare for objects that need adding or updating
    //
    if( isset($indexable_objects) ) {
        foreach($indexable_objects as $oid => $obj) {
            //
            // Check if object doesn't exist or is newer
            //
            if( !isset($indexed_objects[$oid]) 
                || $indexed_objects[$oid]['last_updated_ts'] < $obj['last_updated_ts']
                ) {
                $obj['index_id'] = isset($indexed_objects[$oid]['id']) ? $indexed_objects[$oid]['id'] : 0;
                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'objectIndexUpdate');
                $rc = ciniki_wng_objectIndexUpdate($ciniki, $tnid, $site, $section, $obj);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.199', 'msg'=>'Unable to update indexed object', 'err'=>$rc['err']));
                }
            }
            elseif( isset($obj['image_id']) && $obj['image_id'] > 0 ) {
                $site['indexed_images'][] = $obj['image_id'];
            }
        }
    }

    //
    // Check for objects that need deleting
    //
    foreach($indexed_objects as $oid => $obj) {
        if( !isset($indexable_objects[$oid]) ) {
            $obj['index_id'] = $obj['id'];
            $rc = ciniki_wng_objectIndexDelete($ciniki, $tnid, $site, $section, $obj);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.220', 'msg'=>'Unable to remove object', 'err'=>$rc['err']));
            }
        }
    }

    $site['indexed_sections'][] = $section['id'];

    return array('stat'=>'ok');
}
?>
