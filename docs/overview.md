# Web Next Generation Module (ciniki.wng)

This module is designed to be the successor to ciniki.web module and treats everything
as a "Section". 

Each page consists of the header, 1 or more sections followed by footer. The sections
can be added and rearranged by the tenant.

Each section will return a series of blocks, similar to ciniki.web blocks. These blocks
will render standard CSS contained in the theme site.css. 

## Hooks

Each module that has web data contains module/wng/sections.php which returns the list of sections for that module.
The sections will have references 'PACKAGE.MODULE.SECTION.ID' where ID is optional.

The wng module will look for the file module/wng/SECTION.php where SECTION is from the section ref.

## Caching

- The theme is pre-cached anytime a change is made that will affect the theme
    - wng/themes/theme/site.css + db theme settings + db custom css > cache/site.css
    - wng/themes/theme/site.js + db javascript + db custom js > cache/site.js
    - custom favicon or wng/themes/theme/favicon.png > cache/favicon.png
