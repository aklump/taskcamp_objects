## Overview
A Feature is a block of markdown text that is parsed to extract the following types of information:

* Title
* Description
* URLS
* Todos (as a Priority List)
* Files

### Title
The highest markdown heading e.g. `# My title`, or the first line of text if no headings exist.

### Flags
Flags should be appended to the title.  See Features::getAvailableTags().
### Description
The description is the first markdown paragraph to follow the title.  It should follow this pattern:

1. '# some title'
2. blank line
3. series of one or more lines = description
4. blank line
5. rest of document

    # Title @w-10 @pAaron

    This
    is
    the
    description.

    but this is not.

### URLS
Any string of text that begins with `http://` through to the first whitespace.  All URLS are available as a _unique_ array, in the order they appear in the text.

### Todos
All todo items will be copied into and are available as a Priority List whose title and description inherit those of the Feature. See also [Todos](todo.html), [Priorities](priorities.html).

### Files
A list of file paths preceeded by a header of `Files`, e.g.

    ## Files
    /some/great/path/index.html
    /some/great/path/import.php