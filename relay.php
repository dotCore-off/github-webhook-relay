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
    "HTTP_X_HUB_SIGNATURE-256" => "sha256=*"
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

// Verify signature to make sure it's from GitHub
try {
    // Get signature and raw payload
    $sSignature = $_SERVER["HTTP_X_HUB_SIGNATURE_256"];
    $sRawPayload = file_get_contents("php://input");

    // Get algorithm and hash from signature
    list($sAlgo, $sHash) = explode("=", $sSignature, 2);

    // Generate payload hash based on algorithm and secret
    $sPayloadHash = hash_hmac($sAlgo, $sRawPayload, $dc_secret);

    if ($sHash !== $sPayloadHash) {
        http_response_code(403); die("Forbidden\n");
    }
} catch (Exception $e) {
    // if something goes wrong, just die
    http_response_code(403); die("Forbidden\n");
}

// Test if we got a payload or if someone accessed the website directly
if (isset($_POST["payload"])) {
    $sPayload = $_POST["payload"];
    $aPayload = json_decode($sPayload, true);

    // Grab commits
    $aCommits = $aPayload["commits"];
    $sContent = "";

    // Grab repository info
    if (count($dc_hiddenreps) == 0 || !in_array($aPayload["repository"]["name"], $dc_hiddenreps)) {
        $sRepository = "[" . $aPayload["repository"]["name"];
        $sRepository .= ":" . $aPayload["repository"]["default_branch"] . "]";
        $sRepository .= " " . count($aCommits) . " new commit" . (count($aCommits) <= 1 ? "" : "s");
        $sRepositoryUrl = $aPayload["repository"]["url"];
    } else {
        $sRepository = "[hidden-repository:main] " . count($aCommits) . " new commit" . (count($aCommits) <= 1 ? "" : "s");
    }
    
    // Grab sender info
    $sSender = $aPayload["sender"]["login"];
    $sAccount = "https://github.com/" . $sSender;
    $sAvatar = $aPayload["sender"]["avatar_url"];

    // Fill content by looping through commits
    foreach($aCommits as $commit) {
        $commit_hash = substr($commit["id"], 0, 7);
        $commit_message = $commit["message"];
        $commit_desc = $sRepositoryUrl != "" ? "[`" . $commit_hash . "`](" . $commit["url"] . ") " : "`" . $commit_hash . "`" . (str_starts_with($commit_message, $dc_hiddenchar) ? $dc_hiddenmsg : $commit_message) . "\n";
        $sContent .= $commit_desc;
    }

    /*
        MESSAGE CONSTRUCTION - DEFAULT AVAILABLE VARIABLES
        - $info["repository"] = Repository + Branch + New commits
        - $info["repository_url"] = URL to repository
        - $info["sender"] = GitHub sender username
        - $info["account"] = GitHub sender profile URL
        - $info["avatar"] = GitHub sender profile avatar
        - $info["commits"] = list of all commits

        Other variables can be fetched from $aPayload - see a GitHub Push webhook payload to view available keys
    */
    $aInfo = array(
        ["sender"] => $sSender,
        ["account"] => $sAccount,
        ["avatar"] => $sAvatar,
        ["repository"] => $sRepository,
        ["repository_url"] => $sRepositoryUrl,
        ["commits"] => $sContent
    );

    // Generate a new DiscordWebhook and send it - view README for further customization
    function sendToWebhook($url, $info) {
        DiscordWebhook::new($url)
        ->addEmbed(Embed::new()
            ->setAuthor($info["sender"], $info["account"], $info["avatar"])
            ->setTitle($info["repository"])
            ->setUrl($info["repository_url"])
            ->setDescription($info["commits"])
        )
        ->execute();
    }

    // Send to all configured Discord webhooks
    foreach($dc_webhookurl as $webhook) {
        sendToWebhook($webhook, $aInfo);
    }
}
