<?php
//
// Description
// -----------
// This function will return the list of available sections to the ciniki.wng module.
//
// Arguments
// ---------
// ciniki:
// tnid:     
// args:            The possible arguments for.
//
//
// Returns
// -------
//
function ciniki_wng_wng_sections(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.wng']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.37', 'msg'=>"I'm sorry, the section you requested does not exist."));
    }

    $sections = array();

    //
    // Image, Menu with no drop downs/submenus
    //
    $sections['ciniki.wng.accountbuttons'] = array(
        'name'=>'Account/Signin Buttons',
        'module' => 'Website',
        'settings'=>array(
            'signin-label' => array('label'=>'Sign In Label', 'type'=>'text', 'hint'=>'Sign In'),
            'logout-label' => array('label'=>'Logout Label', 'type'=>'text', 'hint'=>'Logout'),
            'account-label' => array('label'=>'Account Label', 'type'=>'text', 'hint'=>'Account'),
            'cart-label' => array('label'=>'Cart Label', 'type'=>'text', 'hint'=>'Cart'),
            ),
        );

    //
    // Image, Menu with no drop downs/submenus
    //
    $sections['ciniki.wng.headermenu'] = array(
        'name'=>'Header Menu',
        'module' => 'Website',
        'settings'=>array()
        );
    if( ciniki_core_checkModuleActive($ciniki, 'ciniki.customers') ) {
        $sections['ciniki.wng.headermenu']['settings']['account-buttons'] = array(
            'label'=>'Sign In Buttons', 'type'=>'toggle', 'default'=>'no', 'toggles'=>array(
                'no' => 'No',
                'yes' => 'Yes',
                ));
        $sections['ciniki.wng.headermenu']['settings']['signin-label'] = array(
            'label'=>'Sign In Label', 
            'type'=>'text', 
            'hint'=>'Sign In',
            );
        $sections['ciniki.wng.headermenu']['settings']['logout-label'] = array(
            'label'=>'Logout Label', 
            'type'=>'text', 
            'hint'=>'Logout',
            );
        $sections['ciniki.wng.headermenu']['settings']['account-label'] = array(
            'label'=>'Account Label', 
            'type'=>'text', 
            'hint'=>'Account',
            );
        // Check if shopping cart enabled
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x08) ) {
            $sections['ciniki.wng.headermenu']['settings']['cart-label'] = array(
                'label'=>'Cart Label', 
                'type'=>'text', 
                'hint'=>'Cart',
                );
        }
        $sections['ciniki.wng.headermenu']['settings']['social-icons'] = array(
            'label'=>'Social Icons', 'type'=>'toggle', 'default'=>'no', 'toggles'=>array(
                'no' => 'No',
                'yes' => 'Yes',
                ));
    }
    $sections['ciniki.wng.headermenu']['settings']['image-id'] = array(
        'label'=>'Image', 'type'=>'image_id', 'controls'=>'all', 'separator'=>'yes', 'size'=>'medium',
        );
    $sections['ciniki.wng.headermenu']['settings']['image-position'] = array(
        'label'=>'Image Position', 'type'=>'toggle', 'default'=>'left', 'toggles'=>array(
                'left' => 'Left',
                'center' => 'Center',
//                'right' => 'Right', // **Future**
                ));
    $sections['ciniki.wng.headermenu']['settings']['title'] = array('label'=>'Title', 'type'=>'text');
    $sections['ciniki.wng.headermenu']['settings']['dropdown'] = array(
        'label'=>'Dropdown Menus', 'type'=>'toggle', 'default'=>'off', 'toggles'=>array(
                'off' => 'Off',
                'hamburger' => 'Mobile',
                'full' => 'Desktop',
                'both' => 'Both',
//                'right' => 'Right', // **Future**
                ));
    $sections['ciniki.wng.headermenu']['settings']['hide-home'] = array(
        'label'=>'Home Link', 'type'=>'toggle', 'default'=>'no', 'toggles'=>array(
                'no' => 'Show',
                'yes' => 'Hide',
                ));
    $sections['ciniki.wng.headermenu']['settings']['toggle-em'] = array('label'=>'Menu Size', 'type'=>'select', 
                'default'=>'60',
                'options'=>array(
                    '30' => 'XX-Small',
                    '40' => 'X-Small',
                    '50' => 'Small',
                    '60' => 'Medium',
                    '70' => 'Large',
                    '80' => 'X-Large',
                    '90' => 'XX-Large',
                    'custom' => 'Custom (Advanced)',
                ));
    if( ciniki_core_checkModuleActive($ciniki, 'ciniki.customers') ) {
        $sections['ciniki.wng.headermenu']['settings']['account-toggle-hide'] = array(
            'label'=>'Account Buttons', 'type'=>'toggle', 'default'=>'yes', 'toggles'=>array(
                'no' => 'Always Visible',
                'yes' => 'Only With Menu',
                ));
        //
        // Check if shopping cart enabled
        // **never implemented**
        //
/*        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.sapos', 0x08) ) {
            $sections['ciniki.wng.headermenu']['settings']['cart-icon'] = array(
                'label'=>'Cart Icon', 'type'=>'toggle', 'default'=>'no', 'toggles'=>array(
                    'no' => 'No',
                    'yes' => 'Yes',
                    ));
        } */
    }
    $sections['ciniki.wng.headermenu']['settings']['right-button-page'] = array(
        'label' => 'Right Button',
        'type' => 'select',
        'pages' => 'yes',
        );
    $sections['ciniki.wng.headermenu']['settings']['right-button-text'] = array(
        'label' => 'Right Button Text',
        'type' => 'text',
        );

    //
    // Footer Menu - No Image
    //
    $sections['ciniki.wng.footermenu'] = array(
        'name'=>'Footer Menu',
        'module' => 'Website',
        'settings'=>array()
        );
    $sections['ciniki.wng.footermenu']['settings']['hide-home'] = array(
        'label'=>'Home Link', 'type'=>'toggle', 'default'=>'no', 'toggles'=>array(
                'no' => 'Show',
                'yes' => 'Hide',
                ));
    $sections['ciniki.wng.footermenu']['settings']['toggle-em'] = array('label'=>'Menu Size', 'type'=>'select', 
                'default'=>'60',
                'options'=>array(
                    '30' => 'XX-Small',
                    '40' => 'X-Small',
                    '50' => 'Small',
                    '60' => 'Medium',
                    '70' => 'Large',
                    '80' => 'X-Large',
                    '90' => 'XX-Large',
                    'custom' => 'Custom (Advanced)',
                ));

    //
    // Headline scroller
    //
    $sections['ciniki.wng.headlinescroll'] = array(
        'name'=>'Headline Scroll',
        'module' => 'Website',
        'settings'=>array(
            'speed'=>array('label'=>'Speed', 'type'=>'toggle', 'default'=>'medium', 'toggles'=>array(    
                'xslow' => 'X-Slow',
                'slow' => 'Slow',
                'medium' => 'Medium',
                'fast' => 'Fast',
                'xfast' => 'X-Fast',
                )),
            'headline-1'=>array('label'=>'Headline 1', 'type'=>'text'),
            'headline-2'=>array('label'=>'Headline 2', 'type'=>'text'),
            'headline-3'=>array('label'=>'Headline 3', 'type'=>'text'),
            'headline-4'=>array('label'=>'Headline 4', 'type'=>'text'),
            'headline-5'=>array('label'=>'Headline 5', 'type'=>'text'),
            'headline-6'=>array('label'=>'Headline 6', 'type'=>'text'),
            'headline-7'=>array('label'=>'Headline 7', 'type'=>'text'),
            'headline-8'=>array('label'=>'Headline 8', 'type'=>'text'),
            'headline-9'=>array('label'=>'Headline 9', 'type'=>'text'),
            'headline-10'=>array('label'=>'Headline 10', 'type'=>'text'),
            ),
        );

    //
    // Testimonials/Quotes
    //
    $sections['ciniki.wng.testimonials'] = array(
        'name'=>'Testimonials',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            'scrolling' => array('label'=>'Scrolling', 'type'=>'toggle', 'default'=>'no', 'toggles'=>array('no'=>'No', 'yes'=>'Yes')),
            'speed'=>array('label'=>'Speed', 'type'=>'toggle', 'default'=>'medium', 'toggles'=>array(    
                'none' => 'No Auto Advance',
                'xslow' => 'X-Slow',
                'slow' => 'Slow',
                'medium' => 'Medium',
                'fast' => 'Fast',
                'xfast' => 'X-Fast',
                )),
            'size'=>array('label'=>'Size', 'type'=>'toggle', 'default'=>'medium', 'toggles'=>array(    
                'tiny' => 'Tiny',
                'small' => 'Small',
                'medium' => 'Medium',
                'large' => 'Large',
                'xlarge' => 'X-Large',
                )),
            ),
        'repeats' => array(
            'label' => 'Testimonials',
            'headerValues' => array('Testimonial', 'Author'),
            'cellClasses' => array('', ''),
            'dataMaps' => array('content', 'author'),
            'addTxt' => 'Add Testimonial',
            'fields' => array(
                'content' => array(
                    'label' => 'Testimonial',
                    'type' => 'textarea', 
                    'size' => 'medium', 
                    ),
                'author' => array(
                    'label' => 'Author',
                    'type' => 'text', 
                    ),
                )),
        );
