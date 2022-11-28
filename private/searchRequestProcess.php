<?php
//
// Description
// -----------
// Process the search page
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_searchRequestProcess(&$ciniki, $tnid, &$request) {

    $blocks = array();

    $search_str = '';
    if( isset($request['uri_split'][($request['cur_uri_pos']+1)]) ) {
        $search_str = urldecode($request['uri_split'][($request['cur_uri_pos']+1)]);
    }

    $blocks[] = array(
        'type' => 'title',
        'title' => 'Search',
        );
    $blocks[] = array(
        'type' => 'form',
        'submit-hide' => 'yes',
        'fields' => array(
            'search_string' => array(
                'id' => 'search_string',
                'label' => 'Search For',
                'class' => 'hidden-label',
                'ftype' => 'text',
                'size' => 'large',
                'onkeyup' => 'updateSearch();',
                'value' => $search_str,
                ),
            ),
        );

    if( !isset($request['response']['js']) ) {
        $request['response']['js'] = '';
    }
    $request['response']['js'] .= ''
        . "var pss='';"
        . "function updateSearch() {"
            . "var v=C.gE('f-search_string').value;"
            . "if(pss!=v){"
                . "window.history.replaceState(null,null,'" . $request['base_url'] . "/search/'+v);"
                . "C.getBg('{$request['ssl_domain_base_url']}/cpi/ciniki/wng/search/'+C.eU(v),null,updateSearchResults);"
                . "pss=v;"
            . "}"
        . "};";
    if( $search_str != '' ) {
        $request['response']['js'] .= "window.addEventListener('load',(e)=>{updateSearch();});";
    }
    //
    // FIXME: Add ability to decide how the output looks (trading cards, flex cards, etc)
    //
    if( isset($request['site']['settings']['search-results-format'])
        && $request['site']['settings']['search-results-format'] == 'flexcards'
        ) {

    } 
    //
    // Default to trading cards
    //
    else {
        $blocks[] = array(
            'type' => 'html',
            'html' => "<div class='block-tradingcards block-search-results'>"
                . "<div class='wrap'><div class='content'>"
                . "<div id='live-search-results' class='items'>"
                . "</div></div></div></div>",
        );

        $request['response']['js'] .= ''
            . "function updateSearchResults(rsp) {"
                . "var d = C.gE('live-search-results');"
                . "C.clr(d);"
                . "if(rsp.results!=null&&rsp.results.length>0) {"
                    . "var ct=0;"
                    . "for(i in rsp.results) {"
                        . "var r=rsp.results[i];"
                        . "var c='';"
                        . "c+=\"<a href='\"+r.url+\"'>\";"
                        . "if(r.image_url!=''){"
                            . "c+=\"<div class='image' style='background:url(\"+r.image_url+\") center;"
                            . "background-size:cover;"
                            . "'></div>\";"
                        . "}"
                        . "c+=\"<div class='details'>\";"
                        . "if(r.title!=''){c+=\"<div class='title'>\"+r.title+\"</div>\";}"
                        . "if(r.subtitle!=''){c+=\"<div class='subtitle'>\"+r.subtitle+\"</div>\";}"
                        . "if(r.meta!=''){c+=\"<div class='meta'>\"+r.meta+\"</div>\";}"
                        . "if(r.synopsis!=''){c+=\"<div class='synopsis'>\"+r.synopsis+\"</div>\";}"
                        . "c+='</div>';"
                        . "c+='</a>';"
                        . "var e=C.aE('div',null,'item live-search-' + r.label.toLowerCase(),c);"
                        . "d.appendChild(e);"
                    . "}"
                . "}else if(pss==''){"
                    . "d.innerHTML='<div class=\"live-search-empty\"></div>';"
                . "}else{"
                    . "d.innerHTML='<div class=\"live-search-empty\">I\'m sorry we couldn\'t find what you were looking for.</div>';"
                . "}"
            . "};";
    }


//    $request['response']['blocks'][] = array(
//        'type' => 'content',
//        'content' => "<br/></br><center>Search Page - Not yet implemented</center><br/><br/><br/>",
//        );
    
    return array('stat'=>'ok', 'blocks'=>$blocks);
}
?>
