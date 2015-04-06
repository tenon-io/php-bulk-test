<?php
require('db.class.php');
require('gFormConfig.php');
require('gFormUtils.class.php');
require('gForm.class.php');
//////////////////////////////////////////////////////
// THIS IS THE SECTION THAT SETS UP THE FORM
$form = new gForm();
$form->setConfigOpts($_gFormConfig);

// THIS SECTION DOES THE VALIDATION & PROCESSING
if (isset($_POST['submit'])) {

    //add the POST values to the presets
    $form->setPresets($_POST);

    // validate dat shit
    $form->validate('0', 'first_name', "How 'bout you fill in that first name field, sparky?");

    $form->validate(12, 'timezone', "Select your time zone", $_gFormConfig['timezone_opts']);

    $form->validate(44, 'tehnumber', 'Number must be between 0 and 100', 0, 100);

    $bday = $_POST['log_month'] . "-" . $_POST['log_day'] . "-" . $_POST['log_year'];
    $form->validate(29, 'birthday', 'Please Select A Valid Date', $bday);

    //if valfailed is FALSE then we've passed validation
    if (false === $form->valfailed) {
        echo 'WHEE!';
        exit;
    }
} else {
    // Define some presets if form not submitted
    $presets['numCheckboxes'] = array('1', '3', '5');
    $form->setPresets($presets);

    // Another way would be to add it as a key:value pair
    $form->addPreset('first_name', 'Karl');

    // You can also add an array as the 2nd argument
    $form->addPreset('multiSelect', array('3', '6', '9'));
}

////////////////////////////////
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>gForm Demonstration</title>
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
<style type="text/css">
/* General typography and formatting */
html, body {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, Verdana, sans-serif;
    background-color: #eee;
    color: #000;
    font-size: small;
    line-height: 20px;
}

h1 {
    font-size: 1.4em;
    color: #003973;
}

h2 {
    font-size: medium;
    font-style: oblique;
    line-height: 24px;
}

img {
    border: 0;
}

/* Abbreviations and acronyms */
acronym, abbr, .abbrev {
    border-bottom: 1px dotted #000;
    font-style: normal;
    cursor: help;
}

header {
    text-align: center;
    margin-bottom: 0;
    padding: 10px;
    background-color: #003973;
    background-repeat: repeat-x;
    border-bottom: 1px solid #fff;
    height: 50px;
}

header h1 {
    font-size: 24px;
    font-family: "Times New Roman", times;
    font-variant: small-caps;
    letter-spacing: 3px;
    margin: 0;
    padding: 0;
    color: #eee;
}

/* Form Styles */
#content {
    margin: 2em;
    background-color: #fff;
    padding: 1em;
    border: 2px solid #ddd;
}

form div {
    margin: 10px;
    clear: both;
}

form label {
    display: block;
}

label.inline {
    display: inline;
}

/* A much better approach would be to use the :not pseudo-class CSS3 selectors */
input[type="text"], input[type="email"], input[type="url"], input[type="date"], input[type="month"], input[type="week"], input[type="password"], input[type="time"], input[type="datetime"], input[type="datetime-local"], input[type="color"], input[type="search"], input[type="range"], input[type="number"] {
    width: 250px;
}

form textarea {
    height: 250px;
    width: 500px;
}

/* '<input type="radio">' elements */
div.form_radio {
    clear: both;
    padding-top: 5px;
}

/* '<input type="checkbox">' elements */
input[type="checkbox"] {
    clear: both;
    padding-top: 5px;
}

/**
 *
 * Change the color of all <legend> elements to black
 * because Internet Explorer thinks they should be blue
 *
 */
legend {
    color: #000;
}

/* The fieldset element */
fieldset {
    padding: 0px 5px 5px 5px;
}

/* Basically just the "*" that indicates required fields */
.reqd {
    vertical-align: top;
    color: red;
    font-weight: bold;
    font-size: medium;
    margin-left: 4px;
}

/* Properties for the warning messages displayed during form validation */
.warn_msg, #warn_box {
    border: 1px solid #ccc;
    background-color: #eee;
    padding: 5px;
    font-weight: bold;
    color: red;
}

.warn_img {
    border: 0;
    margin-right: 5px;
}

#warn_list {
    list-style-type: square;
    color: #000;
}

.error {
    border: 1px solid red;
}

.warn {
    border: 1px solid #ddd;
    background-color: #eee;
    padding: 2px;
    font-weight: bold;
    color: red;
}

/* Class applied to form elements when an error happens */
.element_error_class {
    border: 1px solid red;
    color: red;
}

