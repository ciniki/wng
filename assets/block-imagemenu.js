C.imagemenu = {
    toggle: function(m) {   
        /* Close other menus if open */
        var e = document.getElementsByClassName("hamburger-menu-nav");
        if( e != null ) {
            for(var i in e) {
                if( e[i].id != null && e[i].id != ('block-imagemenu-hamburger-menu-' + m) ) {
                    C.aC(e[i], 'hidden');
                }
            }
        }
        C.tC(C.gE('block-imagemenu-hamburger-menu-' + m), 'hidden');
    },
    mT: function(i) {
        var e = C.gE(i);
        C.tC(e,'dd-show');
    },
};
