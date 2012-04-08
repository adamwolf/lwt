<?php

/**************************************************************
"Learning with Texts" (LWT) is released into the Public Domain.
This applies worldwide.
In case this is not legally possible, any entity is granted the
right to use this work for any purpose, without any conditions, 
unless such conditions are required by law.

Developed by J. Pierre in 2011.
***************************************************************/

/**************************************************************
Call: set_text_mode.php?text=[textid]&mode=0/1
Change the text display mode
***************************************************************/

include "connect.inc.php";
include "settings.inc.php";
include "utilities.inc.php"; 

$tid = getreq('text') + 0;
$showAll = getreq('mode') + 0;
saveSetting('showallwords',$showAll);

pagestart("Text Display Mode changed", false);

echo '<p><span id="waiting"><img src="icn/waiting.gif" alt="Please wait" title="Please wait" />&nbsp;&nbsp;Please wait ...</span>';
flush();
?>

<script type="text/javascript">
//<![CDATA[
window.parent.frames['l'].location.reload();
$('#waiting').html('<b>OK -- </b>');
//]]>
</script>

<?php

if ($showAll == 1) 
	echo '<b><i>Show All</i></b> is set to <b>ON</b>.<br /><br />ALL terms are now shown, and all multi-word terms are shown as superscripts before the first word. The superscript indicates the number of words in the multi-word term.<br /><br />To concentrate more on the multi-word terms and to display them without superscript, set <i>Show All</i> to OFF.</p>';
else
	echo '<b><i>Show All</i></b> is set to <b>OFF</b>.<br /><br />Multi-word terms now hide single words and shorter or overlapping multi-word terms. The creation and deletion of multi-word terms can be a bit slow in long texts.<br /><br />To  manipulate ALL terms, set <i>Show All</i> to ON.</p>';

pageend();

?>