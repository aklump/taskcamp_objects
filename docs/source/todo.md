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

## Flags
Flags give meta information about the todo item including the estimate time to complete, who it's assigned to and when it was finished, to name a few.

1. Multiple flags should be separated by a single space.
2. Flags should come at the end of a todo item, not in the middle.

This is incorrect:

    - [ ] @s6:33 This todo was @d today.
    
This is correct:

    - [ ] This todo was today. @s6:33 @d

###Time

* This regex is used in all time regexes below...

###Estimated Time To Complete

* Estimates are written in hours.
* For minutes enter the decimal fraction, where 30 minutes is written as `.5`.

The following todo item is estimated to take 15 minutes:

    - [ ] Refactor the css @e.25

###Start Time

_Default_: Current time, HH:SS (Current time will be appended if only `@s` is present)

The following todo item was begun at 5:45 am

    - [ ] Refactor the css @e.25 @s5:45
    
### Milestone/Target Date

The date this todo item is targeted for completion

###Done (Completed) Time

_Default_: Current datetime, YYYY-MM-DDTHH:SS

The following todo item will be marked complete on compile

    - [ ] Refactor the css @e.25 @s5:45 @d
    
###Weight (or Priority/Rank)

_Default_: 0

These two todos, will be listed in reverse order when parsed:

    - [ ] Finish the job
    - [ ] Start the job @w-1

###Assigned To

The following todo is assigned to charlie

    - [ ] Finish page.tpl @pcharlie
    
###Basecamp Linked
The basecamp flag defines the Basecamp id of the linked todo list item

This item is linked to a basecamp todo item of id `10690704`

    - [ ] A linked Basecamp todo item @bc10690704

## UML
![Todo UML](images/uml-todo.png)