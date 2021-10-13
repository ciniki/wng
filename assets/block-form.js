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
    /* aa = API args */
    aa: {},
    /* Switch Section */
    sS: function(i) {
        this.qSave();
        C.rC(C.gE('b-' + this.cs), 'selected');
        C.rC(C.gE('s-' + this.cs), 'selected');
        C.aC(C.gE('b-' + i), 'selected');
        C.aC(C.gE('s-' + i), 'selected');
        this.cs = i;
    },
    /* Switch Repeat tab */
    sR: function(s,i) {
        this.qSave();
        C.rC(C.gE('t-' + this.cs + '-' + this.cr), 'selected');
        C.rC(C.gE('s-' + this.cs + '-' + this.cr), 'selected');
        C.aC(C.gE('t-' + this.cs + '-' + i), 'selected');
        C.aC(C.gE('s-' + this.cs + '-' + i), 'selected');
        this.cr = i;
    },
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
                if( inputs[i].type != 'file' ) {
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
        console.log(rsp);
        if( rsp.api_args != null ) {   
            this.aa = rsp.api_args;
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
        console.log(rsp);
        if( rsp.api_args != null ) {
            this.aa = rsp.api_args;
        }
        if( rsp.image_urls != null ) {
            for(var i in rsp.image_urls) {
                var e = C.gE('p-' + i);
                if( e != null ) {
                    e.innerHTML = "<img src='" + rsp.image_urls[i] + "'/>";
                }
            }
        }
    },
    /* Image Clear */
    iC: function(s, f) {
        this.aa['f-' + f] = '0';
        C.getBg(this.ssu, this.aa, C.form.qSaved);
        delete(this.aa['f-'+f]);
    },
    start: function(e, s, ssu, ipu, aa) {
        this.cs = s;
        this.cr = 1;
        this.ssu = ssu;
        this.aa = aa;
        this.ipu = ipu;
    }
};
