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
function ciniki_wng_objectIndexUpdate(&$ciniki, $tnid, &$site, $section, $obj) {

    //
    // Check time first, only run 25 seconds
    //
    if( isset($site['start_time']) && ($site['start_time']+25) < time() ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.225', 'msg'=>'outatime'));
    }

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
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.200', 'msg'=>'Unable to load object', 'err'=>$rc['err']));
        }
        $indexed_object = isset($rc['object']) ? $rc['object'] : array();
    } 
    else {
        $indexed_object = array();
    }
    
    //
    // Load the indexable object 
    //
    $s = explode('.', $section['ref']);
    if( isset($s[1]) ) {
        $rc = ciniki_core_loadMethod($ciniki, $s[0], $s[1], 'wng', 'indexObject');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, $site, $section, $obj);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.216', 'msg'=>'Unable to get index objects', 'err'=>$rc['err']));
            }
            $indexable_object = $rc['object'];

            foreach(['primary_words', 'secondary_words', 'tertiary_words'] as $field) {
                if( isset($indexable_object[$field]) ) {
                    $indexable_object[$field] = ciniki_core_makeKeywords($ciniki, $indexable_object[$field]);
                }
            }
        }
    }

    //
    // Compare the fields and update indexed_object
    //
    if( isset($indexable_object) ) {
        //
        // Check if object already exists that needs updating
        //
        if( isset($indexed_object['id']) && $indexed_object['id'] > 0 ) {
            $update_args = array();
            $indexable_object['flags'] = $section['index_flags'];
            foreach(['flags', 'label', 'title', 'subtitle', 'meta', 'image_id', 'synopsis', 'primary_words', 'secondary_words', 'tertiary_words', 'weight', 'url'] as $field) {
                if( !isset($indexed_object[$field]) 
                    || (isset($indexable_object[$field]) && $indexed_object[$field] != $indexable_object[$field]) 
                    ) {
                    $update_args[$field] = $indexable_object[$field];
                    if( $field == 'image_id' ) {
                        $rc = ciniki_wng_objectImageIndexUpdate($ciniki, $tnid, $site, $indexable_object['image_id'], $indexed_object['id']);
                        if( $rc['stat'] != 'ok' ) {
                            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.217', 'msg'=>'', 'err'=>$rc['err']));
                        }
                    }
                }
            }
            if( count($update_args) > 0 ) {
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.index', $indexed_object['id'], $update_args, 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.202', 'msg'=>'Unable to update the ciniki.wng.index', 'err'=>$rc['err']));
                }
            }
        } 
        //
        // Create object if none already exists
        //
        else {
            $indexable_object['site_id'] = $site['id'];
            $indexable_object['section_id'] = $section['id'];
            $indexable_object['flags'] = $section['index_flags'];
            $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.wng.index', $indexable_object, 0x04);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.203', 'msg'=>'Unable to add the index', 'err'=>$rc['err']));
            }
            $index_id = $rc['id'];
            if( isset($indexable_object['image_id']) && $indexable_object['image_id'] > 0 ) {
                $rc = ciniki_wng_objectImageIndexUpdate($ciniki, $tnid, $site, $indexable_object['image_id'], $index_id);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.218', 'msg'=>'', 'err'=>$rc['err']));
                }
            }
            
        }
    }
    //
    // No indexable object exists
    //
    elseif( isset($indexed_object['id']) && $indexed_object['id'] > 0 ) {
        //
        // FIXME: Check if object image exists in cache
        //
        $rc = ciniki_wng_objectImageIndexDelete($ciniki, $tnid, $site, $indexable_object['image_id']);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.204', 'msg'=>'Unable to remove image', 'err'=>$rc['err']));
        }

        //
        // Remove the object
        //
        $rc = ciniki_core_objectDelete($ciniki, $tnid, 'ciniki.wng.index', $obj['id'], $obj['uuid'], 0);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.201', 'msg'=>'Unable to delete missing object', 'err'=>$rc['err']));
        }
    }

    return array('stat'=>'ok');
}
?>
