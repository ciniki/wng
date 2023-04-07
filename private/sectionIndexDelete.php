<?php
//
// Description
// -----------
// Remove the index for the website section
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_sectionIndexDelete(&$ciniki, $tnid, &$site, $section) {

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
        . "image_id, "
        . "UNIX_TIMESTAMP(last_updated) AS last_updated_ts "
        . "FROM ciniki_wng_index "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $site['id']) . "' "
        . "AND section_id = '" . ciniki_core_dbQuote($ciniki, $section['id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'objects', 'fname'=>'oid', 
            'fields'=>array('oid', 'id', 'uuid', 'image_id', 'last_updated_ts'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.233', 'msg'=>'Unable to load items', 'err'=>$rc['err']));
    }
    $indexed_objects = isset($rc['objects']) ? $rc['objects'] : array();

    //
    // Delete the objects
    //
    foreach($indexed_objects as $oid => $obj) {
        $obj['index_id'] = $obj['id'];
        $rc = ciniki_wng_objectIndexDelete($ciniki, $tnid, $site, $section, $obj);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.234', 'msg'=>'Unable to remove object', 'err'=>$rc['err']));
        }
    }

    $site['indexed_sections'][] = $section['id'];

    return array('stat'=>'ok');
}
?>
