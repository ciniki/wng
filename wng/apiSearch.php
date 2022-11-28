<?php
//
// Description
// -----------
// Search the wng index
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_wng_apiSearch(&$ciniki, $tnid, $request) {

    $search_str = urldecode($request['uri_split'][4]);
    $limit = 50;

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makeKeywords');
    $words = ciniki_core_makeKeywords($ciniki, $search_str, true);
    $primary_sql = '';
    $secondary_sql = '';
    $tertiary_sql = '';
    foreach($words as $word) {
        if( trim($word) == '' ) { 
            continue;
        }
        $primary_sql .= "AND (primary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR primary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%') ";
        $secondary_sql .= "AND ("
            . "primary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR primary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . "OR secondary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR secondary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . ") ";
        $tertiary_sql .= "AND ("
            . "primary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR primary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . "OR secondary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR secondary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . "OR tertiary_words LIKE '" . ciniki_core_dbQuote($ciniki, $word) . "%' OR tertiary_words LIKE '% " . ciniki_core_dbQuote($ciniki, $word) . "%'"
            . ") ";
    }

    if( $primary_sql == '' ) {
        return array('stat'=>'ok', 'results'=>array());
    }

    //
    // Start with searching primary words
    //
    $strsql = "SELECT id, label, title, subtitle, meta, image_id, synopsis, object, url "
        . "FROM ciniki_wng_index "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . $primary_sql
        . "ORDER BY weight DESC "
        . "LIMIT $limit "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
    $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.web', 'results', 'id');
    if( $rc['stat'] == 'ok' && isset($rc['results']) ) {
        $results = $rc['results'];
    } else {
        $results = array();
    }

    //
    // Add secondary results
    //
    if( count($results) < $limit ) {
        $strsql = "SELECT id, label, title, subtitle, meta, image_id, synopsis, object, url "
            . "FROM ciniki_wng_index "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . $secondary_sql
            . "ORDER BY weight DESC "
            . "LIMIT $limit "
            . "";
        $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.web', 'results', 'id');
        if( $rc['stat'] == 'ok' && isset($rc['results']) ) {
            $results = array_replace($results, $rc['results']);
        } else {
            $results = array();
        }
    }

    //
    // Add tertiary results
    //
    if( count($results) < $limit ) {
        $strsql = "SELECT id, label, title, subtitle, meta, image_id, synopsis, object, url "
            . "FROM ciniki_wng_index "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . $tertiary_sql
            . "ORDER BY weight DESC "
            . "LIMIT $limit "
            . "";
        $rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.web', 'results', 'id');
        if( $rc['stat'] == 'ok' && isset($rc['results']) ) {
            $results = array_replace($results, $rc['results']);
        } else {
            $results = array();
        }
    }

    $final_results = array();
    foreach($results as $rid => $result) {
        //
        // create image url
        //
        if( $result['image_id'] > 0 ) {
            $result['image_url'] = $request['site']['cache_url'] . sprintf("/search/%012d.jpg", $result['image_id']);
        } else {
            $result['image_url'] = '';
        }
        $result['url'] = $request['ssl_domain_base_url'] . $result['url'];
        $result['class'] = str_replace('.', '-', $result['object']);
        unset($result['object']);
        $final_results[] = $result;
    }

    return array('stat'=>'ok', 'results'=>$final_results);
}
?>
