<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| BBCODE
| -------------------------------------------------------------------
| This file contains two arrays of bbcode for use with the bbcode helper.
|
*/

/**
 * This is a keyed array of bbcodes
 *
 * The key is the bbcode.
 * The value is the onclick function for the javascript links.
 *
 * @name $config['bbcodes']
 */

$config['bbcodes'] = array(
	"b"     => "javascript:insert_bbcode('[b]', '[/b]');return(false)",
	"i"     => "javascript:insert_bbcode('[i]', '[/i]');return(false)",
	"u"     => "javascript:insert_bbcode('[u]', '[/u]');return(false)",
	"code"  => "javascript:insert_bbcode('[code]', '[/code]');return(false)",
	"quote" => "javascript:insert_bbcode('[quote=AUTHOR]', '[/quote]');return(false)",
	"url"   => "javascript:insert_bbcode('[url]', '[/url]');return(false)",
	"email" => "javascript:insert_bbcode('[email=]', '[/email]');return(false)"
);


/**
 * This takes an array of keyed arrays with the following format:
 *
 * key = RegEx Pattern
 * value is an array:
 * [0] = RegEx replacement
 * [1] = Result when striping bb tags
 * [2] = Nested levels to check for (n-1) (i.e. 4 nested levels = 5 here).
 *
 * @name $config['bbcodes_to_parse']
 */
$config['bbcodes_to_parse'] = array(
	"#\[base_url\]#i"						=> array(base_url(),  base_url(), 1),
	"#\[b\](.+)\[/b\]#isU"					=> array("<span style=\"font-weight: bold;\">$1</span>", "", 1),
	"#\[i\](.+)\[/i\]#isU"					=> array("<em>$1</em>", "", 1),
	"#\[u\](.+)\[/u\]#isU"					=> array("<u>$1</u>", "", 1),
	"#\[img\](.+)\[/img\]#isU"				=> array("<img src=\"$1\" alt=\"\" border=\"0\" />", "", 1),
	"#\[img=(.+)\]#isU"						=> array("<img src=\"$1\" alt=\"\" border=\"0\" />", "", 1),
	"#\[email\](.+)\[/email\]#isU"			=> array("<a href=\"mailto:$1\">$1</a>", "$1", 1),
	"#\[email=(.+)\](.+)\[/email\]#isU"		=> array("<a href=\"mailto:$1\">$2</a>", "$1 ($2)", 1),
	"#\[url\](.+)\[/url\]#isU"				=> array("<a href=\"$1\" target=\"_blank\">$1</a>", "$1", 1),
	"#\[quote\](.+)\[/quote\]#isU"			=> array("<blockquote>$1</blockquote>", "\"$1\"", 5),
	"#\[quote=(.+)\](.+)\[/quote\]#isU"		=> array("<blockquote cite=\"$1\">$2</blockquote>", "\"$2\" ($1)", 5),
	"#\[code\](.+)\[/code\]#isU"			=> array("<code>$1</code>", "", 1),
);