<?
class NP_SmartParagraphs extends NucleusPlugin {
function getName() { return 'SmartParagraphs'; }
function getAuthor() { return 'Moni @ Traweb'; }
function getURL() { return 'http://www.traweb.com/extra/nucleusplugin/'; }
function getVersion() { return '1.5'; }
function getDescription() {
   return 'This plugin will convert line breaks and paragraphs in your entries, by adding the corresponding markup tags to produce valid HTML or XHTML. It is smart enough not to insert these tags in places where they may conflict with block elements and other HTML markup tags you may already be using in your posts. Basically, this is a more powerful line break converter than the default one embedded in Nucleus. It is now based entirely on the autop script by Matthew Mullenweg - see at see at http://photomatt.net/scripts/autop.';
}

function getEventList() {
return array('PreItem','PrepareItemForEdit');
}

function event_PreItem($data) {
$this->doSmartParagraphs($data['item']->body);
if ( !($data['item']->more == "") ) {
   $this->doSmartParagraphs($data['item']->more);
}
}

// remove before editing if auto convert breaks had been enabled
function event_PrepareItemForEdit($data) {
$this->undoSmartParagraphs($data['item']->body);
$this->undoSmartParagraphs($data['item']->more);
}

function autop(&$pee, $br=1) {
if ($pee == '') return;

$pee = $pee . "\n"; // just to make things a little easier, pad the end
$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
$pee = preg_replace('!(<(?:table|ul|ol|li|pre|form|blockquote|h[1-6])[^>]*>)!', "\n$1", $pee); // Space things out a little
$pee = preg_replace('!(</(?:table|ul|ol|li|pre|form|blockquote|h[1-6])>)!', "$1\n", $pee); // Space things out a little
$pee = preg_replace("/(\r\n|\r)/", "\n", $pee); // cross-platform newlines
$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
$pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "<p>$1</p>\n", $pee); // make paragraphs, including one at the end
$pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
$pee = preg_replace('!<p>\s*(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)!', "$1", $pee);
$pee = preg_replace('!(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee);
if ($br) $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
$pee = preg_replace('!(</?(?:table|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*<br />!', "$1", $pee);
$pee = preg_replace('!<br />(\s*</?(?:p|li|div|th|pre|td|ul|ol)>)!', '$1', $pee);
$pee = preg_replace('/&([^#])(?![a-z]{1,8};)/', '&$1', $pee);
$pee = preg_replace_callback('!(<pre.*?>)(.*?)</pre>!s', create_function('$matches', 'return $matches[1] . str_replace(array("</p>\n<p>","<p>", "</p>", "<br />"), array("\n\n","","",""), $matches[2]) . "</pre>";'), $pee);
}

function doSmartParagraphs(&$data) {
$this->autop($data);
}

function undoSmartParagraphs(&$data) {

$data = str_replace("<p>",'',$data);
$data = str_replace("</p>\n\n","\n\n",$data);
$data = str_replace("<br />\n","\n",$data);
}

}
?>