/*    for($i = 1; $i <= 10; $i++) {
        $sections['ciniki.wng.testimonials']['settings']["content-{$i}"] = array(
            'label' => 'Testimonial #' . $i, 
            'type' => 'textarea', 
            'size' => 'medium', 
            'separator' => 'yes',
            );
        $sections['ciniki.wng.testimonials']['settings']["author-{$i}"] = array(
            'label' => 'Author', 
            'type' => 'text', 
            );
    } */

    //
    // Basic Text Content
    //
    $sections['ciniki.wng.text'] = array(
        'name'=>'Text',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            'content' => array('label'=>'Content', 'type'=>'textarea'),
//            'button-1-page' => array('label'=>'Page', 'pages'=>'yes', 'type'=>'text', 'separator'=>'yes'),
//            'button-1-text' => array('label'=>'Button 1 Text', 'type'=>'text'),
//            'button-1-url' => array('label'=>'Button URL', 'type'=>'text'),
//            'button-2-page' => array('label'=>'Page', 'pages'=>'yes', 'type'=>'text', 'separator'=>'yes'),
//            'button-2-text' => array('label'=>'Button 2 Text', 'type'=>'text'),
//            'button-2-url' => array('label'=>'Button URL', 'type'=>'text'),
            ),
        );

    //
    // A series of buttons
    //
    $sections['ciniki.wng.buttons'] = array(
        'name'=>'Buttons',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            'align' => array('label'=>'Alignment', 'type'=>'toggle', 'default'=>'left', 'toggles'=>array(
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right',
                )),
            ),
        'repeats' => array(
            'label' => 'Buttons',
            'headerValues' => array('Text', 'Linked To'),
            'cellClasses' => array('', 'page-link'),
            'dataMaps' => array('link-text', 'link-page'),
            'addTxt' => 'Add Button',
            'fields' => array(
                'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
                'link-text' => array('label'=>'Text', 'type'=>'text'),
                'link-url' => array('label'=>'URL', 'type'=>'text'),
                ),
            ));
