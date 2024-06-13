<?php
//
// Description
// -----------
// Process the list of files to be displayed for download.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_processors_files(&$ciniki, $tnid, &$request, $section) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');

    $blocks = array();
    $s = isset($section['settings']) ? $section['settings'] : array();

error_log('process files');
    //
    // Get the list of files for this section
    //
    $file_ids = array();
    $names = array();
    for($i = 1; $i <= 100; $i++ ) {
        if( isset($s["file-id-{$i}"]) && $s["file-id-{$i}"] > 0 ) {
            $file_ids[] = $s["file-id-{$i}"];
            if( isset($s["name-{$i}"]) && $s["name-{$i}"] != '' ) {
                $names[$s["file-id-{$i}"]] = $s["name-{$i}"];
            }
        }
    }
    if( count($file_ids) > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');
        $strsql = "SELECT files.id, "
            . "files.uuid, "
            . "files.filename, "
            . "files.permalink, "
            . "files.extension, "
            . "files.content_type "
            . "FROM ciniki_files AS files "
            . "WHERE files.id IN (" . ciniki_core_dbQuoteIDs($ciniki, $file_ids) . ") "
            . "AND files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
        $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.wng', array(
            array('container'=>'files', 'fname'=>'id', 
                'fields'=>array('id', 'uuid', 'filename', 'permalink', 'extension', 'content_type')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.241', 'msg'=>'Unable to load files', 'err'=>$rc['err']));
        }
        $files = array();
        foreach($file_ids as $id) {
            if( isset($rc['files'][$id]) ) {
                $files[] = $rc['files'][$id];
            }
        }
    }
   
    if( isset($files) && count($files) > 0 
        && isset($request['cur_uri_pos']) 
        && isset($request['uri_split'][($request['cur_uri_pos']+2)])
        && $request['uri_split'][($request['cur_uri_pos']+1)] == 'download'
        && $request['uri_split'][($request['cur_uri_pos']+2)] != '' 
        ) {
        foreach($files as $file) {
            if( $request['uri_split'][($request['cur_uri_pos']+2)] == $file['permalink'] ) {

                //
                // Get the tenant storage directory
                //
                ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'hooks', 'storageDir');
                $rc = ciniki_tenants_hooks_storageDir($ciniki, $tnid, array());
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $tenant_storage_dir = $rc['storage_dir'];

                $storage_filename = $tenant_storage_dir . '/ciniki.files/' . $file['uuid'][0]
                    . '/' . $file['uuid'];
                if( !is_file($storage_filename) ) {
                    return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.25', 'msg'=>'Unable to find file'));
                }

                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                if( $file['content_type'] != '' ) {
                    header('Content-Type: ' . $file['content_type']);
                }
//                header('Content-Disposition: attachment;filename="' . $file['filename'] . '"');
                header('Content-Disposition: filename="' . $file['filename'] . '"');
                header('Content-Length: ' . filesize($storage_filename));
                header('Cache-Control: max-age=0');

                $fp = fopen($storage_filename, 'rb');
                fpassthru($fp);
                return array('stat'=>'exit');
            }
        }
        $blocks[] = array(
            'type' => 'msg', 
            'level' => 'error', 
            'content' => 'File not found',
            );
    } elseif( isset($files) && count($files) > 0 ) {
        foreach($files as $fid => $file) {
            $files[$fid]['url'] = ($request['page']['path'] != '/' ? $request['page']['path'] : '') . '/download/' . $file['permalink'];
            error_log($files[$fid]['url']);
            error_log($request['ssl_domain_base_url']);
            if( isset($names[$file['id']]) ) {
                $files[$fid]['name'] = $names[$file['id']];
            } else {
                $files[$fid]['name'] = $file['filename'];
            }
        }
/*        if( isset($s['content']) && $s['content'] != '' ) {
            $blocks[] = array(
                'type' => 'text',
                'level' => $section['sequence'] == 1 ? 1 : 2,
                'title' => isset($s['title']) ? $s['title'] : '',
                'subtitle' => isset($s['subtitle']) ? $s['subtitle'] : '',
                'content' => isset($s['content']) ? $s['content'] : '',
                );
        } else {
            $blocks[] = array(
                'type' => 'title',
                'level' => $section['sequence'] == 1 ? 1 : 2,
                'title' => isset($s['title']) ? $s['title'] : '',
                'subtitle' => isset($s['subtitle']) ? $s['subtitle'] : '',
                'content' => isset($s['content']) ? $s['content'] : '',
                );
        } */

        $blocks[] = array(
            'type' => 'filelist',
            'level' => $section['sequence'] == 1 ? 1 : 2,
            'title' => isset($s['title']) ? $s['title'] : '',
            'subtitle' => isset($s['subtitle']) ? $s['subtitle'] : '',
            'content' => isset($s['content']) ? $s['content'] : '',
            'class' => 'section-' . ciniki_core_makePermalink($ciniki, $section['label']),
            'link-class' => isset($s['class']) ? $s['class'] : 'button',
            'items' => $files,
            );
    }

    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
