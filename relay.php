<?php

// Includes needed stuff
include("config.php");
require_once __DIR__.'/vendor/autoload.php';
use Livaco\EasyDiscordWebhook\DiscordWebhook;
use Livaco\EasyDiscordWebhook\Objects\Embed;

// Required data in headers
$aHeaders = array(
    "REQUEST_METHOD" => "POST",
    "HTTP_X_GITHUB_EVENT" => "push",
    "HTTP_USER_AGENT" => "GitHub-Hookshot/*",
);

// Check if request is sent from GitHub - Credits: https://gist.github.com/jplitza/88d64ce351d38c2f4198
function verifyHeaders($received, $check, $name = "array") {
    $allowed = false;

    if (is_array($received)) {
        foreach($check as $k => $v) {
            if (!array_key_exists($k, $received)) { break; }
            if (is_array($v) && is_array($received[$k])) { verifyHeaders($received[$k], $v); }
            if (is_array($v) || is_array($received[$k])) { break; }
            if (!fnmatch($v, $received[$k])) { break; }

            $allowed = true;
        }
    }

    return $allowed;
}

// Headers verification here
$bHeaders = verifyHeaders($_SERVER, $aHeaders, "$_SERVER");
if (!$bHeaders) { http_response_code(403); die("Forbidden\n"); }

// Test if we got a payload or if someone accessed the website directly
if (isset($_POST["payload"])) {
    $sPayload = $_POST["payload"];
    $aPayload = json_decode($sPayload, true);

    // Grab commits
    $aCommits = $aPayload["commits"];
    $sContent = "";

    // Grab repository info
    $sTitle = "[" . $aPayload["repository"]["name"];
    $sTitle .= ":" . $aPayload["repository"]["default_branch"] . "]";
    $sTitle .= " " . count($aCommits) . " new commit" . (count($aCommits) <= 1 ? "" : "s");
    $sTitleUrl = $aPayload["repository"]["url"];
    
    // Grab sender info
    $sSender = $aPayload["sender"]["login"];
    $sAccount = "https://github.com/" . $sSender;
    $sAvatar = $aPayload["sender"]["avatar_url"];

    // Fill content by looping through commits
    foreach($aCommits as $commit) {
        $commit_hash = substr($commit["id"], 0, 7);
        $commit_message = $commit["message"];
        $commit_desc = "[`" . $commit_hash . "`](" . $commit["url"] . ") " . (str_starts_with($commit_message, $dc_hiddenchar) ? $dc_hiddenmsg : $commit_message) . "\n";
        $sContent .= $commit_desc;
    }

    // Send webhook as an embed
    DiscordWebhook::new($dc_webhookurl)
        ->addEmbed(Embed::new()
            ->setAuthor($sSender ?? "Unknown", $sAccount ?? "", $sAvatar ?? "")
            ->setTitle($sTitle ?? "No title provided")
            ->setUrl($sTitleUrl ?? "")
            ->setDescription($sContent ?? "No commit passed.")
        )
        ->execute();
} else {
    // Send a test webhook
    DiscordWebhook::new($dc_webhookurl)
        ->setContent("This is a test ping sent from our Commits tracker.")
        ->execute();
}