/* the styling for the side-by-side form elements, i.e. "yes/no" radio buttons */
span.sidexside {
    margin-right: 20px;
}

input[type="submit"][value="OK"], input[type="button"][value="Cancel"], input[type="submit"][value="Edit..."]
input[type="submit"][value="Delete"]
input[type="submit"][value="&lt;&lt; Back"] {
    height: 22px;
    width: 70px;
    padding-bottom: 2px;
}

legend a {
    cursor: help;
}

input:focus, textarea:focus, select:focus {
    border: 3px solid purple;
}

.dateSet label, .dateSet select {
    display: inline;
}

.dateSet label {
    position: absolute;
    left: -10000px;
}

#light_bg {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0%;
    left: 0%;
    background: #7F7F7F;
    opacity: 0.5;
    display: none;
}

#login {
    color: black;
    padding: 10px;
    width: 70px;
    text-align: center;
    margin-left: 650px;
    margin-top: 50px;
    border: 1px solid #E5E5E5;
    border-radius: 5px 5px 5px 5px;
    box-shadow: 0 4px 10px -1px rgba(200, 200, 200, 0.98);
}

#tehF0rm {
    width: 600px;
    z-index: 1000;
    background: #FFF;
    position: absolute;
    top: 0%;
    margin-left: 450px;
    margin-top: 4em;
    padding-left: 20px;
    display: none;
    border: 1px solid #949494;
    border-radius: 5px 5px 5px 5px;
    box-shadow: 0 4px 10px -1px rgba(200, 200, 200, 0.98);
}

#close {
    color: #ACAAAA;
    margin-left: 350px;
}
</style>
<script type="text/javascript">
    /* jQuery stuff can go here */
    $(document).ready(function () {
        $('input[value=Cancel]').click(function () {
            parent.history.back();
            return false;
        });
    });

    $(document).ready(function () {
        $("#launch").click(function () {
            $("#light_bg").fadeIn("slow");
            $("#tehF0rm").fadeIn("slow");
            $("body").css("overflow", "hidden");
        });

        $("#close").click(function () {
            $("#light_bg").fadeOut("slow");
            $("#tehF0rm").fadeOut("slow");
            $("body").css("overflow", "visible");
        });
    });

</script>
</head>
<body>
<header>
    <h1>gForm Demo</h1>
</header>
<div id="content"
<div>
    <button id="launch">Launch</button>

</div>

