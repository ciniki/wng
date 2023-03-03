<?php
//
// Description
// -----------
// This script will move images from the database to ciniki-storage
//

//
// Initialize Ciniki by including the ciniki_api.php
//
global $ciniki_root;
$ciniki_root = dirname(__FILE__);
if( !file_exists($ciniki_root . '/ciniki-api.ini') ) {
    $ciniki_root = dirname(dirname(dirname(dirname(__FILE__))));
}
// loadMethod is required by all function to ensure the functions are dynamically loaded
require_once($ciniki_root . '/ciniki-mods/core/private/loadMethod.php');
require_once($ciniki_root . '/ciniki-mods/core/private/init.php');

$rc = ciniki_core_init($ciniki_root, 'rest');
if( $rc['stat'] != 'ok' ) {
    error_log("unable to initialize core");
    exit(1);
}

//
// Setup the $ciniki variable to hold all things ciniki.  
//
$ciniki = $rc['ciniki'];

ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheOriginal');


$strsql = "SELECT id, ref, settings "
    . "FROM ciniki_wng_sections "
    . "WHERE ref = 'ciniki.wng.imagebuttons' "
    . "OR ref = 'ciniki.wng.flexcards' "
    . "OR ref = 'ciniki.wng.carousel' "
    . "OR ref = 'ciniki.wng.buttons' "
    . "";
$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'settings'); 
if( $rc['stat'] != 'ok' ) {
    error_log("Unable to get list of settings");
    exit(0);
}
if( isset($rc['rows']) ) {
    $sections = $rc['rows'];
} else {
    $sections = array();
}
//    error_log(print_r($sections,true));

foreach($sections as $section) {
    $settings = unserialize($section['settings']);

    for($i = 1; $i < 25; $i++) {
        if( isset($settings["title-{$i}-text"]) ) {
            $settings["title-text-{$i}"] = $settings["title-{$i}-text"];
            unset($settings["title-{$i}-text"]);
        }
        if( isset($settings["button-{$i}-page"]) ) {
            $settings["link-page-{$i}"] = $settings["button-{$i}-page"];
            unset($settings["button-{$i}-page"]);
        }
        if( isset($settings["button-{$i}-text"]) ) {
            $settings["link-text-{$i}"] = $settings["button-{$i}-text"];
            unset($settings["button-{$i}-text"]);
        }
        if( isset($settings["button-{$i}-url"]) ) {
            $settings["link-url-{$i}"] = $settings["button-{$i}-url"];
            unset($settings["button-{$i}-url"]);
        }
        if( isset($settings["link-{$i}-page"]) ) {
            $settings["link-page-{$i}"] = $settings["link-{$i}-page"];
            unset($settings["link-{$i}-page"]);
        }
        if( isset($settings["link-{$i}-text"]) ) {
            $settings["link-text-{$i}"] = $settings["link-{$i}-text"];
            unset($settings["link-{$i}-text"]);
        }
        if( isset($settings["link-{$i}-url"]) ) {
            $settings["link-url-{$i}"] = $settings["link-{$i}-url"];
            unset($settings["link-{$i}-url"]);
        }
        if( $section['ref'] == 'ciniki.wng.carousel' ) {
            if( isset($settings["image-{$i}"]) && $settings["image-{$i}"] == 0 ) {
                unset($settings["image-{$i}"]);
                if( isset($settings["title-{$i}"]) ) {
                    unset($settings["title-{$i}"]);
                }
                if( isset($settings["image-position-{$i}"]) ) {
                    unset($settings["image-position-{$i}"]);
                }
                if( isset($settings["content-{$i}"]) ) {
                    unset($settings["content-{$i}"]);
                }
                if( isset($settings["link-page-{$i}"]) ) {
                    unset($settings["link-page-{$i}"]);
                }
                if( isset($settings["link-text-{$i}"]) ) {
                    unset($settings["link-text-{$i}"]);
                }
                if( isset($settings["link-url-{$i}"]) ) {
                    unset($settings["link-url-{$i}"]);
                }
            }
        }
        if( $section['ref'] == 'ciniki.wng.imagebuttons' ) {
            if( isset($settings["image-{$i}"]) && $settings["image-{$i}"] == 0 ) {
                unset($settings["image-{$i}"]);
                if( isset($settings["title-text-{$i}"]) ) {
                    unset($settings["title-text-{$i}"]);
                }
                if( isset($settings["image-position-{$i}"]) ) {
                    unset($settings["image-position-{$i}"]);
                }
                if( isset($settings["link-page-{$i}"]) ) {
                    unset($settings["link-page-{$i}"]);
                }
                if( isset($settings["link-url-{$i}"]) ) {
                    unset($settings["link-url-{$i}"]);
                }
            }
        }
        if( $section['ref'] == 'ciniki.wng.buttons' ) {
            if( isset($settings["link-text-{$i}"]) && $settings["link-text-{$i}"] == '' ) {
                unset($settings["image-{$i}"]);
                if( isset($settings["link-page-{$i}"]) ) {
                    unset($settings["link-page-{$i}"]);
                }
                if( isset($settings["link-text-{$i}"]) ) {
                    unset($settings["link-text-{$i}"]);
                }
                if( isset($settings["link-url-{$i}"]) ) {
                    unset($settings["link-url-{$i}"]);
                }
            }
        }
        if( $section['ref'] == 'ciniki.wng.flexcards' ) {
            if( isset($settings["title-{$i}"]) && $settings["title-{$i}"] == '' 
                && isset($settings["image-{$i}"]) && $settings["image-{$i}"] == 0 
                && isset($settings["image-position-{$i}"]) 
                ) {
                unset($settings["title-{$i}"]);
                unset($settings["image-{$i}"]);
                unset($settings["image-position-{$i}"]);
                unset($settings["content-{$i}"]);
                unset($settings["link-page-{$i}"]);
                unset($settings["link-text-{$i}"]);
                unset($settings["link-url-{$i}"]);
            }
        }
    }

    $strsql = "UPDATE ciniki_wng_sections "
        . "SET settings = '" . ciniki_core_dbQuote($ciniki, serialize($settings)) . "' "
        . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $section['id']) . "' "
        . "";
    $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.wng'); 
    if( $rc['stat'] != 'ok' ) {
        error_log("Unable to get list of settings");
        exit(0);
    }
}



?>
