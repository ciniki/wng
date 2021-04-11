C.carousel = {
    cur: 0,
    prev: function() {
        console.log('prev');
        this.open(C.carousel.cur-1);
    },
    next: function() {
        console.log('next');
        this.open(C.carousel.cur+1);
    },
    open: function(i) {
        var e = C.gE('carousel-items');
        var max = (e.children.length - 1);
        if( i < 0 ) {
            i = max;
        } else if( i > max ) {
            i = 0;
        }
        console.log('goto: ' + i);
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
    },
};
