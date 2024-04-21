<?php
//
// Description
// -----------
// Generate a html form, typically produced by ciniki.forms module, but could
// also be from wng or other modules.
//
// The form can be a sectioned form with API callbacks for saving sections,
// OR the form can be a simple set of fields with submit/cancel.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_form(&$ciniki, $tnid, $request, $block) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');

    $content = '';
    $js = '';

    //
    // Setup error message area
    //
    $content .= "<div id='form-errors' class='block-msg error center form-errors"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : ' limit-width')
        . (isset($block['problem-list']) && $block['problem-list'] != '' ? '' : ' hidden')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";
    $content .= "<div id='form-errors-msg' class='msg'>";

    if( isset($block['problem-list']) && $block['problem-list'] != '' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['problem-list']);   
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['content']) && $rc['content'] != '' ) {
            $content .= $rc['content'];
        }
    }

    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div>";

    //
    // Start the form block
    //
    $content .= "<div class='block-form"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . (isset($block['form-sections']) ? ' sectioned' : '')
        . (isset($block['section-selector']) && $block['section-selector'] == 'yes' ? ' section-selector' : '')
        . (isset($block['fields']) ? ' simple' : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    if( isset($block['sequence']) && $block['sequence'] == 1 && isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h1>" . $block['title'] . "</h1>";
    } elseif( isset($block['title']) && $block['title'] != '' ) {
        $content .= "<h2>" . $block['title'] . "</h2>";
    }

    //
    // Check if guidelines specified
    //
    if( isset($block['guidelines']) && $block['guidelines'] != '' ) {
        $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $block['guidelines']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['content']) && $rc['content'] != '' ) {
            $content .= "<div class='guidelines'>{$rc['content']}</div>";
        }
    }


    if( isset($block['form-sections']) ) {
        
        //
        // Setup the next/prev for sections
        //
        $prev_sid = -1;
        foreach($block['form-sections'] as $sid => $section) {
            if( $prev_sid >= 0 ) {
                $block['form-sections'][$sid]['prev_sid'] = $prev_sid;
                $block['form-sections'][$prev_sid]['next_sid'] = $sid;
            }
            $prev_sid = $sid;
        }

        //
        // Process the sections
        //
        $section_list = '';
        $sections = '';
        $js_calcs = '';
        $cur_section_id = '';
        if( isset($block['cur-section-id']) && $block['cur-section-id'] != '' ) {
            $cur_section_id = $block['cur-section-id'];
        }
        $sections .= "<form action='' method='POST'>";
        if( isset($block['checkout']) && $block['checkout'] == 'yes' ) {
            $sections .= "<input type='hidden' name='checkout' value='Checkout' />";
            $sections .= "<input type='hidden' name='regreviewed' value='yes' />";
        }
        foreach($block['form-sections'] as $sid => $section) {
            if( isset($section['fields']) && count($section['fields']) > 0 ) {
                if( $cur_section_id == '' ) {
                    $cur_section_id = $section['id'];
                }
                if( $cur_section_id == $section['id'] ) {
                    $cur_section_label = $section['label'];
                }
                $sections .= "<div id='s-{$section['id']}' class='form-section"
                    . (isset($section['class']) ? ' ' . $section['class'] : '')
                    // Add class for when selected if open by default
                    . ($cur_section_id == $section['id'] ? ' selected' : '')
                    . "'>"; 
                if( isset($section['label']) && $section['label'] != '' ) {
                    $sections .= "<h2>" . $section['label'] . "</h2>";
                }
                if( isset($section['sublabel']) && $section['sublabel'] != '' ) {
                    $sections .= "<h3>" . $section['sublabel'] . "</h3>";
                }
                if( isset($section['description']) && $section['description'] != '' ) {
                    $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $section['description']);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    if( isset($rc['content']) && $rc['content'] != '' ) {
                        $sections .= "<div class='form-section-description'>{$rc['content']}</div>";
                    }
                }

                //
                // Build next-prev links to determine if checkbox is in a list or not, etc
                //
                $prev = -1;
                foreach($section['fields'] as $fid => $field) {
                    if( $prev > -1 ) {
                        $section['fields'][$prev]['next_fid'] = $fid;
                        $section['fields'][$fid]['prev_fid'] = $prev;
                    }
                    $prev = $fid;
                }
                
                //
                // Check if this section is repeatable
                //
                $repeats = 1;
                $cur_repeat_num = 1;
                if( isset($section['flags']) && ($section['flags']&0x01) == 0x01 ) {
                    $repeats = $section['max_repeats'];
                }
                if( $repeats > 1 ) {
                    $sections .= "<div class='tabs repeat-tabs'>";
                    for($i = 1; $i <= $repeats; $i++ ) {
                        $sections .= "<a id='t-{$section['id']}-{$i}' onclick='C.form.sR(\"{$section['id']}\",{$i});' class='tab" 
                            . ($i == $cur_repeat_num ? ' selected' : '')
                            . ($i <= $section['min_repeats'] ? ' required' : '')
                            . "'>{$i}</a>";
                    }
                    $sections .= "</div>";
                }

                for($i = 1; $i <= $repeats; $i++ ) {
                    if( $repeats > 1 ) {
                        $sections .= "<div id='s-{$section['id']}-{$i}' class='repeated-fields"
                            . ($repeats > 1 ? ' repeatable' : '')
                            . ($repeats > 1 && $cur_repeat_num == $i ? ' selected' : '')
                            . ($repeats > 1 && $i <= $section['min_repeats'] ? ' required' : '')
                            . "'>";
                        if( isset($section['repeat-prefix']) && $section['repeat-prefix'] != '' ) {
                            $sections .= "<h2>{$section['repeat-prefix']} {$i}</h2>";
                        }
                    }
/*                    if( $repeats > 1 ) {
                        $sections .= "<div id='d-{$section['id']}-{$i}' class='fields'>";
                    } else {
                        $sections .= "<div id='d-{$section['id']}' class='fields'>";
                    } */
                    $sections .= "<div class='fields'>";
                    foreach($section['fields'] AS $field) {
                        if( $repeats > 1 ) {
                            $field['id'] .= "-{$i}";
                            if( isset($field['values'][$i]) ) {
                                $field['value'] = $field['values'][$i];
                            } else {
                                $field['value'] = '';
                            }
                        }
                        if( $field['ftype'] == 'newline' ) {
                            $sections .= "<div class='newline'></div>";
                            continue;
                        }
                        if( $field['ftype'] == 'break' ) {
                            // Close previous section
                            $sections .= "</div>";
                            if( isset($field['label']) && $field['label'] != '' ) {
                                $sections .= "<h2>" . $field['label'] . "</h2>";
                            }
                            if( isset($field['description']) && $field['description'] != '' ) {
                                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
                                $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $field['description']);
                                if( $rc['stat'] != 'ok' ) {
                                    return $rc;
                                }
                                if( isset($rc['content']) && $rc['content'] != '' ) {
                                    $sections .= "<div class='form-section-description'>{$rc['content']}</div>";
                                }
                            }
                            $sections .= "<div class='fields'>";

                            continue;
                        }
                        $size = 'large';
                        if( isset($field['size']) && $field['size'] != '' ) {
                            $size = $field['size'];
                        }
                        $req = '';
                        if( isset($field['required']) && $field['required'] == 'yes' ) {
                            $req = ' required';
                            // Still mark required even when section repeat not required.
//                            if( $repeats > 1 && $i > $section['min_repeats'] ) {
//                                $req = '';
//                            }
                        }
                        $class = $req;
                        $editable = 'yes';
                        if( isset($field['editable']) && $field['editable'] == 'no' ) {
                            $editable = 'no';
                        }
                        $field_description = '';
                        if( isset($field['description']) && $field['description'] != '' ) {
                            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $field['description']);
                            if( isset($rc['content']) && $rc['content'] != '' ) {
                                $field_description = "<div class='field-description'>"
                                    . $rc['content']
                                    . "</div>";
                                $class .= ' description-included';
                            }
                        }
                        //
                        // Check if form has formulas
                        //
                        if( isset($block['formulas']) && $block['formulas'] == 'yes' ) {
                            if( !isset($field['onkeyup']) ) {
                                $field['onkeyup'] = '';
                            }
                            $field['onkeyup'] = "C.form.calc();" . $field['onkeyup'];
                        }
                       
                        if( $field['ftype'] == 'checkbox' 
                            && isset($field['prev_fid']) 
                            && ($section['fields'][$field['prev_fid']]['ftype'] == 'content'
                                ||  $section['fields'][$field['prev_fid']]['ftype'] == 'checkbox'
                                )
                            ) {
                            $class .= ' checkbox-list';
                        }
                        elseif( $field['ftype'] == 'checkbox' 
                            && isset($field['next_fid']) 
                            && ($section['fields'][$field['next_fid']]['ftype'] == 'content'
                                ||  $section['fields'][$field['next_fid']]['ftype'] == 'checkbox'
                                )
                            ) {
                            $class .= ' checkbox-list';
                        }
                        elseif( $field['ftype'] == 'content' 
                            && isset($field['next_fid']) 
                            && $section['fields'][$field['next_fid']]['ftype'] == 'checkbox'
                            ) {
                            $class .= ' checkbox-list checkbox-label';
                        }
                        $sections .= "<div class='field field-{$field['ftype']}{$class}"
                            . (isset($field['size']) && $field['size'] != '' ? ' size-' . $field['size'] : '')
                            . (isset($field['class']) && $field['class'] != '' ? ' ' . $field['class'] : '')
                            . "'"
                            . (isset($field['flex-basis']) ? " style='flex-basis: {$field['flex-basis']}'" : '')
                            . ">";

                        if( $field['ftype'] == 'hidden' ) {
                            $sections .= "<input type='hidden' id='f-{$field['id']}' name='f-{$field['id']}' value='{$field['value']}'/>";
                        }
                        elseif( $field['ftype'] == 'text' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . (isset($field['max-characters']) && $field['max-characters'] > 0 ? " maxlength='" . $field['max-characters'] . "'" : '')
                                . (isset($field['onkeyup']) ? " onkeyup='{$field['onkeyup']}'" : '')
                                . (isset($field['onchange']) ? " onchange='{$field['onchange']}'" : '')
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                        } 
                        elseif( $field['ftype'] == 'email' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='text' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . (isset($field['max-characters']) && $field['max-characters'] > 0 ? " maxlength='" . $field['max-characters'] . "'" : '')
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                        } 
                        elseif( $field['ftype'] == 'url' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='text' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . (isset($field['max-characters']) && $field['max-characters'] > 0 ? " maxlength='" . $field['max-characters'] . "'" : '')
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                        } 
                        elseif( $field['ftype'] == 'number' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . (isset($field['onkeyup']) ? " onkeyup='{$field['onkeyup']}'" : '')
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                        }
                        elseif( $field['ftype'] == 'price' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='text' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . " maxlength='50'"
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                        }
                        elseif( $field['ftype'] == 'phone' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='tel' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . " maxlength='25'"
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                        }
                        elseif( $field['ftype'] == 'date' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='date' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                        }
                        elseif( $field['ftype'] == 'address' ) {
                            $sections .= "<div class='size-medium'>";
                            $sections .= "<label for='f-{$field['id']}-address1' class='{$req}'>Address Line 1</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}-address1'"
                                . " id='f-{$field['id']}-address1'"
                                . ' value="' . (isset($field['value']['address1']) ? htmlspecialchars($field['value']['address1']) : '') . '"'
                                . " maxlength='100'"
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                            $sections .= "</div>";
                            $sections .= "<div class='size-medium address-line-2'>";
                            $sections .= "<label for='f-{$field['id']}-address2'>Address Line 2</label>";
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}-address2'"
                                . " id='f-{$field['id']}-address2'"
                                . ' value="' . (isset($field['value']['address2']) ? htmlspecialchars($field['value']['address2']) : '') . '"'
                                . " maxlength='100'"
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                            $sections .= "</div>";
                            $sections .= "<div class='size-small-medium'>";
                            $sections .= "<label for='f-{$field['id']}-city' class='{$req}'>City</label>";
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}-city' "
                                . " id='f-{$field['id']}-city'"
                                . ' value="' . (isset($field['value']['city']) ? htmlspecialchars($field['value']['city']) : '') . '"'
                                . " maxlength='100'"
                                . ($editable == 'no' ? " readonly" : '')
                                . ">"; 
                            $sections .= "</div>";
                            $sections .= "<div class='size-small'>";
                            $sections .= "<label for='f-{$field['id']}-province' class='{$req}'>Province/State</label>";
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}-province'"
                                . " id='f-{$field['id']}-province'"
                                . ' value="' . (isset($field['value']['province']) ? htmlspecialchars($field['value']['province']) : '') . '"'
                                . " maxlength='100'"
                                . ($editable == 'no' ? " readonly" : '')
                                . ">"; 
                            $sections .= "</div>";
                            $sections .= "<div class='size-small'>";
                            $sections .= "<label for='f-{$field['id']}-postal' class='{$req}'>Postal/Zip Code</label>";
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}-postal'"
                                . " id='f-{$field['id']}-postal'"
                                . ' value="' . (isset($field['value']['postal']) ? htmlspecialchars($field['value']['postal']) : '') . '"'
                                . " maxlength='10'"
                                . ($editable == 'no' ? " readonly" : '')
                                . ">"; 
                            $sections .= "</div>";
                        }
                        elseif( $field['ftype'] == 'textarea' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $maxwords = 0;
                            if( isset($field['max-words']) && $field['max-words'] > 0 ) {
                                $maxwords = $field['max-words'];
                            }
                            $sections .= "<textarea name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ($maxwords > 0 ? " onkeyup='return C.form.wC(event,\"{$field['id']}\",{$maxwords});'" : '')
                                . ($editable == 'no' ? " readonly" : '')
                                . (isset($field['onkeyup']) ? " onkeyup='{$field['onkeyup']}'" : '')
                                . (isset($field['onchange']) ? " onchange='{$field['onchange']}'" : '')
                                . ">"
                                . (isset($field['value']) ? htmlspecialchars($field['value']) : '')
                                . "</textarea>";
                            if( $maxwords > 0 ) {
                                $sections .= "<div id='wc-{$field['id']}' class='word-count'>"
                                    . (isset($field['value']) ? str_word_count($field['value']) : 0) . " words"
                                    . "</div>";
                            }
                        }
                        elseif( $field['ftype'] == 'select' ) {
                            if( isset($field['label']) && $field['label'] != '' ) {
                                $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            }
                            $sections .= $field_description;
                            $sections .= "<select name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ($editable == 'no' ? " readonly" : '')
                                . (isset($field['onchange']) ? " onchange='{$field['onchange']}'" : '')
                                . ">";
                            if( !isset($field['blank']) || $field['blank'] == 'yes' ) {
                                $sections .= "<option value=''></option>";
                            }
                            if( isset($field['options']) && is_array($field['options']) ) {
                                foreach($field['options'] as $id => $option) {
                                    if( isset($field['option-id-field']) && isset($option[$field['option-id-field']]) ) {
                                        $sections .= "<option value='{$option[$field['option-id-field']]}'"
                                            . (isset($field['value']) && $field['value'] == $option[$field['option-id-field']] ? ' selected' : '');
                                    } elseif( isset($option['id']) ) {
                                        $sections .= "<option value='{$option['id']}'"
                                            . (isset($field['value']) && $field['value'] == $option['id'] ? ' selected' : '');
                                    } else {
                                        $sections .= "<option value='{$id}'"
                                            . (isset($field['value']) && $field['value'] == $id ? ' selected' : '');
                                    }
                                    $sections .= (isset($option['class']) && $option['class'] != '' ? " class='{$option['class']}' " : '');
                                    $sections .= ">";
                                    if( isset($field['option-value-field']) && isset($option[$field['option-id-field']]) ) {
                                        $sections .= $option[$field['option-value-field']];
                                    } elseif( isset($option['name']) ) {
                                        $sections .= $option['name'];
                                    } else {
                                        $sections .= $option;
                                    }
                                    $sections .= "</option>";
                                }
                            } else {
                                for($j = 0;$j < 20;$j++) {
                                    if( isset($field["option-{$j}"]) && $field["option-{$j}"] != '' ) {
                                        $value = $field["option-{$j}"];
                                        $sections .= "<option value='{$value}'"
                                            . (isset($field['value']) && $field['value'] == $value ? ' selected' : '')
                                            . ">{$value}</option>";
                                    }
                                }
                            }
                            $sections .= "</select>";
                        }
                        elseif( $field['ftype'] == 'radio' ) {
                            $sections .= "<div class='label {$req}'>{$field['label']}</div>";
                            $sections .= $field_description;
                            for($j = 0;$j < 20;$j++) {
                                if( isset($field["option-{$j}"]) && $field["option-{$j}"] != '' ) {
                                    $value = $field["option-{$j}"];
                                    $sections .= "<div class='option-wrap'>";
                                    $sections .= "<input type='radio' name='f-{$field['id']}' id='f-{$field['id']}-{$j}' value=\"" 
                                        . preg_replace('/"/', '\"', $value) . "\""
                                        . (isset($field['value']) && $field['value'] == $value ? ' checked' : '')
                                        . ($editable == 'no' ? " readonly" : '')
                                        . ">"
                                        . "<label for='f-{$field['id']}-{$j}'>{$value}</label>"
                                        . "";
                                    $sections .= "</div>";
                                }
                            }
                        }
                        elseif( $field['ftype'] == 'checkbox' ) {
                            $sections .= "<input type='checkbox' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . (isset($field['value']) && $field['value'] == 'on' ? ' checked' : '')
                                . ($editable == 'no' ? " readonly" : '')
                                . ">";
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>{$field['label']}</label>";
                            $sections .= "</input>";
                            $sections .= $field_description;
                        }
                        elseif( $field['ftype'] == 'content' ) {
                            if( isset($field['label']) && $field['label'] != '' ) {
                                $sections .= "<div class='label' id='f-{$field['id']}'>{$field['label']}</div>";
                            }
                            $sections .= $field_description;
                        }
                        elseif( $field['ftype'] == 'videocontent' ) {
                            if( isset($field['label']) && $field['label'] != '' ) {
                                $sections .= "<div class='label' id='f-{$field['id']}'>{$field['label']}</div>";
                            }
                            ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'videoProcess');
                            $rc = ciniki_wng_videoProcess($ciniki, $tnid, $request, array('url'=>$field['url']));
                            if( isset($rc['content']) && $rc['content'] != '' ) {
                                $sections .= "<div class='field-video'>"
                                    . $rc['content']
                                    . "</div>";
                            }
                        }
                        elseif( $field['ftype'] == 'image' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<div id='l-{$field['id']}' class='loading hidden'>Uploading Image...</div>";
                            $sections .= "<div id='p-{$field['id']}' class='img-preview'><img src='{$block['api-image-url']}/"
                                . (isset($field['value']) ? $field['value'] : '')
                                . "'/></div>";
                            $sections .= "<div class='hidden'>"
                                . "<input type='file' id='f-{$field['id']}' accept='image/jpeg,image/png' onchange='C.form.iU(event,\"{$section['id']}\",\"{$field['id']}\");'/>"
                                . "</div>";
                            if( $editable == 'yes' ) {
                                $sections .= "<div class='form-buttons'>";
                                $sections .= "<a class='button' onclick='C.gE(\"f-{$field['id']}\").click();'>Upload Image</a>";
                                $sections .= "<a class='button' onclick='C.form.iC(\"{$field['id']}\");'>Clear Image</a>";
                                $sections .= "</div>";
                            }
                        }
                        elseif( $field['ftype'] == 'document' ) {
                            // FIXME: Add document support
                        }
                        elseif( $field['ftype'] == 'formula' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<input type='{$field['ftype']}' name='f-{$field['id']}' id='f-{$field['id']}'"
                                . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                                . ' readonly'
                                . ">";
                            if( isset($field['formula']) ) {
                                ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'formFormulaParse');
                                $rc = ciniki_wng_formFormulaParse($ciniki, $tnid, array(
                                    'sections' => $block['form-sections'],
                                    'formula' => $field['formula'],
                                    ));
                                if( isset($rc['js_formula']) ) {
                                    $js_calcs .= "C.gE('f-{$field['id']}').value=" . $rc['js_formula'] . ";";
                                }
                            }
                        }
                        elseif( $field['ftype'] == 'button' ) {
                            $sections .= "<label for='f-{$field['id']}' class='{$req}'>" . $field['label'] . "</label>";
                            $sections .= $field_description;
                            $sections .= "<a class='button' id='f-{$field['id']}' "
                                . (isset($field['target']) ? "target='{$field['target']}' " : '')
                                . "href='{$field['href']}'>" 
                                . $field['value'] 
                                . "</a>";
                        } 
                        elseif( $field['ftype'] == 'termsofuse' ) {
                            $sections .= "<label for='termsofuse' class='required hidden'>"
                                . (isset($field['prefix']) ? $field['prefix'] . ' ' : '') 
                                . $field['label']
                                . "</label>";
                            $sections .= "<input type='checkbox' name='termsofuse' id='termsofuse'"
                                . ($field['value'] == 'on' ? ' checked' : '')
                                . " onchange='C.form.qSave();'" 
                                . ">";
                            $sections .= "<span class='termsofuse required'>" 
                                . (isset($field['prefix']) ? $field['prefix'] . ' ' : '')
                                . "<a onclick='C.form.sTOU();'>" . $field['label'] . "</a>"
                                . "</span>";
                            $sections .= $field_description;
                            $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $field['tou']);
                            if( $rc['stat'] != 'ok' ) {
                                return $rc;
                            }
                            if( isset($rc['content']) && $rc['content'] != '' ) {
                                $sections .= "<div id='tou-message' class='tou hidden'>"
                                    . "<div class='tou-wrap'>"
                                    . "<h3>Terms of Use</h3>"
                                    . $rc['content']
                                    . "</div>"
                                    . "</div>";
                            }
                        }
                        elseif( $field['ftype'] == 'payment' && isset($field['amount']) ) {
                            $sections .= "<div class='form-payment'>";
                            if( isset($field['label']) && $field['label'] != '' ) {
                                $sections .= "<span class='fee-label'>" . $field['label'] . "</span>";
                            }
                            $sections .= "<span class='fee-amount'>$" . number_format($field['amount'], 2) . "</span>";
                            if( isset($field['paid']) && $field['paid'] == 'yes' ) {
                                $sections .= "<span class='invoice-paid'>Paid</span>";
                            } elseif( isset($field['paid']) && $field['paid'] == 'unpaidcart' ) {
                                $sections .= "<a class='fee-button button' href='"
                                    . (isset($field['cart-url']) ? $field['cart-url'] : '')
                                    . "'>"
                                    . (isset($field['button-label']) && $field['button-label'] != '' ? $field['button-label'] : 'Pay Now')
                                    . "</a>";
                            } else {
                                $sections .= "<a class='fee-button button' onclick='C.form.cartSubmit(\""
                                    . (isset($field['cart-url']) ? $field['cart-url'] : '')
                                    . "\");'>"
                                    . (isset($field['button-label']) && $field['button-label'] != '' ? $field['button-label'] : 'Pay Now')
                                    . "</a>";
                            }
                            $sections .= "</div>";
                        }
                        elseif( $field['ftype'] == 'submit' ) {
                            $sections .= "<input type='hidden' name='action' value='submit'>";
                            $sections .= "<input type='submit' name='submit' class='button' value='"
                                . (isset($field['label']) && $field['label'] != '' ? $field['label'] : '')
                                . "' >";
//                            $sections .= "<a class='button' onclick='C.form.validate();'>Validate</a>";
                        }
                        elseif( $field['ftype'] == 'cancel' ) {
                            if( isset($field['url']) && $field['url'] != '' ) {
                                $sections .= "<input type='hidden' name='cancel-url' value='{$field['url']}'>";
                                $sections .= "<a class='button' href='{$field['url']}'>"
                                    . (isset($field['label']) && $field['label'] != '' ? $field['label'] : 'Cancel')
                                    . "</a>";
                            } else {
                                $sections .= "<input type='submit' name='submit' class='button' value='"
                                    . (isset($field['label']) && $field['label'] != '' ? $field['label'] : '')
                                    . "' >";
                            }
                        }
                        
                        $sections .= "</div>";
                    }
                    $sections .= "</div>";
                    if( $repeats > 1 ) {
                        if( isset($section['repeat-prefix']) && $section['repeat-prefix'] != '' ) {
                            $sections .= "<div class='form-buttons repeat-buttons'>";
                            if( isset($section['prev_sid']) ) {
                                $sections .= "<a onclick='C.form.sR(\"{$section['id']}\"," . ($i-1) . ");' "
                                    . "class='button prev'>"
                                    . "Previous {$section['repeat-prefix']}"
                                    . "</a>";
                            }
                            if( $i < $section['max_repeats'] ) {
                                $sections .= "<a onclick='C.form.sR(\"{$section['id']}\"," . ($i+1) . ");' "
                                    . "class='button prev'>"
                                    . "Next {$section['repeat-prefix']}"
                                    . "</a>";
                            }
                            $sections .= "</div>";
                        }
                        $sections .= "</div>";
                    }
                } // End of the repeats loop

                //
                // Add the prev/next buttons
                //
                $sections .= "<div class='form-buttons form-section-buttons'>";
                if( isset($block['section-selector']) && $block['section-selector'] == 'yes' && isset($section['prev_sid']) ) {
                    $sections .= "<a onclick='C.form.sS(\"{$block['form-sections'][$section['prev_sid']]['id']}\");' class='button prev'>Previous</a>";
                }
                if( isset($block['section-selector']) && $block['section-selector'] == 'yes' && isset($section['next_sid']) ) {
                    $sections .= "<a onclick='C.form.sS(\"{$block['form-sections'][$section['next_sid']]['id']}\");' class='button next'>Next</a>";
                }
                $sections .= "</div>";

                // 
                // Close the .section div
                //
                $sections .= "</div>";
                
                //
                // Add the section to the list of selectable sections
                //
                $section_list .= "<div id='b-{$section['id']}' class='form-section"
                    . ($cur_section_id == $section['id'] ? ' selected' : '')
                    . ($repeats > 1 ? ' repeatable' : '')
                    . "'>"
                    . "<a onclick='C.form.sS(\"{$section['id']}\");' class='button'>{$section['label']}</a>"
                    . "</div>";
                
            }
        }
        $sections .= "</form>";

        $content .= "<div class='form'>";
        $content .= "<div class='current-section'>{$cur_section_label}</div>";
        if( isset($block['section-selector']) && $block['section-selector'] == 'yes' ) {
            $content .= "<div class='form-sections-list'>" 
                . "<h2>Sections</h2>"
                . "<div class='list'>" . $section_list . "</div>"
                . "</div>";
        }
        $content .= "<div class='form-sections-fields'>" . $sections . "</div>";
        $content .= '</div>';

        if( (isset($block['api-save-url']) 
            || isset($block['api-image-url']) 
            || isset($block['api-cartsubmit-url']) 
            || isset($block['api-formcheck-url']) 
            )
            && isset($block['api-args']) && is_array($block['api-args']) 
            ) {
            $js = "window.addEventListener('load', (e)=>{C.form.start(e,'{$cur_section_id}',"
                . "'" . (isset($block['api-save-url']) ? $block['api-save-url'] : '') . "',"
                . "'" . (isset($block['api-image-url']) ? $block['api-image-url'] : '') . "',"
                . "'" . (isset($block['api-formcheck-url']) ? $block['api-formcheck-url'] : '') . "',"
                . "'" . (isset($block['api-cartsubmit-url']) ? $block['api-cartsubmit-url'] : '') . "',"
                . json_encode($js_calcs)
                . ","
                . json_encode($block['api-args'])
                . ")});";
        } elseif( isset($block['section-selector']) && $block['section-selector'] == 'yes' ) {
            $js = "window.addEventListener('load', (e)=>{C.form.start(e, '{$cur_section_id}','','','',''," . json_encode($js_calcs) . ",'')});";
        }
    }

    //
    // Simple form with no sections
    // Used in jury voting
    //
    elseif( isset($block['fields']) ) {
       
        $js_calcs = '';
        //
        // Build next-prev links to determine if checkbox is in a list or not, etc
        //
        $prev = -1;
        foreach($block['fields'] as $fid => $field) {
            if( $prev > -1 ) {
                $block['fields'][$prev]['next_fid'] = $fid;
                $block['fields'][$fid]['prev_fid'] = $prev;
            }
            $prev = $fid;
        }
        
        //
        // Process the fields
        //
        $fields_html = '<div class="fields">';
        foreach($block['fields'] as $field) {
            if( $field['ftype'] == 'newline' ) {
                // Start next field on a new line
                $fields_html .= "<div class='newline'></div>";
                continue;
            }
            if( $field['ftype'] == 'break' ) {
                // Close previous section
                if( isset($field['prev_fid']) ) {
                    $fields_html .= "</div>";
                } else {
                    $fields_html = '';
                }
                if( isset($field['label']) && $field['label'] != '' ) {
                    $fields_html .= "<h2>" . $field['label'] . "</h2>";
                }
                if( isset($field['description']) && $field['description'] != '' ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'contentProcess');
                    $rc = ciniki_wng_contentProcess($ciniki, $tnid, $request, $field['description']);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    if( isset($rc['content']) && $rc['content'] != '' ) {
                        $fields_html .= "<div class='form-section-description'>{$rc['content']}</div>";
                    }
                }
//                if( isset($field['prev_fid']) ) {
                    $fields_html .= "<div "
                        . (isset($field['id']) && $field['id'] != '' ? " id='{$field['id']}'" : '')
                        . "class='fields"
                        . (isset($field['class']) && $field['class'] != '' ? " {$field['class']}" : '')
                        . "'>";
//                }

                continue;
            }
            if( $field['ftype'] == 'line' ) {
                // Draw a line
                $fields_html .= "<div"
                    . (isset($field['id']) && $field['id'] != '' ? " id='f-{$field['id']}'" : '')
                    . " class='line"
                    . (isset($field['class']) && $field['class'] != '' ? ' ' . $field['class'] : '')
                    . "'></div>";
                continue;
            }
            $req = '';
            if( isset($field['required']) && $field['required'] == 'yes' ? ' required' : '' ) {
                $req = ' required';
            }
            $field_description = '';
            if( isset($field['description']) && $field['description'] != '' ) {
                $field_description = "<div class='field-description'>"
                    . $field['description']
                    . "</div>";
                $class .= ' description-included';
                if( isset($field['class']) && $field['class'] != '' ) {
                    $field['class'] .= ' description-included';
                } else {
                    $field['class'] = ' description-included';
                }
            }
           
            $editable = 'yes';
            if( isset($field['editable']) && $field['editable'] == 'no' ) {
                $editable = 'no';
            }

            //
            // Check if form has formulas
            //
            if( isset($block['formulas']) && $block['formulas'] == 'yes' ) {
                if( !isset($field['onkeyup']) ) {
                    $field['onkeyup'] = '';
                }
                $field['onkeyup'] = "C.form.calc();" . $field['onkeyup'];
            }

            $class = $req;
            if( $field['ftype'] == 'checkbox' 
                && isset($field['prev_fid']) 
                && ($block['fields'][$field['prev_fid']]['ftype'] == 'content'
                    ||  $block['fields'][$field['prev_fid']]['ftype'] == 'checkbox'
                    )
                ) {
                $class .= ' checkbox-list';
                if( $block['fields'][$field['prev_fid']]['ftype'] == 'content'
                    && isset($block['fields'][$field['prev_fid']]['required'])
                    && $block['fields'][$field['prev_fid']]['required'] == 'yes' 
                    ) {
                    $class .= ' checkbox-list-labeled';
                }
            }
            elseif( $field['ftype'] == 'checkbox' 
                && isset($field['next_fid']) 
                && ($block['fields'][$field['next_fid']]['ftype'] == 'content'
                    ||  $block['fields'][$field['next_fid']]['ftype'] == 'checkbox'
                    )
                ) {
                $class .= ' checkbox-list';
            }
            elseif( $field['ftype'] == 'content' 
                && isset($field['next_fid']) 
                && $block['fields'][$field['next_fid']]['ftype'] == 'checkbox'
                ) {
                $class .= ' checkbox-list checkbox-label';
            } 
            elseif( $field['ftype'] == 'hidden' ) {
                $fields_html .= "<input type='hidden' id='f-{$field['id']}' name='f-{$field['id']}' value='{$field['value']}'/>";
                continue;
            }
            $fields_html .= "<div class='field field-{$field['ftype']}{$class}"
                . (isset($field['size']) && $field['size'] != '' ? ' size-' . $field['size'] : '')
                . (isset($field['class']) && $field['class'] != '' ? ' ' . $field['class'] : '')
                . "'"
                . (isset($field['flex-basis']) ? " style='flex-basis: {$field['flex-basis']}'" : '')
                . ">";

            if( $field['ftype'] == 'viewtext' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<span type='{$field['ftype']}' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ">"
                    . (isset($field['value']) ? htmlspecialchars($field['value']) : '')
                    . "</span>";
            }
            elseif( $field['ftype'] == 'text' || $field['ftype'] == 'password' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . (isset($field['onkeyup']) ? " onkeyup='{$field['onkeyup']}'" : '')
                    . (isset($field['max-characters']) && $field['max-characters'] > 0 ? " maxlength='" . $field['max-characters'] . "'" : '')
                    . (isset($field['autocomplete']) ? " autocomplete='{$field['autocomplete']}'" : '')
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            } 
            elseif( $field['ftype'] == 'email' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='text' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . (isset($field['max-characters']) && $field['max-characters'] > 0 ? " maxlength='" . $field['max-characters'] . "'" : '')
                    . (isset($field['autocomplete']) ? " autocomplete='{$field['autocomplete']}'" : '')
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            }
            elseif( $field['ftype'] == 'url' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='text' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . (isset($field['max-characters']) && $field['max-characters'] > 0 ? " maxlength='" . $field['max-characters'] . "'" : '')
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            }
            elseif( $field['ftype'] == 'number' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . (isset($field['onkeyup']) ? " onkeyup='{$field['onkeyup']}'" : '')
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            }
            elseif( $field['ftype'] == 'price' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='text' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . " maxlength='50'"
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            }
            elseif( $field['ftype'] == 'phone' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='tel' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . " maxlength='25'"
                    . (isset($field['autocomplete']) ? " autocomplete='{$field['autocomplete']}'" : '')
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            }
            elseif( $field['ftype'] == 'date' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='date' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            }
            elseif( $field['ftype'] == 'url' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='url' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . " maxlength='500'"
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
            }
            elseif( $field['ftype'] == 'address' ) {
                $fields_html .= "<div class='size-medium'>";
                $fields_html .= "<label for='f-{$field['id']}-address1'>Address Line 1</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}-address1' id='f-{$field['id']}-address1'"
                    . ' value="' . (isset($field['value']['address1']) ? htmlspecialchars($field['value']['address1']) : '') . '"'
                    . " maxlength='100'"
                    . ($editable == 'no' ? '' : " autocomplete='address-line1'")
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
                $fields_html .= "</div>";
                $fields_html .= "<div class='size-medium address-line-2'>";
                $fields_html .= "<label for='f-{$field['id']}-address2'>Address Line 2</label>";
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}-address2' id='f-{$field['id']}-address2'"
                    . ' value="' . (isset($field['value']['address2']) ? htmlspecialchars($field['value']['address2']) : '') . '"'
                    . " maxlength='100'"
                    . ($editable == 'no' ? '' : " autocomplete='address-line2'")
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
                $fields_html .= "</div>";
                $fields_html .= "<div class='size-small-medium'>";
                $fields_html .= "<label for='f-{$field['id']}-city'>City</label>";
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}-city' id='f-{$field['id']}-city'"
                    . ' value="' . (isset($field['value']['city']) ? htmlspecialchars($field['value']['city']) : '') . '"'
                    . " maxlength='100'"
                    . ($editable == 'no' ? '' : " autocomplete='address-level1'")
                    . ($editable == 'no' ? " readonly" : '')
                    . ">"; 
                $fields_html .= "</div>";
                $fields_html .= "<div class='size-small'>";
                $fields_html .= "<label for='f-{$field['id']}-province'>Province/State</label>";
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}-province' id='f-{$field['id']}-province'"
                    . ' value="' . (isset($field['value']['province']) ? htmlspecialchars($field['value']['province']) : '') . '"'
                    . " maxlength='100'"
                    . ($editable == 'no' ? '' : " autocomplete='address-level2'")
                    . ($editable == 'no' ? " readonly" : '')
                    . ">"; 
                $fields_html .= "</div>";
                $fields_html .= "<div class='size-small'>";
                $fields_html .= "<label for='f-{$field['id']}-postal'>Postal/Zip Code</label>";
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}-postal' id='f-{$field['id']}-postal'"
                    . ' value="' . (isset($field['value']['postal']) ? htmlspecialchars($field['value']['postal']) : '') . '"'
                    . " maxlength='10'"
                    . ($editable == 'no' ? '' : " autocomplete='postal-code'")
                    . ($editable == 'no' ? " readonly" : '')
                    . ">"; 
                $fields_html .= "</div>";
            }
            elseif( $field['ftype'] == 'textarea' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $maxwords = 0;
                if( isset($field['max-words']) && $field['max-words'] > 0 ) {
                    $maxwords = $field['max-words'];
                }
                if( $editable == 'no' ) {
                    $fields_html .= "<div id='f-{$field['id']}' class='textarea-readonly'>"
                        . (isset($field['value']) ? $field['value'] : '')
                        . "</div>";
                } else {
                    $fields_html .= "<textarea id='f-{$field['id']}' name='f-{$field['id']}'"
                        . ($maxwords > 0 ? " onkeyup='return C.form.wC(event,\"{$field['id']}\",{$maxwords});'" : '')
                        . ($editable == 'no' ? " readonly" : '')
                        . ">"
                        . (isset($field['value']) ? htmlspecialchars($field['value']) : '')
                        . "</textarea>";
                }
                if( $maxwords > 0 ) {
                    $fields_html .= "<div id='wc-{$field['id']}' class='word-count'>"
                        . (isset($field['value']) ? str_word_count($field['value']) : 0) . " words"
                        . "</div>";
                }
            }
            elseif( $field['ftype'] == 'select' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<select name='f-{$field['id']}' id='f-{$field['id']}'"
                    . (isset($field['onchange']) ? " onchange='{$field['onchange']}'" : '')
                    . ">";
                if( !isset($field['blank']) || $field['blank'] == 'yes' ) {
                    if( isset($field['blank-label']) ) {
                        $fields_html .= "<option value=''>{$field['blank-label']}</option>";
                    } else {
                        $fields_html .= "<option value=''></option>";
                    }
                }
                if( isset($field['options']) && is_array($field['options']) ) {
                    foreach($field['options'] as $id => $option) {
                        if( isset($field['option-id-field']) && isset($option[$field['option-id-field']]) ) {
                            $fields_html .= "<option value='{$option[$field['option-id-field']]}'"
                                . (isset($field['value']) && $field['value'] == $option[$field['option-id-field']] ? ' selected' : '');
                        } elseif( isset($option['id']) ) {
                            $fields_html .= "<option value='{$option['id']}'"
                                . (isset($field['value']) && $field['value'] == $option['id'] ? ' selected' : '');
                        } else {
                            $fields_html .= "<option value='{$id}'"
                                . (isset($field['value']) && $field['value'] == $id ? ' selected' : '');
                        }
                        $fields_html .= (isset($option['class']) && $option['class'] != '' ? " class='{$option['class']}' " : '');
                        $fields_html .= ">";
                        if( isset($field['option-value-field']) && isset($option[$field['option-id-field']]) ) {
                            $fields_html .= $option[$field['option-value-field']];
                        } elseif( isset($option['name']) ) {
                            $fields_html .= $option['name'];
                        } else {
                            $fields_html .= $option;
                        }
                        $fields_html .= "</option>";
                    }
                } else {
                    for($i = 0;$i < 20;$i++) {
                        if( isset($field["option-{$i}"]) && $field["option-{$i}"] != '' ) {
                            $value = $field["option-{$i}"];
                            $fields_html .= "<option value='{$value}'"
                                . (isset($field['value']) && $field['value'] == $value ? ' selected' : '')
                                . ">{$value}</option>";
                        }
                    }
                }
                $fields_html .= "</select>";
            }
            elseif( $field['ftype'] == 'radio' ) {
                $fields_html .= "<div class='label'>{$field['label']}</div>";
                $fields_html .= $field_description;
                for($i = 0;$i < 20;$i++) {
                    if( isset($field["option-{$i}"]) && $field["option-{$i}"] != '' ) {
                        $value = $field["option-{$i}"];
                        $fields_html .= "<div class='option-wrap'>";
                        $fields_html .= "<input type='radio' name='f-{$field['id']}' id='f-{$field['id']}-{$i}' value='{$value}'"
                            . (isset($field['value']) && $field['value'] == $value ? ' checked' : '')
                            . ($editable == 'no' ? " readonly" : '')
                            . ">"
                            . "<label for='f-{$field['id']}-{$i}'>{$value}</label>"
                            . "";
                        $fields_html .= "</div>";
                    }
                }
            }
            elseif( $field['ftype'] == 'checkbox' ) {
                $fields_html .= "<input type='checkbox' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . (isset($field['value']) && $field['value'] == 'on' ? ' checked' : '')
                    . ($editable == 'no' ? " readonly" : '')
                    . ">";
                $fields_html .= "<label for='f-{$field['id']}'>{$field['label']}</label>";
                $fields_html .= "</input>";
                $fields_html .= $field_description;
            }
            elseif( $field['ftype'] == 'content' ) {
                if( isset($field['label']) && $field['label'] != '' ) {
                    $fields_html .= "<div class='label'>{$field['label']}</div>";
                }
                $fields_html .= $field_description;
            }
            elseif( $field['ftype'] == 'minsec' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<span class='nobreak'>";
                $fields_html .= "<select name='f-{$field['id']}-min' id='f-{$field['id']}-min'"
                    . (isset($field['onchange']) ? " onchange='{$field['onchange']}'" : '')
                    . ">";
                $max_minutes = isset($field['max-minutes']) ? $field['max-minutes'] : 60;
                $minutes = intval($field['value']/60);
                $seconds = $field['value'] % 60;
                for($i = 0; $i <= $max_minutes; $i++) {
                    $fields_html .= "<option value='{$i}' "
                        . ($minutes == $i ? ' selected' : '')
                        . ">$i</option>";
                }
                $fields_html .= "</select>";
                $fields_html .= " minute(s) ";
                $fields_html .= "</span>";
                if( !isset($field['seconds']) || $field['seconds'] != 'no' ) {
                    $fields_html .= "<span class='nobreak'>";
                    $fields_html .= "<select name='f-{$field['id']}-sec' id='f-{$field['id']}-sec'"
                        . (isset($field['onchange']) ? " onchange='{$field['onchange']}'" : '')
                        . ">";
                    $second_interval = isset($field['second-interval']) ? $field['second-interval'] : 5;
                    for($i = 0; $i < 60; $i += $second_interval) {
                        $fields_html .= "<option value='{$i}' "
                            . ($seconds == $i ? ' selected' : '')
                            . ">$i</option>";
                    }
                    $fields_html .= "</select>";
                    $fields_html .= " second(s) ";
                    $fields_html .= "</span>";
                }
            }
            elseif( $field['ftype'] == 'image' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<div id='l-{$field['id']}' class='loading hidden'>Uploading Image...</div>";
                $fields_html .= "<div id='p-{$field['id']}' class='img-preview'><img src='"
                    . (isset($field['src']) ? $field['src'] : '')
                    . "'/></div>";
                $fields_html .= "<div class='hidden'>"
                    . "<input type='file' id='f-{$field['id']}' name='f-{$field['id']}' accept='image/jpeg,image/png' onchange='C.form.iP(event,\"{$field['id']}\");'/>"
                    . "</div>";
                if( $editable == 'yes' ) {
                    $fields_html .= "<div class='form-buttons'>";
                    $fields_html .= "<a class='button' onclick='C.gE(\"f-{$field['id']}\").click();'>Upload Image</a>";
                    $fields_html .= "<a class='button' onclick='C.form.iPC(\"{$field['id']}\");'>Clear Image</a>";
                    $fields_html .= "</div>";
                }
            }
            elseif( $field['ftype'] == 'document' ) {
                // FIXME: Add document support
            }
            elseif( $field['ftype'] == 'formula' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='{$field['ftype']}' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
                    . ' readonly'
                    . ">";
                if( isset($field['formula']) ) {
                    ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'formFormulaParse');
                    $rc = ciniki_wng_formFormulaParse($ciniki, $tnid, array(
                        'fields' => $block['fields'],
                        'formula' => $field['formula'],
                        ));
                    if( isset($rc['js_formula']) ) {
                        $js_calcs .= "C.gE('f-{$field['id']}').value=" . $rc['js_formula'] . ";";
                    }
                }
            }
            elseif( $field['ftype'] == 'file' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<input type='text' name='f-{$field['id']}' id='f-{$field['id']}'"
                    . ' value="' . (isset($field['value']) ? htmlspecialchars($field['value']) : '') . '"'
//                    . (isset($field['onkeyup']) ? " onkeyup='{$field['onkeyup']}'" : '')
//                    . (isset($field['max-characters']) && $field['max-characters'] > 0 ? " maxlength='" . $field['max-characters'] . "'" : '')
                    . " readonly>";
                if( $editable == 'yes' ) {
                    $fields_html .= "<a class='button' onclick='C.form.fU(\"{$field['id']}\");'>Upload</a>";
                    $fields_html .= "<div class='hidden'>"
                        . "<input type='file' id='file-{$field['id']}' name='file-{$field['id']}'"
                        . (isset($field['accept']) ? " accept='{$field['accept']}'" : '')
                        . " onchange='C.form.fUN(\"{$field['id']}\");'"
                        . " />"
                        . "</div>";
                }
            }
            elseif( $field['ftype'] == 'button' ) {
                $fields_html .= "<label for='f-{$field['id']}'>" . $field['label'] . "</label>";
                $fields_html .= $field_description;
                $fields_html .= "<a class='button' id='f-{$field['id']}' "
                    . "href='{$field['href']}'>" 
                    . $field['value'] 
                    . "</a>";
            } 
            elseif( $field['ftype'] == 'payment' && isset($field['amount']) ) {
                $fields_html .= "<div class='form-payment'>";
                if( isset($field['label']) && $field['label'] != '' ) {
                    $fields_html .= "<span class='fee-label'>" . $field['label'] . "</span>";
                }
                $fields_html .= "<span class='fee-amount'>$" . number_format($field['amount'], 2) . "</span>";
                if( isset($field['paid']) && $field['paid'] == 'yes' ) {
                    $fields_html .= "<span class='invoice-paid'>Paid</span>";
                } elseif( isset($field['paid']) && $field['paid'] == 'unpaidcart' ) {
                    $fields_html .= "<a class='fee-button button' href='"
                        . (isset($field['cart-url']) ? $field['cart-url'] : '')
                        . "'>"
                        . (isset($field['button-label']) && $field['button-label'] != '' ? $field['button-label'] : 'Pay Now')
                        . "</a>";
                } else {
                    $fields_html .= "<a class='fee-button button' onclick='C.form.cartSubmit(\""
                        . (isset($field['cart-url']) ? $field['cart-url'] : '')
                        . "\");'>"
                        . (isset($field['button-label']) && $field['button-label'] != '' ? $field['button-label'] : 'Pay Now')
                        . "</a>";
                }
                $fields_html .= "</div>";
            }
            elseif( $field['ftype'] == 'submit' ) {
            }
            
            $fields_html .= "</div>";
        }

        //
        // Generate the form
        //
        $content .= "<div class='form'>";
        $content .= "<form"
            . (isset($block['form-id']) && $block['form-id'] != '' ? " id={$block['form-id']}" : '')
            . " enctype='multipart/form-data'"
            . " action='" . (isset($block['form-action']) ? $block['form-action'] : '') . "' method='POST'>";
        if( isset($block['checkout']) && $block['checkout'] == 'yes' ) {
            $content .= "<input type='hidden' name='checkout' value='Checkout' />";
        }
