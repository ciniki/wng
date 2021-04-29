<?php
//
// Description
// -----------
// account change password
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_accountchgpwd(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $content = "<div class='block-accountchgpwd'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $content .= "<form action='' method='POST'>";
    $content .= "<div class='change-password-form'>";
    $content .= "<p>If you would like to change your password, enter your old password followed by a new one.</p>";
    $content .= "<input type='hidden' name='action' value='update'/>";
    $content .= "<div class='input'>"
        . "<label for='oldpassword'>Old Password:</label>"
        . "<input class='text password' id='oldpassword' type='password' name='oldpassword' />"
        . "</div>";
    $content .= "<div class='input'>"
        . "<label for='newpassword'>New Password:</label>"
        . "<input class='text password' id='newpassword' type='password' name='newpassword' />"
        . "</div>";
    $content .= "<div class='submit'><input type='submit' class='button' value='Change Password'></div>\n";
    $content .= "</div>";
    $content .= "</form>";
    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div>";
        

    return array('stat'=>'ok', 'content'=>$content);
}
?>
