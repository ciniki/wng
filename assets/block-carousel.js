C.carousel = {
    cur: {},
    delay: {},
    timer: {},
    prev: function(id) {
        this.open(id,C.carousel.cur[id]-1);
    },
    next: function(id) {
        this.open(id,C.carousel.cur[id]+1);
    },
    open: function(id,i) {
        clearTimeout(this.timer[id]);
        var e = C.gE('carousel-items-'+id);
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
        this.cur[id] = i;
        if( this.delay[id] > 0 ) {
            this.timer[id] = setTimeout(function(){C.carousel.next(id);},this.delay[id]);
        }
    },
    start: function(e,id,d) {
        console.log('starting');
        this.cur[id] = 0;
        this.delay[id] = d;
        /* Add extra delay for first slide, give time to get everything loaded */
        if( d > 0 ) {
            this.timer[id] = setTimeout(function(){C.carousel.next(id);},(d+3000));
        }
    }
};
