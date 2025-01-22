<?php
//
// Description
// -----------
// Process the section that will display a photo and paragraph.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_contactform(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();


    //
    // Check if contact form was submitted
    //
    $success_message = '';
    $error_message = '';
    $fieldsmsg = '';

    $fields = array(
        'contact-form-name' => array(
            'label' => 'Name', 
            'value' => (isset($_POST['contact-form-name']) ? $_POST['contact-form-name'] : ''),
            ),
        'contact-form-email' => array(
            'label' => 'Email', 
            'value' => (isset($_POST['contact-form-email']) ? $_POST['contact-form-email'] : ''),
            ),
        'contact-form-email-again' => array(
            'label' => 'Email Again', 
            'class' => 'hidden',
            'value' => (isset($_POST['contact-form-email']) ? $_POST['contact-form-email'] : ''),
            ),
        );
    //
    // Check for additional fields
    //
    for($i = 1; $i <= 10; $i++) {
        if( isset($s["field-{$i}-label"]) && $s["field-{$i}-label"] != '' ) {
            $fields["contact-form-field-{$i}"] = array(
                'label' => $s["field-{$i}-label"], 
                'value' => (isset($_POST["contact-form-field-{$i}"]) ? $_POST["contact-form-field-{$i}"] : ''),
                );
            if( isset($_POST["contact-form-field-{$i}"]) && $_POST["contact-form-field-{$i}"] != '' ) {
                $fieldsmsg = $s["field-{$i}-label"] . ": " . $_POST["contact-form-field-{$i}"] . "\n";
            }
        }
    }
    $fields['contact-form-subject'] = array(
        'label' => 'Subject', 
        'value' => (isset($_POST['contact-form-subject']) ? $_POST['contact-form-subject'] : ''),
        );
    $fields['contact-form-message'] = array(
        'label' => 'Message', 
        'type' => 'textarea',
        'value' => (isset($_POST['contact-form-message']) ? $_POST['contact-form-message'] : ''),
        );

    if( isset($_POST['contact-form-name']) ) {
        if( !isset($_POST['contact-form-name']) || $_POST['contact-form-name'] == '' ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error', 
                'content' => 'You must specify a name when using the contact form.',
                );
            $error_message = "You must enter your name.<br/>";
//            return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
        }
        if( $error_message == '' && (!isset($_POST['contact-form-email']) || $_POST['contact-form-email'] == '') ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error', 
                'content' => 'You must specify an email address when using the contact form.',
                );
            $error_message = "You must enter your email address to get a response.<br/>";
//            return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
        }
        if( $error_message == '' && !preg_match('/^[^ ]+\@[^ ]+\.[^ ]+$/', trim($_POST['contact-form-email'])) ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error', 
                'content' => 'You must specify an email address when using the contact form.',
                );
            $error_message = "You must enter a valid email address to get a response.<br/>";
//            return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
        }
        if( $error_message == '' && (!isset($_POST['contact-form-subject']) || $_POST['contact-form-subject'] == '') ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error', 
                'content' => 'You must specify a subject when using the contact form.',
                );
            $error_message = "Please add a subject.<br/>";
