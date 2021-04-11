<?php
//
// Description
// -----------
// Update the section sequences for a page, can be after sequence update or delete.
// 
// To update all sequences and make sure in order, pass with section_id=0, new_seq = 0;
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_sectionSequencesUpdate(&$ciniki, $tnid, $site_id, $page_id, $flags, $section_id, $new_seq) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');

    //
    // Get the current sections for the page or footer(page_id=-1) for a site
    //
    $strsql = "SELECT ciniki_wng_sections.id, "
        . "ciniki_wng_sections.sequence "
        . "FROM ciniki_wng_sections "
        . "WHERE ciniki_wng_sections.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wng_sections.page_id = '" . ciniki_core_dbQuote($ciniki, $page_id) . "' "
        . "AND ciniki_wng_sections.site_id = '" . ciniki_core_dbQuote($ciniki, $site_id) . "' "
        . "AND (ciniki_wng_sections.flags&0x03) = '" . ciniki_core_dbQuote($ciniki, $flags) . "' "
        . "ORDER BY sequence "
        . "";
    $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.wng', 'sections');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.42', 'msg'=>'Unable to load section', 'err'=>$rc['err']));
    }
    $sections = isset($rc['sections']) ? $rc['sections'] : array();

    if( $section_id > 0 && !isset($sections[$section_id]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.43', 'msg'=>'Section not found'));
    }
    
    $cur_num = 1;
    foreach($sections as $sid => $sequence) {
        //
        // If this is where new section is to be, then skip sequence
        //
        if( $cur_num == $new_seq && $sid != $section_id ) {
            // 
            // Make sure the specified section was not already moved, or added to correct position
            //
            if( $section_id > 0 && $sections[$section_id] != $cur_num ) {
                $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.section', $section_id, array('sequence'=>$cur_num), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.44', 'msg'=>'Unable to update the section', 'err'=>$rc['err']));
                }
            }
            $cur_num++;
        } 
        // If this section is found before it's new sequence, skip
        elseif( $sid == $section_id ) {
            continue;
        }
        if( $sequence != $cur_num ) {
            $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.section', $sid, array('sequence'=>$cur_num), 0x04);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.45', 'msg'=>'Unable to update the section', 'err'=>$rc['err']));
            }
        }
        $cur_num++;
    }
    if( $new_seq >= $cur_num && $section_id > 0 && $sections[$section_id] != $cur_num ) {
        $rc = ciniki_core_objectUpdate($ciniki, $tnid, 'ciniki.wng.section', $section_id, array('sequence'=>$cur_num), 0x04);
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.46', 'msg'=>'Unable to update the section', 'err'=>$rc['err']));
        }
    }

    return array('stat'=>'ok');
}
?>
