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
            'editFn':function(s, i, d) {
//                if( (M.userPerms&0x01) == 0x01 ) {
                    return 'M.ciniki_wng_main.edit.open(\'M.ciniki_wng_main.site.open();\',\'' + d.id + '\',M.ciniki_wng_main.site.site_id);';
//                }
//                return '';
            },
            'addTxt':'Add Page',
            'addFn':'M.ciniki_wng_main.site.save("M.ciniki_wng_main.edit.open(\'M.ciniki_wng_main.site.open();\',0,M.ciniki_wng_main.site.site_id);");',
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
            'editFn':function(s, i, d) {
                if( (M.userPerms&0x01) == 0x01 ) {
                    return 'M.ciniki_wng_main.edit.open(\'M.ciniki_wng_main.site.open();\',\'' + d.id + '\',M.ciniki_wng_main.site.site_id);';
                }
                return '';
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
            'editFn':function(s, i, d) {
                if( (M.userPerms&0x01) == 0x01 ) {
                    return 'M.ciniki_wng_main.edit.open(\'M.ciniki_wng_main.site.open();\',\'' + d.id + '\',M.ciniki_wng_main.site.site_id);';
                }
                return '';
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
                    'visible':function() { return M.modFlagAny('ciniki.customers', 0x03); },
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
                'clearcache':{'label':'Clear Cache', 'fn':'M.ciniki_wng_main.site.clearCache();'},
                'indexupdate':{'label':'Update Index', 
                    'visible':function() { return M.modFlagSet('ciniki.wng', 0x4000); },
                    'fn':'M.ciniki_wng_main.site.rebuildIndex("no");',
                    },
                'indexrebuild':{'label':'Rebuild Index', 
                    'visible':function() { return M.modFlagSet('ciniki.wng', 0x4000); },
                    'fn':'M.ciniki_wng_main.site.rebuildIndex("yes");',
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
                'menu_flags':{'label':'Menu Options', 'type':'flags', 'field':'menu_flags',
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
                'flags2':{'label':'Private', 'type':'flagtoggle', 'bit':0x02, 'field':'flags', 'default':'off',
                    'active':function() { return M.modFlagSet('ciniki.customers', 0x01); },
                    },
                'flags3':{'label':'Members Only', 'type':'flagtoggle', 'bit':0x04, 'field':'flags', 'default':'off',
                    'active':function() { return M.modFlagSet('ciniki.customers', 0x02); },
                    },
                'ptype':{'label':'Format', 'type':'toggle', 
                    'onchange':'M.ciniki_wng_main.site.switchType',
                    'toggles':{
                        '10':'Sectioned',
                        '40':'Redirect',
                    }},
                'page_url':{'label':'Page URL', 'type':'noedit', 'editable':'no'},
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
            'active':function() { 
                if( M.ciniki_wng_main.site.view == 'page' && M.ciniki_wng_main.site.data.page.ptype == '10' ) {
                    return 'yes';
                }
                return 'no';
                },
//            'visible':function() { return (M.ciniki_wng_main.site.view == 'page' &&? 'yes' : 'hidden'); },
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
        'pageredirect':{'label':'Redirect', 
            'active':function() { 
                if( M.ciniki_wng_main.site.view == 'page' && M.ciniki_wng_main.site.data.page.ptype == '40' ) {
                    return 'yes';
                }
                return 'no';
                },
            'fields':{
                'redirect_url':{'label':'URL', 'type':'text'},
                }},
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
                'footer-jump-to-top':{'label':'Jump to Top Button', 'type':'toggle', 'default':'no', 'toggles':{
                    'no':'No',
                    'yes':'Yes',
                    }},
                'footer-copyright-name':{'label':'Copyright Name', 'type':'text'},
                'footer-copyright-message':{'label':'Copyright Message', 'type':'textarea'},
//                'footer-social-icons':{'label':'Show Social Icons', 'type':'toggle', 'default':'yes', 'toggles':{'no':'No', 'yes':'Yes'}},
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
//                'account-password-change':{'label':'Allow Password Changes', 'type':'toggle', 'default':'yes', 'toggles':{'no':'No', 'yes':'Yes'}},
                'account-password-change':{'label':'Allow Password Changes', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'account-membership-change':{'label':'Allow Membership Purchases', 'type':'toggle', 'default':'yes', 'toggles':{'no':'No', 'yes':'Yes'}},
                'account-forgot-link-text':{'label':'Forgot Link Text', 'type':'text'},
                'account-create-account-text':{'label':'Create Account Link Text', 'type':'text'},
                'account-create-type':{'label':'Create Account Form', 'type':'select', 'options':{
                    '':'None',
                    'simple':'Simple',
                    'phone-billing':'Phone & Billing Info',
                    }},
                'account-children-update':{'label':'Add/Remove Children', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'account-public-member-info':{'label':'Public Member Info', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'account-shipping-address':{'label':'Shipping Address', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'account-menu-toggle-em':{'label':'Menu Size', 'type':'select', 'default':'40', 'options':{
                    '30':'XX-Small',
                    '40':'X-Small',
                    '50':'Small',
                    '60':'Medium',
                    '70':'Large',
                    '80':'X-Large',
                    '90':'XX-Large',
                    'custom':'Custom (Advanced)',
                    }},
//                'account-allowed-attempts':{'label':'Allowed Attempts', 'type':'text'},
//                'account-lock-hours':{'label':'Lock Hours', 'type':'text'},
            }},
        'accountmenu':{'label':'Account Menu Items', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'account' ? 'yes' : 'no'); },
            'fields':{
            }},
        //
        // cart page settings
        //
        'cartsettings':{'label':'Cart Settings', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cart' ? 'yes' : 'no'); },
            'fields':{
                'cart-active':{'label':'Enable Cart', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'cart-currency-display':{'label':'Display Currency', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'cart-registration-child-select':{'label':'Registration Children', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'cart-child-create-button':{'label':'Create Child Button', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
                'cart-customer-notes':{'label':'Customer Notes', 'type':'toggle', 'default':'no', 'toggles':{'no':'No', 'yes':'Yes'}},
//                'paypal-ec-site':{'label':'Paypal Site', 'type':'toggle', 'default':'sandbox', 
//                    'visible':function() { M.modFlagSet('ciniki.sapos', 0x200000); },
//                    'toggles':{'sandbox':'Sandbox (Test)', 'live':'Live'},
//                    },
//                'paypal-ec-clientid':{'label':'Username', 'type':'text',
//                    'visible':function() { M.modFlagSet('ciniki.sapos', 0x200000); },
//                    },
//                'paypal-ec-password':{'label':'Password', 'type':'text',
//                    'visible':function() { M.modFlagSet('ciniki.sapos', 0x200000); },
//                    },
//                'paypal-ec-signature':{'label':'Signature', 'type':'text',
//                    'visible':function() { M.modFlagSet('ciniki.sapos', 0x200000); },
//                    },
                'stripe-pk':{'label':'Stripe Public Key', 'type':'text',
                    'visible':function() { M.modFlagSet('ciniki.sapos', 0x800000); },
                    },
                'stripe-sk':{'label':'Stripe Secret Key', 'type':'text',
                    'visible':function() { M.modFlagSet('ciniki.sapos', 0x800000); },
                    },
            }},
        'cartmessages':{'label':'Cart Messages', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cart' ? 'yes' : 'no'); },
            'fields':{
                'cart-noaccount-message':{'label':'No Account Message', 'type':'textarea'},
                'cart-regreview-message':{'label':'Review Registrations Message', 'type':'textarea'},
                'cart-bottom-message':{'label':'Below Cart Message', 'type':'textarea'},
                'cart-checkout-message':{'label':'Checkout Message', 'type':'textarea'},
                'cart-etransfer-submitted-message':{'label':'e-transfer Submitted', 'type':'textarea',
                    'visible':function() { return M.modFlagSet('ciniki.sapos', 0x40000000); },
                    },
                'cart-payment-success-message':{'label':'Payment Success', 'type':'textarea'},
                'cart-payment-success-emails':{'label':'Email Notifications', 'type':'text'},
            }},
        'cartdonations':{'label':'Cart Donation Request', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'cart' && M.modFlagOn('ciniki.sapos', 0x02000000) ? 'yes' : 'no'); },
            'fields':{
                'cart-donation-message':{'label':'Message', 'type':'textarea', 'size':'small'},
                'cart-donation-amounts':{'label':'Amounts', 'type':'text'},
                'cart-donation-thankyou':{'label':'Thank You', 'type':'textarea', 'size':'small'},
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
        'meta':{'label':'Meta Tags', 'data':'settings',
            'active':function() { return (M.ciniki_wng_main.site.view == 'meta' ? 'yes' : 'no'); },
            'fields':{
                'meta-google-analytics-account':{'label':'Google Analytics', 'type':'text'},
                'meta-google-site-verification':{'label':'Google Site Verification', 'type':'text'},
                'meta-google-tag-manager':{'label':'Google Tag (gtag)', 'type':'text'},
                'meta-facebook-pixel-id':{'label':'Facebook Pixel ID', 'type':'text'},
                'meta-facebook-domain-verification':{'label':'Facebook Domain Verification', 'type':'text'},
                'meta-fathom-analytics-siteid':{'label':'Fathom Analytics', 'type':'text'},
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
        M.confirm('Are you sure you want to remove this image?',null,function() {
            M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 'view':M.ciniki_wng_main.site.view, 'site_id':M.ciniki_wng_main.site.site_id, 
                'page_id':M.ciniki_wng_main.site.page_id, 'action':'removethemeimage', 'image_id':image_id}, 
                M.ciniki_wng_main.site.openFinish);
/*            M.api.getJSONCb('ciniki.wng.pageDelete', {'tnid':M.curTenantID, 'page_id':M.ciniki_wng_main.site.page_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wng_main.site;
                p.page_id = 0;
                p.view = 'menu';
                p.open();
            }); */
            });
/*        if( confirm("Are you sure you want to remove this image?") ) {
            M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 'view':this.view, 'site_id':this.site_id, 
                'page_id':this.page_id, 'action':'removethemeimage', 'image_id':image_id}, 
                this.openFinish);
        } */
    }
    this.site.fieldValue = function(s, i, d) {
        if( s == 'pagedetails' || s == 'pageredirect' ) {
            if( i == 'page_url' ) {
                return '<a target="_preview" href="' + this.data.page.page_url + '">' + this.data.page.page_url + '</a>';
            }
            return this.data.page != null ? this.data.page[i] : '';
        }
        if( this.sections[s] != null && this.sections[s].data != null && this.sections[s].data == 'settings' ) {
            if( this.data.site.settings[i] != null ) {
                return this.data.site.settings[i];
            }
            return '';
        }
        if( s == '' ) {
            return this.data.page != null ? this.data.page[i] : '';
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
    this.site.switchType = function(e,s,f) {
        this.save("M.ciniki_wng_main.site.openPage(" + this.page_id + ");");
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
                case 0: return d.name + ((d.flags&0x01) == 0 ? ' (Hidden)' : '');
            }
        }
        if( s == 'headersections' || s == 'pagesections' || s == 'footersections' ) {
            switch(j) {
                case 0: return d.label + ((d.flags&0x10) == 0x10 ? ' (Hidden)' : '');
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
        p.sections.accountmenu.fields = {};
        if( rsp['account-menuitems'] != null ) {
            for(var i in rsp['account-menuitems']) {
                p.sections.accountmenu.fields['account-menu-' + rsp['account-menuitems'][i].pkg + '-' + rsp['account-menuitems'][i].mod] = {
                    'label':rsp['account-menuitems'][i].name,
                    'type':'toggle',
                    'default':'auto',
                    'toggles':{'auto':'Auto', 'off':'Off', 'on':'On'},
                    };
            }
        }
/*        if( M.emWidth() < 70 ) {
            p.size = 'large';
            if( p.view == 'menu' ) {
                p.view_aside = 'yes';
                p.view_content == 'no';
            } else {
                p.view_aside = 'no';
                p.view_content == 'yes';
            }
        } else { */
            if( p.view == 'menu' ) {
                p.size = 'xlarge narrowaside';
                p.view_aside = 'yes';
                p.view_content = 'yes';
            } else {
                p.size = 'xlarge narrowaside';
                p.view_aside = 'yes';
                p.view_content = 'yes';
            }
/*        } */
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
        M.confirm('Are you sure you want to remove this page and the sections?',null,function() {
            M.api.getJSONCb('ciniki.wng.pageDelete', {'tnid':M.curTenantID, 'page_id':M.ciniki_wng_main.site.page_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wng_main.site;
                p.page_id = 0;
                p.view = 'menu';
                p.open();
            });
        });
    }
    this.site.clearCache = function() {
        M.api.getJSONCb('ciniki.wng.cacheClear', {'tnid':M.curTenantID, 'site_id':this.site_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.alert('Cache cleared');
            M.ciniki_wng_main.site.open();
        });
    }
    this.site.rebuildIndex = function(clr) {
        M.api.getJSONCb('ciniki.wng.siteIndexRefresh', {'tnid':M.curTenantID, 'site_id':this.site_id, 'clear':clr}, function(rsp) {
            if( rsp.stat == 'outatime' ) {
                M.ciniki_wng_main.site.rebuildIndex(clr);
            } else {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                if( clr == 'yes' ) {
                    M.alert("Index Rebuilt");
                } else {
                    M.alert("Index Updated");
                }
                M.ciniki_wng_main.site.open();
            }
        });

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
    // Override to stop save pos and always return to top
    this.site.savePos = function() {
        return true;
    }


    //
    // The panel to edit Page
    //
    this.edit = new M.panel('Page', 'ciniki_wng_main', 'edit', 'mc', 'medium', 'sectioned', 'ciniki.wng.main.edit');
    this.edit.data = {};
    this.edit.page_id = 0;
    this.edit.sections = {
        'parent':{'label':'New page under', 'fields':{
            'parent_id':{'label':'', 'hidelabel':'yes', 'required':'yes', 'type':'select', 
                'options':{},
                'complex_options':{'name':'name', 'value':'id'},
                'onchange':'M.ciniki_wng_main.edit.updateForm',
                },
            }},
        'details':{'label':'', 'fields':{
            'title':{'label':'Menu Title', 'required':'yes', 'type':'text'},
            'page_title':{'label':'Page Title', 'type':'text'},
            'sequence':{'label':'Page Order', 'type':'text', 'size':'small'},
            'menu_flags':{'label':'Menu Options', 'type':'flags', 'visible':'no', 'flags':{'1':{'name':'Header'},'2':{'name':'Footer'}}},
            'flags1':{'label':'Visible', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'on'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wng_main.edit.save();'},
            }},
        };
    this.edit.updateForm = function(s, i) {
        var parent_id = this.formFieldValue(this.sections.parent.fields.parent_id, 'parent_id');
        if( parent_id == M.ciniki_wng_main.site.data.site.homepage_id ) {
            this.sections.details.fields.menu_flags.visible = 'yes';
        } else {
            this.sections.details.fields.menu_flags.visible = 'no';
        }
        this.showHideFormField('details', 'menu_flags');
    }
    this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
    this.edit.open = function(cb, pid, sid) {
        this.page_id = pid;
        this.site_id = sid;
        if( pid != null && pid > 0 ) {
            M.api.getJSONCb('ciniki.wng.pageGet', {'tnid':M.curTenantID, 'page_id':this.page_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wng_main.edit;
                p.sections.parent.fields.parent_id.options = M.ciniki_wng_main.site.data.pagelist;
                p.data = rsp.page;
                p.refresh();
                p.show(cb);
            });
        } else {
            this.reset();
            this.data = {};
            this.sections.parent.fields.parent_id.options = M.ciniki_wng_main.site.data.pagelist;
            this.refresh();
            this.show(cb);
            this.updateForm();
        }
    }
    this.edit.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_wng_main.edit.close();'; }
        if( !this.checkForm() ) { return false; }
        if( this.page_id != null && this.page_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wng.pageUpdate', {'tnid':M.curTenantID, 'page_id':this.page_id, 'site_id':this.site_id}, c, function(rsp) {
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
    }
    this.edit.addButton('save', 'Save', 'M.ciniki_wng_main.edit.save();');
    this.edit.addClose('Cancel');

    //
    // The panel to edit a section
    //
    this.section = new M.panel('Section', 'ciniki_wng_main', 'section', 'mc', 'large', 'sectioned', 'ciniki.wng.main.section');
    this.section.data = null;
    this.section.section_id = 0;
    this.section.site_id = 0;
    this.section.ref = 0;
    this.section.curDragging = 0;
    this.section.nplist = [];
    this.section.sections = {
        'general':{'label':'', 'aside':'yes', 'fields':{
            'ref':{'label':'Section Content', 'type':'select', 'options':{}, 
                'onchange':'M.ciniki_wng_main.section.setSectionOptions',
                },
            'label':{'label':'Label', 'required':'yes', 'type':'text', 'size':'medium'},
            'sequence':{'label':'Order', 'required':'yes', 'type':'text', 'size':'small'},
            'flags5':{'label':'Hidden', 'type':'flagtoggle', 'bit':0x10, 'field':'flags', 'default':'on'},
            }},
        '_settings':{'label':'Settings', 'visible':'hidden', 'aside':'yes', 'fields':{
            }},
        'repeats':{'label':'Repeats', 'type':'simplegrid', 'num_cols':1,
            'visible':'hidden', 
            'headerValues':[],
            'cellClasses':[],
            'dataMaps':[],
            'addFn':'M.ciniki_wng_main.section.save("M.ciniki_wng_main.section.editRepeat(0);");',
            'seqDrop':function(e,from,to) {
                M.ciniki_wng_main.section.moveRepeats(from,to);
/*                M.api.getJSONCb('ciniki.wng.site', {'tnid':M.curTenantID, 
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
                    });*/
                },
            },
        '_buttons':{'label':'', 'aside':'no', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wng_main.section.save();'},
            'delete':{'label':'Delete', 
                'visible':function() {return M.ciniki_wng_main.section.section_id > 0 ? 'yes' : 'no'; },
                'fn':'M.ciniki_wng_main.section.remove();'},
            }},
        };
    this.section.fieldValue = function(s, i, d) { 
        if( s == '_settings' ) {
            if( this.data.settings[i] != null && this.data.settings[i] != 'undefined' ) {
                return this.data.settings[i];
            } else {
                return '';
            }
        }
        return this.data[i]; 
    }
    this.section.cellValue = function(s, i, j, d) {
        if( s == 'repeats' && this.sections[s].dataMaps[j] != null ) {
            if( this.sections[s].cellClasses[j] != null
                && this.sections[s].cellClasses[j] == 'thumbnail'
                ) {
                if( d[this.sections[s].dataMaps[j]] > 0 ) {
                    return '<img width="75px" height="75px" src=\'' + M.api.getBinaryURL('ciniki.images.get',{'tnid':M.curTenantID, 'image_id':d[this.sections[s].dataMaps[j]], 'version':'thumbnail', 'maxwidth':'75'}) + '\'/>';
                } else {
                    return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\'/>';
                }
            }
            else if( this.sections[s].cellClasses[j] != null
                && this.sections[s].cellClasses[j] == 'page-link'
                ) {
                if( d[this.sections[s].dataMaps[j]] > 0 ) {
                    for(var k in M.ciniki_wng_main.site.data.pagelist) {
                        if( M.ciniki_wng_main.site.data.pagelist[k].id == d[this.sections[s].dataMaps[j]] ) {
                            return M.ciniki_wng_main.site.data.pagelist[k].name;
                        }
                    }
                } else if( d[this.sections[s].dataMaps[j]] == 0 ) {
                    if( d[this.sections[s].dataMaps[j].replace(/page/,'url')] != null ) {
                        return d[this.sections[s].dataMaps[j].replace(/page/,'url')];
                    }
                }
                return '';
            }
            return d[this.sections[s].dataMaps[j]];
        }
    }
    this.section.rowFn = function(s, i, d) {
        if( s == 'repeats' ) {
            return 'M.ciniki_wng_main.section.save("M.ciniki_wng_main.section.editRepeat(\'' + i + '\');");';
        }
        return '';
    }
    this.section.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wng.sectionHistory', 'args':{'tnid':M.curTenantID, 'section_id':this.section_id, 'field':i}};
    }
    this.section.editRepeat = function(i) {
        if( i == 0 ) {
            M.ciniki_wng_main.sectionrepeat.data = {};
            i++;
            while(i <= 100 ) {
                if( this.data.repeats == null || this.data.repeats[i] == null ) {
                    break;
                }
                i++;
            }
        } 
        else if( this.data.repeats[i] != null ) {
            M.ciniki_wng_main.sectionrepeat.data = this.data.repeats[i];
        }
        M.ciniki_wng_main.sectionrepeat.open('M.ciniki_wng_main.section.open();',i,this.section_id);
    }
    this.section.moveRepeats = function(from, to) {
        if( this.data.repeats[from] != null
            && this.data.repeats[to] != null 
            && from != to 
            ) {
            from = parseInt(from);
            to = parseInt(to);
            var c = this.serializeForm('no');
            for(var i in M.ciniki_wng_main.section.data.repeats[from]) {
                c += i + '-' + to + '=' + M.eU(this.data.repeats[from][i]) + '&';
            }
            if( from > to ) {
                for(var j = from; j > to; j--) {
                    for(var i in M.ciniki_wng_main.section.data.repeats[(j-1)]) {
                        c += i + '-' + j + '=' + M.eU(this.data.repeats[(j-1)][i]) + '&';
                    }
                }
            } else {
                for(var j = from; j < to; j++) {
                    for(var i in M.ciniki_wng_main.section.data.repeats[(j+1)]) {
                        c += i + '-' + j + '=' + M.eU(this.data.repeats[(j+1)][i]) + '&';
                    }
                }
            }
            if( c != '' ) {
                M.api.postJSONCb('ciniki.wng.sectionUpdate', {'tnid':M.curTenantID, 'section_id':this.section_id, 'site_id':this.site_id, 'page_id':this.page_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_wng_main.section.open();
                });
            }
        }
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
                else if( this.sections._settings.fields[i].type != null 
                    && this.sections._settings.fields[i].type == 'select'
                    && this.sections._settings.fields[i].pages != null 
                    && this.sections._settings.fields[i].pages == 'yes'
                    ) {
                    this.sections._settings.fields[i].options = {}
                    this.sections._settings.fields[i].complex_options = {'value':'v', 'name':'l'};
                    var onum=0;
                    this.sections._settings.fields[i].options[onum++] = {'v':'', 'l':'None'};
                    var u_fid = i.replace(/page/, 'url');
                    var t_fid = i.replace(/page/, 'text');
                    if( this.sections._settings.fields[u_fid] != null ) {
                        this.sections._settings.fields[i].options[onum++] = {'v':'0', 'l':'Custom URL'};
                    }
                    if( this.sections._settings.fields[t_fid] != null || this.sections._settings.fields[u_fid] != null ) {
                        this.sections._settings.fields[i].onchange = 'M.ciniki_wng_main.section.showHideSettingFields();';
                    }
                    for(var j in M.ciniki_wng_main.site.data.pagelist) {
                        var p = M.ciniki_wng_main.site.data.pagelist[j];
                        this.sections._settings.fields[i].options[onum] = {'v':p.id, 'l':p.name};
                        onum++;
                    }
                }
            }
            this.sections._settings.visible = 'yes';
        }
        if( this.data.availablesections[ref] != null
            && this.data.availablesections[ref].repeats != null 
            && JSON.stringify(this.data.availablesections[ref].repeats)!=JSON.stringify({})
            && JSON.stringify(this.data.availablesections[ref].repeats)!=JSON.stringify([])
            ) {
            this.size = 'xlarge mediumaside';
            var s = this.data.availablesections[ref].repeats;
            this.sections.repeats.label = s.label;
            this.sections.repeats.num_cols = s.dataMaps != null ? s.dataMaps.length : 1;
            this.sections.repeats.headerValues = s.headerValues != null ? s.headerValues : [];
            this.sections.repeats.cellClasses = s.cellClasses != null ? s.cellClasses : [];
            this.sections.repeats.dataMaps = s.dataMaps != null ? s.dataMaps : [];
            this.sections.repeats.addTxt = s.addTxt != null ? s.addTxt : '';
            this.sections.repeats.visible = 'yes';
            M.ciniki_wng_main.sectionrepeat.sections.fields.fields = s.fields;
        } else {
            this.size = 'large';
            this.sections.repeats.visible = 'hidden';
        }
        var e = M.gE(this.panelUID).children[0].className = this.size;
            
        this.refreshSection("_settings");
        this.refreshSection("repeats");
        this.showHideSection("_settings", "repeats");
        this.showHideSettingFields();
    }
    this.section.showHideSettingFields = function() {
        var prev_draggable = 0;
        for(var i in this.sections._settings.fields) {
            if( this.sections._settings.fields[i].type != null 
                && this.sections._settings.fields[i].type == 'select'
                && this.sections._settings.fields[i].pages != null 
                && this.sections._settings.fields[i].pages == 'yes'
//                && this.sections._settings.fields[i].url != null 
//                && this.sections._settings.fields[i].url != ''
                ) {
                var t_fid = i.replace(/page/, 'text');
                var u_fid = i.replace(/page/, 'url');
                var v = this.formValue(i);
                if( this.sections._settings.fields[u_fid] != null ) {
                    if( v != '' && v == 0 ) {
                        this.sections._settings.fields[u_fid].visible = 'yes';
                    } else {
                        this.sections._settings.fields[u_fid].visible = 'no';
                    }
                    this.showHideFormField('_settings', u_fid);
                }
                if( this.sections._settings.fields[t_fid] != null ) {
                    if( v != '' ) {
                        this.sections._settings.fields[t_fid].visible = 'yes';
                    } else {
                        this.sections._settings.fields[t_fid].visible = 'no';
                    }
                    this.showHideFormField('_settings', t_fid);
                }
            }
            if( this.sections._settings.fields[i].draggable != null 
                && this.sections._settings.fields[i].draggable != prev_draggable 
                ) {
                var f = this.sections._settings.fields[i];
                var e = M.gE(this.panelUID + '_' + i).parentNode.parentNode;
                e.setAttribute('draggable',true);
                e.classList.add('draggable');
                e.setAttribute('ondragstart', 'M.ciniki_wng_main.section.dragStart(event,"' + f.draggable + '");');
                e.setAttribute('ondrop', 'M.ciniki_wng_main.section.dragDrop(event,"' + f.draggable + '");');
                e.addEventListener('dragenter', function(e) {
                    this.classList.add('drophighlight');
                    }, false);
                e.addEventListener('dragleave', function(e) {
                    this.classList.remove('drophighlight');
                    }, false);
                prev_draggable = this.sections._settings.fields[i].draggable;
            }
        }
    }
    this.section.dragStart = function(e, i) {
        this.curDragging = i;
    }
    this.section.dragDrop = function(e, d) {
        var from = this.curDragging;
        var to = d;
        if( from == to ) {
            return true;
        }
        if( from < to ) {
            from = d;
            to = this.curDragging;
        }
        for(var i in this.sections._settings.fields) {
            if( this.sections._settings.fields[i].draggable != null && this.sections._settings.fields[i].draggable == to ) {
                var to_field = this.sections._settings.fields[i];
                var to_value = this.formValue(i);
                var from_id = i.replace(to, from);
                if( this.sections._settings.fields[from_id] != null ) {
                    var from_field = this.sections._settings.fields[from_id];
                    var from_value = this.formValue(from_id);
                    this.setFieldValue(i, from_value);
                    this.setFieldValue(from_id, to_value);
                }
            }
        }
        this.showHideSettingFields();
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
            p.sections.general.fields.ref.options[''] = 'Select a section';
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
    // The panel to edit a type name card
    //
    this.sectionrepeat = new M.panel('Type Name Card', 'ciniki_wng_main', 'sectionrepeat', 'mc', 'medium', 'sectioned', 'ciniki.wng.main.sectionrepeat');
    this.sectionrepeat.repeatnum = 0;
    this.sectionrepeat.sections = {
        'fields':{'label':'', 'fields':{}},
        '_buttons':{'label':'', 'aside':'no', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wng_main.sectionrepeat.save();'},
            'delete':{'label':'Delete', 
                'fn':'M.ciniki_wng_main.sectionrepeat.remove();'},
            }},
    };
    this.sectionrepeat.fieldValue = function(s, i, d) { 
        return this.data[i];
    };
//    this.sectionrepeat.fieldHistoryArgs = function(s, i) {
//        return {'method':'ciniki.wng.settingsHistory', 'args':{'tnid':M.curTenantID, 'field':i}};
//    };
/*    this.sectionrepeat.addDropImage = function(iid) {
        M.ciniki_wng_main.sectionrepeat.setFieldValue('image', iid);
        return true;
    }
    this.sectionrepeat.deleteImage = function(fid) {
        this.setFieldValue(fid, 0);
        return true;
    } */
    this.sectionrepeat.open = function(cb,i,sid) {
        this.repeatnum = i;
        this.section_id = sid;
        for(var i in this.sections.fields.fields) {
            if( this.sections.fields.fields[i].type != null 
                && this.sections.fields.fields[i].type == 'image_id'
                ) {
                this.sections.fields.fields[i].addDropImage = new Function('iid', 
                    'M.ciniki_wng_main.sectionrepeat.setFieldValue(\'' + i + '\',iid); '
                    + 'return true;');
                this.sections.fields.fields[i].deleteImage = new Function('iid', 
                    'M.ciniki_wng_main.sectionrepeat.setFieldValue(\'' + i + '\',0); '
                    + 'return true;');
            }
            else if( this.sections.fields.fields[i].type != null 
                && this.sections.fields.fields[i].type == 'select'
                && this.sections.fields.fields[i].pages != null 
                && this.sections.fields.fields[i].pages == 'yes'
                ) {
                this.sections.fields.fields[i].options = {}
                this.sections.fields.fields[i].complex_options = {'value':'v', 'name':'l'};
                var onum=0;
                this.sections.fields.fields[i].options[onum++] = {'v':'', 'l':'None'};
                var u_fid = i.replace(/page/, 'url');
                var t_fid = i.replace(/page/, 'text');
                if( this.sections.fields.fields[u_fid] != null ) {
                    this.sections.fields.fields[i].options[onum++] = {'v':'0', 'l':'Custom URL'};
                }
                if( this.sections.fields.fields[t_fid] != null || this.sections.fields.fields[u_fid] != null ) {
                    this.sections.fields.fields[i].onchange = 'M.ciniki_wng_main.sectionrepeat.showHideSettingFields();';
                }
                for(var j in M.ciniki_wng_main.site.data.pagelist) {
                    var p = M.ciniki_wng_main.site.data.pagelist[j];
                    this.sections.fields.fields[i].options[onum] = {'v':p.id, 'l':p.name};
                    onum++;
                }
            }
        }
        this.refresh();
        this.show(cb);
        this.showHideSettingFields();
    }
    this.sectionrepeat.showHideSettingFields = function() {
        var prev_draggable = 0;
        for(var i in this.sections.fields.fields) {
            if( this.sections.fields.fields[i].type != null 
                && this.sections.fields.fields[i].type == 'select'
                && this.sections.fields.fields[i].pages != null 
                && this.sections.fields.fields[i].pages == 'yes'
                ) {
                var t_fid = i.replace(/page/, 'text');
                var u_fid = i.replace(/page/, 'url');
                var v = this.formValue(i);
                if( this.sections.fields.fields[u_fid] != null ) {
                    if( v != '' && v == 0 ) {
                        this.sections.fields.fields[u_fid].visible = 'yes';
                    } else {
                        this.sections.fields.fields[u_fid].visible = 'no';
                    }
                    this.showHideFormField('fields', u_fid);
                }
                if( this.sections.fields.fields[t_fid] != null ) {
                    if( v != '' ) {
                        this.sections.fields.fields[t_fid].visible = 'yes';
                    } else {
                        this.sections.fields.fields[t_fid].visible = 'no';
                    }
                    this.showHideFormField('fields', t_fid);
                }
            }
        }
    }
    this.sectionrepeat.save = function() {
        var c = '';
        for(var i in this.sections.fields.fields) {
            var n = this.formFieldValue(this.sections.fields.fields[i], i);
            if( n != this.data[i] && n != null && n != 'undefined' ) {
                c += encodeURIComponent(i + '-' + this.repeatnum) + '=' + encodeURIComponent(n) + '&';
            }
        }
        if( c != '' ) {
            console.log(c);
            M.api.postJSONCb('ciniki.wng.sectionUpdate', {'tnid':M.curTenantID, 'site_id':M.ciniki_wng_main.section.site_id, 'section_id':this.section_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
                M.ciniki_wng_main.sectionrepeat.close();
            });
        } else {
            M.ciniki_wng_main.sectionrepeat.close();
        } 
    }
    this.sectionrepeat.remove = function() {
        if( this.repeatnum > 0 ) {
            M.confirm('Are you sure you want to remove this item?',null,function() {
                M.api.getJSONCb('ciniki.wng.sectionUpdate', {'tnid':M.curTenantID, 
                    'site_id':M.ciniki_wng_main.section.site_id,
                    'section_id':M.ciniki_wng_main.sectionrepeat.section_id, 
                    'delete_repeat':M.ciniki_wng_main.sectionrepeat.repeatnum,
                    }, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_wng_main.sectionrepeat.close();
                });
            });
        } else {
            M.alert('No item to be removed');
        }
    }
    this.sectionrepeat.addButton('save', 'Save', 'M.ciniki_wng_main.sectionrepeat.save();');
    this.sectionrepeat.addClose('Cancel');

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
