<?php
//
// Description
// -----------
// This function will parse a formula from a form and return
// both PHP formula and Javascript formula to be used on website.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_formFormulaParse(&$ciniki, $tnid, $args) {

    if( !isset($args['formula']) || $args['formula'] == '' ) {
        return array('stat'=>'ok');
    }
    $args['formula'] = trim($args['formula']);
    if( $args['formula'][0] == '=' ) {
        $args['formula'][0] = ' ';
    }
    $js_formula = trim($args['formula']);
    $php_formula = trim($args['formula']);

    $fields = array();
    if( isset($args['sections']) ) {
        foreach($args['sections'] as $sid => $section) {
            $repeats = 1;
            if( isset($section['flags']) && ($section['flags']&0x01) == 0x01 && isset($section['max_repeats']) && $section['max_repeats'] > 0 ) {
                $repeats = $section['max_repeats'];
            }
            if( isset($section['fields']) ) {
                for($i = 1; $i <= $repeats; $i++) {
                    foreach($section['fields'] as $fid => $field) {
                        if( $field['ftype'] == 'number' || $field['ftype'] == 'formula' ) {
                            $fields[] = $field;
                            $js_formula = str_replace("{_{$field['label']}_}", "C.form.iV('f-{$field['id']}')", $js_formula);
                        }
                    }
                }
            }
        }
    }

    if( preg_match("/SUM\(C.form.iV\('f-([0-9]+)'\),C.form.iV\('f-([0-9]+)'\)\)/", $js_formula, $m) ) {
        $include = 0;
        $list = "";
        foreach($fields as $field) {
            if( $field['id'] == $m[1] ) {
                $include = 1;
            }
            if( $include == 1 && $field['ftype'] == 'number' ) {
                $list .= ($list != '' ? '+' : '') . "C.form.iV('f-{$field['id']}')";
            } 
            if( $include == 1 && $field['id'] == $m[2] ) {
                $include = 0;
                break;
            }
        }
        $js_formula = str_replace("SUM(C.form.iV('f-{$m[1]}'),C.form.iV('f-{$m[2]}'))", $list, $js_formula);
    }
    if( preg_match("/DOLLARS\(/", $js_formula, $m) ) {
        $js_formula = str_replace("DOLLARS(", "C.fD(", $js_formula);
    }

    return array('stat'=>'ok', 'js_formula' => $js_formula);
}
?>
