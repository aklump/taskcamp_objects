# Flags
Flags give meta information about the object including the estimate time to complete, who it's assigned to and when it was finished, to name a few.

1. You can escape a non flag like this `\@d`, using the backslash.
1. All flags must be preceded with at least one char of whitespace, e.g. `this@s14:54` will not be considered an @s flag.
1. Multiple flags should be separated by a single space.
2. By convention, flags should come at the end of the line, not in the middle.
3. Flag values with a space should be wrapped in double quotes, e.g. `@qb"In the Loft Studios"`
4. Flag values cannot contain double quotes.

This is incorrect:

    - [ ] @s6:33 This todo was @d today.
    
This is correct:

    - [ ] This todo was today. @s6:33 @d

##Time

* This regex is used in all time regexes below...

##Estimated Time To Complete

* Estimates are written in _minutes_.

The following todo item is estimated to take 15 minutes:

    - [ ] Refactor the css @e15

##Start Time

_Default_: Current time, HH:SS (Current time will be appended if only `@s` is present)

The following todo item was begun at 5:45 am

    - [ ] Refactor the css @e15 @s5:45
    
## Milestone/Target Date

The date this todo item is targeted for completion

##Done (Completed) Time

_Default_: Current datetime, YYYY-MM-DDTHH:SS

The following todo item will be marked complete on compile

    - [ ] Refactor the css @e15 @s5:45 @d
    
##Weight (or Priority/Rank)

_Default_: 0

These two todos, will be listed in reverse order when parsed:

    - [ ] Finish the job
    - [ ] Start the job @w-1

##Person Assigned To

The following are examples of items assigned to two different people:

    - [ ] Finish page.tpl @pcharlie
    - [ ] Finish page.tpl @p"Abe Lincoln"
    
##Basecamp Linked
The basecamp flag defines the Basecamp id of the linked todo list item

This item is linked to a basecamp todo item of id `10690704`

    - [ ] A linked Basecamp todo item @bc10690704