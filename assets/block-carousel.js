C.carousel = {
    cur: 0,
    delay: 0,
    timer: null,
    prev: function() {
        this.open(C.carousel.cur-1);
    },
    next: function() {
        this.open(C.carousel.cur+1);
    },
    open: function(i) {
        clearTimeout(this.timer);
        var e = C.gE('carousel-items');
        var max = (e.children.length - 1);
        if( i < 0 ) {
            i = max;
        } else if( i > max ) {
            i = 0;
        }
        for(var j in e.children) {
            if( j == i ) {
                e.children[j].className = 'item current';
            } else if( j == (i-1) || (j == max && i == 0) ) {
                e.children[j].className = 'item prev';
            } else if( j == (i+1) || (j == 0 && i == max) ) {
                e.children[j].className = 'item next';
            } else {
                e.children[j].className = 'item';
            }
        }
        this.cur = i;
        if( this.delay > 0 ) {
            this.timer = setTimeout(function(){C.carousel.next();},this.delay);
        }
    },
    start: function(e,d) {
        this.delay = d;
        /* Add extra delay for first slide, give time to get everything loaded */
        this.timer = setTimeout(function(){C.carousel.next();},(d+3000));
    }
};
