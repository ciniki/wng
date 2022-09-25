<?php
//
// Description
// -----------
// This method will update any valid page settings and content in the database.
//
// The contact display values are taken from the tenant settings.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:                         The ID of the tenant to update the settings for.
//
function ciniki_wng_siteSettingsUpdate(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'site_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Site'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // The list of valid settings for wng pages
    //
    $settings_fields = array(
        // Header options
        'header-site-title',
        'header-image-id',
        'header-social-icons',
        'account-active',
        'account-password-change',
        'account-membership-change',
        'account-forgot-link-text',
        'account-create-account-text',
        'account-create-type',
        'account-children-update',
        'account-menu-toggle-em',
        // Cart options
        'cart-active',
        'cart-currency-display',
        'cart-registration-child-select',
        'cart-child-create-button',
        'cart-customer-notes',
        'cart-noaccount-message',
        'cart-regreview-message',
        'cart-bottom-message',
        'cart-checkout-message',
        'cart-etransfer-submitted-message',
        'cart-payment-success-message',
        'cart-payment-success-emails',
        'cart-donation-message',
        'cart-donation-amounts',
        'cart-donation-thankyou',
        'paypal-ec-site',
        'paypal-ec-clientid',
        'paypal-ec-password',
        'paypal-ec-signature',
        'stripe-pk',
        'stripe-sk',
        // Theme options
        'theme-css-imports',
        'theme-css-overrides',
        // Meta tags
        'meta-google-analytics-account',
        'meta-google-site-verification',
        'meta-google-tag-manager',
        'meta-facebook-pixel-id',
        'meta-facebook-domain-verification',
        // Social media accounts for this website
        'social-facebook-url',
        'social-instagram-username',
        'social-pinterest-username',
        'social-etsy-url',
        'social-twitter-business-name',
        'social-twitter-username',
        'social-linkedin-url',
        'social-youtube-url',
        'social-vimeo-url',
        // Footer items
        'footer-copyright-name',
        'footer-copyright-message',
        'footer-social-icons',
//        'social-flickr-url',  // Not used
//        'social-tumblr-url',  // Not used
        );

    //
    // Check access to tnid as owner, and load module list
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'checkAccess');
    $ac = ciniki_wng_checkAccess($ciniki, $args['tnid'], 'ciniki.wng.siteSettingsUpdate');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }

    
    foreach($ciniki['tenant']['modules'] as $module) {
        $settings_fields[] = 'account-menu-' . $module['package'] . '-' . $module['module'];
    }

    //
    // Grab the existing settings
    //
    $strsql = "SELECT id, detail_key, detail_value "
        . "FROM ciniki_wng_settings "
        . "WHERE site_id = '" . ciniki_core_dbQuote($ciniki, $args['site_id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wng', array(
        array('container'=>'settings', 'fname'=>'detail_key', 
            'fields'=>array('id', 'detail_key', 'detail_value'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.33', 'msg'=>'Unable to load settings', 'err'=>$rc['err']));
    }
    $settings = isset($rc['settings']) ? $rc['settings'] : array();

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.wng');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Check if the field was passed, and then try an insert, but if that fails, do an update
    //
    foreach($settings_fields as $field) {
        //
        // Check to see if the field was passed
        //
        if( isset($ciniki['request']['args'][$field]) ) {
            if( !isset($settings[$field]['detail_value']) ) {
                $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.wng.setting', array(
                    'site_id' => $args['site_id'],
                    'detail_key' => $field,
                    'detail_value' => $ciniki['request']['args'][$field],
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.34', 'msg'=>'Unable to add the setting', 'err'=>$rc['err']));
                }
            } elseif( $settings[$field]['detail_value'] != $ciniki['request']['args'][$field] ) {
                $rc = ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.wng.setting', $settings[$field]['id'], array(
                    'detail_value' => $ciniki['request']['args'][$field],
                    ), 0x04);
                if( $rc['stat'] != 'ok' ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.35', 'msg'=>'Unable to update the setting', 'err'=>$rc['err']));
                }
            }
        }
    }
    //
    // Commit the changes to the database
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.wng');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Rebuild the theme cache
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'cacheRebuild');
    $rc = ciniki_wng_cacheRebuild($ciniki, $args['tnid'], $args['site_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'wng');

    return array('stat'=>'ok');
}
?>
