<?php
//
// Description
// -----------
// This function returns the list of objects for the module.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wng_objects(&$ciniki) {
    //
    // Build the objects
    //
    $objects = array();
    $objects['site'] = array(
        'name' => 'Site',
        'sync' => 'yes',
        'o_name' => 'site',
        'o_container' => 'sites',
        'table' => 'ciniki_wng_sites',
        'fields' => array(
            'name' => array('name'=>'Name'),
            'status' => array('name'=>'Status', 'default'=>'10'),
            'domain_id' => array('name'=>'Domain', 'ref'=>'ciniki.tenants.domain'),
            'permalink' => array('name'=>'Permalink'),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            'theme' => array('name'=>'Theme', 'default'=>'twentyone'),
            'lang' => array('name'=>'Language', 'default'=>'en'),
            'css_classes' => array('name'=>'CSS Classes', 'default'=>''),
            ),
        'history_table' => 'ciniki_wng_history',
        );
    $objects['page'] = array(
        'name' => 'Page',
        'sync' => 'yes',
        'o_name' => 'page',
        'o_container' => 'pages',
        'table' => 'ciniki_wng_pages',
        'fields' => array(
            'site_id' => array('name'=>'Site', 'ref'=>'ciniki.wng.site'),
            'parent_id' => array('name'=>'Parent', 'ref'=>'ciniki.wng.page'),
            'ptype' => array('name'=>'Page Type', 'default'=>'10'),
            'sequence' => array('name'=>'Order', 'default'=>'1'),
            'title' => array('name'=>'Title'),
            'page_title' => array('name'=>'Page Title', 'default'=>''),
            'permalink' => array('name'=>'Permalink'),
            'path' => array('name'=>'path'),
            'menu_flags' => array('name'=>'Menu Options', 'default'=>'1'),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            'password' => array('name'=>'Password', 'default'=>''),
            'redirect_url' => array('name'=>'Redirect URL', 'default'=>''),
            'image_id' => array('name'=>'Image', 'ref'=>'ciniki.images.image', 'default'=>'0'),
            'image_caption' => array('name'=>'Image Caption', 'default'=>''),
            'synopsis' => array('name'=>'Synopsis', 'default'=>''),
            ),
        'history_table' => 'ciniki_wng_history',
        );
    $objects['section'] = array(
        'name' => 'Section',
        'sync' => 'yes',
        'o_name' => 'section',
        'o_container' => 'sections',
        'table' => 'ciniki_wng_sections',
        'fields' => array(
            'site_id' => array('name'=>'Site', 'ref'=>'ciniki.wng.site'),
            'page_id' => array('name'=>'Page', 'ref'=>'ciniki.wng.page'),
            'ref' => array('name'=>'Section Reference'),
            'sequence' => array('name'=>'Order', 'default'=>'1'),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            'label' => array('name'=>'Label'),
            'settings' => array('name'=>'Settings', 'default'=>''),
            ),
        'history_table' => 'ciniki_wng_history',
        );
    $objects['setting'] = array(
        'name' => 'Setting',
        'sync' => 'yes',
        'o_name' => 'setting',
        'o_container' => 'settings',
        'table' => 'ciniki_wng_settings',
        'fields' => array(
            'site_id' => array('name'=>'Site', 'ref'=>'ciniki.wng.site'),
            'detail_key' => array('name'=>'Key'),
            'detail_value' => array('name'=>'Value', 'default'=>''),
            ),
        'history_table' => 'ciniki_wng_history',
        );
    $objects['index'] = array(
        'name' => 'Index',
        'sync' => 'yes',
        'o_name' => 'items',
        'o_container' => 'item',
        'table' => 'ciniki_wng_index',
        'fields' => array(
            'site_id' => array('name'=>'Site', 'ref'=>'ciniki.wng.site'),
            'section_id' => array('name'=>'Section', 'ref'=>'ciniki.wng.section'),
            'flags' => array('name'=>'Options', 'default'=>'0'),
            'label' => array('name'=>'Label', 'default'=>''),
            'title' => array('name'=>'Title', 'default'=>''),
            'subtitle' => array('name'=>'Subtitle', 'default'=>''),
            'meta' => array('name'=>'Meta', 'default'=>''),
            'image_id' => array('name'=>'Image', 'default'=>'0', 'ref'=>'ciniki.images.image'),
            'synopsis' => array('name'=>'Synopsis', 'default'=>''),
            'object' => array('name'=>'Object'),
            'object_id' => array('name'=>'Object ID',),
            'primary_words' => array('name'=>'Primary Words', 'default'=>''),
            'secondary_words' => array('name'=>'Secondary Words', 'default'=>''),
            'tertiary_words' => array('name'=>'Tertiary Words', 'default'=>''),
            'weight' => array('name'=>'Weight', 'default'=>'20000'),
            'url' => array('name'=>'URL', 'default'=>''),
            ),
        );
    //
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
