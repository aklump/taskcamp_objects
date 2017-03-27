## Table of Flags/Headers
`@see Object::getFlagSchema()`

| flag | type | header key | meaning |
|------|---------|----------|----------|
| @a  | int      | Actual | Actual time to complete in minutes  |
| @d  | time or date       | Done  | When it was done/finished/completed |
| @e  | int      | Estimate | Estimated time to complete in minutes  |
| @f  | date \| interval | Finish  | Targeted finish date |
| @g  | string     | Group  | The group name |
| @m  | date \| interval       | Milestone  | Milestone other than finish date |
| @p  | string     | Person  | Person responsible |
| @s  | time or date \| interval   | Start  | Start Time |
| @w  | int \| float | Weight | Weight (or Priority Rank) |

### Deprecated?

| flag | type | header key | meaning |
|------|---------|----------|----------|
| @h  | float      | Hours | Actual hours from start to finish |
| @bc | string     | Basecamp  | Basecamp uuid of the item |
| @mt  | string    | Mantis  | Mantis uuid |
| @qb | string     | Quickbooks | Quickbooks uuid |

## Dates, times and intervals
For dates you must use one of the following formats:

    YYYY-MM-DD
    YYYY-MM-DDTHH:MM+0000
    HH:MM+0000

### Date Context

When you use time without a date, the date context will be used to interpret the time value.  Here is the list of contexts

| flag | context |
|----------|----------|
| @s | config.date_default OR now |
| @d | @s OR config.date_default OR now |


And you can also use [Time Intervals](http://en.wikipedia.org/wiki/ISO_8601#Time_intervals) for some flags.

    P1M
    P7D

For example here are a couple of todo items that are both due in 1 week.

    - [ ] This needs to be done in 1 week @fP1W
    - [ ] This needs to be done in 1 week @fP7D

## Feature with Headers instead of Flags
    Client: In the Loft Studios, LLC
    Project: intheloftstudios.com
    Weight: 190
    Person: Aaron Klump
    Estimate: 100
    Group: Next Week
    Start: 2014-01-12
    Milestone: 2014-03-01
    Finish: 2014-06-01
    Completed:
    Basecamp: https://intheloftstudios.basecamphq.com/projects/1234-intheloftstudios-com/todo_lists
    Mantis: http://mantis.intheloftstudios.com.com/view.php?id=49
    Quickbooks: In the Loft:Redesign

    # New Feature

##Todo with Headers instead of Flags
    Client: In the Loft Studios, LLC
    Project: intheloftstudios.com
    Feature: Responsive Design
    Weight: 190
    Person: Aaron Klump
    Estimate: 3
    Start: 2014-01-12
    Milestone: 2014-03-01
    Finish: 2014-06-01
    Completed:
    Basecamp: https://intheloftstudios.basecamphq.com/projects/1234-intheloftstudios-com/todo_lists

    - [ ] meet with the designer

