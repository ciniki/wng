C.tablefilter = {
    selector: 'tr',
    cb: '',
    init: function(e,id,selector,cb) {
        this.selector = selector;
        this.cb = cb;
    },
    update: function(id) {
        var e=C.gE('tablefilter-'+id);
        var rows = document.querySelectorAll(this.selector);
        var regexp = new RegExp(e.value, 'i');
        for(var i = 0; i < rows.length; i++) {
            if( rows[i].innerText != null && rows[i].innerText.match(regexp) ) {
                C.rC(rows[i], 'hidden');
            } else {
                C.aC(rows[i], 'hidden');
            }
        }
        if( this.cb != '' ) {
            this.cb(e.value);
        }
    },
};
