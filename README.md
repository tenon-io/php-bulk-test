# PHP Bulk Tenon Tester

## Intro

You need a Tenon API key to use this.  To get a key, go to [http://www.tenon.io/register.php](http://www.tenon.io/register.php) 

You should eventually dive right into the documentation to make your own awesome stuff with Tenon API [https://bitbucket.org/tenon-io/tenon.io-documentation/](https://bitbucket.org/tenon-io/tenon.io-documentation/)

## How this thing works
This is a really simple queue tester. It takes a list of URLs and submits them to Tenon in synchronous fashion. As it submits the URLs to Tenon, pages are tested, and results written to a series of CSV files containing all of the issues one for each page. It also creates a single CSV file of all the issues as well. Finally, it creates a log file to show the  response summaries for each submitted URL

## Install

If you want to use this immediately, all you need to do is upload the contents of the /dist/ folder, configure as described below, and upload to a PHP-enabled server. Your PHP server must have the cURL extension.

IMPORTANT: The PHP files in this repository are different from the PHP files located in other demo repositories! Do not attempt to use them interchangeably unless your idea of fun is seeing things fail.

To install everything needed to modify/ contribute to the repo, `git clone` this repo, then run `npm install && bower install && grunt`

## Configure

### Configure the PHP
Before using this, you must configure a few things. Open the config-sample.php file, edit the following details, and save it as config.php
Here is a description of each setting:

* `TENON_API_KEY`: enter your Tenon API key here.
* `TENON_API_URL`: enter the URL to the Tenon API here. By default it is set to http://tenon.io/api/ but if you're a beta tester we might have given you a different one.
* `HASH_FILEPATH`: enter the full file path and name for a file that will hold hash values. This MUST NOT be left blank! This location must be writeable.
* `CSV_FILE_PATH`: This comes with the ability to write CSV files with issue results listed. If you intend to use that feature, enter the full file system path to a folder where the CSV files will be saved. This location must be writeable
* `CSV_FILE_NAME`: enter a name for the CSV file, if you plan on logging issues to CSV
* `CSV_FILE_MODE`: enter the appropriate [fopen](http://php.net/manual/en/function.fopen.php) mode for the CSV file.
* `DEBUG`: this requires a boolean value of either `true` or `false` and indicates whether you want to see some debugging messages. In nearly all cases, you want this set to `false`, which is the default.

Sample:

```
define('TENON_API_KEY', 'PUT-YOUR-API-KEY-HERE);
define('TENON_API_URL', 'http://tenon.io/api/');
define('HASH_FILEPATH', $_SERVER['DOCUMENT_ROOT'] . '/src/hashes.txt');
define('CSV_FILE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/csv/');
define('CSV_FILE_NAME', 'tenon.csv');
define('CSV_FILE_MODE', 'a+');
define('DEBUG', false);
```
### Configure the jQuery

At its most basic, all you need to do to call this is add the following code to the bottom of each page on your site:

```
<script src="tenon_post.js"></script>
<script>
    $(document).ready(function () {
        $.tenon_post();
    }
</script>

```

However, because the plugin accepts options that directly mirror Tenon's request parameters, you can highly customize the way the testing is performed - up to and including the portion of the page being tested. For a full list of options, check out the `tenon_post.js` file.


## Use it
The full final version of the code is found in the `dist` folder. If you plan on uploading the examples for use somewhere, that's where you want to go.

To use as-is simply upload the content of the dist folder to the root directory of your site. Then call tenon_post.js in the footer of each page.


## Contributing

View the CONTRIBUTING.md doc in the root of this repository.

## License
This is license under the MIT license. Please see the License file for more details.

## Disclaimer

Tenon does not offer support for this code. The code in this repository is only to show a fully functioning example of the Tenon API. This is instead intended to show you it works and give you a potential starting point for your own application.