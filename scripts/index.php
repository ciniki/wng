<?php
//
// Description
// -----------
// This script will deliver the website for clients,
// or the default page for main domain.
//
// All web requests for tenant websites are funnelled through this script.
//
$start_time = microtime(true);

//
// Load ciniki
//
global $ciniki_root;
$ciniki_root = dirname(__FILE__);
// Some systems don't follow symlinks like others
if( !file_exists($ciniki_root . '/ciniki-api.ini') ) {
    $ciniki_root = dirname(dirname(dirname(dirname(__FILE__))));
}

//
// Initialize Ciniki
//
$ciniki = array();
require_once($ciniki_root . '/ciniki-mods/core/private/loadCinikiConfig.php');
if( ciniki_core_loadCinikiConfig($ciniki, $ciniki_root) == false ) {
    ciniki_wng_printError($ciniki, null, 'There is currently a configuration problem, please try again later.');
    exit;
}

// standard functions
require_once($ciniki_root . '/ciniki-mods/core/private/loadMethod.php');
require_once($ciniki_root . '/ciniki-mods/core/private/dbQuote.php');
require_once($ciniki_root . '/ciniki-mods/core/private/dbHashQuery.php');
require_once($ciniki_root . '/ciniki-mods/core/private/checkModuleFlags.php');
require_once($ciniki_root . '/ciniki-mods/core/private/checkModuleActive.php');

//
// Initialize Database
//
require_once($ciniki['config']['ciniki.core']['modules_dir'] . '/core/private/dbInit.php');
$rc = ciniki_core_dbInit($ciniki);
if( $rc['stat'] != 'ok' ) {
    ciniki_wng_printError($ciniki, null, 'There is currently a problem with our systems.  We are working to fix it as quickly as possible.  Please try again in a few minutes.');
    exit;
}

//
// Setup the defaults
//
$request = array(
    'query_string' => '',
    'args' => array(),
    'ssl' => 'no',
    );

// 
// Split the request URI into parts
//
$uri = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);              // Remove leading slash (/)
$u = preg_split('/\?/', $uri);                                          // Separate out arguments
$request['uri_split'] = preg_split('/\//', $u[0]);            // Split on slash (/) to get each piece
if( isset($u[1]) ) {
    $request['query_string'] = $u[1];
}
if( !is_array($request['uri_split']) ) {
    $request['uri_split'] = array($request['uri_split']);
}

//
// Parse the query_string args
//
if( isset($_GET) && is_array($_GET) ) {
    foreach($_GET as $arg_key => $arg_value) {
        if( is_array($arg_value) ) {
            error_log("Unsupported arguments: $arg_key");
        } else {
            $request['args'][$arg_key] = rawurldecode($arg_value);
        }
    }
}

//
// Check if SSL 
//
if( (isset($_SERVER['HTTP_CLUSTER_HTTPS']) && $_SERVER['HTTP_CLUSTER_HTTPS'] == 'on')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' )
    ) {
    $request['ssl'] = 'yes';
}

$site = null;
//
// Check if the request is for the master domain
//
if( isset($_SERVER['HTTP_HOST']) && $ciniki['config']['ciniki.wng']['master.domain'] == $_SERVER['HTTP_HOST'] ) {
    $tenant_permalink = isset($request['uri_split'][0]) ? $request['uri_split'][0] : '';
    if( $tenant_permalink != '' ) {
        $request['domain'] = $_SERVER['HTTP_HOST'];
        $request['base_url'] = '/' . $tenant_permalink;
        //
        // Get the sites for a tenant based on sitename
        //
        $strsql = "SELECT tenants.id AS tnid, "
            . "sites.id, "
            . "sites.flags, "
            . "sites.permalink "
            . "FROM ciniki_tenants AS tenants "
            . "INNER JOIN ciniki_wng_sites AS sites ON ("
                . "tenants.id = sites.tnid "
                . "AND sites.status = 10 "
                . ") "
            . "WHERE tenants.sitename = '" . ciniki_core_dbQuote($ciniki, $tenant_permalink) . "' "
            . "AND tenants.status = 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'site');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.5', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
        }
        if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
            $site_permalink = isset($request['uri_split'][1]) ? $request['uri_split'][1] : '';
            foreach($rc['rows'] as $s) {
                // Check if default for domain and non-specified
                if( $s['permalink'] != '' && $s['permalink'] == $site_permalink ) {
                    $site = $s;
                    $request['base_url'] .= '/' . $s['permalink'];
                    array_shift($request['uri_split']);
                } elseif( ($s['flags']&0x01) == 0x01 && $site == null ) {
                    $site = $s;
                } elseif( $s['permalink'] == '' && $site == null ) {
                    $site = $s;
                }
            }
        }
        if( $site != null ) {
            array_shift($request['uri_split']);
        }
    }
}

