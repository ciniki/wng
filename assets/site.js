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