/*    for($i = 1; $i < 15; $i++ ) {
        $sections['ciniki.wng.buttons']['settings']["button-{$i}-page"] = array(
            'label' => "Button {$i}", 'type' => 'select', 'pages' => 'yes', 'separator' => 'yes', 'draggable'=>$i,
            );
        $sections['ciniki.wng.buttons']['settings']["button-{$i}-text"] = array(
            'label' => "Text", 'type' => 'text', 'draggable'=>$i,
            );
        $sections['ciniki.wng.buttons']['settings']["button-{$i}-url"] = array(
            'label' => "URL", 'type' => 'text', 'draggable'=>$i,
            );
    }*/

    //
    // Text Content & Photo
    //
    $sections['ciniki.wng.contentphoto'] = array(
        'name'=>'Text & Photo',
        'module' => 'Website',
        'settings'=>array(
            'image-id' => array('label'=>'Image', 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium'),
            'image-position'=>array('label'=>'Image Position', 'type'=>'select', 'default'=>'top-right', 'options'=>array(
                'top-left' => 'Top Left',
                'top-left-inline' => 'Top Left Inline',
                'bottom-left' => 'Bottom Left',
                'top-right' => 'Top Right',
                'top-right-inline' => 'Top Right Inline',
                'bottom-right' => 'Bottom Right',
                )),
            'image-size'=>array('label'=>'Image Size', 'type'=>'toggle', 'default'=>'half', 'toggles'=>array(
                'half' => 'Full',
                'large' => 'Large',
                'medium' => 'Medium',
                'small' => 'Small',
                'tiny' => 'Tiny',
                )),
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            'content' => array('label'=>'Content', 'type'=>'textarea'),
            'button-1-page' => array('label'=>'Button 1', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes'),
            'button-1-text' => array('label'=>'Text', 'type'=>'text'),
            'button-1-url' => array('label'=>'URL', 'type'=>'text'),
            'button-2-page' => array('label'=>'Button 2', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes'),
            'button-2-text' => array('label'=>'Text', 'type'=>'text'),
            'button-2-url' => array('label'=>'URL', 'type'=>'text'),
            'button-3-page' => array('label'=>'Button 3', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes'),
            'button-3-text' => array('label'=>'Text', 'type'=>'text'),
            'button-3-url' => array('label'=>'URL', 'type'=>'text'),
            ),
        );

    //
    // Text Content & Video
    //
    $sections['ciniki.wng.contentvideo'] = array(
        'name'=>'Text & Video',
        'module' => 'Website',
        'settings'=>array(
            'video-url' => array('label'=>'Video URL', 'type'=>'text'),
            'video-position'=>array('label'=>'Video Position', 'type'=>'toggle', 'default'=>'top-right', 'toggles'=>array(
                'top' => 'Top Wide',
                'top-left' => 'Top Left',
                'bottom-left' => 'Bottom Left',
                'top-right' => 'Top Right',
                'bottom-right' => 'Bottom Right',
                'bottom' => 'Bottom Wide',
                )),
            'clickload' => array('label'=>'Fast Load', 'type'=>'toggle', 'default'=>'yes', 'toggles'=>array(
                'no' => 'No',
                'yes' => 'Yes',
                )),
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            'content' => array('label'=>'Content', 'type'=>'textarea'),
            'button-1-page' => array('label'=>'Button 1', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes'),
            'button-1-text' => array('label'=>'Text', 'type'=>'text'),
            'button-1-url' => array('label'=>'URL', 'type'=>'text'),
            'button-2-page' => array('label'=>'Button 2', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes'),
            'button-2-text' => array('label'=>'Text', 'type'=>'text'),
            'button-2-url' => array('label'=>'URL', 'type'=>'text'),
            'button-3-page' => array('label'=>'Button 3', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes'),
            'button-3-text' => array('label'=>'Text', 'type'=>'text'),
            'button-3-url' => array('label'=>'URL', 'type'=>'text'),
            ),
        );

    //
    // List & Photo
    //
    $sections['ciniki.wng.listphoto'] = array(
        'name'=>'List & Photo',
        'module' => 'Website',
        'settings'=>array(
            'image-id' => array('label'=>'Image', 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium'),
            'image-position'=>array('label'=>'Image Position', 'type'=>'toggle', 'toggles'=>array(    
                'top-left' => 'Top Left',
                'bottom-left' => 'Bottom Left',
                'top-right' => 'Top Right',
                'bottom-right' => 'Bottom Right',
                )),
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            ),
        );

    for($i = 1; $i <= 10; $i++) {
        $sections['ciniki.wng.listphoto']['settings']["text-{$i}"] = array(
            'label'=>"Item {$i}", 'type'=>'textarea', 'size'=>'small', 'separator'=>'yes',
            );
        $sections['ciniki.wng.listphoto']['settings']["link-{$i}-page"] = array(
            'label'=>'Link to', 'type'=>'select', 'pages'=>'yes', 
            );
        $sections['ciniki.wng.listphoto']['settings']["link-{$i}-text"] = array(
            'label'=>'Text', 'type'=>'text', 
            );
        $sections['ciniki.wng.listphoto']['settings']["link-{$i}-url"] = array(
            'label'=>'Custom URL', 'type'=>'text',
            );
    }
    $sections['ciniki.wng.listphoto']['settings']['list-footer'] = array('label'=>'Content', 'type'=>'textarea', 'separator'=>'yes');
    $sections['ciniki.wng.listphoto']['settings']['button-1-page'] = array('label'=>'Button 1', 'type'=>'select', 'pages'=>'yes');
    $sections['ciniki.wng.listphoto']['settings']['button-1-text'] = array('label'=>'Text', 'type'=>'text');
    $sections['ciniki.wng.listphoto']['settings']['button-1-url'] = array('label'=>'URL', 'type'=>'text');
    $sections['ciniki.wng.listphoto']['settings']['button-2-page'] = array('label'=>'Button 2', 'type'=>'select', 'pages'=>'yes');
    $sections['ciniki.wng.listphoto']['settings']['button-2-text'] = array('label'=>'Text', 'type'=>'text');
    $sections['ciniki.wng.listphoto']['settings']['button-2-url'] = array('label'=>'URL', 'type'=>'text');
    $sections['ciniki.wng.listphoto']['settings']['button-3-page'] = array('label'=>'Button 3', 'type'=>'select', 'pages'=>'yes');
    $sections['ciniki.wng.listphoto']['settings']['button-3-text'] = array('label'=>'Text', 'type'=>'text');
    $sections['ciniki.wng.listphoto']['settings']['button-3-url'] = array('label'=>'URL', 'type'=>'text');

    //
    // List Content & Photo
    //
    $sections['ciniki.wng.iconlistphoto'] = array(
        'name'=>'Icon List & Photo',
        'module' => 'Website',
        'settings'=>array(
            'image-id' => array('label'=>'Image', 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium'),
            'image-position'=>array('label'=>'Image Position', 'type'=>'toggle', 'toggles'=>array(    
                'top-left' => 'Top Left',
                'bottom-left' => 'Bottom Left',
                'top-right' => 'Top Right',
                'bottom-right' => 'Bottom Right',
                )),
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            ),
        );

    for($i = 1; $i <= 6; $i++) {
        $sections['ciniki.wng.iconlistphoto']['settings']["icon-{$i}"] = array(
            'label'=>"Image {$i}", 'type'=>'image_id', 'controls'=>'all', 'separator'=>'yes', 'size'=>'icon',
            );
        $sections['ciniki.wng.iconlistphoto']['settings']["text-{$i}"] = array(
            'label'=>'Text', 'type'=>'textarea', 'size'=>'small',
            );
        $sections['ciniki.wng.iconlistphoto']['settings']["link-{$i}-page"] = array(
            'label'=>'Link to', 'type'=>'select', 'pages'=>'yes', 
            );
        $sections['ciniki.wng.iconlistphoto']['settings']["link-{$i}-text"] = array(
            'label'=>'Text', 'type'=>'text', 
            );
        $sections['ciniki.wng.iconlistphoto']['settings']["link-{$i}-url"] = array(
            'label'=>'Custom URL', 'type'=>'text',
            );
    }
    $sections['ciniki.wng.iconlistphoto']['settings']['button-1-page'] = array(
        'label'=>'Button 1 Page', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes',
        );
    $sections['ciniki.wng.iconlistphoto']['settings']['button-1-text'] = array(
        'label'=>'Text', 'type'=>'text', 
        );
    $sections['ciniki.wng.iconlistphoto']['settings']['button-1-url'] = array(
        'label'=>'Custom URL', 'type'=>'text',
        );
    $sections['ciniki.wng.iconlistphoto']['settings']['button-2-page'] = array(
        'label'=>'Button 2 Page', 'type'=>'select', 'pages'=>'yes', 'separator'=>'yes',
        );
    $sections['ciniki.wng.iconlistphoto']['settings']['button-2-text'] = array(
        'label'=>'Text', 'type'=>'text',
        );
    $sections['ciniki.wng.iconlistphoto']['settings']['button-2-url'] = array(
        'label'=>'Custom URL', 'type'=>'text',
        );

    //
    // Image/Content Carousel
    //
    $sections['ciniki.wng.carousel'] = array(
        'name'=>'Image Carousel',
        'module' => 'Website',
        'settings'=>array(
            'speed'=>array('label'=>'Speed', 'type'=>'toggle', 'default'=>'medium', 'toggles'=>array(    
                'none' => 'No Auto Advance',
                'xslow' => 'X-Slow',
                'slow' => 'Slow',
                'medium' => 'Medium',
                'fast' => 'Fast',
                'xfast' => 'X-Fast',
                )),
            'image-format'=>array('label'=>'Images', 'type'=>'toggle', 'default'=>'cropped', 'toggles'=>array(
                'cropped' => 'Cropped',
                'padded' => 'Padded',
                )),
            ),
        'repeats' => array(
            'label' => 'Images',
            'headerValues' => array('Image', 'Title', 'Linked To'),
            'cellClasses' => array('thumbnail', '', 'page-link'),
            'dataMaps' => array('image', 'title', 'link-page'),
            'addTxt' => 'Add Image',
            'fields' => array(
                'image' => array(
                    'label'=>"Image", 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium',
                    ),
                'image-position' => array(
                    'label' => 'Image Position', 'type'=>'select', 'default'=>'center-center', 'options'=>array(
                        'top-left' => 'Top Left',
                        'top-center' => 'Top Center',
                        'top-right' => 'Top Right',
                        'center-left' => 'Left',
                        'center-center' => 'Centered',
                        'center-right' => 'Right',
                        'bottom-left' => 'Bottom Left',
                        'bottom-center' => 'Bottom Center',
                        'bottom-right' => 'Bottom Right',
                    )),
                'title' => array('label'=>'Title', 'type'=>'text'),
                'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
                'link-text' => array('label'=>'SEO Label', 'type'=>'text'),
                'link-url' => array('label'=>'URL', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'small'),
                ),
            ));

    //
    // The 3 text column
    //
    $sections['ciniki.wng.threetextcol'] = array(
        'name'=>'3 Text Columns',
        'module' => 'Website',
        'settings'=>array(
            'title'=>array('label'=>'Title', 'type'=>'text'),
            ),
        );
    for($i = 1; $i <= 3; $i++) {
        $sections['ciniki.wng.threetextcol']['settings']["title-{$i}"] = array(
            'label'=>"Column {$i} Title", 'type'=>'text', 'separator'=>'yes');
        $sections['ciniki.wng.threetextcol']['settings']["content-{$i}"] = array('label'=>'Content', 'type'=>'textarea');
        $sections['ciniki.wng.threetextcol']['settings']["btext-{$i}"] = array('label'=>'Button Text', 'type'=>'text');
        $sections['ciniki.wng.threetextcol']['settings']["burl-{$i}"] = array('label'=>'Button URL', 'type'=>'text');
    }

    //
    // The 4 text column
    //
    $sections['ciniki.wng.fourtextcol'] = array(
        'name'=>'4 Text Columns',
        'module' => 'Website',
        'settings'=>array(
            'section-title'=>array('label'=>'Title', 'type'=>'text'),
            ),
        );
    for($i = 1; $i <= 4; $i++) {
        $sections['ciniki.wng.fourtextcol']['settings']["title-{$i}"] = array(
            'label'=>"Title #1", 'type'=>'text', 'separator'=>'yes');
        $sections['ciniki.wng.fourtextcol']['settings']["content-{$i}"] = array('label'=>'Content', 'type'=>'textarea');
        $sections['ciniki.wng.fourtextcol']['settings']["btext-{$i}"] = array('label'=>'Button Text', 'type'=>'text');
        $sections['ciniki.wng.fourtextcol']['settings']["burl-{$i}"] = array('label'=>'Button URL', 'type'=>'text');
    }

    //
    // Google Map
    //
    $sections['ciniki.wng.googlemap'] = array(
        'name'=>'Google Map',
        'module' => 'Website',
        'settings'=> array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'zoom' => array('label'=>'Initial Zoom', 'type'=>'toggle', 'default'=>'13', 'toggles'=>array(
                '8' => '8',
                '9' => '9',
                '10' => '10',
                '11' => '11',
                '12' => '12',
                '13' => '13',
                '14' => '14',
                '15' => '15',
                )),
            'latitude'=>array('label'=>'Latitude', 'type'=>'text'),
            'longitude'=>array('label'=>'Longitude', 'type'=>'text'),
            'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'medium'),
            'map-position'=>array('label'=>'Map Position', 'type'=>'select', 'default'=>'top-right', 'options'=>array(
                'top-left' => 'Top Left',
                'bottom-left' => 'Bottom Left',
                'top-right' => 'Top Right',
                'bottom-right' => 'Bottom Right',
                )),
            ),
        );

    $sections['ciniki.wng.title'] = array(
        'name'=>'Title',
        'module' => 'Website',
        'settings'=>array(
            'title'=>array('label'=>'Title', 'type'=>'text'),
            ),
        ); 

    $sections['ciniki.wng.contactform'] = array(
        'name'=>'Contact Form',
        'module' => 'Website',
        'settings'=>array(
            'title'=>array('label'=>'Title', 'type'=>'text'),
            'contact-intro' => array('label'=>'Contact Intro', 'type'=>'textarea', 'size'=>'small'),
            'address' => array('label'=>'Location', 'type'=>'textarea', 'size'=>'small'),
            'mailing' => array('label'=>'Mailing', 'type'=>'textarea', 'size'=>'small'),
            'phone' => array('label'=>'Phone', 'type'=>'text'),
            'fax' => array('label'=>'Fax', 'type'=>'text'),
            'email' => array('label'=>'Email', 'type'=>'text'),
            'staff-1-name' => array('label'=>'Staff #1 Name', 'type'=>'text'),
            'staff-1-phone' => array('label'=>'Phone', 'type'=>'text'),
            'staff-1-email' => array('label'=>'Email', 'type'=>'text'),
            'staff-2-name' => array('label'=>'Staff #2 Name', 'type'=>'text'),
            'staff-2-phone' => array('label'=>'Phone', 'type'=>'text'),
            'staff-2-email' => array('label'=>'Email', 'type'=>'text'),
            'staff-3-name' => array('label'=>'Staff #3 Name', 'type'=>'text'),
            'staff-3-phone' => array('label'=>'Phone', 'type'=>'text'),
            'staff-3-email' => array('label'=>'Email', 'type'=>'text'),
            'staff-4-name' => array('label'=>'Staff #4 Name', 'type'=>'text'),
            'staff-4-phone' => array('label'=>'Phone', 'type'=>'text'),
            'staff-4-email' => array('label'=>'Email', 'type'=>'text'),
            'staff-5-name' => array('label'=>'Staff #5 Name', 'type'=>'text'),
            'staff-5-phone' => array('label'=>'Phone', 'type'=>'text'),
            'staff-5-email' => array('label'=>'Email', 'type'=>'text'),
            'hours-monday' => array('label'=>'Monday Hours', 'type'=>'text'),
            'hours-tuesday' => array('label'=>'Tuesday Hours', 'type'=>'text'),
            'hours-wednesday' => array('label'=>'Wednesday Hours', 'type'=>'text'),
            'hours-thursday' => array('label'=>'Thursday Hours', 'type'=>'text'),
            'hours-friday' => array('label'=>'Friday Hours', 'type'=>'text'),
            'hours-saturday' => array('label'=>'Saturday Hours', 'type'=>'text'),
            'hours-sunday' => array('label'=>'Sunday Hours', 'type'=>'text'),
            'directions' => array('label'=>'Directions', 'type'=>'textarea', 'size'=>'small'),
            'contact-outro' => array('label'=>'Contact Message', 'type'=>'textarea', 'size'=>'small'),
            'form-position' => array('label'=>'Form Position', 'type'=>'toggle', 'default'=>'bottom-right', 'separator'=>'yes', 'toggles'=>array(
                'top-left' => 'Top Left',
                'bottom-left' => 'Bottom Left',
                'top-right' => 'Top Right',
                'bottom-right' => 'Bottom Right',
                )),
            'form-title'=>array('label'=>'Form Title', 'type'=>'text'),
            'form-intro'=>array('label'=>'Form Intro', 'type'=>'textarea', 'size'=>'medium'),
            ),
        );
    // Extra fields for contact form
    for($i = 1; $i <= 5; $i++) {
        $sections['ciniki.wng.contactform']['settings']["field-{$i}-label"] = array(
            'label' => "Extra Field {$i}", 'type' => 'text',
            );
    }
    $sections['ciniki.wng.contactform']['settings']['notify-emails'] = array(
        'label' => 'Emails', 'type' => 'text',
        );
    $sections['ciniki.wng.contactform']['settings']['submitted-message'] = array(
        'label'=>'Thank You Message', 'type'=>'textarea', 'size'=>'medium',
        );

    //
    // Section for display address/social icons in header
    //
    $sections['ciniki.wng.socialicons'] = array(
        'name'=>'Social Icons',
        'module' => 'Website',
        'settings'=>array(
            'content' => array('label'=>'Content', 'type'=>'text'),
            'toggle-em' => array('label'=>'Show on screen', 'type'=>'select', 
                'default'=>'60',
                'options'=>array(
                    '30' => 'XX-Small',
                    '40' => 'X-Small',
                    '50' => 'Small',
                    '60' => 'Medium',
                    '70' => 'Large',
                    '80' => 'X-Large',
                    '90' => 'XX-Large',
                    'custom' => 'Custom (Advanced)',
                    )),
            ));

    //
    // Section for text cards
    //
    $sections['ciniki.wng.textcards'] = array(
        'name'=>'Text Cards',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            ),
        'repeats' => array(
            'label' => 'Cards',
            'headerValues' => array('Title', 'Linked To'),
            'cellClasses' => array('', 'page-link'),
            'dataMaps' => array('title', 'link-page'),
            'addTxt' => 'Add Card',
            'fields' => array(
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'medium'),
                'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
                'link-text' => array('label'=>'Text', 'type'=>'text'),
                'link-url' => array('label'=>'URL', 'type'=>'text'),
                ),
            ));
    //
    // Section for flex cards
    //
    $sections['ciniki.wng.flexcards'] = array(
        'name'=>'Flex Cards',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'image-ratio' => array('label' => 'Image Ratio', 
                'type'=>'select', 
                'default'=>'1-1', 
                'options'=>array(
                    '2-1' => 'Panoramic',
                    '16-9' => 'Letterbox',
                    '6-4' => 'Wider',
                    '4-3' => 'Wide',
                    '1-1' => 'Square',
                    '3-4' => 'Tall',
                    '4-6' => 'Taller',
                )),
            'title-position' => array('label' => 'Title Position', 
                'type'=>'select', 
                'default'=>'below', 
                'options'=>array(
                    'above' => 'Above',
                    'overlay-top' => 'Overlay Top',
                    'overlay-tophalf' => 'Overlay Top Half',
                    'overlay-center' => 'Centered',
                    'overlay-bottomhalf' => 'Bottom Half',
                    'overlay-bottom' => 'Bottom',
                    'below' => 'Below',
                )),
            ),
        'repeats' => array(
            'label' => 'Images',
            'headerValues' => array('Image', 'Title', 'Linked To'),
            'cellClasses' => array('thumbnail', '', 'page-link'),
            'dataMaps' => array('image', 'title', 'link-page'),
            'addTxt' => 'Add Card',
            'fields' => array(
                'image' => array(
                    'label'=>"Image", 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium',
                    ),
                'image-position' => array(
                    'label' => 'Image Position', 'type'=>'select', 'default'=>'center-center', 'options'=>array(
                        'top-left' => 'Top Left',
                        'top-center' => 'Top Center',
                        'top-right' => 'Top Right',
                        'center-left' => 'Left',
                        'center-center' => 'Centered',
                        'center-right' => 'Right',
                        'bottom-left' => 'Bottom Left',
                        'bottom-center' => 'Bottom Center',
                        'bottom-right' => 'Bottom Right',
                    )),
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'small'),
                'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
                'link-text' => array('label'=>'Text', 'type'=>'text'),
                'link-url' => array('label'=>'URL', 'type'=>'text'),
                ),
            ));

    //
    // Section for trading cards
    //
    $sections['ciniki.wng.tradingcards'] = array(
        'name'=>'Trading Cards',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'image-ratio' => array('label' => 'Image Ratio', 
                'type'=>'select', 
                'default'=>'1-1', 
                'options'=>array(
                    '2-1' => 'Panoramic',
                    '16-9' => 'Letterbox',
                    '6-4' => 'Wider',
                    '4-3' => 'Wide',
                    '1-1' => 'Square',
                    '3-4' => 'Tall',
                    '4-6' => 'Taller',
                )),
            'title-position' => array('label' => 'Title Position', 
                'type'=>'select', 
                'default'=>'below', 
                'options'=>array(
                    'above' => 'Above',
                    'overlay-top' => 'Overlay Top',
                    'overlay-tophalf' => 'Overlay Top Half',
                    'overlay-center' => 'Centered',
                    'overlay-bottomhalf' => 'Bottom Half',
                    'overlay-bottom' => 'Bottom',
                    'below' => 'Below',
                )),
            ),
        'repeats' => array(
            'label' => 'Images',
            'headerValues' => array('Image', 'Title', 'Linked To'),
            'cellClasses' => array('thumbnail', '', 'page-link'),
            'dataMaps' => array('image', 'title', 'link-page'),
            'addTxt' => 'Add Card',
            'fields' => array(
                'image' => array(
                    'label'=>"Image", 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium',
                    ),
                'image-position' => array(
                    'label' => 'Image Position', 'type'=>'select', 'default'=>'center-center', 'options'=>array(
                        'top-left' => 'Top Left',
                        'top-center' => 'Top Center',
                        'top-right' => 'Top Right',
                        'center-left' => 'Left',
                        'center-center' => 'Centered',
                        'center-right' => 'Right',
                        'bottom-left' => 'Bottom Left',
                        'bottom-center' => 'Bottom Center',
                        'bottom-right' => 'Bottom Right',
                    )),
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'small'),
                'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
                'link-text' => array('label'=>'Text', 'type'=>'text'),
                'link-url' => array('label'=>'URL', 'type'=>'text'),
                ),
            ));

    //
    // Section for multiple content photo blocks
    //
    $sections['ciniki.wng.multicontentphoto'] = array(
        'name'=>'Multi Text & Image',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'image-size'=>array('label'=>'Image Size', 'type'=>'toggle', 'default'=>'half', 'toggles'=>array(
                'half' => 'Full',
                'large' => 'Large',
                'medium' => 'Medium',
                'small' => 'Small',
                'tiny' => 'Tiny',
                )),
            'image-position' => array(
                'label' => 'Image Position', 'type'=>'select', 'default'=>'center-center', 'options'=>array(
                    'top-left' => 'Top Left',
                    'top-left-inline' => 'Top Left Inline',
                    'bottom-left' => 'Bottom Left',
                    'top-right' => 'Top Right',
                    'top-right-inline' => 'Top Right Inline',
                    'bottom-right' => 'Bottom Right',
                )),
            'image-alternate' => array(
                'label' => 'Alternate Positions', 'type'=>'toggle', 'default'=>'yes', 'toggles'=>array(
                    'no' => 'No',
                    'yes' => 'Yes',
                )),
            ),
        'repeats' => array(
            'label' => 'Images',
            'headerValues' => array('Image', 'Title', 'Linked To'),
            'cellClasses' => array('thumbnail', '', 'page-link'),
            'dataMaps' => array('image', 'title', 'link-page'),
            'addTxt' => 'Add Card',
            'fields' => array(
                'image' => array(
                    'label'=>"Image", 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium',
                    ),
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'small'),
//                'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
//                'link-text' => array('label'=>'Text', 'type'=>'text'),
//                'link-url' => array('label'=>'URL', 'type'=>'text'),
                ),
            ));

    //
    // Section for image buttons
    //
    $sections['ciniki.wng.imagebuttons'] = array(
        'name'=>'Image Buttons',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'image-ratio' => array('label' => 'Image Ratio', 
                'type'=>'select', 
                'default'=>'1-1', 
                'options'=>array(
                    '2-1' => 'Panoramic',
                    '16-9' => 'Letterbox',
                    '6-4' => 'Wider',
                    '4-3' => 'Wide',
                    '1-1' => 'Square',
                    '3-4' => 'Tall',
                    '4-6' => 'Taller',
                )),
            'title-position' => array('label' => 'Title Position', 
                'type'=>'select', 
                'default'=>'below', 
                'options'=>array(
                    'above' => 'Above',
                    'overlay-top' => 'Overlay Top',
                    'overlay-tophalf' => 'Overlay Top Half',
                    'overlay-center' => 'Centered',
                    'overlay-bottomhalf' => 'Bottom Half',
                    'overlay-bottom' => 'Bottom',
                    'below' => 'Below',
                )),
            ),
        'repeats' => array(
            'label' => 'Images',
            'headerValues' => array('Image', 'Title', 'Linked To'),
            'cellClasses' => array('thumbnail', '', 'page-link'),
            'dataMaps' => array('image', 'title-text', 'link-page'),
            'addTxt' => 'Add Image',
            'fields' => array(
                'image' => array(
                    'label'=>"Image", 'type'=>'image_id', 'controls'=>'all', 'separator'=>'yes', 'size'=>'small',
                    ),
                'image-position' => array(
                    'label' => 'Image Position', 'type'=>'select', 'default'=>'center-center', 'options'=>array(
                        'top-left' => 'Top Left',
                        'top-center' => 'Top Center',
                        'top-right' => 'Top Right',
                        'center-left' => 'Left',
                        'center-center' => 'Centered',
                        'center-right' => 'Right',
                        'bottom-left' => 'Bottom Left',
                        'bottom-center' => 'Bottom Center',
                        'bottom-right' => 'Bottom Right',
                    )),
                'title-text' => array('label'=>'Text', 'type'=>'text'),
                'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
                'link-url' => array('label'=>'URL', 'type'=>'text'),
                ),
            ));