//
// Request is not a tenant request under the master domain, check the domain for sites
// This could include master domain sites
//
if( $site == null && isset($_SERVER['HTTP_HOST']) ) {
    $request['domain'] = $_SERVER['HTTP_HOST'];
    $request['base_url'] = '';
    $strsql = "SELECT tenants.id AS tnid, "
        . "domains.domain, "
        . "domains.flags AS domain_flags, "
        . "sites.id, "
        . "sites.flags, "
        . "sites.permalink "
        . "FROM ciniki_tenant_domains AS domains "
        . "INNER JOIN ciniki_tenants AS tenants ON ("
            . "domains.tnid = tenants.id "
            . "AND tenants.status = 1 "
            . ") "
        . "INNER JOIN ciniki_wng_sites AS sites ON ("
            . "domains.id = sites.domain_id "
            . "AND sites.status = 10 "
            . "AND domains.tnid = sites.tnid "
            . ") "
        . "WHERE domains.domain = '" . ciniki_core_dbQuote($ciniki, $_SERVER['HTTP_HOST']) . "' "
        . "AND domains.status = 1 "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'site');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.6', 'msg'=>'Unable to load sites', 'err'=>$rc['err']));
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        $site_permalink = isset($request['uri_split'][0]) ? $request['uri_split'][0] : '';
        foreach($rc['rows'] as $s) {
            // Check if default for domain and non-specified
            if( $s['permalink'] != '' && $s['permalink'] == $site_permalink ) {
                $site = $s;
                $request['base_url'] = '/' . $s['permalink'];
                array_shift($request['uri_split']);
            } elseif( ($s['flags']&0x01) == 0x01 && $site == null ) {
                $site = $s;
            } elseif( $s['permalink'] == '' && $site == null ) {
                $site = $s;
            }
        }
    }

    //
    // Nothing found, Check if domain is a forward
    //
    if( $site == null ) {
        $strsql = "SELECT domains.domain, "
            . "domains.flags "
            . "FROM ciniki_tenant_domains AS aliases "
            . "INNER JOIN ciniki_tenant_domains AS domains ON ("
                . "aliases.parent_id = domains.id "
                . "AND aliases.tnid = domains.tnid "
                . "AND domains.status = 1 "
                . ") "
            . "WHERE aliases.domain = '" . ciniki_core_dbQuote($ciniki, $_SERVER['HTTP_HOST']) . "' "
            . "AND aliases.status = 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wng', 'alias');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wng.127', 'msg'=>'Unable to load sites', 'err'=>$rc['err']));
        }
        if( isset($rc['alias']['domain']) && $rc['alias']['domain'] != '' ) {
            Header('HTTP/1.1 301 Moved Permanently'); 
            if( ($rc['alias']['flags']&0x10) == 0x10 ) {
                header('Location: https://' . $rc['alias']['domain'] . $_SERVER['REQUEST_URI']);
            } else {
                header('Location: http://' . $rc['alias']['domain'] . $_SERVER['REQUEST_URI']);
            }
            exit;
        }
    }

    if( $site != null ) {
        $request['domain'] = $site['domain'];
    }
}

//
// FUTURE: Check if reseller domain is with reseller
//
if( $site == null ) {
}

//
// No ciniki.wng site found, revert to ciniki.web module
//
if( $site == null ) {
    $ciniki['request'] = $request;
    ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processRequest');
    $rc = ciniki_web_processRequest($ciniki);
    exit;
}

//
// Note: Redirect to primary domain disabled so multiple domains and sites can be on 1 tenant.
//

