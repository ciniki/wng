<?php
//
// Description
// -----------
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_accountpwdreset(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $content .= "<div class='block-accountpwdreset"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<div class='reset-form'>\n";

    if( isset($block['message']) && $block['message'] != '' ) {
        $content .= "<p>" . $block['message'] . "</p>";
    } else {
        $content .= "<p>Please enter a new password.  It must be at least 8 characters long.</p>";
    }

    $content .= "<form method='POST' action='" . $block['action'] . "'>";
    $content .= "<input type='hidden' name='action' value='passwordreset'>\n";
    $content .= "<input type='hidden' name='email' value='" 
        . (isset($block['email']) ? $block['email'] : '') 
        . "'>\n";
    $content .= "<input type='hidden' name='temppassword' value='" 
        . (isset($_GET['pwd']) ? $_GET['pwd'] : (isset($_POST['pwd']) ? $_POST['pwd'] : '')) 
        . "'>\n";
    $content .= "<div class='input'>"
            . "<label for='password'>New Password</label>"
            . "<input id='password' type='password' class='text' maxlength='100' name='newpassword' value='' />"
        . "</div>\n"
        . "<div class='submit'>"
            . "<input type='submit' class='button' value='Set Password' />"
        . "</div>\n"
        . "</form>"
        . "</div>\n";

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
