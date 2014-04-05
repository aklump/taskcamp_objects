#Definition

Taskcamp headers can be thought of and are represented in the same way as email headers or HTTP headers.  Basically the first lines of text can provide meta information (sometimes equivalent to (flags)[flags.html]).  Headers are key/value pairs, separated by a colon.  The keys are NOT case-sensitive. The header is separated from the body by the a blank line.  Here is an example document (a Feature) showing headers.  Headers can be applied to anything but they make the most sense in Projects or Features.  As you can see too, they serve to tie Clients, Projects and Features together.  Notice that header values do not need to be wrapped in double quotes (as do flags).

    Project: intheloftstudios.com
    Client: In the Loft Studios, LLC
    Weight: 10
    Finish: 2014-06-01

    # Website Redesign

    This will be a revamp of the current website to allow for responsive design.

    - [ ] meet with designer @pAaron
    - [ ] design the site @pSturla
    - [ ] approve design @pAaron
    - [ ] implement design @pAaron

## The header is:

    Project: intheloftstudios.com
    Client: In the Loft Studios, LLC
    Weight: 10
    Finish: 2014-06-01

## The title is:

    # Website Redesign

## The description is:

    This will be a revamp of the current website to allow for responsive design.

## A priority list is seen here:

    - [ ] meet with designer @pAaron
    - [ ] design the site @pSturla
    - [ ] approve design @pAaron
    - [ ] implement design @pAaron

## Alternative Respresentation using flags for headers.

Notice some of the meta information can be represented with flags.  This is an equivalent way to write the same information using flags for weight and finish.  However some meta information is not available as a flag, in this case `Project` and `Client`.

    Project: intheloftstudios.com
    Client: In the Loft Studios, LLC

    # Website Redesign @w10 @f2014-06-01

    This will be a revamp of the current website to allow for responsive design.

    - [ ] meet with designer @pAaron
    - [ ] design the site @pSturla
    - [ ] approve design @pAaron
    - [ ] implement design @pAaron