//
// Check if force SSL
//
if( isset($site['domain_flags']) && ($site['domain_flags']&0x10) == 0x10 && $request['ssl'] == 'no' ) {
    Header('HTTP/1.1 301 Moved Permanently'); 
    Header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

//
// Setup domain_base_url
//
if( $request['ssl'] == 'yes' ) {
    $request['domain_base_url'] = 'https://' . $request['domain'] . $request['base_url'];
    $request['ssl_domain_base_url'] = 'https://' . $request['domain'] . $request['base_url'];
}
else {
    $request['domain_base_url'] = 'http://' . $request['domain'] . $request['base_url'];
    if( !isset($ciniki['config']['ciniki.core']['ssl']) || $ciniki['config']['ciniki.core']['ssl'] != 'off' ) {
        $request['ssl_domain_base_url'] = 'https://' . $request['domain'] . $request['base_url'];
    } else {
        $request['ssl_domain_base_url'] = 'http://' . $request['domain'] . $request['base_url'];
    }
}

//
// Setup the API url
//
$request['api_url'] = $request['base_url'] . '/cpi';

//
// Load the tenant information
//
$strsql = "SELECT uuid, name "
    . "FROM ciniki_tenants "
    . "WHERE id = '" . ciniki_core_dbQuote($ciniki, $site['tnid']) . "' "
    . "";
$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
if( $rc['stat'] != 'ok' ) {
    ciniki_wng_printError($ciniki, $rc, 'Unable to load site');
    exit;
}
if( !isset($rc['tenant']) ) {
    ciniki_wng_printError($ciniki, null, 'Unable to load site');
    exit;
}
$ciniki['tenant'] = array(
    'uuid' => $rc['tenant']['uuid'],
    'name' => $rc['tenant']['name'],
    'modules' => array(),
    );

//
// Check if the module is enabled for this tenant, don't really care about the ruleset
//
$strsql = "SELECT modules.status AS module_status, "
    . "modules.package, "
    . "modules.module, "
    . "CONCAT_WS('.', modules.package, modules.module) AS module_id, "
    . "modules.flags "
    . "FROM ciniki_tenants AS tenants, ciniki_tenant_modules AS modules "
    . "WHERE tenants.id = '" . ciniki_core_dbQuote($ciniki, $site['tnid']) . "' "
    . "AND tenants.id = modules.tnid "
    . "AND (modules.status = 1 OR modules.status = 2) "
    . "";
ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashIDQuery');
$rc = ciniki_core_dbHashIDQuery($ciniki, $strsql, 'ciniki.tenants', 'modules', 'module_id');
if( $rc['stat'] != 'ok' ) {
    ciniki_wng_printError($ciniki, $rc, 'Unable to load site');
    exit;
}
$ciniki['tenant']['modules'] = isset($rc['modules']) ? $rc['modules'] : array();

//
// Process the request for the site
//
$request['site_id'] = $site['id'];
ciniki_core_loadMethod($ciniki, 'ciniki', 'wng', 'private', 'siteRequestProcess');
$rc = ciniki_wng_siteRequestProcess($ciniki, $site['tnid'], $request);
if( $rc['stat'] != 'ok' && $rc['stat'] != 'exit' ) {
    ciniki_wng_printError($ciniki, $rc, $rc['err']['msg']);
    exit;
}

exit;

//
// Supporting functions for the main page
//
// FIXME: MOve print error to private/printError

function ciniki_wng_printError($ciniki, $rc, $msg) {
print "<!DOCTYPE html>\n";
?>
<html>
<head><title>Error</title></head>
<body>
<div id="m_error">
    <div id="me_content">
        <div id="mc_content_wrap" class="medium">
            <p>Oops, we seem to have hit a snag.  <?php echo $msg; ?></p>
            <?php if($rc != null && $rc['stat'] != 'ok' ) { ?>
            <table class="list header border" cellspacing='0' cellpadding='0'>
                <thead>
                    <tr><th>Code</th><th>Message</th></tr>
                </thead>
                <tbody>
                    <?php
                    print "<tr><td>" . $rc['err']['code'] . "</td><td>" . $rc['err']['msg'] . "</td></tr>\n";
                    ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>
<?php
}

?>
