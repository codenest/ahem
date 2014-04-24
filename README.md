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

```
"codenest/ahem": "dev-master"
```

Then run ```composer update``` in your terminal.

In order to start using Ahem, you need to add it's service provider and facade in your application. To do this open `app/config/app.php`. 

In your `providers` array add

```php
'Codenest\Ahem\AhemServiceProvider',
```

And in your `aliases` array add

```php
'Ahem'  =>  'Codenest\Ahem\Facades\Ahem',
```

## Configuration.
By default, Ahem has  ``success``, ``error``, ``warning`` and ``info`` notification types defined in its configuration file and you may wish to add your own, edit or remove some of these notification types.  To do this, you need to publish the config file to your app by running the command below.

```
php artisan config:publish codenest/ahem
```

## Basic Usage.
Before getting into details, lets have a look at how we can use Ahem's default notification types. 

### Adding Notifications
========================

#### Adding single message notifications.

	Ahem::success()->message('Login was successfully. Welcome.');
	Ahem::error()->message('Wrong email or password.');
	Ahem::info()->message('Somebody send you a message');
	Ahem::warning()->message('Your account subscription will expire in 3 days. Please renew.');

#### Giving notifications their unique ``id`` 

	Ahem::success('login_success')->message('Login was successfully. Welcome.');
	Ahem::error('login_error')->message('Wrong email or password.');

In the above case, I have used ``login_success`` and ``login_error`` as the ids. This id uniquely identified the notification and I recommend it if you have multiple notifications on one request and you would like to reference some of them at a later stage. As you saw in our first example, the ``id`` is not necessary. Your can leave it blank and the notification will be assigned a unique integer ``id``.

#### Multiple messages and notifications headings.
Adding an array of messages with an heading.
	
	Ahem::error('login_error')
					->messages(array('email' => 'Enter a valid email address', 'password' => 'The password field is required'))
					->heading('Something went wrong');
					
Adding validation error messages.

	public function postLogin()
    {
        $rules = array (
                    'email' => 'email|required',
                    'password'   => 'required'
            );
       $validator = Validator::make( Input::all(), $rules);
       if($validator->passes())
       {
           Ahem::success('login_success')->message('Login was successfully. Welcome!!');
		   return Redirect::to('/');
       }
       else 
       {
          Ahem::error('login_success')->messages($validator->messages())->heading('Something went wrong.');
		  return Redirect::back()->withInput();
       }       
    }
	
#### Add notifications for the same requests.
As we are going to see later, notifications are automatically flashed into the session on creation and cleared once they are rendered, you might want to add notifications for a single requests that don't need to be flashed. We do this by simply setting ``flashable`` to ``false``
	
	Ahem::error('login_error')->message('Login error. Try again.')->flashable(false);
	
	
### Displaying Message.
=======================
There are various way to display notifications as we are going to see below.


## Extending

-------