![Priorities Relationship](images/overview.png)

## Definition
A list of Projects, Features and/or Todos (they do not have to be from the same project or client), each independently deployable with milestone and weight tags (among others).  The purpose of the list is to indicate the following information:

* What items are most important (`@w`)?
* In what order should items be built?
* What items must deploy simultaneously?
* When will a given feature be deployed (`@m`)?

## Conventions
Furthermore, observe the following conventions:

* If items must deploy simultaneously, then group them as a comma separated list, but enter as a single todo item.  In the example below, this can be seen in the line beginning: `Shopping Cart Flow, Eduction...`
* Priorities should be the top-most todo list in Basecamp.
* Each item should contain an `@w` take so the priority is obvious; this takes precendence over the sort order in Basecamp.

![example](images/priority_list.png)

## Allowed Flags

    @w @e @m @p

## UML
![Priorities UML](images/uml-priority-list.png)