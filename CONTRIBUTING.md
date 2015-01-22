#Contributing

This is not an official project of Tenon, but rather just a sample for use by our user community. It is unlikely we'll maintain it ourselves. We do, however,
welcome contributions by the broad user community.  Here are some general guidelines for contributing:

## First, understand the structure
Before you get started, you'll want to understand how it is structured.

### General structure
* The repository root folder contains project assets such as configuration files, etc. 
* The `src` folder contains all of the source files for the examples. This is where you do your work to create or improve examples.
* The `dist` folder contains the final example files generated whenever the Grunt task is run. DO NOT modify anything in the `dist` folder. It WILL be overwritten by the Grunt task! This is by design and is not a bug.

## Installation
### Get the repo
* [Fork this Repository](https://help.github.com/articles/fork-a-repo)
* `git pull` your fork down to your local machine

### Get ready to to work
* It might be a good idea to [Set this repo as an upstream remote](https://help.github.com/articles/fork-a-repo#step-3-configure-remotes)
* Go to Terminal and run `npm install && bower install`
* Then, run `grunt`
* It should say 'Done, without errors.' right above a block that shows how long the task(s) took.

That final step is a good sanity check to ensure someone else hasn't already broken stuff you need.  If that default Grunt task fails, that means something is wrong. *If you can, please fix whatever is broken and issue a PR*

## General Guidance
Now that you're all started and ready to go, here's some general guidance on making changes and additions:

* It is good practice to create an issue in the main repo to discuss what you'll be doing.
* It is also good practice to [create a new branch](http://git-scm.com/book/en/Git-Branching-Basic-Branching-and-Merging) if your changes are big (such as a new example). Its a good idea if your branch name has some similarity to the issue title or, at least, issue title.
* Make your code changes and push them to your repo.
* [Issue a pull request](https://help.github.com/articles/using-pull-requests)
* Drink beer.

You can further be a good citizen by adding your custom JS files to the JSHint task in the Grunt file.

## Building
Once you've made your changes/ created your examples, run the Grunt tasks. In terminal, just enter: `grunt`.  This will run all of the Grunt tasks, including making a deployment-ready copy of everything and placing it into the `/dist/` folder.  You can now upload/ deploy the contents of that folder anywhere you like.

If the Grunt task fails, you *must* fix whatever is causing the failure before you attempt to isue a Pull Request! No PR will be accepted if it doesn't run the Grunt tasks successfully.