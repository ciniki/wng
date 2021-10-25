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
    /* Queue saving of form section */
    qSave: function() {
        /* Check if form is to be updated via api */
        if( this.ssu != null && this.ssu != '' ) {
            /* Check if the section is repeatable */
            var e = C.gE('b-' + this.cs);
            if( e.classList.contains('repeatable') ) {
                var d = C.gE('s-' + this.cs + '-' + this.cr);
            } else {
                var d = C.gE('s-' + this.cs);
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
            /* FIXME: add error handling */
        }
        if( rsp.api_args != null ) {   
            this.aa = rsp.api_args;
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
        console.log(rsp);
        if( rsp.stat == 'fail' && rsp.problems != null ) {
            alert("You are still missing required fields.");
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
        if( rsp.stat == 'fail' && rsp.problems != null ) {
            alert("You have incomplete fields, please review your form and complete all required fields.");
        } else if( rsp.stat != 'ok' ) {
            alert("Error adding to your cart, please contact us for help.");
        } else {
            if( rsp.api_args != null ) {   
                this.aa = rsp.api_args;
            }
            if( rsp.redirect_url != null ) {
                window.location.href = rsp.redirect_url;
            }
        }
    },
    /* Image Upload */
    iU: function(e,s,f) {
        C.gE('p-' + f).innerHTML = "Loading...";
        var i = C.gE('f-' + f);
        if( i != null && i.files != null && i.files[0] != null ) {
            var fD = new FormData;
            fD.append('f-' + f, i.files[0]);
            C.postFDBg(this.ssu, this.aa, fD, C.form.iPU);
        }
    },
    /* Image Preview Update */
    iPU: function(rsp) {
        if( rsp.api_args != null ) {
            this.aa = rsp.api_args;
        }
        if( rsp.image_urls != null ) {
            C.form.iUU(rsp);
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
    /* Image Clear */
    iC: function(s, f) {
        this.aa['f-' + f] = '0';
        C.getBg(this.ssu, this.aa, function(rsp) {
            if( rsp.stat != 'ok' ) {
                alert('Unable to clear image, please try again or contact us for help');
            } else {
                C.form.iUU(rsp);
            }
            });
        delete(this.aa['f-'+f]);
    },
    iCleared: function(rsp) {
    },
    sTOU: function() {
        console.log('unhide');
        C.tC(C.gE('tou-message'), 'hidden');
    },
    start: function(e, s, ssu, ipu, fcu, csu, aa) {
        this.cs = s;
        this.cr = 1;
        this.ssu = ssu;
        this.ipu = ipu;
        this.fcu = fcu;
        this.csu = csu;
        this.aa = aa;
    }
};
