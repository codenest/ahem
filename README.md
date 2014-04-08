# Ahem!!!
## A Laravel 4 package for creating and managing notifications

Ahem is a simplly enables you to :-

* Create notifications and add their messages. 
* Add multiple messages on a sinlge notification.
* Define custom notification types or extend existing ones. 
* Easily define how each notification type is rendered.
* Easily render your notifications in HTML or JSON.

## Installation

Add the following to your `composer.json`

    "codenest/ahem": "dev-master"

Then run ```composer update``` in your terminal and there you have it.

In order to start using Ahem, you need to add it's service provider and facade in your application. To do this open `app/config/app.php`. 

In your `providers` array add

    'Codenest\Ahem\AhemServiceProvider',

And in your `aliases` array add

    'Ahem'  =>  'Codenest\Ahem\Facades\Ahem',

You are now ready to go!!!