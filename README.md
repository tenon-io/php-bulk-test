# PHP Bulk Tenon Tester

This is a proof-of-concept created to demonstrate testing of a series of URLs using Tenon.io.

## Intro

You need a Tenon API key to use this.  To get a key, go to [http://www.tenon.io/register.php](http://www.tenon.io/register.php) 

You should eventually dive right into the documentation to make your own awesome stuff with Tenon API [https://tenon.io/documentation](https://tenon.io/documentation)

## How this thing works

This is a really simple queue tester. It takes a list of URLs (located in a text file) and submits them to Tenon in an asynchronous fashion. As it submits the URLs to Tenon, pages are tested, and results written to a database.

## Requirements

This must be run on a PHP web server with CURL, MySQL, and PDO.

## Install Source

To install everything needed for this project you will need to install [Node/ NPM](http://nodejs.org/) and [Grunt](http://gruntjs.com/). Then `git clone` this repo.  

Finally, run the following commands in Terminal/ Windows Command Prompt:

* `npm install`
* `bower install`

This will install all project dependencies.

## Configure
Next step is to configure it. Open up the file located at `src/lib/config-sample.php`.  That file is very well commented. Make the necessary changes and then save the file as `src/lib/config.php`.

## Set up your queue
The config file discussed above has a config option called `QUEUE_FILE_PATH`. This must be a plain text file of URLs - **one URL per line** - that you want to test. This repo includes an empty `queue.txt` file as well as a sample file called `fortune-500.txt`. Naturally, you'll want to create your own queue of URLs for the pages you want to test.

*We don't recommend that you actually use the `fortune-500.txt` file because doing so will, obviously, run up against your plan's daily limit*. 

## Install & Use it
Next step is to run the following command in Terminal/ Windows Command Prompt:

`grunt`

This will run the necessary Grunt tasks to make the system ready to use. Upload the contents of the `/dist/` folder to the document root of your web server.

Finally, navigate to the installed location and open the `/install.php` file. That will install all of the database tables and populate the queue.  If all goes well, it'll prompt you to go to the `/index.php` file. If everything has been configured properly testing will begin and in a few seconds, you'll start seeing results display on screen!

## Get your results
When all your testing is done, simply go to `/export.php`. This will create a master CSV file for all results found and a CSV file for each page tested. They will be located in `/results/`

## View Reports
You can also get high-level stats information by going to the `/reports.php` page. This information comes in via AJAX due to the possibility of a large amount of data being collected.  Reports will automatically update every 2 minutes via AJAX.

## Contributing

View the CONTRIBUTING.md doc in the root of this repository.

## License
This is license under the MIT license. Please see the License file for more details.

## Disclaimer

Tenon does not offer support for this code. The code in this repository is only to show a fully functioning example of the Tenon API. This is instead intended to show you it works and give you a potential starting point for your own application.
