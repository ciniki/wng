<?php
//
// Description
// -----------
// Generate the HTML for a block.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_wng_blocksGenerate(&$ciniki, $tnid, &$request, $blocks) {

    $content = '';
    foreach($blocks as $block) {
        //
        // Load the generator for the block
        //
        $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'generators', $block['type']);
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            //
            // Generate the HTML for the block
            //
            $rc = $fn($ciniki, $tnid, $request, $block);
            if( $rc['stat'] == 'ok' ) {
                if( isset($rc['content']) ) {
                    $content .= $rc['content'];
                }
                if( isset($rc['js']) ) {
                    $request['response']['js'] .= $rc['js'];
                }
            }
        }
    }

    return array('stat'=>'ok', 'content'=>$content);
}
?>