/*    for($i = 1; $i <= 9; $i++) {
        $sections['ciniki.wng.imagebuttons']['settings']["image-{$i}"] = array(
            'label'=>"Image {$i}", 'type'=>'image_id', 'controls'=>'all', 'separator'=>'yes', 'size'=>'small', 'draggable'=>$i,
            );
        $sections['ciniki.wng.imagebuttons']['settings']["image-position-{$i}"] = array(
            'label' => 'Image Position', 'type'=>'select', 'default'=>'center-center', 'draggable'=>$i, 'options'=>array(
                'top-left' => 'Top Left',
                'top-center' => 'Top Center',
                'top-right' => 'Top Right',
                'center-left' => 'Left',
                'center-center' => 'Centered',
                'center-right' => 'Right',
                'bottom-left' => 'Bottom Left',
                'bottom-center' => 'Bottom Center',
                'bottom-right' => 'Bottom Right',
                ));
        $sections['ciniki.wng.imagebuttons']['settings']["title-{$i}-text"] = array('label'=>'Text', 'type'=>'text', 'draggable'=>$i);
        $sections['ciniki.wng.imagebuttons']['settings']["link-{$i}-page"] = array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes', 'draggable'=>$i);
        $sections['ciniki.wng.imagebuttons']['settings']["link-{$i}-url"] = array('label'=>'URL', 'type'=>'text', 'draggable'=>$i);
    } */

    //
    // Section for simple image
    //
    $sections['ciniki.wng.image'] = array(
        'name' => 'Image',
        'module' => 'Website',
        'settings' => array(
            'image-id' => array('label'=>"Image", 'type'=>'image_id', 'controls'=>'all', 'separator'=>'yes', 'size'=>'medium'),
            'layout' => array('label'=>'Layout', 'type'=>'toggle', 'default'=>'normal', 'toggles'=>array(
                'normal' => 'Normal',
                'banner' => 'Banner',
                )),
            'link-page' => array('label'=>'Link to', 'type'=>'select', 'pages'=>'yes'),
            'link-text' => array('label'=>'SEO Label', 'type'=>'text'),
            'link-url' => array('label'=>'URL', 'type'=>'text'),
            ),
        );

    //
    // Image Gallery
    //
    $sections['ciniki.wng.images'] = array(
        'name'=>'Image Gallery',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'small'),
            ),
        'repeats' => array(
            'label' => 'Images',
            'headerValues' => array('Image', 'Title', 'Caption'),
            'cellClasses' => array('thumbnail', '', ''),
            'dataMaps' => array('image', 'title', 'content'),
            'addTxt' => 'Add Image',
            'fields' => array(
                'image' => array(
                    'label'=>"Image", 'type'=>'image_id', 'controls'=>'all', 'size'=>'medium',
                    ),
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Caption', 'type'=>'textarea', 'size'=>'small'),
                ),
            ));

    //
    // Section for Files
    //
    $sections['ciniki.wng.files'] = array(
        'name' => 'Files',
        'module' => 'Website',
        'settings' => array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'content' => array('label'=>'Content', 'type'=>'textarea'),
            ),
        'repeats' => array(
            'label' => 'Files',
            'headerValues' => array('Name', 'File'),
            'cellClasses' => array(''),
            'dataMaps' => array('name', 'file-id_filename'),
            'addTxt' => 'Add File',
            'fields' => array(
                'file-id' => array('label'=>"File", 'type'=>'file_id'),
                'name' => array('label'=>"Name", 'type'=>'text'),
                ),
            ));

    //
    // Accordian section
    //
    $sections['ciniki.wng.accordian'] = array(
        'name'=>'Accordian',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'content' => array('label'=>'Intro', 'type'=>'textarea'),
            ),
        'repeats' => array(
            'label' => 'Blocks',
            'headerValues' => array('Title'),
            'cellClasses' => array(''),
            'dataMaps' => array('title'),
            'addTxt' => 'Add Block',
            'fields' => array(
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'large'),
                ),
            ));

    //
    // Section for numbered lists
    //
    $sections['ciniki.wng.orderedlist'] = array(
        'name'=>'Ordered List',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            'content' => array('label'=>'Intro', 'type'=>'textarea'),
            'list-type' => array('label'=>'Type', 'type'=>'toggle', 'default'=>'1', 'toggles'=>array(
                '1' => '1',
                'A' => 'A',
                'a' => 'a',
                'i' => 'i',
                'I' => 'I',
                )),
            'start' => array('label'=>'Start', 'type'=>'text', 'default'=>'previous', 'size'=>'small'),
            ),
        'repeats' => array(
            'label' => 'List Items',
            'headerValues' => array('Title', 'Content'),
            'cellClasses' => array('', ''),
            'dataMaps' => array('title', 'content'),
            'addTxt' => 'Add Item',
            'fields' => array(
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'large'),
                ),
            ));

    //
    // Section for tables
    //
/*    $sections['ciniki.wng.table'] = array(
        'name'=>'Table',
        'module' => 'Website',
        'settings'=>array(
            'title' => array('label'=>'Title', 'type'=>'text'),
            'subtitle' => array('label'=>'Subtitle', 'type'=>'text'),
            'content' => array('label'=>'Intro', 'type'=>'textarea'),
            'num_cols' => array('label'=>'# Columns', 'type'=>'text', 'size'=>'small'),
            ),
        'repeats' => array(
            'label' => 'Table Rows',
            'headerValues' => array('Title', 'Content'),
            'cellClasses' => array('', ''),
            'dataMaps' => array('title', 'content'),
            'addTxt' => 'Add Item',
            'fields' => array(
                'title' => array('label'=>'Title', 'type'=>'text'),
                'content' => array('label'=>'Content', 'type'=>'textarea', 'size'=>'large'),
                ), 
            )); */

    return array('stat'=>'ok', 'sections'=>$sections);
}
?>
