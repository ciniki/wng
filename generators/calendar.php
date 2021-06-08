<?php
//
// Description
// -----------
// calendar
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
function ciniki_wng_generators_calendar(&$ciniki, $tnid, $request, $block) {

    $content = '';

    $content .= "<div class='block-calendar"
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
    $start_dt = new DateTime('now', new DateTimezone($intl_timezone));
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
            $start_dt->sub(new DateInterval('P' . (7 - $startdayofweek) . 'D'));
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
    $weeks = array();   // Keep track of each weeks events, array indexed by $row
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
            );
        //
        // Setup the weeks array to store each week (based on row) and then the events for that week
        //
        if( !isset($weeks[$row]) ) {
            $weeks[$row] = array(
                'events' => array(),
                );
        }

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
    // Process the events and figure out what week they are in
    //
    $max_slices = 0;
    if( isset($block['events']) ) {
        foreach($block['events'] as $eid => $event) {
            // Skip event if no dates set
            if( !isset($event['dates']) ) {
                continue;
            }
            $cur_row = 0;
            $output_event = null;
            foreach($event['dates'] as $date) {
                // Skip dates not in our days array
                if( !isset($days[$date['date']]) ) {
                    continue;
                }
                $row = $days[$date['date']]['row'];
                // New row/week, add 
                if( $row != $cur_row && $output_event != null ) {
                    $weeks[$cur_row]['events'][] = $output_event;
                    $output_event = null;
                }
                if( $output_event == null ) {
                    $output_event = array(
                        'name' => $event['name'],
                        'days' => array(),
                        );
                }
                $output_event['days'][] = $date['dayofweek'];
                $cur_row = $row;
            }
            if( $output_event != null ) {
                $weeks[$cur_row]['events'][] = $output_event;
            }
        }

        //
        // Output the events
        //
        foreach($weeks as $row => $week) {
            $slice = 0; 
            foreach($week['events'] as $eid => $event) {
                $cal_content .= "<div class='event "
                    . "row-{$row} start-col-1 end-col-7 slice-{$slice}"
                    . "'>";
                $cal_content .= "<div class='days'>";
                $dayofweek = isset($block['weekdaystart']) ? $block['weekdaystart'] : 0;
                for($i = 0; $i < 7; $i++ ) {
                    if( in_array($dayofweek, $event['days']) ) {
                        $cal_content .= "<div class='day-marker selected'>";
                        switch($dayofweek) {
                            case 0: $cal_content .= "<span class='sname'>S<span class='lname'>un</span></span>"; break;
                            case 1: $cal_content .= "<span class='sname'>M<span class='lname'>on</span></span>"; break;
                            case 2: $cal_content .= "<span class='sname'>T<span class='lname'>ue</span></span>"; break;
                            case 3: $cal_content .= "<span class='sname'>W<span class='lname'>ed</span></span>"; break;
                            case 4: $cal_content .= "<span class='sname'>T<span class='lname'>hu</span></span>"; break;
                            case 5: $cal_content .= "<span class='sname'>F<span class='lname'>ri</span></span>"; break;
                            case 6: $cal_content .= "<span class='sname'>S<span class='lname'>at</span></span>"; break;
                        }
                        $cal_content .= "</div>";
                    } else {
                        $cal_content .= "<div class='day-marker'></div>";
                    }
                    $dayofweek++;
                    if( $dayofweek > 6 ) {
                        $dayofweek = 0;
                    }
                }
                $cal_content .= "</div>";
                $cal_content .= "<div class='name'>" . $event['name'] . "</div>"
                    . "</div>";
                $slice++;
            }
            if( $slice >= $max_slices ) {
                $max_slices = $slice + 1;
            }
        }
    }

    $content .= "<div class='calendar-month'>"
        . $block['year'] . ' - ' . $block['month']
        . "<br/>"
//        . $start_dt->format('Y-m-d')
//        . " - "
//        . $end_dt->format('Y-m-d')
        . "</div>";

    $content .= "<div class='calendar slices-{$max_slices}'>";
    $content .= $cal_content;
    $content .= '</div>';


    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';



    return array('stat'=>'ok', 'content'=>$content);
}
?>
