#!/bin/bash
#
# @file
# Configuration

##
 # An array of output formats to disable, if any
 #
#disabled = "website drupal html text mediawiki doxygene"

##
 # File path to the php you want to use for compiling
 #
php = '/Applications/MAMP/bin/php/php5.3.14/bin/php'

##
 # Lynx is required for output of .txt files
 #
lynx = $(which lynx)

##
 # File path to Markdown.pl e.g., core/Markdown.pl
 #
markdown = '/Users/aklump/bin/Markdown_1.0.1/Markdown.pl'

##
 # The drupal credentials for a user who can access your iframe content
 #
#credentials = "http://user:pass@www.my-site.com/user/login";

##
 # The name of the drupal module to build advanced help output for, if
 # applicable
 #
#drupal_module = 'taskcamp_objects';

##
 # The location of the advanced help output; this location is used in place of
 # the default, if enabled.
 #
#drupal_dir = '../help'

##
 # The file path to an extra README.txt file; when README.md is compiled and
 # this variable is set, the .txt version will be copied to this location.
 #
#README = '../README.txt'

# This would also copy README.md as well as README.txt
README = '../README.md'
