## Overview
A Feature is a block of markdown text that is parsed to extract the following types of information:

* Title
* Description
* Todos (as a Priority List)
* Questions
* URLS
* Files

### Title
The first markdown h1 e.g. `# My title` on the page.

### Flags
Flags should be appended to the title.  See Features::getAvailableTags().

### Description
The description is the first markdown paragraph to follow the title.  It should follow this pattern:

1. '# some title'
2. blank line
3. series of one or more lines = description
4. blank line
5. rest of document

Example 1:

    # Title @w-10 @pAaron

    This
    is
    the
    description.

    but this is not.

Example 2:

    Some line of text

    ## Not the title

    Some
    paragraph
    of
    text not the description

    # This is the Title @w-10 @pAaron

    - [ ] this todo gets skipped over

    This
    is
    the
    description since it's first paragraph after title.

    # This H1 is not, it comes second

    and this is just a paragraph.

**Please note that paragraphs that are questions, or which start with http are not allowed as descriptions.**

### Todos
All todo items will be copied into and are available as a Priority List whose title and description inherit those of the Feature. See also [Todos](todo.html), [Priorities](priorities.html).

### Questions
Questions are paragraphs which start with a '?'

    ? This is a question.

### URLS
Any string of text that begins with `http://` through to the first whitespace.  All URLS are available as a _unique_ array, in the order they appear in the text.

### Files
A list of file paths preceeded by a header of `Files`, e.g.

    ## Files
    /some/great/path/index.html
    /some/great/path/import.php