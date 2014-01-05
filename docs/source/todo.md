[md]:http://daringfireball.net/projects/markdown/syntax
[gitmd]:https://help.github.com/articles/github-flavored-markdown
[datetime]:http://www.w3.org/TR/NOTE-datetime
[gittodo]:https://github.com/blog/1375-task-lists-in-gfm-issues-pulls-comments

The inspiration for this markup comes from [Markdown][md] and [Github flavored markdown][gitmd].

##Format

1. A todo is a single line of text (no line breaks).
1. It must begin with `--` or `- [ ]`; however see the note below about sloppy format.
2. Various time flags are based on the [DateTime format][datetime].
1. See also [github.com][gittodo].


### Sloppy Format
All of the following are shorthand methods that expand on compile to become todo items.

    --a todo item
    -- a todo item
    -[]a todo item
    -[ ]a todo item
    - []a todo item
    --x a todo item
    --X a todo item
    --xa todo item
    --Xa todo item
    -[x]a todo item
    -[X]a todo item
    - [x]a todo item
    - [X]a todo item
    
Becomes...

    - [ ] a todo item
    - [ ] a todo item
    - [ ] a todo item
    - [ ] a todo item
    - [ ] a todo item
    - [X] a todo item
    - [X] a todo item
    - [X] a todo item
    - [X] a todo item
    - [X] a todo item
    - [X] a todo item
    - [X] a todo item
    - [X] a todo item

###Completed Status

The completed status is shown by the presence or absence of an x in the todo prefix.

This item is pending

    - [ ] A pending item

This item is complete

    - [X] A complete item
    
### Completed Items
Completed items will have 1000 added to their weight flag value to move them to the bottom of lists.

## UML
![Todo UML](images/uml-todo.png)