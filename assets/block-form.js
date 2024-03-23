C.form = {
    // sid = Submission ID
    sid: 0,
    // cs = current section
    cs: '', 
    /* cr = current repeat on the current section, currently designed for only 1 repeatable seciton per form */
    cr: 1,
    /* ssu = API url for submission save */
    ssu: '',
    /* ipu = API url for submission image url */
    ipu: '',
    /* csu = API url for add the submission fee to the cart */
    csu: '',
    /* fcu = API url for performing a form check and validate fields */
    fcu: '',
    /* jsc = Javascript Formula Calculations */
    jsc: {},
    /* aa = API args */
    aa: {},
    /* Switch Section */
    sS: function(i) {
        this.qSave();
        C.rC(C.gE('b-' + this.cs), 'selected');
        C.rC(C.gE('s-' + this.cs), 'selected');
        C.aC(C.gE('b-' + i), 'selected');
        var e = C.gE('s-' + i)
        C.aC(e, 'selected');
        window.scroll(0,e.offsetTop);
        this.cs = i;
    },
    /* Switch Repeat tab */
    sR: function(s,i) {
        this.qSave();
        C.rC(C.gE('t-' + this.cs + '-' + this.cr), 'selected');
        C.rC(C.gE('s-' + this.cs + '-' + this.cr), 'selected');
        C.aC(C.gE('t-' + this.cs + '-' + i), 'selected');
        C.aC(C.gE('s-' + this.cs + '-' + i), 'selected');
        window.scroll(0,C.gE('s-'+this.cs).offsetTop);
        this.cr = i;
    },
    /* Switch select options */
    oS: function(e,i,uf) {
        console.log(i);
        console.log(uf);
    },
    /* Queue saving of form section */
    qSave: function() {
        /* Check if form is to be updated via api */
        if( this.ssu != null && this.ssu != '' ) {
            /* Check if the section is repeatable */
            var e = C.gE('b-' + this.cs);
            if( e != null ) {
                if( e.classList.contains('repeatable') ) {
                    var d = C.gE('s-' + this.cs + '-' + this.cr);
                } else {
                    var d = C.gE('s-' + this.cs);
                }
            } else {    
                /* Load entire form */
                var d = C.gE('s-' + this.cs).parentNode;
            }
            var fD = new FormData;
            var inputs = d.getElementsByTagName('input');
            for(var i = 0; i < inputs.length; i++) {
                if( inputs[i].type == 'checkbox' ) {
                    fD.append(inputs[i].id, (inputs[i].checked ? 'on' : 'off'));
                } else if( inputs[i].type == 'radio' ) {
                    if( inputs[i].checked ) {
                        fD.append(inputs[i].name, inputs[i].value);
                    }
                } else if( inputs[i].type != 'file' ) {
                    fD.append(inputs[i].id, inputs[i].value);
                }
            }
            var inputs = d.getElementsByTagName('select');
            for(var i = 0; i < inputs.length; i++) {
                fD.append(inputs[i].id, inputs[i].value);
            }
            var inputs = d.getElementsByTagName('textarea');
            for(var i = 0; i < inputs.length; i++) {
                fD.append(inputs[i].id, inputs[i].value);
            }
            C.postFDBg(this.ssu, this.aa, fD, C.form.qSaved);
        }
    },
    qSaved: function(rsp) {
        if( rsp.stat != 'ok' ) {
            C.form.showErrors(rsp, "Oops, we had a problem saving, please click on save at the bottom or refresh the page.");
        } else {
//            C.form.clearErrors();
            C.form.updateLastSaved(rsp);
        }
        if( rsp.api_args != null ) {   
            this.aa = rsp.api_args;
        }
    },
    /* Check textarea word count */
    wC: function(e,i,m) {
        var len = e.target.value.split(/[\s]+/);
        if( len.length == 1 && len[0] == '' ) {
            len = [];
        }
        C.gE('wc-' + i).innerHTML = len.length + ' words';
        if( len.length > m) {
            C.aC(C.gE('wc-'+i),'error');
        } else {
            C.rC(C.gE('wc-'+i),'error');
        }
    },
    /* check for required fields and validate against server */
    validate: function() {
        /* Submit to cart */
        if( this.fcu != null && this.fcu != '' ) {
            C.getBg(this.fcu, this.aa, C.form.validated);
        }
    },
    validated: function(rsp) {
        if( rsp.stat == 'fail' && rsp.problems != null ) {
            C.form.showErrors(rsp);
        } else if( rsp.stat == 'ok' ) {
            console.log('validated');
        }
    },
    cartSubmit: function() {
        /* Submit to cart */
        if( this.csu != null && this.csu != '' ) {
            C.getBg(this.csu, this.aa, C.form.cartSubmitted);
        }
    },
    cartSubmitted: function(rsp) {
        if( rsp.stat != 'ok' ) {
            C.form.showErrors(rsp, "Unable to add to your cart, please contact us for help.");
        } else {
            if( rsp.api_args != null ) {   
                this.aa = rsp.api_args;
            }
            if( rsp.redirect_url != null ) {
                window.location.href = rsp.redirect_url;
            }
        }
    },
    showErrors: function(rsp,m) {
        var e = C.gE('form-errors');
        C.rC(e, 'hidden');
        if( rsp.problems != null ) {
            var msg = "<p>The following fields need to be completed:</p><p>";
            for(var i in rsp.problems) {
                msg += rsp.problems[i] + '<br/>';
            }
            msg += '</p>';
            C.gE('form-errors-msg').innerHTML = msg;
        } else {
            C.gE('form-errors-msg').innerHTML = '<p>' + m + '</p>';
        }
        window.scroll(0,e.offsetTop);
    },
    clearErrors: function(rsp) {
        var e = C.gE('form-errors');
        C.aC(e, 'hidden');
    },
    updateLastSaved: function(rsp) {
        var e = C.gE('form-last-saved-msg');
        if( e != null && rsp != null && rsp.last_saved != null ) {
            C.rC(e, 'hidden');
            e.innerHTML = 'Last saved: ' + rsp.last_saved;
        }
    },
    calc: function() {
        eval(this.jsc);
    },
    iV: function(f) {
        var v=C.gE(f).value;
        if( v != null && v != '' ) {
            return parseInt(v);
        }
        return 0;
    },
    /* File Upload */
    fU: function(f) {
        C.gE('file-'+f).click();
    },
    fUN: function(f) {
        var file=C.gE('file-'+f);
        if(file!=null&&file.files!=null&&file.files[0].name!=null){
            C.gE('f-'+f).value = file.files[0].name;
        }
    },
    /* Image Upload */
    iU: function(e,s,f) {
        C.rC(C.gE('l-' + f), 'hidden');
        C.aC(C.gE('p-' + f), 'hidden');
        var i = C.gE('f-' + f);
        if( i != null && i.files != null && i.files[0] != null ) {
            var fD = new FormData;
            fD.append('f-' + f, i.files[0]);
            C.postFDBg(this.ssu, this.aa, fD, function(rsp) {
                C.aC(C.gE('l-' + f), 'hidden');
                C.rC(C.gE('p-' + f), 'hidden');
                if( rsp.err != null && rsp.err.err != null && rsp.err.err.problem != null 
                    && (rsp.err.err.problem == 'tosmall' || rsp.err.err.problem == 'tolarge' || rsp.err.err.problem == 'other' ) ) {
                    C.form.showErrors(rsp, rsp.err.err.msg);
                }
                if( rsp.api_args != null ) {
                    this.aa = rsp.api_args;
                }
                if( rsp.image_urls != null ) {
                    C.form.iUU(rsp);
                }
                });
        }
    },
    /* Image Update URLS */
    iUU: function(rsp) {
        for(var i in rsp.image_urls) {
            var e = C.gE('p-' + i);
            if( e != null ) {
                e.innerHTML = "<img src='" + rsp.image_urls[i] + "'/>";
            }
        }
    },
    /* Image Preview - Simple forms */
    iP: function(e,f) {
        var p=C.gE('p-'+f);
        if( e.target.files[0] != null ) {
            p.children[0].src=URL.createObjectURL(e.target.files[0]);
            p.children[0].onload = function() {
                URL.revokeObjectURL(p.children[0].src); // free memory
            }
        } else {
            p.children[0].src='';
        }
    },
    /* Image Preview Clear - Simple forms */
    iPC: function(f) {
        var e=C.gE('f-'+f);
        e.value = '';
        var p=C.gE('p-'+f);
        p.children[0].src='';
    },
    /* Image Clear */
    iC: function(f) {
        this.aa['f-' + f] = '0';
        C.getBg(this.ssu, this.aa, function(rsp) {
            if( rsp.stat != 'ok' ) {
                C.form.showErrors(msg, 'Unable to clear image, please try again or contact us for help');
            } else {
                C.form.iUU(rsp);
            }
            });
        delete(this.aa['f-'+f]);
    },
    iCleared: function(rsp) {
    },
    sTOU: function() {
        C.tC(C.gE('tou-message'), 'hidden');
    },
    start: function(e, s, ssu, ipu, fcu, csu, jsc, aa) {
        this.cs = s;
        this.cr = 1;
        this.ssu = ssu;
        this.ipu = ipu;
        this.fcu = fcu;
        this.csu = csu;
        this.jsc = jsc;
        this.aa = aa;
    }
};
