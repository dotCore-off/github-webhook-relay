# Github Webhooks Relay
Relay sent webhooks for repository `push` events to your Discord server.

# Features
- **customizable Discord message** - from a detailed embed to simple commit summary
- **ability to hide a commit message**
- **supports Discord Role mention**

# Installation
### Without Composer
1. **Download** ``webhook-relay.zip`` file from [latest release](https://github.com/dotCore-off/webhook-relay/releases/download/1.1/webhook-relay.zip)
2. **Unzip it** and **upload folder content** to your webhost

### With Composer
1. **Download** ``relay.php`` and ``config.php`` files and **upload them** to your webhost  
2. **Install** [Livaco Discord Webhook library](https://github.com/LivacoNew/EasyDiscordWebhook) using ``composer install`` command

---

3. **Edit ``config.php`` to your likings**
> For ``$dc_webhookurl``: Edit channel > Integrations > Webhooks > Create or select one > Copy Webhook URL  
> For ``$dc_secret``: You can set a secret, which will be used to verify the webhook's authenticity. (It needs to be the same as in your repository settings and should be secure.)
4. In your **repository settings**, **add a Webhook** with the following details
> Payload URL: `URL to your relay.php file` *(https://example.com/relay.php)*  
> Content Type: `application/x-www-form-urlencoded`  
> Secret: Here you can enter a secret, which will be used to verify the webhook's authenticity. (It needs to be the same as in your config.php and should be secure.)
5. Upon webhook creation, you should now receive a **message upon every push to the repository**
> ![image](https://user-images.githubusercontent.com/64563384/223610428-4b47fafd-1f90-4e71-b515-7093bf83edb1.png)

# Customization
**Too simple?** Fear not, Livaco got your back!  
**You can customize your embed in** ``relay.php`` file at ``line 81``.  
> [List of available methods to customize your embed](https://github.com/LivacoNew/EasyDiscordWebhook/blob/master/README.md)  
> [A complicated example by Livaco](https://github.com/LivacoNew/EasyDiscordWebhook/blob/master/examples/Complicated%20Example.php)  
> ![image](https://user-images.githubusercontent.com/64563384/223616465-510eddcf-74ef-4347-bc5d-fadb52341a57.png)


# Credits
- [Livaco](https://github.com/LivacoNew) for his wonderful [Discord Webhook library](https://github.com/LivacoNew/EasyDiscordWebhook)
