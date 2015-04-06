<?php

/**
 *
 * This file contains a number of options for use with the gForms class.
 * This separate file was created to keep the class file clean.
 * Needless to say, this file needs to be called as an include in the same file
 * which includes the main class.
 *
 */


// this is the global message that goes at the top of the form when errors are found
// this can contain essentially any string of anything (including HTML, CSS, JavaScript, etc.)
$_gFormConfig['form_warning_msg'] = _('Errors are preventing successful submission of this form.');

// whether or not to list the individual messages with the "global" messages
// great idea if you'll be providing helpful, clear error messages.
// bad idea if your error messages are vague and uninformative
$_gFormConfig['list_errors'] = true;

// CSS class for individual warning messages
// @todo modify the gForm class to use this
$_gFormConfig['warning_wrapper_class'] = "warn_msg";

// this is the snippet of HTML to denote that a field is required
// this doesn't have to be used, but would be nice so people know what's going on
// don't like mine? make your own
$_gFormConfig['reqd_snippet'] = '<span class="reqd" title="' . _('Required') . ':">*</span>';

// default CSS class for the form element/ label wrapper.
// will be overridden if something more specific is given in the method call
// @todo modify the gForm class to use this
$_gFormConfig['default_element_wrap_class'] = null;

// class applied to elements found to be in error
$_gFormConfig['element_error_class'] = 'error';

// attributes for error messages which appear above form elements when they have failed validation
$_gFormConfig['valError_msg_attrs'] = array('class' => 'warn');

// default CSS class for the form label.
// will be overridden if something more specific is given in the method call
// @todo modify the gForm class to use this
$_gFormConfig['default_label_class'] = null;

// default value for 'name' attribute on submit button
// will be overridden if something more specific is given in the method call
$_gFormConfig['default_submit_name'] = 'submit';

// default value for the 'value' attribute on Submit button (IOW the text on the button)
// will be overridden if something more specific is given in the method call
$_gFormConfig['default_submit_value'] = _('OK');

// default value for the 'value' attribute on Cancel button (IOW the text on the button)
// will be overridden if something more specific is given in the method call
$_gFormConfig['default_cancel_value'] = _('Cancel');
//helpful hint use NULL if you don't want the cancel button at all

// array of attributes for the individual warning messages generated for each element
// @todo modify the gForm class to use this
$_gFormConfig['val_error_msg_attrs'] = array('class' => 'form_error');

// array of attributes for the wrapper around the entire form.
$_gFormConfig['default_form_wrapper_attrs'] = array();

// options for sending e-mails
$_gFormConfig['default_email_sender'] = "karl@karlgroves.com";
$_gFormConfig['default_email_recipient'] = "karl@karlgroves.com";

// array of bad strings. Any bad string you want to avoid.
// the intent here is to avoid attempts at e-mail header injection, but you can
// pretty much add anything you want here.
$_gFormConfig['bad_strings'] = array("content-type:", "mime-version:", "multipart/mixed", "Content-Transfer-Encoding:", "bcc:", "cc:", "to:");

#########################################################################
# ARRAY USED FOR YES/NO RADIO BUTTON SETS
#########################################################################
$_gFormConfig['yesno'] = array(_('yes'), _('no'));
$_gFormConfig['yesnopipe'] = array(_('yes') . '|1', _('no') . '|0');

#########################################################################
# ARRAYS USED FOR DATE DROPDOWNS
#########################################################################
// years array - does last year, this year, and the next ten years
$_gFormConfig['this_year'] = date("Y");
$_gFormConfig['years_array'][0] = _('Year');
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] - 1;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'];
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 1;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 2;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 3;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 4;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 5;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 6;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 7;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 8;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 9;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 10;
$_gFormConfig['years_array'][] = $_gFormConfig['this_year'] + 11;

// days array
$_gFormConfig['days_array'] = array(_('Day'), "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");

// months array
$_gFormConfig['months_array'] = array(_('Month'), "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");

// Bad Strings Array
$_gFormConfig['bad_strings'] = array("content-type:", "mime-version:", "multipart/mixed", "Content-Transfer-Encoding:", "bcc:", "cc:", "to:");