//        $content .= "<div class='fields'>" . $fields_html . "</div>";
        $content .= $fields_html . "</div>";
        if( (isset($block['cancel-label']) && $block['cancel-label'] != '')
            || !isset($block['submit-hide']) 
            || $block['submit-hide'] != 'yes'
            ) {
            $content .= "<div "
                . (isset($block['form-id']) && $block['form-id'] != '' ? " id='{$block['form-id']}_submit_buttons'" : '')
                . "class='submit-buttons"
                . (isset($block['submit-buttons-class']) && $block['submit-buttons-class'] != '' ? " {$block['submit-buttons-class']}" : '')
                . "'>";
            if( isset($block['form-id']) && $block['form-id'] != '' 
                && isset($block['js-submit']) && $block['js-submit'] == 'yes' 
                ) {
                if( isset($block['cancel-label']) && $block['cancel-label'] != '' ) {
                    if( isset($block['js-cancel']) && $block['js-cancel'] != '' ) {
                        $content .= "<a class='button' href='javascript:{$block['js-cancel']}'>{$block['cancel-label']}</a>";
                    } elseif( isset($block['cancel-url']) && $block['cancel-url'] != '' ) {
                        $content .= "<a class='button' href='{$block['cancel-url']}'>{$block['cancel-label']}</a>";
                    } else {
                        $content .= "<a class='button' href='javascript:submit();'>{$block['cancel-label']}</a>";
                    }
                }
                $content .= "<input type='submit' class='button' value='"
                    . (isset($block['submit-label']) && $block['submit-label'] != '' ? $block['submit-label'] : 'Submit')
                    . "' >";
            } else {
                if( isset($block['cancel-label']) && $block['cancel-label'] != '' ) {
                    if( isset($block['js-cancel']) && $block['js-cancel'] != '' ) {
                        $content .= "<a class='button' href='javascript:{$block['js-cancel']}'>{$block['cancel-label']}</a>";
                    } elseif( isset($block['cancel-url']) && $block['cancel-url'] != '' ) {
                        $content .= "<a class='button' href='{$block['cancel-url']}'>{$block['cancel-label']}</a>";
                    } else {
                        $content .= "<input type='submit' name='cancel' class='button' value='{$block['cancel-label']}' >";
                    }
                }
                if( isset($block['js-submit']) && $block['js-submit'] != '' ) {
                    $content .= "<a class='button' href='javascript:{$block['js-submit']}'>"
                        . (isset($block['submit-label']) && $block['submit-label'] != '' ? $block['submit-label'] : 'Submit')
                        . "</a>";
                } elseif( !isset($block['submit-hide']) || $block['submit-hide'] != 'yes' ) {
                    $content .= "<input type='submit' name='submit' class='button' value='"
                        . (isset($block['submit-label']) && $block['submit-label'] != '' ? $block['submit-label'] : 'Submit')
                        . "' >";
                }
            }
            $content .= '</div>';
        }
        if( isset($js_calcs) && $js_calcs != '' ) {
            $js = "window.addEventListener('load', (e)=>{C.form.start(e, '','','','',''," . json_encode($js_calcs) . ",'');C.form.calc();});";
        }

        $content .= "</form>";
        $content .= '</div>';
    }

    //
    // Setup last saved message area
    //
    if( isset($block['last-saved-msg']) ) {
        $content .= "<div class='last-saved'><div id='form-last-saved-msg'>";
        if( $block['last-saved-msg'] != '' ) {
            $content .= $block['last-saved-msg']; 
        }
        $content .= "</div></div>";
    }

    //
    // Close out the block
    //
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';


    //
    // Check if there is custom javascript to add
    //
    if( isset($block['js']) && $block['js'] != '' ) {
        $js .= $block['js'];
    }

    return array('stat'=>'ok', 'content'=>$content, 'js'=>$js);
}
?>
