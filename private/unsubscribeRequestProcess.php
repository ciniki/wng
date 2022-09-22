<?php
//
// Description
// -----------
// Process the search page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_unsubscribeRequestProcess(&$ciniki, $tnid, &$request) {

    $blocks = array();

    //
    // Check for unsubscribe requests from subscriptions
    //
    if( isset($_GET['e']) && $_GET['e'] != '' 
        && isset($_GET['s']) && $_GET['s'] != ''
        && isset($_GET['k']) && $_GET['k'] != ''
        ) {
        //
        // Get the information about the customer, from the link provided in the email.  The
        // email must be less than 30 days since it was sent for the link to still be active
        //
        $strsql = "SELECT customers.subscription_id AS id, "
            . "customers.customer_id, "
            . "subscriptions.name "
            . "FROM ciniki_mail AS mail "
            . "INNER JOIN ciniki_subscription_customers AS customers ON ("
                . "mail.customer_id = customers.customer_id "
                . "AND customers.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "INNER JOIN ciniki_subscriptions AS subscriptions ON ("
                . "customers.subscription_id = subscriptions.id "
                . "AND subscriptions.uuid = '" . ciniki_core_dbQuote($ciniki, $_GET['s']) . "' "
                . "AND subscriptions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "INNER JOIN ciniki_customer_emails AS emails ON ("
                . "customers.customer_id = emails.customer_id "
                . "AND emails.email = '" . ciniki_core_dbQuote($ciniki, $_GET['e']) . "' "
                . "AND emails.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
                . ") "
            . "WHERE mail.unsubscribe_key = '" . ciniki_core_dbQuote($ciniki, $_GET['k']) . "' "
            . "AND mail.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (UNIX_TIMESTAMP(UTC_TIMESTAMP())-UNIX_TIMESTAMP(mail.date_sent)) < 2592000 " // Mail was sent within 30 days
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.mail', 'subscription');
        if( isset($rc['subscription']) ) {
            $sub = $rc['subscription'];
            if( !isset($ciniki['session']['change_log_id']) ) {
                $ciniki['session']['change_log_id'] = 'mail.' . date('ymd.His');
                $ciniki['session']['user'] = array('id'=>'-3');
            }
            $subscription_name = $sub['name'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'subscriptions', 'wng', 'unsubscribe');
            $rc = ciniki_subscriptions_wng_unsubscribe($ciniki, $tnid, $request, $sub['id'], $sub['customer_id']);
            if( $rc['stat'] != 'ok' ) {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'error', 
                    'content'=>"We're sorry, there was an error processing your unsubscribe. Please contact us for assistance.",
                    );
            } else {
                $blocks[] = array(
                    'type' => 'msg', 
                    'level' => 'success',
                    'content'=>"You have been unsubscribed from the Mailing List.",
                    );
            }
        } else {
            // Also occurs when clicking unsubscribe from blog test email.
            $blocks[] = array(
                'type' => 'msg', 
                'level' => 'error', 
                'content'=>"I'm sorry but you must unsubscribe within 30 days.",
                );
        }
    } else {
        $blocks[] = array(
            'type' => 'msg', 
            'level' => 'error', 
            'content'=>"Invalid link.",
            );
    }

    return array('stat'=>'ok', 'blocks' => $blocks);
}
?>