<div id="tehF0rm" role="application">
    <?php
    //////////////////////////////////////////////
    // THIS IS THE SECTION THAT DISPLAYS THE FORM

    $warnAttrs = array('class' => 'warn_msg');
    $form->formWarn(null, $warnAttrs);
    $form->formStart();
    $form->hiddenbox('foo', 'bar');

    $daydrop['name'] = "log_day";
    $daydrop['id'] = "log_day";
    $daydrop['default_label'] = "Day";
    $daydrop['label'] = "Day";
    if (!isset($form->presets['log_day'])) {
        $form->addPreset('log_day', date("d"));
    }
    $birthdaySettings['daydrop'] = $daydrop;

    $monthdrop['name'] = "log_month";
    $monthdrop['id'] = "log_month";
    $monthdrop['default_label'] = "Month";
    $monthdrop['label'] = "Month";
    if (!isset($form->presets['log_month'])) {
        $form->addPreset('log_month', date("m"));
    }
    $birthdaySettings['monthdrop'] = $monthdrop;

    $yeardrop['name'] = "log_year";
    $yeardrop['id'] = "log_year";
    $yeardrop['default_label'] = "Year";
    $yeardrop['label'] = "Year";
    $yeardrop['start'] = date("Y") - 1;
    $yeardrop['end'] = date("Y") + 3;
    if (!isset($form->presets['log_year'])) {
        $form->addPreset('log_year', date("Y"));
    }
    $birthdaySettings['yeardrop'] = $yeardrop;

    $birthdaySettings['order'] = 'month|day|year';

    $form->date_drop_set('Birthday', $birthdaySettings, 'birthday', array('class' => 'dateSet'), array('class' => 'inline'));

    $form->editbox('First Name', 'first_name');
    $form->editbox('Last Name' . $form->req(), 'last_name', true);
    $form->formExp("Really just a random string of text inside the form");

    $pw_attrs['type'] = 'password';
    $form->editbox('Password', 'tehpasswd', true, $pw_attrs);

    $emailAttrs = array('type' => 'email');
    $form->editbox('E-mail', 'tehemail', false, $emailAttrs);

    $urlAttrs = array('type' => 'url', 'list' => 'urlOpts');
    $form->editbox('URL', 'tehurl', false, $urlAttrs);

    $urlOpts['CNN'] = "http://www.cnn.com";
    $urlOpts['MSNBC'] = "http://www.msnbc.com";
    $urlOpts['Fox'] = "http://www.foxnews.com";
    $form->formExp(gFormUtils::arrayToDataList($urlOpts, 'urlOpts'), null, true);

    $h5attrs['placeholder'] = 'Enter Text Here';
    $form->editbox('HTML5 Input with Placeholder Attribute', 'h5', true, $h5attrs);

    array_unshift($_gFormConfig['timezone_opts'], "Select One|");
    $form->dropdown_val('Time Zone', 'timezone', $_gFormConfig['timezone_opts']);

    $someOpts[] = "Honesty";
    $someOpts[] = "is";
    $someOpts[] = "the";
    $someOpts[] = "first";
    $someOpts[] = "chapter";
    $someOpts[] = "in";
    $someOpts[] = "the";
    $someOpts[] = "book";
    $someOpts[] = "of";
    $someOpts[] = "wisdom";
    $someOpts[] = "Thomas Jefferson";
    // array_unshift is a great way to pop something onto the beginning of an array
    // esp. useful when getting the actual choices from a database
    array_unshift($someOpts, "Select One");
    $form->dropdown('Some Opts', 'someopts', $someOpts);

    $form->stateDropdown('What state do you live in?', 'tehState');

    $form->fieldsetStart('Are These Radio Buttons?');
    $radioLabelOpts = array('class' => 'inline');
    $form->radioButton('radios', $_gFormConfig['yesno'], null, 'block', null, null, $radioLabelOpts);
    $form->fieldsetEnd();

    $form->fieldsetStart('Are These Radio Buttons Next To Each Other?');
    $radioLabelOpts = array('class' => 'inline');
    $form->radioButton('radios2', $_gFormConfig['yesno'], null, 'inline', null, null, $radioLabelOpts);
    $form->fieldsetEnd();

    $form->fieldsetStart('How many checboxes are here?');
    $checkboxLabelOpts = array('class' => 'inline');
    $checkboxOpts = range(1, 5);
    $form->checkbox('numCheckboxes[]', $checkboxOpts, null, 'block', null, null, $checkboxLabelOpts);
    $form->fieldsetEnd();

    $numAttrs = array('type' => 'number', 'min' => '0', 'max' => '100', 'step' => '1', 'aria-describedby' => 'numFormatDesc');
    $form->editbox('Enter a number', 'tehnumber', false, $numAttrs);

    $numFormatAttrs = array('id' => 'numFormatDesc');
    $form->formExp("Must be between 0 and 100", $numFormatAttrs);

    $rangeAttrs = array('type' => 'range', 'min' => '0', 'max' => '100', 'step' => '1');
    $form->editbox('Select a Range', 'tehrange', false, $rangeAttrs);
    $form->formExp("<output name=\"rangeresult\" onforminput=\"value=tehrange.valueAsNumber\"></output>");

    $form->textarea('A bunch of stuff can fit in here', 'tehtextarea');

    $html5date_attrs['type'] = 'date';
    $form->editbox('HTML5 Date', 'html5date', true, $html5date_attrs);

    $html5month_attrs['type'] = 'month';
    $form->editbox('HTML5 Month', 'html5month', true, $html5month_attrs);

    $html5week_attrs['type'] = 'week';
    $form->editbox('HTML5 Week', 'html5week', true, $html5week_attrs);

    $html5time_attrs['type'] = 'time';
    $form->editbox('HTML5 Time', 'html5time', true, $html5time_attrs);

    $html5datetime_attrs['type'] = 'datetime';
    $form->editbox('HTML5 Datetime', 'html5datetime', true, $html5datetime_attrs);

    $html5datetimelocal_attrs['type'] = 'datetime-local';
    $form->editbox('HTML5 Datetime-local', 'html5datetime-local', true, $html5datetimelocal_attrs);

    $html5search_attrs['type'] = 'search';
    $form->editbox('HTML5 Search', 'html5search', true, $html5search_attrs);

    $html5color_attrs['type'] = 'color';
    $form->editbox('HTML5 Color', 'html5color', true, $html5color_attrs);

    $multiOpts = range(1, 25);
    $multiAttrs = array('size' => '5');
    $form->multiSelect('Select more than one of these', 'multiSelect', $multiOpts, false, $multiAttrs);

    $form->formEnd();
    $form->display();
    //////////////////////////////////////////////
    ?>
</div>
</div>
<div id="light_bg"></div>
</body>
</html>