//            return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
        } elseif( isset($_POST['contact-form-subject']) ) {
            $subject = $_POST['contact-form-subject'];
        }
        if( $error_message == '' && (!isset($_POST['contact-form-message']) || trim($_POST['contact-form-message']) == '') ) {
            $blocks[] = array(
                'type' => 'msg',
                'level' => 'error', 
                'content' => 'You must specify a message when using the contact form.',
                );
            $error_message = "Please enter a message.<br/>";
//            return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>'');
        } elseif( isset($_POST['contact-form-message']) ) {
            $msg = $_POST['contact-form-message'];
        }

        //
        // SPAM Checker. Make sure second email field isn't filled in
        // Filter specific subjects
        //
        if( $error_message == '' ) {
            if( isset($_POST['contact-form-email-again']) && $_POST['contact-form-email-again'] != '' ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
                ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
                    'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - ' 
                        . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'NO REFERER'));
                return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
            }
            if( !isset($_SERVER['HTTP_REFERER']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
                ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
                    'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - NO REFERER');
                return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
            }
            if( isset($subject) && preg_match("/^[0-9]+$/", trim($subject)) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
                ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
                    'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - NUMERIC SUBJECT');
                return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
            }
            if( isset($msg) && preg_match("/^[0-9]+$/", trim($msg)) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
                ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
                    'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - NUMERIC MESSAGE');
                return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
            }
            if( preg_match("/domainreg[a-z]*.com/", $_POST['contact-form-email']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
                ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
                    'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - domainworld.com');
                return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
            }
            if( preg_match("/Effective PPC Campaigns/", $subject) 
                || preg_match("/Sculpting Tomorrow's Digital Icons/", $subject)
                || preg_match("/EXPIR.*DOMAIN/", $subject) 
                ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
                ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
                    'BLOCKED SUBJECT ' . $subject . ' - ' . $_POST['contact-form-email']);
                return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
            }
            if( preg_match("/domainworld.com/", $_POST['contact-form-email']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'logFileMsg');
                ciniki_core_logFileMsg($ciniki, $tnid, 'spam', 
                    'BLOCKED FROM ' . $_POST['contact-form-email'] . ' - domainworld.com');
                return array('stat'=>'ok', 'error_message'=>$error_message, 'success_message'=>"Your message was sent");
            }
        }

        if( $error_message == '' ) {

            //
            // If the mail inbox flag has been sent, put the message into the inbox
            //
            if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.mail', 0x10) ) {
                //
                // Create the email message content
                //
                $htmlmsg = preg_replace("/\n/", '<br/>', $msg);
                ciniki_core_loadMethod($ciniki, 'ciniki', 'mail', 'hooks', 'inboxAddMessage');
                $rc = ciniki_mail_hooks_inboxAddMessage($ciniki, $tnid, array(
                    'from_name' => $_POST['contact-form-name'],
                    'from_email' => $_POST['contact-form-email'],
                    'subject' => $subject,
                    'text_content' => $msg,
                    'html_content' => $htmlmsg,
                    'notification' => 'yes',
                    'notification_emails' => (isset($s['notify-emails'])?$s['notify-emails']:''),
                    ));
                if( $rc['stat'] != 'ok' ) {
                    $error_message = "I'm sorry, we had a problem delivering your message. Please try again, or contact us by phone.";
                    error_log('WEB [' . $ciniki['tenant']['details']['name'] . ']: Error with form submit (2606)');
                }
            } 
            
            //
            // No inbox, email the message to specified email addresses or the tenant owners
            //
            else {
                //
                // Create the email message content
                //
                $textmsg = "You have received a new message: \n\n"
                    . "From: " . $_POST['contact-form-name'] . "\n"
                    . "Email: " . $_POST['contact-form-email'] . "\n"
                    . $fieldsmsg
                    . "\n"
                    . $msg
                    . "";
                $htmlmsg = preg_replace("/\n/", '<br/>', $textmsg);
                if( isset($s['notify-emails']) && $s['notify-emails'] != '' ) {
                    $send_to_emails = explode(',', $s['notify-emails']);
                    foreach($send_to_emails as $email) {
                        $ciniki['emailqueue'][] = array(
                            'tnid' => $tnid,
                            'to' => trim($email),
                            'replyto_email' => $_POST['contact-form-email'],
                            'replyto_name'=>$_POST['contact-form-name'],
                            'subject' => $subject,
                            'textmsg' => $textmsg,
                            'htmlmsg' => $htmlmsg,
                            );
                    }
                } else {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'hooks', 'tenantOwners');
                    $rc = ciniki_tenants_hooks_tenantOwners($ciniki, $tnid, array());
                    if( $rc['stat'] != 'ok' ) {
                        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.145', 'msg'=>'Unable to get tenant owners', 'err'=>$rc['err']));
                    }
                    $owners = $rc['users'];
                    foreach($owners as $user_id => $owner) {
                        // Force.mailto config option will be applied by mailqueue process
                        $ciniki['emailqueue'][] = array(
                            'tnid' => $tnid,
                            'user_id' => $user_id,
                            'replyto_email' => $_POST['contact-form-email'],
                            'replyto_name' => $_POST['contact-form-name'],
                            'subject' => $subject,
                            'textmsg' => $textmsg,
                            'htmlmsg' => $htmlmsg,
                            );
                    }
                }
            }
        }

        if( $error_message == '' ) {
            //
            // Success message
            //
            if( isset($s['submitted-message']) && $s['submitted-message'] != '' ) {
                $blocks[] = array(
                    'type' => 'msg',
                    'level' => 'success',
                    'content' => $s['submitted-message'],
                    );
//                $success_message .= $s['submitted-message'];
            } else {
                $blocks[] = array(
                    'type' => 'msg',
                    'level' => 'success',
                    'content' => 'Your message has been sent.',
                    );
//                $success_message .= "Your message has been sent.";
            }
            foreach($fields as $fid => $field) {
                if( isset($fields[$fid]['value']) ) {
                    $fields[$fid]['value'] = '';
                }
            }
        }
    }

    $staff = array();
    for($i = 1; $i <= 5; $i++) {
        if( isset($s["staff-{$i}-name"]) && $s["staff-{$i}-name"] != '' ) {
            $staff[] = array(
                'name' => $s["staff-{$i}-name"],
                'phone' => isset($s["staff-{$i}-phone"]) ? $s["staff-{$i}-phone"] : '',
                'email' => isset($s["staff-{$i}-email"]) ? $s["staff-{$i}-email"] : '',
                );
        }
    }
    $blocks[] = array(
        'type' => 'contactform',
        'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']) . ' limit-width center',
        'form-position' => 'bottom-right',
        'title' => isset($s['title']) ? $s['title'] : '',
        'contact-intro' => isset($s['contact-intro']) ? $s['contact-intro'] : '',
        'address' => isset($s['address']) ? $s['address'] : '',
        'mailing' => isset($s['mailing']) ? $s['mailing'] : '',
        'phone' => isset($s['phone']) ? $s['phone'] : '',
        'fax' => isset($s['fax']) ? $s['fax'] : '',
        'email' => isset($s['email']) ? $s['email'] : '',
        'staff' => $staff,
        'hours-monday' => isset($s['hours-monday']) ? $s['hours-monday'] : '',
        'hours-tuesday' => isset($s['hours-tuesday']) ? $s['hours-tuesday'] : '',
        'hours-wednesday' => isset($s['hours-wednesday']) ? $s['hours-wednesday'] : '',
        'hours-thursday' => isset($s['hours-thursday']) ? $s['hours-thursday'] : '',
        'hours-friday' => isset($s['hours-friday']) ? $s['hours-friday'] : '',
        'hours-saturday' => isset($s['hours-saturday']) ? $s['hours-saturday'] : '',
        'hours-sunday' => isset($s['hours-sunday']) ? $s['hours-sunday'] : '',
        'directions' => isset($s['directions']) ? $s['directions'] : '',
        'contact-outro' => isset($s['contact-outro']) ? $s['contact-outro'] : '',
        'status' => ($success_message != '' ? 'success' : ($error_message != '' ? 'error' : '')),
        'success-message' => $success_message,
        'error-message' => $error_message,
        'form-title' => isset($s['form-title']) ? $s['form-title'] : '',
        'form-intro' => isset($s['form-intro']) ? $s['form-intro'] : '',
        'fields' => $fields,
        );


    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
