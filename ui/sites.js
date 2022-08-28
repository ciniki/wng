//
// This is the sites app for the wng module
//
function ciniki_wng_sites() {
    //
    // The panel to list the sites
    //
    this.menu = new M.panel('site', 'ciniki_wng_sites', 'menu', 'mc', 'medium', 'sectioned', 'ciniki.wng.sites.menu');
    this.menu.data = {};
    this.menu.sections = {
//        'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':1,
//            'cellClasses':[''],
//            'hint':'Search site',
//            'noData':'No site found',
//            },
        'sites':{'label':'Site', 'type':'simplegrid', 'num_cols':4,
            'headerValues':['Domain', 'Permalink', 'Name', 'Status'],
            'noData':'No site',
            'addTxt':'Add Site',
            'addFn':'M.ciniki_wng_sites.site.open(\'M.ciniki_wng_sites.menu.open();\',0,null);'
            },
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( s == 'search' && v != '' ) {
            M.api.getJSONBgCb('ciniki.wng.siteSearch', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'25'}, function(rsp) {
                M.ciniki_wng_sites.menu.liveSearchShow('search',null,M.gE(M.ciniki_wng_sites.menu.panelUID + '_' + s), rsp.sites);
                });
        }
    }
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        return d.name;
    }
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return 'M.ciniki_wng_sites.site.open(\'M.ciniki_wng_sites.menu.open();\',\'' + d.id + '\');';
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'sites' ) {
            switch(j) {
                case 0: return d.domain;
                case 1: return d.permalink;
                case 2: return d.name;
                case 3: return d.status_text;
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'sites' ) {
            return 'M.ciniki_wng_sites.site.open(\'M.ciniki_wng_sites.menu.open();\',\'' + d.id + '\');';
        }
    }
    this.menu.open = function(cb) {
        M.api.getJSONCb('ciniki.wng.siteList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wng_sites.menu;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to edit Site
    //
    this.site = new M.panel('Site', 'ciniki_wng_sites', 'site', 'mc', 'medium', 'sectioned', 'ciniki.wng.sites.site');
    this.site.data = null;
    this.site.site_id = 0;
    this.site.sections = {
        'general':{'label':'', 'fields':{
            'name':{'label':'Name', 'required':'yes', 'type':'text'},
            'status':{'label':'Status', 'type':'toggle', 'toggles':{'5':'Development', '10':'Active', '90':'Archive'}},
            'permalink':{'label':'Permalink', 'type':'text'},
            'domain_id':{'label':'Domain', 'type':'select', 'options':{},
                'complex_options':{'value':'id', 'name':'domain'},
                },
            'flags':{'label':'Options', 'type':'flags', 'flags':{'1':{'name':'Default'}}},
            'theme':{'label':'Theme', 'type':'text'},
            'css_classes':{'label':'CSS Classes', 'type':'text'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wng_sites.site.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wng_sites.site.site_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wng_sites.site.remove();'},
            }},
        };
    this.site.fieldValue = function(s, i, d) { return this.data[i]; }
    this.site.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wng.siteHistory', 'args':{'tnid':M.curTenantID, 'site_id':this.site_id, 'field':i}};
    }
    this.site.open = function(cb, sid) {
        if( sid != null ) { this.site_id = sid; }
        M.api.getJSONCb('ciniki.wng.siteGet', {'tnid':M.curTenantID, 'site_id':this.site_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wng_sites.site;
            p.data = rsp.site;
            p.sections.general.fields.domain_id.options = rsp.domains;
            p.refresh();
            p.show(cb);
        });
    }
    this.site.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wng_sites.site.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.site_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wng.siteUpdate', {'tnid':M.curTenantID, 'site_id':this.site_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.wng.siteAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wng_sites.site.site_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.site.remove = function() {
        if( confirm('Are you sure you want to remove site?') ) {
            M.api.getJSONCb('ciniki.wng.siteDelete', {'tnid':M.curTenantID, 'site_id':this.site_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wng_sites.site.close();
            });
        }
    }
    this.site.addButton('save', 'Save', 'M.ciniki_wng_sites.site.save();');
    this.site.addClose('Cancel');

    //
    // Start the app
    // cb - The callback to run when the user leaves the sites panel in the app.
    // ap - The application prefix.
    // ag - The app arguments.
    //
    this.start = function(cb, ap, ag) {
        args = {};
        if( ag != null ) {
            args = eval(ag);
        }
        
        //
        // Create the app container
        //
        var ac = M.createContainer(ap, 'ciniki_wng_sites', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }
        
        this.menu.open(cb);
    }
}
