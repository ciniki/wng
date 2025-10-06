C.tablefilter = {
    selector: 'tr',
    init: function(e,id,selector) {
        this.selector = selector;
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
    },
};
