#README

## About gs_libs

This is a PHP library for use in creating CRUD interfaces with a database backend. I've been using this library for years, skipping out on the major frameworks mostly because my projects aren't that big and because a lot of this was written before most frameworks even existed.

Over the years I've cleaned up a lot of things, moved things around and made stuff better. IMO the forms class and the database classes are excellent. The forms class creates accessible forms with minimal effort.

## Known issues
* There's a bit of code smell here and there. Many of the methods in the forms class allow far more parameters than what's considered best practice
* Loads of methods use deprecated ereg_* functions and need to be updated
* No unit tests exist for any of this. 

## Contribute?
* Go for it.  Fork it, fix it, and issue your PRs.