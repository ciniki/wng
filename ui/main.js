//
// This is the main app for the wng module
//
function ciniki_wng_main() {
    //
    // The panel to list the page
    //
    this.menu = new M.panel('Sites', 'ciniki_wng_main', 'menu', 'mc', 'medium', 'sectioned', 'ciniki.wng.main.menu');
    this.menu.data = {};
    this.menu.nplist = [];
    this.menu.sections = {
        'sites':{'label':'Sites', 'type':'simplegrid', 'num_cols':1,
            'noData':'No sites',
            },
    }
    this.menu.cellValue = function(s, i, j, d) {
        if( s == 'sites' ) {
            switch(j) {
                case 0: return d.name;
            }
        }
    }
    this.menu.rowFn = function(s, i, d) {
        if( s == 'sites' ) {
            return 'M.ciniki_wng_main.site.open(\'M.ciniki_wng_main.menu.open();\',\'' + d.id + '\');';
        }
    }
    this.menu.open = function(cb) {
        M.api.getJSONCb('ciniki.wng.siteList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wng_main.menu;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    }
    this.menu.addClose('Back');

    //
    // The panel to manage a website
    //
    this.site = new M.panel('Site', 'ciniki_wng_main', 'site', 'mc', 'medium', 'sectioned', 'ciniki.wng.main.site');
    this.site.data = {};
    this.site.view = 'menu';
    this.site.view_aside = 'yes';
    this.site.view_content = 'no';
    this.site.site_id = 0;
    this.site.page_id = '';
    this.site.sections = {
        '_tabs':{'label':'', 'type':'paneltabs', 'selected':'pages', 'aside':'yes',
            'tabs':{
                'pages':{'label':'Pages', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.switchTab(\'pages\');");'},
//                'footer':{'label':'Footer', 'fn':'M.ciniki_wng_main.site.switchTab("footer");'},
                'settings':{'label':'Settings', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.switchTab(\'settings\');");'},
            }},
        'headerpages':{'label':'', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { 
                    if( M.ciniki_wng_main.site.view_aside == 'yes' 
                        && M.ciniki_wng_main.site.sections._tabs.selected == 'pages' 
                        ) {
                        return 'yes';
                    }
                    return 'no';
                },
            'addTxt':'Add Page',
            'addFn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.addpage.open(\'M.ciniki_wng_main.site.open();\',M.ciniki_wng_main.site.site_id);");',
            },
        'footerpages':{'label':'Footer Menu', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { 
                    if( M.ciniki_wng_main.site.view_aside == 'yes'
                        && M.ciniki_wng_main.site.sections._tabs.selected == 'pages' 
                        && M.ciniki_wng_main.site.data.footerpages != null
                        ) { 
                        return 'yes';
                    }
                return 'no';
                },
            },
        'orphanpages':{'label':'Other Pages', 'type':'simplegrid', 'num_cols':1, 'aside':'yes',
            'visible':function() { 
                    if( M.ciniki_wng_main.site.view_aside == 'yes'
                        && M.ciniki_wng_main.site.sections._tabs.selected == 'pages' 
                        && M.ciniki_wng_main.site.data.orphanpages != null
                        ) { 
                        return 'yes';
                    }
                    return 'no';
                },
            },
        'options':{'label':'Settings', 'type':'simplelist', 'aside':'yes',
            'visible':function() { return (M.ciniki_wng_main.site.view_aside == 'yes' && M.ciniki_wng_main.site.sections._tabs.selected == 'settings' ? 'yes' : 'no'); },
            'list':{
                'header':{'label':'Header', 'visible':'yes', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'header\');");'},
                'footer':{'label':'Footer', 'visible':'yes', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'footer\');");'},
                'social':{'label':'Social Media', 'visible':'yes', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'social\');");'},
// NOthing in theme yet, enable when required
//                'theme':{'label':'Theme', 'visible':'yes', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'theme\');");'},
                'account':{'label':'Account', 
                    'visible':function() { return M.modFlagSet('ciniki.customers', 0x01); },
                    'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'account\');");',
                    },
                'cart':{'label':'Cart', 
                    'visible':function() { return M.modFlagSet('ciniki.sapos', 0x08); },
                    'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'cart\');");',
                    },
                'search':{'label':'Search', 'visible':'yes', 
                    'visible':function() { return M.modFlagSet('ciniki.wng', 0x4000); },
                    'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'search\');");',
                    },
                },
            },
        'admin':{'label':'Admin', 'type':'simplelist', 'aside':'yes',
            'visible':function() { return (M.ciniki_wng_main.site.view_aside == 'yes' && M.ciniki_wng_main.site.sections._tabs.selected == 'settings' && (M.userPerms&0x01) == 0x01 ? 'yes' : 'no'); },
            'list':{
                'cssimports':{'label':'CSS Imports', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'cssimports\');");'},
                'cssoverrides':{'label':'Custom CSS', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'cssoverrides\');");'},
                'themeimages':{'label':'Theme Images', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'themeimages\');");'},
                'meta':{'label':'Meta Tags', 'fn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openSettings(\'meta\');");'},
                },
            },
        'adminbuttons':{'label':'', 'aside':'yes',
            'visible':function() { return (M.ciniki_wng_main.site.view_aside == 'yes' && M.ciniki_wng_main.site.sections._tabs.selected == 'settings' && (M.userPerms&0x01) == 0x01 ? 'yes' : 'no'); },
            'buttons':{
                'rebuildcache':{'label':'Rebuild Cache', 'fn':'M.ciniki_wng_main.site.rebuildCache();'},
                'rebuildindex':{'label':'Rebuild Index', 
                    'visible':function() { return M.modFlagSet('ciniki.wng', 0x4000); },
                    'fn':'M.ciniki_wng_main.site.rebuildIndex();',
                    },
                'rebuildall':{'label':'Rebuild All', 'fn':'M.ciniki_wng_main.site.rebuildAll();'},
            }},
        //
        // Page sections
        //
        'pagedetails':{'label':'Page Details',
            'active':function() { return (M.ciniki_wng_main.site.view == 'page' ? 'yes' : 'no'); },
            'fields':{
                'title':{'label':'Menu Title', 'required':'yes', 'type':'text'},
                'page_title':{'label':'Page Title', 'type':'text'},
                'sequence':{'label':'Page Order', 'type':'text', 'size':'small'},
                'menu_flags':{'label':'Menu Options', 'type':'flags', 
                    'visible':function() { 
                        if( M.ciniki_wng_main.site.data.page != null 
                            && M.ciniki_wng_main.site.data.page.parent_id == M.ciniki_wng_main.site.data.site.homepage_id 
                            ) {
                            return 'yes';
                        }
                        return 'no';
                        },
                    'flags':{'1':{'name':'Header'},'2':{'name':'Footer'}},
                    },
                'flags1':{'label':'Visible', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'on'},
//                'flags4':{'label':'Password', 'type':'flagtoggle', 'bit':0x08, 'field':'flags_4', 'default':'off',
//                    'active':(M.modFlagSet('ciniki.web', 0x2000)),
//                    'on_fields':['page_password'],
//                    },
//                'password':{'label':'', 'type':'text', 'visible':(M.modFlagOn('ciniki.web', 0x2000) && (rsp.page.flags&0x08) == 0x08 ? 'yes' : 'no')},
                },
            },
//        'page_type':{'label':'Page Type', 'aside':'yes', 'visible':'no', 'fields':{
//            'page_type':{'label':'', 'hidelabel':'yes', 'type':'toggle', 'toggles':{}, 'onchange':'M.ciniki_web_pages[\'' + pn + '\'].setPageType();'},
//            }},
//                '_redirect':{'label':'Redirect', 'visible':'no', 
//                    'active':function() { return M.ciniki_web_pages[pn].sectionVisible('_redirect'); },
//                    'fields':{
//                        'page_redirect_url':{'label':'URL', 'type':'text'},
//                    }},
        'pagesections':{'label':'Sections', 'type':'simplegrid', 'num_cols':1,
            'active':function() { return (M.ciniki_wng_main.site.view == 'page' ? 'yes' : 'no'); },
            'addTxt':'Add Section',
            'addFn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.section.open(\'M.ciniki_wng_main.site.open();\',0,M.ciniki_wng_main.site.page_id,M.ciniki_wng_main.site.site_id);");',
            'seqDrop':function(e,from,to) {
                M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 
                    'action':'sectionsequenceupdate',
                    'view':M.ciniki_wng_main.site.view,
                    'site_id':M.ciniki_wng_main.site.site_id,
                    'page_id':M.ciniki_wng_main.site.page_id,
                    'section_id':M.ciniki_wng_main.site.data.pagesections[from].id, 
                    'section_sequence':M.ciniki_wng_main.site.data.pagesections[to].sequence, 
                    'section_flags':0,
                    }, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_wng_main.site;
                        p.data.pagesections = rsp.pagesections;
                        p.refreshSection("pagesections");
                    });
                },
            },
//        'pagebuttons':{'label':'',
//            'active':function() { return (M.ciniki_wng_main.site.view == 'page' ? 'yes' : 'no'); },
//            'buttons':{
//                'save':{'label':'Save', 'fn':'M.ciniki_wng_main.site.save();'},
//                'delete':{'label':'Delete', 'fn':'M.ciniki_wng_main.site.remove();'},
//            }},
/*        '_image_id':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                'addDropImage':function(iid) {
                    M.ciniki_wng_main.page.setFieldValue('image_id', iid);
                    return true;
                    },
                'addDropImageRefresh':'',
             },
        }},
        '_synopsis':{'label':'Synopsis', 'fields':{
            'synopsis':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }}, */
        //
        // theme sections
        //
        'themesettings':{'label':'Theme Settings', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'theme' ? 'yes' : 'no'); },
            'fields':{
                'theme-test':{'label':'Test', 'type':'text'},
                }},
        //
        // header sections
        //
/*        'headerimage':{'label':'Header Logo', 'type':'simpleform', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'header' ? 'yes' : 'no'); },
            'fields':{
                'header-image-id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                    'addDropImage':function(iid) {
                        M.ciniki_wng_main.site.setFieldValue('header-image-id', iid);
                        return true;
                        },
                    'addDropImageRefresh':'', 
                    },
            }}, */
        'headersettings':{'label':'Header Settings',  'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'header' ? 'yes' : 'no'); },
            'fields':{
                'header-site-title':{'label':'Site Title', 'type':'text'},
                'header-social-icons':{'label':'Show Social Icons', 'type':'toggle', 'default':'yes', 'toggles':{'no':'No', 'yes':'Yes'}},
                // FIXME: Add header-seo-title, header-seo-description
            }},
        'headersections':{'label':'Header Sections', 'type':'simplegrid', 'num_cols':1,
            'active':function() { return (M.ciniki_wng_main.site.view == 'header' ? 'yes' : 'no'); },
            'addTxt':'Add Section',
            'addFn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.section.open(\'M.ciniki_wng_main.site.open();\',0,\'header\',M.ciniki_wng_main.site.site_id);");',
            'seqDrop':function(e,from,to) {
                M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 
                    'action':'sectionsequenceupdate',
                    'view':M.ciniki_wng_main.site.view,
                    'site_id':M.ciniki_wng_main.site.site_id,
                    'page_id':M.ciniki_wng_main.site.data.site.homepage_id,
                    'section_id':M.ciniki_wng_main.site.data.headersections[from].id, 
                    'section_sequence':M.ciniki_wng_main.site.data.headersections[to].sequence, 
                    'section_flags':0x01,
                    }, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_wng_main.site;
                        p.data.headersections = rsp.headersections;
                        p.refreshSection("headersections");
                    });
                },
            },
        //
        // footer sections
        //
        'footersections':{'label':'Footer Sections', 'type':'simplegrid', 'num_cols':1,
            'active':function() { return (M.ciniki_wng_main.site.view == 'footer' ? 'yes' : 'no'); },
            'addTxt':'Add Section',
            'addFn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.section.open(\'M.ciniki_wng_main.site.open();\',0,\'footer\',M.ciniki_wng_main.site.site_id);");',
            'seqDrop':function(e,from,to) {
                M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 
                    'action':'sectionsequenceupdate',
                    'view':M.ciniki_wng_main.site.view,
                    'site_id':M.ciniki_wng_main.site.site_id,
                    'page_id':M.ciniki_wng_main.site.data.site.homepage_id,
                    'section_id':M.ciniki_wng_main.site.data.footersections[from].id, 
                    'section_sequence':M.ciniki_wng_main.site.data.footersections[to].sequence, 
                    'section_flags':0x02,
                    }, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        var p = M.ciniki_wng_main.site;
                        p.data.footersections = rsp.footersections;
                        p.refreshSection("footersections");
                    });
                },
            },
        'footersettings':{'label':'Footer Settings', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'footer' ? 'yes' : 'no'); },
            'fields':{
                'footer-copyright-name':{'label':'Copyright Name', 'type':'text'},
                'footer-copyright-message':{'label':'Copyright Message', 'type':'textarea'},
                'footer-social-icons':{'label':'Show Social Icons', 'type':'toggle', 'default':'yes', 'toggles':{'no':'No', 'yes':'Yes'}},
            }},
        //
        // Social Media Accounts
        //
        'socialsettings':{'label':'Social Settings', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'social' ? 'yes' : 'no'); },
            'fields':{
                'social-facebook-url':{'label':'Facebook URL', 'type':'text'},
                'social-instagram-username':{'label':'Instagram Username', 'type':'text'},
                'social-youtube-url':{'label':'YouTube URL', 'type':'text'},
                'social-etsy-url':{'label':'Etsy URL', 'type':'text'},
                'social-twitter-business-name':{'label':'Twitter Name', 'type':'text'},
                'social-twitter-username':{'label':'Twitter Username', 'type':'text'},
                'social-pinterest-username':{'label':'Pinterest Username', 'type':'text'},
                'social-linkedin-url':{'label':'Linked In URL', 'type':'text'},
                'social-vimeo-url':{'label':'Vimeo URL', 'type':'text'},
            }},
        //
        // Account page settings
        //
        'accountsettings':{'label':'Account Settings', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'account' ? 'yes' : 'no'); },
            'fields':{
                'account-active':{'label':'Customer Logins', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
            }},
        //
        // cart page settings
        //
        'cartsettings':{'label':'Cart Settings', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cart' ? 'yes' : 'no'); },
            'fields':{
                'cart-active':{'label':'Enable Cart', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
            }},
        'cartmessages':{'label':'Cart Messages', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cart' ? 'yes' : 'no'); },
            'fields':{
                'cart-noaccount-message':{'label':'No Account Message', 'type':'textarea'},
                'cart-checkout-message':{'label':'Checkout Message', 'type':'textarea'},
                'cart-payment-success-message':{'label':'Payment Success', 'type':'textarea'},
                'cart-payment-success-emails':{'label':'Email Notifications', 'type':'text'},
            }},
        'cartdonations':{'label':'Cart Donation Request', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cart' && M.modFlagOn('ciniki.sapos', 0x02000000) ? 'yes' : 'no'); },
            'fields':{
                'cart-donation-message':{'label':'Message', 'type':'textarea'},
                'cart-donation-amounts':{'label':'Amounts', 'type':'text'},
                'cart-donation-thankyou':{'label':'Thank You', 'type':'textarea'},
            }},
        //
        // Custom CSS
        //
        'cssimports':{'label':'CSS Imports', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cssimports' ? 'yes' : 'no'); },
            'fields':{
                'theme-css-imports':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'xlarge'},
            }},
        //
        // Custom CSS
        //
        'cssoverrides':{'label':'Custom CSS', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cssoverrides' ? 'yes' : 'no'); },
            'fields':{
                'theme-css-overrides':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'xlarge'},
            }},
        //
        // Theme Images, can be used in custom css
        //
        'themeimages':{'label':'Theme Images', 'type':'simplethumbs', 'imgsize':'large',
            'active':function() { return (M.ciniki_wng_main.site.view == 'themeimages' ? 'yes' : 'no'); },
            },
        'themeimageadd':{'label':'',
            'active':function() { return (M.ciniki_wng_main.site.view == 'themeimages' ? 'yes' : 'no'); },
            'buttons':{
                'add':{'label':'Add Image', 'fn':'M.ciniki_wng_main.site.themeImageAdd();'},
            }},
        //
        // Meta Information
        //
        'cartdonations':{'label':'Meta Tags', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'meta' ? 'yes' : 'no'); },
            'fields':{
                'meta-google-analytics-account':{'label':'Google Analytics', 'type':'text'},
                'meta-google-site-verification':{'label':'Google Site Verification', 'type':'text'},
                'meta-google-tag-manager':{'label':'Google Tag Manager', 'type':'text'},
                'meta-facebook-pixel-id':{'label':'Facebook Pixel ID', 'type':'text'},
                'meta-facebook-domain-verification':{'label':'Facebook Domain Verification', 'type':'text'},
            }},
        'buttons':{'label':'',
            'visible':function() { return (M.ciniki_wng_main.site.view != 'menu' ? 'yes' : 'no'); },
            'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_wng_main.site.save();'},
                'delete':{'label':'Delete', 
                    'visible':function() { return (M.ciniki_wng_main.site.view == 'page' ? 'yes' : 'no'); },
                    'fn':'M.ciniki_wng_main.site.remove();',
                    },
            }},
        };
    this.site.thumbFn = function(s, i, d) {
        return 'M.ciniki_wng_main.site.themeImageRemove(\'' + d.image_id + '\');';
//        if( confirm("Are you sure you want to remove this image?") ) {
//            console.log('remove image');
//        }
    }
    this.site.thumbTitle = function(s, i, d) {
        return d.original_filename;
    }
    this.site.themeImageAdd = function() {
        if( this.themeimage_upload == null ) {
            this.themeimage_upload = M.aE('input', this.panelUID + '_themeimage_orgfilename_upload', 'image_uploader');
            this.themeimage_upload.setAttribute('name', 'themeimage_orgfilename');
            this.themeimage_upload.setAttribute('type', 'file');
            this.themeimage_upload.setAttribute('onchange', this.panelRef + '.uploadThemeImage();');
        }
        this.themeimage_upload.value = '';
        this.themeimage_upload.click();
    }
    this.site.uploadThemeImage = function() {
        var f = this.themeimage_upload;
        M.api.postJSONFile('ciniki.wng.site', {'tnid':M.curTenantID, 'view':this.view, 'site_id':this.site_id, 
            'page_id':this.page_id, 'action':'addthemeimage'}, 
            f.files[0], this.openFinish);
    }
    this.site.themeImageRemove = function(image_id) {
        if( confirm("Are you sure you want to remove this image?") ) {
            M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 'view':this.view, 'site_id':this.site_id, 
                'page_id':this.page_id, 'action':'removethemeimage', 'image_id':image_id}, 
                this.openFinish);
        }
    }
    this.site.fieldValue = function(s, i, d) {
        if( s == 'pagedetails' ) {
            return this.data.page != null ? this.data.page[i] : '';
        }
        if( this.sections[s] != null && this.sections[s].data != null && this.sections[s].data == 'settings' ) {
            if( this.data.site.settings[i] != null ) {
                return this.data.site.settings[i];
            }
            return '';
        }
        return this.data[i];
    }
    this.site.switchTab = function(t) {
        this.sections._tabs.selected = t;
        if( t == 'settings' ) {
            this.view = 'menu';
        } else if( t == 'pages' ) {
            if( this.page_id > 0 ) {
                this.view = 'page';
            } else {
                this.view = 'menu';
            }
        }
        this.open();
    }
    this.site.listClass = function(s, i, d) {
        if( i == this.view ) {
            return 'highlight';
        }
        return '';
    }
    this.site.rowClass = function(s, i, d) {
        if( (s == 'headerpages' || s == 'footerpages' || s == 'orphanpages') && this.page_id == d.id ) {
            return 'highlight';
        }
        return '';
    }
    this.site.cellValue = function(s, i, j, d) {
        if( s == 'headerpages' || s == 'footerpages' || s == 'orphanpages' ) {
            switch(j) {
                case 0: return d.name;
            }
        }
        if( s == 'headersections' || s == 'pagesections' || s == 'footersections' ) {
            switch(j) {
                case 0: return d.label;
            }
        }
    }
    this.site.rowFn = function(s, i, d) {
        if( s == 'headerpages' || s == 'footerpages' || s == 'orphanpages' ) {
            return 'M.ciniki_wng_main.site.save("M.ciniki_wng_main.site.openPage(\'' + d.id + '\');");';
        }
        if( s == 'headersections' ) {
            return 'M.ciniki_wng_main.site.save("M.ciniki_wng_main.section.open(\'M.ciniki_wng_main.site.open();\',\'' + d.id + '\',\'header\',M.ciniki_wng_main.site.site_id);");';
        }
        if( s == 'pagesections' ) {
            return 'M.ciniki_wng_main.site.save("M.ciniki_wng_main.section.open(\'M.ciniki_wng_main.site.open();\',\'' + d.id + '\',M.ciniki_wng_main.site.page_id,M.ciniki_wng_main.site.site_id);");';
        }
        if( s == 'footersections' ) {
            return 'M.ciniki_wng_main.site.save("M.ciniki_wng_main.section.open(\'M.ciniki_wng_main.site.open();\',\'' + d.id + '\',\'footer\',M.ciniki_wng_main.site.site_id);");';
        }
    }
    this.site.openSettings = function(s) {
        this.view = s;
        this.open();
    }
    this.site.openPage = function(pid) {
        this.page_id = pid;
        this.view = 'page';
        this.open();
    }
    this.site.open = function(cb,id,view) {
        if( view != null ) { this.view = view; }
        if( id != null ) { this.site_id = id; }
        if( cb != null ) { this.cb = cb; }
        M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 'view':this.view, 'site_id':this.site_id, 'page_id':this.page_id}, this.openFinish);
    }
    this.site.openFinish = function(rsp) {
        if( rsp.stat != 'ok' ) {
            M.api.err(rsp);
            return false;
        }
        var p = M.ciniki_wng_main.site;
        p.data = rsp;
        console.log(rsp);
        if( M.emWidth() < 70 ) {
            p.size = 'large';
            if( p.view == 'menu' ) {
                p.view_aside = 'yes';
                p.view_content == 'no';
            } else {
                p.view_aside = 'no';
                p.view_content == 'yes';
            }
        } else {
            if( p.view == 'menu' ) {
                p.size = 'xlarge narrowaside';
                p.view_aside = 'yes';
                p.view_content = 'yes';
            } else {
                p.size = 'xlarge narrowaside';
                p.view_aside = 'yes';
                p.view_content = 'yes';
            }
        }
        p.refresh();
        p.show();
    }
    this.site.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wng_main.site.open();'; }
        if( this.view == 'page' && this.page_id > 0 ) {
            if( !this.checkForm() ) { return false; }
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wng.pageUpdate', {'tnid':M.curTenantID, 'page_id':this.page_id, 'site_id':this.site_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    if( cb == 'M.ciniki_wng_main.site.open();' ) {
                        M.ciniki_wng_main.site.page_id = 0;
                        M.ciniki_wng_main.site.view = 'menu';
                    }
                    eval(cb);
                });
            } else {
                if( cb == 'M.ciniki_wng_main.site.open();' ) {
                    M.ciniki_wng_main.site.page_id = 0;
                    M.ciniki_wng_main.site.view = 'menu';
                }
                eval(cb);
            }
        } else if( this.view != 'menu' && this.view != 'page' ) {
            if( !this.checkForm() ) { return false; }
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wng.siteSettingsUpdate', {'tnid':M.curTenantID, 'site_id':this.site_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    if( cb == 'M.ciniki_wng_main.site.open();' && M.ciniki_wng_main.site.view != 'cssoverrides' ) {
                        M.ciniki_wng_main.site.view = 'menu';
                    }
                    eval(cb);
                });
            } else {
                if( cb == 'M.ciniki_wng_main.site.open();' && M.ciniki_wng_main.site.view != 'cssoverrides' ) {
                    M.ciniki_wng_main.site.view = 'menu';
                }
                eval(cb);
            }

        } else {
            if( cb == 'M.ciniki_wng_main.site.open();' && M.ciniki_wng_main.site.view != 'cssoverrides' ) {
                M.ciniki_wng_main.site.view = 'menu';
            }
            eval(cb);
        }
    }
    this.site.remove = function() {
        if( confirm('Are you sure you want to remove this page and the sections?') ) {
            M.api.getJSONCb('ciniki.wng.pageDelete', {'tnid':M.curTenantID, 'page_id':this.page_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wng_main.site;
                p.page_id = 0;
                p.view = 'menu';
                p.open();
            });
        }
    }
    this.site.rebuildCache = function() {
        M.alert('FIXME: add interface');
    }
    this.site.rebuildIndex = function() {
        M.alert('FIXME: add interface');
    }
    this.site.rebuildAll = function() {
        M.api.getJSONCb('ciniki.wng.siteRebuild', {'tnid':M.curTenantID, 'site_id':this.site_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.ciniki_wng_main.site.open();
        });
    }
    this.site.addClose('Cancel');


    //
    // The panel to edit Page
    //
    this.addpage = new M.panel('Page', 'ciniki_wng_main', 'addpage', 'mc', 'medium', 'sectioned', 'ciniki.wng.main.addpage');
    this.addpage.data = {};
    this.addpage.page_id = 0;
    this.addpage.sections = {
/*        '_image_id':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                'addDropImage':function(iid) {
                    M.ciniki_wng_main.page.setFieldValue('image_id', iid);
                    return true;
                    },
                'addDropImageRefresh':'',
             },
        }}, */
        'parent':{'label':'New page under', 'fields':{
            'parent_id':{'label':'', 'hidelabel':'yes', 'required':'yes', 'type':'select', 
                'options':{},
                'complex_options':{'name':'name', 'value':'id'},
                'onchange':'M.ciniki_wng_main.addpage.updateForm',
                },
            }},
        'details':{'label':'', 'fields':{
            'title':{'label':'Menu Title', 'required':'yes', 'type':'text'},
            'page_title':{'label':'Page Title', 'type':'text'},
            'sequence':{'label':'Page Order', 'type':'text', 'size':'small'},
            'menu_flags':{'label':'Menu Options', 'type':'flags', 'visible':'no', 'flags':{'1':{'name':'Header'},'2':{'name':'Footer'}}},
            'flags1':{'label':'Visible', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'on'},
//            'image_caption':{'label':'Image Caption', 'type':'text'},
            }},
/*        '_synopsis':{'label':'Synopsis', 'fields':{
            'synopsis':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
            }}, */
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wng_main.addpage.save();'},
            }},
        };
    // 
    // FIXME: Add 
    this.addpage.updateForm = function(s, i) {
        var parent_id = this.formFieldValue(this.sections.parent.fields.parent_id, 'parent_id');
        if( parent_id == M.ciniki_wng_main.site.data.site.homepage_id ) {
            this.sections.details.fields.menu_flags.visible = 'yes';
        } else {
            this.sections.details.fields.menu_flags.visible = 'no';
        }
        this.showHideFormField('details', 'menu_flags');
    }
    this.addpage.fieldValue = function(s, i, d) { return this.data[i]; }
    this.addpage.open = function(cb, sid) {
        this.site_id = sid;
        this.reset();
        this.data = {};
        this.sections.parent.fields.parent_id.options = M.ciniki_wng_main.site.data.headerpages;
//        for(var i in M.ciniki_wng_main.site.data.headerpages) {
//            this.sections.parent.fields.parent_id.options[M.ciniki_wng_main.site.data.headerpages[i].id] = M.ciniki_wng_main.site.data.headerpages[i].name;
//        }
//        this.sections.parent.fields.parent_id.options = M.ciniki_wng_main.site.
        this.refresh();
        this.show(cb);
        this.updateForm();
    }
    this.addpage.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wng_main.addpage.close();'; }
        if( !this.checkForm() ) { return false; }
        var c = this.serializeForm('yes');
        M.api.postJSONCb('ciniki.wng.pageAdd', {'tnid':M.curTenantID, 'site_id':this.site_id}, c, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.ciniki_wng_main.site.page_id = rsp.id;
            M.ciniki_wng_main.site.view = 'page';
            eval(cb);
        });
    }
    this.addpage.addButton('save', 'Save', 'M.ciniki_wng_main.addpage.save();');
    this.addpage.addClose('Cancel');

    //
    // The panel to edit a section
    //
    this.section = new M.panel('Section', 'ciniki_wng_main', 'section', 'mc', 'large', 'sectioned', 'ciniki.wng.main.section');
    this.section.data = null;
    this.section.section_id = 0;
    this.section.site_id = 0;
    this.section.ref = 0;
    this.section.nplist = [];
    this.section.sections = {
        'general':{'label':'', 'fields':{
            'ref':{'label':'Section Content', 'type':'select', 'options':{}, 
                'onchange':'M.ciniki_wng_main.section.setSectionOptions',
                },
            'label':{'label':'Label', 'required':'yes', 'type':'text', 'size':'small'},
            'sequence':{'label':'Order', 'required':'yes', 'type':'text', 'size':'small'},
            }},
        '_settings':{'label':'Settings', 'visible':'hidden', 'fields':{
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wng_main.section.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wng_main.section.section_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wng_main.section.remove();'},
            }},
        };
    this.section.fieldValue = function(s, i, d) { 
        if( s == '_settings' ) {
            return this.data.settings[i];
        }
        return this.data[i]; 
    }
    this.section.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wng.sectionHistory', 'args':{'tnid':M.curTenantID, 'section_id':this.section_id, 'field':i}};
    }
    this.section.setSectionOptions = function() {
        this.sections._settings.visible = 'hidden';
        this.sections._settings.fields = {};
        var ref = this.formValue('ref');
        if( this.data.availablesections[ref] != null 
            && this.data.availablesections[ref].settings != null 
            && JSON.stringify(this.data.availablesections[ref].settings)!=JSON.stringify({})
            && JSON.stringify(this.data.availablesections[ref].settings)!=JSON.stringify([])
            ) {
            this.sections._settings.fields = this.data.availablesections[ref].settings;
            //
            // Setup addDropImage for each field that requires it
            //
            for(var i in this.sections._settings.fields) {
                if( this.sections._settings.fields[i].type != null 
                    && this.sections._settings.fields[i].type == 'image_id'
                    ) {
                    this.sections._settings.fields[i].addDropImage = new Function('iid', 
                        'M.ciniki_wng_main.section.setFieldValue(\'' + i + '\',iid); '
                        + 'return true;');
                    this.sections._settings.fields[i].deleteImage = new Function('iid', 
                        'M.ciniki_wng_main.section.setFieldValue(\'' + i + '\',0); '
                        + 'return true;');
                }
            }
            this.sections._settings.visible = 'yes';
        }
        this.refreshSection("_settings");
        this.showHideSection("_settings");
    }
    this.section.open = function(cb, id, pid, sid, list) {
        if( id != null ) { this.section_id = id; }
        if( pid != null ) { this.page_id = pid; }
        if( sid != null ) { this.site_id = sid; }
        if( list != null ) { this.nplist = list; }
        M.api.getJSONCb('ciniki.wng.sectionGet', {'tnid':M.curTenantID, 'site_id':this.site_id, 'page_id':this.page_id, 'section_id':this.section_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wng_main.section;
            p.data = rsp.section;
            p.data.availablesections = rsp.availablesections;
            p.sections.general.fields.ref.options = [];
            for(var i in rsp.availablesections) {
                p.sections.general.fields.ref.options[i] = rsp.availablesections[i].module + ' - ' + rsp.availablesections[i].name;
            }
            p.refresh();
            p.show(cb);
            p.setSectionOptions();
        });
    }
    this.section.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wng_main.section.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.section_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wng.sectionUpdate', {'tnid':M.curTenantID, 'section_id':this.section_id, 'site_id':this.site_id, 'page_id':this.page_id}, c, function(rsp) {
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
            M.api.postJSONCb('ciniki.wng.sectionAdd', {'tnid':M.curTenantID, 'site_id':this.site_id, 'page_id':this.page_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wng_main.section.section_id = rsp.id;
                eval(cb);
            });
        }
    }
    this.section.remove = function() {
        M.confirm('Are you sure you want to remove this section?',null,function() {
            M.api.getJSONCb('ciniki.wng.sectionDelete', {'tnid':M.curTenantID, 'section_id':M.ciniki_wng_main.section.section_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_wng_main.section.close();
            });
        });
    }
    this.section.nextButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.section_id) < (this.nplist.length - 1) ) {
            return 'M.ciniki_wng_main.section.save(\'M.ciniki_wng_main.section.open(null,' + this.nplist[this.nplist.indexOf('' + this.section_id) + 1] + ');\');';
        }
        return null;
    }
    this.section.prevButtonFn = function() {
        if( this.nplist != null && this.nplist.indexOf('' + this.section_id) > 0 ) {
            return 'M.ciniki_wng_main.section.save(\'M.ciniki_wng_main.section.open(null,' + this.nplist[this.nplist.indexOf('' + this.section_id) - 1] + ');\');';
        }
        return null;
    }
    this.section.addButton('save', 'Save', 'M.ciniki_wng_main.section.save();');
    this.section.addClose('Cancel');
    this.section.addButton('next', 'Next');
    this.section.addLeftButton('prev', 'Prev');

    //
    // Start the app
    // cb - The callback to run when the user leaves the main panel in the app.
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
        var ac = M.createContainer(ap, 'ciniki_wng_main', 'yes');
        if( ac == null ) {
            alert('App Error');
            return false;
        }

        if( args.site_id != null && args.site_id > 0 ) {
            this.site.view = 'menu';
            this.site.page_id = 0;
            this.site.open(cb, args.site_id);
        } else {
            this.menu.open(cb);
        }
    }
}
