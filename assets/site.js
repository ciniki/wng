window.C = {};
//
// Function to toggle a class in an element
//
C.tC = function(e, c) {
    if( e.classList != null ) {
        if( e.classList.contains(c) ) {
            e.classList.remove(c);
        } else {
            e.classList.add(c);
        }
    } else if( e.className != null ) {
        if( e.className.indexOf(c) > -1 ) {
            e.className = e.className.replace(c, '');
        } else {
            e.className += c;
        }
    }
};
//
// Function to add a class
//
C.aC = function(e, c) {
    if( e.classList != null ) {
        if( !e.classList.contains(c) ) {
            e.classList.add(c);
        }
    } else if( e.className != null ) {
        if( e.className.indexOf(c) == -1 ) {
            e.className += c;
        }
    }
};
//
// Function to remove a class
//
C.rC = function(e, c) {
    if( e.classList != null ) {
        if( e.classList.contains(c) ) {
            e.classList.remove(c);
        }
    } else if( e.className != null ) {
        if( e.className.indexOf(c) > -1 ) {
            e.className = e.className.replace(c, '');
        }
    }
};
//
// Function to check if class contains 
//
C.cC = function(e, c) {
    if( e.classList != null ) {
        return e.classList.contains(c);
    } else if( e.className != null ) {
        if( e.className.indexOf(c) > -1 ) {
            return true;
        } else {
            return false;
        }
    }
};
//
// Get an element by id from the document
//
C.gE = function(i) {
    return document.getElementById(i);
};
//
// Call back to API
//
C.getBg = function(c,p,f){
    var u='';
    if(p!=null){
        for(i in p){
            u+=(u==''?'?':'&')+i+'='+encodeURIComponent(p[i]);
        }
    };
    u=c+u;
    var x=new XMLHttpRequest();
    x.open('GET',u,true);
    x.onreadystatechange = function() {
        if(x.readyState==4&&x.status==200){
            var r=eval('('+x.responseText+')');
//            if(r.stat=='fail'){
//                console.log(x.responseText);
//            }
            f(r);
        };
        if(x.readyState>2&&x.status>=300){
            f({'stat':'fail', 'err':{'code':'300', 'msg':'Error connecting to server.'}});
            console.log('apierr:'+x.status);
        }
    };
    x.send(null);
};
// Call back to API with Form Data
C.postFDBg = function(m,p,fd,c) {
    var u = '';
    if(p!=null){
        for(i in p){
            u+=(u==''?'?':'&')+i+'='+encodeURIComponent(p[i]);
        }
    };
    u=m+u;
    var x=new XMLHttpRequest();
    x.open("POST", u, true);
    x.onreadystatechange = function() {
        if( x.readyState == 4 && x.status == 200 ) {
            var r = eval('(' + x.responseText + ')');
            if( r.stat != 'ok' ) {
                console.log(x.responseText);
            } 
            c(r);
        } 
        else if( x.readyState > 2 && (x.status >= 300) ) {
            c({'stat':'fail','err':{'code':'HTTP-' + x.status, 'msg':'Unable to transfer.'}});
        } else if( x.readyState == 4 && x.status == 0 ) {
            M.stopLoad();
            c({'stat':'fail','err':{'code':'HTTP-' + x.status, 'msg':'Unable to transfer.'}});
        }
    };
    x.send(fd);
};
// Clear a element in the dom
C.clr=function(i){
    var e=(typeof i=='object'?i:this.gE(i));
    if(e!=null&e.children!=null){
        while(e.children.length>0){
            e.removeChild(e.children[0]);
        }
    }
    return e;
};
C.sC=function(){
    return document.documentElement || document.body;
};
// Create a new element
C.aE=function(t,i,c,h,f){
    var e=document.createElement(t);
    if(i!=null){e.setAttribute('id',i);}
    if(c!=null){e.className=c;}
    if(h!=null){e.innerHTML=h;}
    if(f!=null&&f!=''){e.setAttribute('onclick',f);}
    return e;
};
// Encode string
C.eU = function(s) {
    return encodeURIComponent(s);
}
// Decode string
C.dU = function(s) {
    return decodeURIComponent(s);
}
