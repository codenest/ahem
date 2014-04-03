<?php namespace Codenest\Ahem;

use Illuminate\Support\ServiceProvider;

class AhemServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('codenest/ahem');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('ahem', function($app){
		    $config = new Config($app['config']);
            $container = new  Container($app['session.store'], $config);
            return new Factory( $container, $config );
            
        }); 
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('ahem');
	}

}