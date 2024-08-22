C.livesearch = {
    searches: {},
    init: function(e,id,url,args) {
        this.searches[id] = {
            'url':url,
            'args':args,
            'curnum':0,
            'disnum':0,
            };
        this.update(id);
    },
    update: function(id) {
        var e=C.gE('livesearch-'+id);
        var num=++this.searches[id].curnum;
        this.searches[id].args.search_string = e.value;
        C.getBg(this.searches[id].url,this.searches[id].args,function(rsp) {
            if( rsp.content != null && num > C.livesearch.searches[id].disnum ) {
                C.livesearch.searches[id].disnum = num;
                var r=C.gE('livesearch-results-'+id);
                r.innerHTML = rsp.content;
            }
            });
    },
};