#########################################################################
# ARRAYS USED FOR STATE DROPDOWNS
#########################################################################
// US States
$_gFormConfig['us_states_array'] = array("AK", "AL", "AR", "AZ", "CA", "CO", "CT", "DC", "DE", "FL", "GA", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME", "MI", "MN", "MO", "MS", "MT", "NC", "ND", "NE", "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "RI", "SC", "SD", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY");

// Canadian Provinces
$_gFormConfig['canada_provinces_array'] = array("AB", "BC", "MB", "NB", "NL", "NT", "NS", "NU", "ON", "PE", "QC", "YT");

////////////////////////////////////////////////
// TIME ZONES ARRAY, IN PIPE FORMAT
$_gFormConfig['timezone_opts'][] = "(GMT - 12:00 hours) Enitwetok, Kwajalien|-12";
$_gFormConfig['timezone_opts'][] = "(GMT - 11:00 hours) Midway Island, Samoa|-11";
$_gFormConfig['timezone_opts'][] = "(GMT - 10:00 hours) Hawaii|-10";
$_gFormConfig['timezone_opts'][] = "(GMT - 9:00 hours) Alaska|-9";
$_gFormConfig['timezone_opts'][] = "(GMT - 8:00 hours) Pacific Time (US &amp; Canada)|-8";
$_gFormConfig['timezone_opts'][] = "(GMT - 7:00 hours) Mountain Time (US &amp; Canada)|-7";
$_gFormConfig['timezone_opts'][] = "(GMT - 6:00 hours) Central Time (US &amp; Canada), Mexico City|-6";
$_gFormConfig['timezone_opts'][] = "(GMT - 5:00 hours) Eastern Time (US &amp; Canada), Bogota, Lima, Quito|-5";
$_gFormConfig['timezone_opts'][] = "(GMT -4:30 hours) Caracas (Venezuela)|-4.5";
$_gFormConfig['timezone_opts'][] = "(GMT - 4:00 hours) Atlantic Time (Canada), La Paz|-4";
$_gFormConfig['timezone_opts'][] = "(GMT - 3:30 hours) Newfoundland|-3.5";
$_gFormConfig['timezone_opts'][] = "(GMT - 3:00 hours) Brazil, Buenos Aires, Georgetown, Falkland Is.|-3";
$_gFormConfig['timezone_opts'][] = "(GMT - 2:00 hours) Mid-Atlantic, Ascention Is., St Helena|-2";
$_gFormConfig['timezone_opts'][] = "(GMT - 1:00 hour) Azores, Cape Verde Islands|-1";
$_gFormConfig['timezone_opts'][] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia|0";
$_gFormConfig['timezone_opts'][] = "(GMT + 1:00 hour) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome, Warsaw|1";
$_gFormConfig['timezone_opts'][] = "(GMT + 2:00 hours) Kaliningrad, South Africa|2";
$_gFormConfig['timezone_opts'][] = "(GMT + 3:00 hours) Baghdad, Riyadh, Moscow, Nairobi|3";
$_gFormConfig['timezone_opts'][] = "(GMT + 3:30 hours) Tehran|3.5";
$_gFormConfig['timezone_opts'][] = "(GMT + 4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi|4";
$_gFormConfig['timezone_opts'][] = "(GMT + 4:30 hours) Kabul|4.5";
$_gFormConfig['timezone_opts'][] = "(GMT + 5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent|5";
$_gFormConfig['timezone_opts'][] = "(GMT + 5:30 hours) Bombay, Colomba, Calcutta, Madras, New Delhi|5.5";
$_gFormConfig['timezone_opts'][] = "(GMT + 5:45 hours) Nepal|5.75";
$_gFormConfig['timezone_opts'][] = "(GMT + 6:00 hours) Almaty, Dhakra|6";
$_gFormConfig['timezone_opts'][] = "(GMT + 7:00 hours) Bangkok, Hanoi, Jakarta|7";
$_gFormConfig['timezone_opts'][] = "(GMT + 8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei|8";
$_gFormConfig['timezone_opts'][] = "(GMT + 9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk|9";
$_gFormConfig['timezone_opts'][] = "(GMT + 9:30 hours) Adelaide, Darwin|9.5";
$_gFormConfig['timezone_opts'][] = "(GMT + 10:00 hours) Melbourne, Papua New Guinea, Sydney, Vladivostok|10";
$_gFormConfig['timezone_opts'][] = "(GMT + 11:00 hours) Magadan, New Caledonia, Solomon Islands|11";
$_gFormConfig['timezone_opts'][] = "(GMT + 12:00 hours) Auckland, Wellington, Fiji, Marshall Island|12";

////////////////////////////////////////////////////////////////////////////////
// Some common regular expressions for use in validation and
// in use in the new pattern attribute available in HTML5
// These regular expressions are borrowed from sources found on Google
// Most of them look fine, but are included here without testing.
// As I use them I will eliminate ones that don't work as intended.
// Your assumption should be that each one you use requires testing

// floating point numbers
$_gFormConfig['regex']['floating_point'] = "^[-+]?[0-9]*\.?[0-9]+$";

//email addresses
$_gFormConfig['regex']['email'] = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";

// positive integers. will not match '0' or any floating point or negative numbers
$_gFormConfig['regex']['positive_integer'] = "^[1-9][0-9]*$";

// Passwords
// Must be at least 10 characters, no more than 128 characters, and must include
// at least one upper case letter, one lower case letter, and one numeric digit
$_gFormConfig['regex']['password1'] = "^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,128}$";

// US social security number
$_gFormConfig['regex']['us_ssn'] = "^(?!000)([0-6]\d{2}|7([0-6]\d|7[012]))([ -]?)(?!00)\d\d\3(?!0000)\d{4}$";

// Matches major credit cards including:
// Visa (length 16, prefix 4),
// Mastercard (length 16, prefix 51-55),
// Discover (length 16, prefix 6011),
// American Express (length 15, prefix 34 or 37).
// All 16 digit formats accept optional hyphens (-) between each group of four digits.
$_gFormConfig['regex']['credit_card1'] = "^((4\d{3})|(5[1-5]\d{2})|(6011))-?\d{4}-?\d{4}-?\d{4}|3[4,7]\d{13}$";

// 2-digit month 01 through 12
$_gFormConfig['regex']['month_num'] = "^((0[1-9])|(1[0-2]))$";

//date validation M/D/YY or M/D/YYYY or MM/DD/YYYY or MM/DD/YY: 1/1/1920 through 12/31/2019; Feb 29 and 30 always allowed, deal with it
$_gFormConfig['regex']['flexible_date'] = "^((0?[13578]|10|12)(-|\/)(([1-9])|(0[1-9])|([12])([0-9]?)|(3[01]?))(-|\/)((19)([2-9])(\d{1})|(20)([01])(\d{1})|([8901])(\d{1}))|(0?[2469]|11)(-|\/)(([1-9])|(0[1-9])|([12])([0-9]?)|(3[0]?))(-|\/)((19)([2-9])(\d{1})|(20)([01])(\d{1})|([8901])(\d{1})))$";

//Simple American date format mm-dd-yyyy or mm-dd-yy, no time.
//Date range is 1900 --> 2099.
//Accepted seperators can be '.','/' or '-'
$_gFormConfig['regex']['american_date'] = "^(((((((0?[13578])|(1[02]))[\.\-/]?((0?[1-9])|([12]\d)|(3[01])))|(((0?[469])|(11))[\.\-/]?((0?[1-9])|([12]\d)|(30)))|((0?2)[\.\-/]?((0?[1-9])|(1\d)|(2[0-8]))))[\.\-/]?(((19)|(20))?([\d][\d]))))|((0?2)[\.\-/]?(29)[\.\-/]?(((19)|(20))?(([02468][048])|([13579][26])))))$";

//MySQL datetime type.
//Years with 2 or 4 digits from any range.
//Months with 2 digits from 00 to 12.
//Days with 2 digits from 00 to 31.
//Hours with 2 digits from 00-23
//Minutes with 2 digits from 00 to 59.
//Seconds with 2 digits from 00 to 59.
$_gFormConfig['regex']['datetime'] = "(\d{2}|\d{4})(?:\-)?([0]{1}\d{1}|[1]{1}[0-2]{1})(?:\-)?([0-2]{1}\d{1}|[3]{1}[0-1]{1})(?:\s)?([0-1]{1}\d{1}|[2]{1}[0-3]{1})(?::)?([0-5]{1}\d{1})(?::)?([0-5]{1}\d{1})";

// ISO Date YYYY-MM-DD
$_gFormConfig['regex']['iso_date'] = "^((((19|20)(([02468][048])|([13579][26]))-02-29))|((20[0-9][0-9])|(19[0-9][0-9]))-((((0[1-9])|(1[0-2]))-((0[1-9])|(1\d)|(2[0-8])))|((((0[13578])|(1[02]))-31)|(((0[1,3-9])|(1[0-2]))-(29|30)))))$";

// US Zip Code matches 5-digit zip or zip+4
$_gFormConfig['regex']['us_zip_code'] = "^\d{5}(-\d{4})?$";

// This regular expression matches 10 digit US Phone numbers in different formats.
// Some examples are
// 1)area code in paranthesis.
// 2)space between different parts of the phone number.
// 3)no space between different parts of the number.
// 4)dashes between parts.
$_gFormConfig['regex']['us_telephone'] = "^\(?[\d]{3}\)?[\s-]?[\d]{3}[\s-]?[\d]{4}$";

// UK post code
$_gFormConfig['regex']['uk_postcode'] = "^([A-PR-UWYZ0-9][A-HK-Y0-9][AEHMNPRTVXY0-9]?[ABEHMNPRVWXY0-9]? {1,2}[0-9][ABD-HJLN-UW-Z]{2}|GIR 0AA)$";

//US or Canadian telephone numbers
$_gFormConfig['regex']['us_ca_telephone'] = "^[01]?[- .]?\(?[2-9]\d{2}\)?[- .]?\d{3}[- .]?\d{4}$";

// hexadecimal color values
$_gFormConfig['regex']['hex'] = "^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$";

// canadian post code
$_gFormConfig['regex']['canadian_postcode'] = "[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]";

//ICQ username
$_gFormConfig['regex']['icq_username'] = "([1-9])+(?:-?\d){4,}";

//alphanumeric
$_gFormConfig['regex']['alphanumeric'] = "[a-zA-Z0-9]+";

//plain username, 2-20 characters inc. underscores and dots
$_gFormConfig['regex']['username'] = "^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$";

//any alphanumeric characters (without any white space)
$_gFormConfig['regex']['alphanumeric'] = "/^[a-zA-Z0-9]*$/";

//alphabetical only
$_gFormConfig['regex']['alpha'] = "/^[a-zA-Z]*$/";

//american express 
$_gFormConfig['regex']['amex'] = "/^(3[47][0-9]{13})*$/";

//australian postal codes
$_gFormConfig['regex']['aus_postal'] = "/^((0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2}))*$/";

//canadian province abbreviations
$_gFormConfig['regex']['canadian_provinces'] = "/^(?:AB|BC|MB|N[BLTSU]|ON|PE|QC|SK|YT)*$/";

//dates in mm/dd/yyyy format (also those separated with '-' and '.')
$_gFormConfig['regex']['mmddyyyy'] = "/^((0?[1-9]|1[012])[- /.](0?[1-9]|[12][0-9]|3[01])[- /.](19|20)?[0-9]{2})*$/";

//dates in yyyy/mm/dd format (also those separated with '-' and '.')
$_gFormConfig['regex']['yyyymmdd'] = "#^((19|20)?[0-9]{2}[- /.](0?[1-9]|1[012])[- /.](0?[1-9]|[12][0-9]|3[01]))*$#";

//digits only
$_gFormConfig['regex']['numeric'] = "/^[0-9]*$/";

//URL
$_gFormConfig['regex']['url'] = "^((((https?|ftps?|gopher|telnet|nntp)://)|(mailto:|news:))(%[0-9A-Fa-f]{2}|[-()_.!~*';/?:@&=+$,A-Za-z0-9])+)([).!';/?:,][[:blank:]])?$";
