PHP GitHub webhook simple
==========================

## Overview ##

This package contains a simple class that allows you to receive webhook-calls
to know about the events that occur in you repository, such as `git push`, etc.

## Webhook script ##

For a webhook script to work you have to do 3 things:
1. Add this package to your composer dependencies
2. Add a simple PHP script that will actually handle calls to webhook
3. Configure your GitHub repository webhook to be called every time commits are
   pushed to GitHub

### Composer ###

To add dependency, simply edit your `composer.json` and add to your `require`
block following dependency - `"ierusalim/github-webhook-simple": "0.1.*"`. 
This is the simplest way to bring github-webhook-simple into your project.

### Webhook script ###

To actually handle webhook calls, you have to add PHP script that will be
accessible publicly and it will run `git pull` every time GitHub calls it.
A simplest example of such webhook script:
```php
<?php
require(__DIR__ . "/vendor/autoload.php");

use Ierusalim\GitHubWebhook\Handler;

$handler = new Handler("<secret_as_on_github>", function($in_arr) {
	// $in_arr contains [event],[data], and [delivery];
	//... do any work in whis function and return "true"...
        //Alternatively, you can return $in_arr
        // and process it outside of this function
	return $in_arr; //or true. Do not return false or NULL if no errors.
});

$in_arr = $handler->handle();

if(!$in_arr) {
    die("ERROR. May be wrong secret?");
}
//... You can process data $in_arr here, or inside the function above ...
print_r($in_arr);

```

In the script above `<secret_as_on_github>` should be some random string 
you choose and it should be later supplied to GitHub when defining webhook.
For more information about secrets and how they are used in GitHub webhook read
[Webhooks | GitHub API](https://developer.github.com/webhooks/).

NOTE: Since this your script has sensitive data <secret_as_on_github>, 
Do not write it directly in the script. It is better to define it in a
separate configuration file.

### GitHub repository configuration ###

To set up a repository webhook on GitHub, head over to the **Settings** page of your
repository, and click on **Webhooks & services**. After that, click on **Add webhook**.

Fill in following values in form:
* **Payload URL** - Enter full URL to your webhook script, with https or http
* **Content type** - Select "application/json" mode
* **Secret** - Same secret you pass to constructor of `Handler` object
* Webhook should receive only push events and of course be active

Click **Add webhook** button and that's it.

## Classes ##

### Handler ###

Handler class actually handles webhook calls. It first checks GitHub signature
and then executes your callable function if signature and secret match.

Here is a complete list of methods:
* **\_\_construct($secret, $callable_hook)** - Constructor. Constructs new
  webhook handler that will verify that requests coming to it are signed.
* **getData()** - Getter. After successful validation returns parsed array of data
  in payload. Otherwise returns `null`.
* **getDelivery()** - Getter. After successful validation returns unique delivery
  number coming from GitHub. Otherwise returns `null`.
* **getEvent** - Getter. After successful validation returns name of event that
  triggered this webhook. Otherwise returns `null`.
* **getSecret()** - Getter. Returns `$secret` that was passed to constructor.
* **handle()** - Handle the request. Validates that incoming request is signed
  correctly with `$secret` and executes `git pull` upon successful validation.
  Returns `true` on succes or `false` if validation failed.
* **validate()** - Validate request only. Returns boolean that indicates whether
  the request is correctly signed by `$secret`.
