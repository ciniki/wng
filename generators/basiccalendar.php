<?php
//
// Description
// -----------
// basiccalendar
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_basiccalendar(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $content .= "<div class='block-basiccalendar"
        . (isset($block['class']) && $block['class'] != '' ? ' ' . $block['class'] : '')
        . "'>";
    $content .= "<div class='wrap'>";
    $content .= "<div class='content'>";

    //
    // Load the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    $day_interval = new DateInterval('P1D');

    //
    // Set date to first of month
    //
    if( isset($block['start_dt']) && is_object($block['start_dt']) ) {
        $start_dt = $block['start_dt'];
    } else {
        $start_dt = new DateTime('now', new DateTimezone($intl_timezone));
    }
    $cal_year = isset($block['year']) ? $block['year'] : $start_dt->format('Y');
    $cal_month = isset($block['month']) ? $block['month'] : $start_dt->format('m');
    $start_dt->setDate($cal_year, $cal_month, 1);

    $end_dt = clone($start_dt);
    $end_dt->add(new DateInterval('P1M'));
    $end_dt->sub($day_interval);

    $cal_content = '';

    //
    // Output weekday names
    //
    $dayofweek = isset($block['weekdaystart']) ? $block['weekdaystart'] : 0;
    for($i = 0; $i < 7; $i++ ) {
        switch($dayofweek) {
            case 0: $cal_content .= "<span class='day-name'>Sun</span>"; break;
            case 1: $cal_content .= "<span class='day-name'>Mon</span>"; break;
            case 2: $cal_content .= "<span class='day-name'>Tue</span>"; break;
            case 3: $cal_content .= "<span class='day-name'>Wed</span>"; break;
            case 4: $cal_content .= "<span class='day-name'>Thu</span>"; break;
            case 5: $cal_content .= "<span class='day-name'>Fri</span>"; break;
            case 6: $cal_content .= "<span class='day-name'>Sat</span>"; break;
        }
        $dayofweek++;
        if( $dayofweek > 6 ) {
            $dayofweek = 0;
        }
    }

    //
    // Check if need to fill in days from previous month
    //
    $startdayofweek = isset($block['weekdaystart']) ? $block['weekdaystart'] : 0;
    if( $start_dt->format('w') != $startdayofweek ) {
        if( $startdayofweek < $start_dt->format('w') ) {
            $start_dt->sub(new DateInterval('P' . ($start_dt->format('w') - $startdayofweek) . 'D'));
        } else {
            $start_dt->sub(new DateInterval('P' . (1 + $start_dt->format('w')) . 'D'));
        }
    }
    $enddayofweek = $startdayofweek - 1;
    if( $enddayofweek < 0 ) {
        $enddayofweek = 6;
    }

    //
    // Check if need to fill in days from next month
    //
    if( $end_dt->format('w') != $enddayofweek ) {
        if( $enddayofweek > $end_dt->format('w') ) {
            $end_dt->add(new DateInterval('P' . ($enddayofweek - $end_dt->format('w')) . 'D'));
        } else {
            $end_dt->add(new DateInterval('P' . (7 - $end_dt->format('w')) . 'D'));
        }
    }

    //
    // Output the days
    //
    $days = array();
    $dt = clone($start_dt);
    $row = 2;
    $col = 1;
    while($dt <= $end_dt) {
        if( $dt->format('m') < $cal_month ) {
            $cal_content .= '<div class="day day-disabled day-prev">';
        } elseif( $dt->format('m') > $cal_month ) {
            $cal_content .= '<div class="day day-disabled day-next">';
        } else {
            $cal_content .= '<div class="day">';
        }
        $days[$dt->format('Y-m-d')] = array(
            'row' => $row,
            'col' => $col,
            'slices' => array(),
            );

        $cal_content .= $dt->format('j');

        $cal_content .= '</div>';

        $dt->add($day_interval);
        $col++;
        if( $col > 7 ) {
            $row++;
            $col = 1;
        }
    }

    //
    // Process the events passed to the calendar
    //
    $max_slices = 1;
    if( isset($block['events']) ) {
        $events = array();
        //
        // Go through each event and determine what slice it should show in
        // Each event is assigned a slice so no events overlap in the calendar display.
        //
        foreach($block['events'] as $eid => $event) {
            $slice = 0;
            //
            // Check if slice is available for the next X days of event
            //
            if( isset($event['start_dt']) ) {
                $event_sdt = new DateTime($event['start_dt'], new DateTimezone('UTC'));
            } else {
                $event_sdt = new DateTime($event['start'], new DateTimezone($intl_timezone));
            }
            if( isset($event['end_dt']) ) {
                $event_edt = new DateTime($event['end_dt'], new DateTimezone('UTC'));
            } else {
                $event_edt = new DateTime($event['end'], new DateTimezone($intl_timezone));
            }
            if( $event_sdt < $start_dt ) {
                $event_sdt = clone($start_dt);
            }
            $dt = clone($event_sdt);
            while($dt <= $end_dt && $dt <= $event_edt) {
                $ymd = $dt->format('Y-m-d');
                if( isset($days[$ymd]['slices'][$slice]) && $days[$ymd]['slices'][$slice] >= 0 ) {
                    // Slice taken, restart check in next slice
                    $slice++;
                    $dt = clone($event_sdt);
                } 
                else {
                    $dt->add($day_interval);
                }
            }
            // Found slice
            if( $slice >= $max_slices ) {
                $max_slices = $slice+1;
            }
            $dt = clone($event_sdt);
            $block['events'][$eid]['slice'] = $slice;
            $output_event = null;
            while($dt <= $event_edt && $dt <= $end_dt) {
                // Mark slice as taken by this event id
                $ymd = $dt->format('Y-m-d');
                $days[$ymd]['slices'][$slice] = $eid;
                // No output yet
                if( $output_event == null ) {
                    $output_event = array(
                        'name' => $event['name'],
                        'slice' => $slice,
                        'row' => $days[$ymd]['row'],
                        'col' => $days[$ymd]['col'],
                        'last_col' => $days[$ymd]['col'],
                        'span' => 1,
                        'class' => (isset($event['class']) ? $event['class'] : ''),
                        'url' => (isset($event['url']) ? $event['url'] : ''),
                        );
                } 
                // Output started and event continues following day
                elseif( $output_event['row'] == $days[$ymd]['row'] && $output_event['last_col'] == ($days[$ymd]['col'] - 1) ) {
                    $output_event['last_col'] = $days[$ymd]['col'];
                    $output_event['span']++;
                } 
                // New Row or skipped day and need to start new output
                else {
                    $events[] = $output_event;
                    $output_event = array(
                        'name' => $event['name'],
                        'slice' => $slice,
                        'row' => $days[$ymd]['row'],
                        'col' => $days[$ymd]['col'],
                        'last_col' => $days[$ymd]['col'],
                        'span' => 1,
                        'class' => (isset($event['class']) ? $event['class'] : ''),
                        'url' => (isset($event['url']) ? $event['url'] : ''),
                        );
                }
                $dt->add($day_interval);
            }
            if( $output_event != null ) {
                $events[] = $output_event;
            }
        }
        
        //
        // Output the events
        //
        foreach($events as $eid => $event) {
            if( isset($event['url']) && $event['url'] != '' ) {
                $cal_content .= "<a href='{$event['url']}' class='event "
                    . "row-{$event['row']} start-col-{$event['col']} end-col-{$event['last_col']} slice-{$event['slice']}"
                    . (isset($event['class']) && $event['class'] != '' ? " {$event['class']}" : '')
                    . "' >"
                . $event['name']
                . "</a>";
            } else {
                $cal_content .= "<div href='{$event['url']}' class='event "
                    . "row-{$event['row']} start-col-{$event['col']} end-col-{$event['last_col']} slice-{$event['slice']}"
                    . (isset($event['class']) && $event['class'] != '' ? " {$event['class']}" : '')
                    . "' >"
                . $event['name']
                . "</div>";
            }

        }
    }

//    if( isset($block['calendar-month']) && $block['calendar-month'] != '' ) {
//        $content .= "<div class='calendar-month'>" . $block['calendar-month'] . '</div>';
//    }

    $content .= "<div class='calendar slices-{$max_slices}'>";
    $content .= $cal_content;
    $content .= '</div>';


    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    return array('stat'=>'ok', 'content'=>$content);
}
?>
