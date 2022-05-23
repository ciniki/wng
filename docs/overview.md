# Web Next Generation Module (ciniki.wng)

This module is designed to be the successor to ciniki.web module and treats everything
as a "Section". 

Each page consists of the header, 1 or more sections followed by footer. The sections
can be added and rearranged by the tenant.

Each section will return a series of blocks, similar to ciniki.web blocks. These blocks
will render standard CSS contained in the theme site.css. 

## Field Sizing

The following are the sizes specifications and how they will adjust depending on size of media.
                    1      2       3       4
|               | 20em  | 40em  | 60em  | 80em  | 100em |
| tiny          | full  | 1/2   | 1/3   | 1/4   | 1-1 | 3 across | 
| small         | full  | 1/2   | 1/2   | 1/4   | 1-2 | 4 across |        
| small-medium  | full  | full  | 1/3   | 1/3   | 2 | 3 across
| medium        | full  | full  | 1/2   | 1/3   | 2 | 3 across
| medium-large  | full  | full  | 1/2   | 1/2   | 2 | 2 across
| large         | full  | full  | 1/2   | 1/3   | 3 | 2 across
| xlarge        | full  | full  | 1/2   | 1/2   | 3 | 2 across
| xxlarge       | full  | full  | full  | 1/2   | 4 
| full          | full  | full  | full  | full  |


                  20   40      60         80            100
                 | _ | __ __ | __ __ __ | __ __ __ __ | __ __ __ __ __  | 
| tiny-5         | X | XX XX | XX XX XX | XXX XXX XXX | XX XX XX XX XX  | min 20, max 39, 4 across (group divisible 5)
|                |   | XX XX | XXX XXX  | XXXX XXXX   |                 | 
|                |   | XXXX  | 
| tiny-4         | X | XX XX | XX XX XX | XX XX XX XX | XXX XXX XXX XXX | min 20, max 39, 4 across (group divisible 3)
|                |   | XXXX  | 
| tiny-3         | X | XX XX | XX XX XX | XXX XXX XXX | XXXX XXXX XXXX  | min 20, max 39, 4 across (group divisible 3)
|                |   | XXXX  | 
| tiny-2         | X | XX XX | XXX XXX  | XX XX XX XX | XXX XXX XXX XXX | min 20, max 39, 2 or 4 across (group divisible by 2)

| small-3        | X | XXXX  | XX XX XX | XXX XXX XXX |                 | min 20, 3 across (group divisible by 3)
| small-2        | X | XXXX  | XXX XXX  | XXXX XXXX   |                 | min 20, max 39, 2 across (group divisible by 2)
| medium-3       | X | XXXX  | XXX XXX  | XXX XXX XXX |                 | min 30, 3 across (group divisible by 3)
| medium-2       | X | XXXX  | XXX XXX  | XXXX XXXX   |                 | min 30, 2 across (group divisible by 2)
| large-2        | X | XXXX  | XXXXXX   | XXXX XXXX   |                 | min 40, 2 across (group divisible by 2)
| xlarge         | X | XXXX  | XXXXXX   | XXXXXXXX    |                 | min 80, 1 across (group divisible by 1)
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |
|                | X | __ __ | __ __ __ | __ __ __ __ |


## Hooks

Each module that has web data contains module/wng/sections.php which returns the list of sections for that module.
The sections will have references 'PACKAGE.MODULE.SECTION.ID' where ID is optional.

The wng module will look for the file module/wng/SECTION.php where SECTION is from the section ref.

## Caching

- The theme is pre-cached anytime a change is made that will affect the theme
    - wng/themes/theme/site.css + db theme settings + db custom css > cache/site.css
    - wng/themes/theme/site.js + db javascript + db custom js > cache/site.js
    - custom favicon or wng/themes/theme/favicon.png > cache/favicon.png
