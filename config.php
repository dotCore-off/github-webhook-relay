<?php

$dc_webhookurl = "";    // Discord webhook URL to use
$dc_hiddenchar = "!";   // If commit message is prefixed with this character, it'll hidden it
$dc_hiddenmsg = "This commit is private."; // Text to show if a commit message is hidden
$dc_hiddenreps = array();  // An array containing repo names to hide - use short version, not Organization/repository
$dc_secret = ""; // Secret key to verify payload