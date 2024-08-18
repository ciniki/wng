C.livesearch = {
    searches: {},
    init: function(e,id,url,args) {
        this.searches[id] = {
            'url':url,
            'args':args,
            };
        this.update(id);
    },
    update: function(id) {
        var e=C.gE('livesearch-'+id);
        this.searches[id].args.search_string = e.value;
        C.getBg(this.searches[id].url,this.searches[id].args,function(rsp) {
            if( rsp.content != null ) {
                var r=C.gE('livesearch-results-'+id);
                r.innerHTML = rsp.content;
            }
            });
    },
};
