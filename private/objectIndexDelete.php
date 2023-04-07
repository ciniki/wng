<?php
//
// Description
// -----------
// Remove an object from the index
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_objectIndexDelete(&$ciniki, $tnid, &$site, $section, $obj) {

    //
    // Load the existing object
    //
    if( isset($obj['index_id']) && $obj['index_id'] > 0 ) {
        $strsql = "SELECT CONCAT('.', object, object_id) AS oid, " 
            . "id, "
            . "uuid, "
            . "label, "
            . "title, "
            . "subtitle, "
            . "meta, "
            . "image_id, "
            . "synopsis, "
            . "object, "
            . "object_id, "
            . "primary_words, "
            . "secondary_words, "
            . "tertiary_words, "
            . "weight, "
            . "url "
            . "FROM ciniki_wng_index "
            . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $obj['index_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND site_id = '" . ciniki_core_dbQuote($ciniki, $site['id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'object');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.230', 'msg'=>'Unable to load object', 'err'=>$rc['err']));
        }
        $indexed_object = isset($rc['object']) ? $rc['object'] : array();
    } 
    else {
        return array('stat'=>'ok');
    }
   
    //
    // Remove image
    //
    if( $indexed_object['image_id'] > 0 ) {
        $rc = ciniki_wng_objectImageIndexDelete($ciniki, $tnid, $site, $indexed_object['image_id']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.231', 'msg'=>'Unable to remove image', 'err'=>$rc['err']));
        } 
    }

    //
    // Remove the object
    //
    $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wng.index', $obj['id'], $obj['uuid'], 0);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.232', 'msg'=>'Unable to delete missing object', 'err'=>$rc['err']));
    }

    return array('stat'=>'ok');
}
?>
