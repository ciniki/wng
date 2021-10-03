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
function ciniki_wng_generators_accountlogin(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $content .= "<div class='block-accountlogin"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    $startform = isset($block['startform']) && $block['startform'] == 'forgot' ? 'forgot' : 'login';
    if( !isset($block['forgot']) || $block['forgot'] != 'yes' ) {
        $startform = 'login';
    }

    $js = '';

    $email = isset($block['email']) ? $block['email'] : '';

    //
    // Display the login form
    //
    $content .= "<div id='signin-form' class='signin-form' style='display:"
        . ($startform == 'login' ? 'block;' : 'none;')
        . "'>"
        . "<form method='POST' action=''>"
        . "<input type='hidden' name='action' value='signin'>"
        . "<div class='input first-field'>"
            . "<label for='email'>Email</label>"
            . "<input id='email' type='email' class='text' maxlength='250' name='email' value='$email' />"
        . "</div>\n" 
        . "<div class='input last-field'>"
            . "<label for='password'>Password</label>"
            . "<input id='password' type='password' class='text' maxlength='100' name='password' value='' />"
        . "</div>\n"
        . "<div class='submit'>"
            . "<input type='submit' class='button' value='Sign In' />"
        . "</div>\n"
        . "</form>"
        . "<br/>";
    if( isset($block['forgot']) && $block['forgot'] == 'yes' ) {
        $content .= "<div class='forgot-link'><p>"
            . "<a class='color' href='javscript:void(0);' onclick='swapLoginForm(\"forgotpassword\");return false;'>";
        if( isset($block['forgot-link-text']) && $block['forgot-link-text'] != '' ) {
            $content .= $block['forgot-link-text'];
        } else {
            $content .= "Forgot password?";
        }
        $content .= "</a></p></div>\n";
    }
    $content .= "</div>\n";
        
    //
    // Forgot password form
    //
    if( isset($block['forgot']) && $block['forgot'] == 'yes' ) {
        //
        // The forgot reset form
        //
        $content .= "<div id='forgotpassword-form' class='forgotpassword-form' style='display:"
            . ($startform == 'forgot' ? 'block;' : 'none;')
            . "'>"
            . "<p>Please enter your email address and you will receive a link to create a new password.</p>"
            . "<form method='POST' action=''>"
            . "<input type='hidden' name='action' value='forgot'>\n"
            . "<div class='input'>"
                . "<label for='forgotemail'>Email</label>"
                . "<input id='forgotemail' type='email' class='text' maxlength='250' name='email' value='$email' />"
            . "</div>\n" 
            . "<div class='submit'>"
                . "<input type='submit' class='button' value='Get New Password' />"
            . "</div>\n"
            . "</form>"
            . "<br/>"
            . "<div class='forgot-link'><p>"
                . "<a class='color' href='javascript:void();' onclick='swapLoginForm(\"signin\"); return false;'>"
                . "Sign In"
                . "</a></p></div>\n"
            . "</div>\n";

        //
        // Javascript to switch login/forgot password forms
        //
        $js = ""
            . " function swapLoginForm(l) {\n"
            . "     if( l == 'forgotpassword' ) {\n"
            . "         C.gE('signin-form').style.display = 'none';\n"
            . "         C.gE('forgotpassword-form').style.display = 'block';\n"
            . "         C.gE('forgotemail').value = C.gE('email').value;\n"
            . "     } else {\n"
            . "         C.gE('signin-form').style.display = 'block';\n"
            . "         C.gE('forgotpassword-form').style.display = 'none';\n"
            . "     }\n"
            . "     return true;\n"
            . " }\n"
            . "";
    }


    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';



    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
