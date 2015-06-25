<?php namespace Microweber\OAuth2;

use RuntimeException;
use Illuminate\Http\Response;
use Dingo\OAuth2\Server\Resource;
use Dingo\OAuth2\Server\Authorization;
use Microweber\OAuth2\Laravel\TableBuilder;
use Microweber\OAuth2\Storage\FluentAdapter;
use Illuminate\Support\ServiceProvider;
use Microweber\OAuth2\Laravel\Console\NewCommand;
use Microweber\OAuth2\Laravel\Console\DeleteCommand;
use Microweber\OAuth2\Laravel\Console\InstallCommand;
use Microweber\OAuth2\Laravel\Console\UninstallCommand;
use Dingo\OAuth2\Exception\InvalidTokenException;

class OAuth2ServiceProvider extends ServiceProvider {

	/**
	 * Boot the service provider.
	 * 
	 * @return void
	 */
	public function boot()
	{
		// $this->package('microweber/oauth2-server-laravel', 'oauth', __DIR__);

		$this->app['Microweber\OAuth2\Server\Authorization'] = function($app)
		{
			return $app['microweber.oauth.authorization'];
		};

		$this->app['Microweber\OAuth2\Server\Resource'] = function($app)
		{
			return $app['microweber.oauth.resource'];
		};

		// Register the "oauth" filter which is used to protect resources by
		// requiring a valid access token with sufficient scopes.
		$this->app['router']->filter('oauth', function($route, $request)
		{
			$scopes = func_num_args() > 2 ? array_slice(func_get_args(), 2) : [];

			try
			{
				$this->app['microweber.oauth.resource']->validateRequest($scopes);
			}
			catch (InvalidTokenException $exception)
			{
				return $this->app['config']['oauth.unauthorized']($exception->getMessage(), $exception->getStatusCode());
			}
		});
	}

	/**
	 * Register the service provider.
	 * 
	 * @return void
	 */
	public function register()
	{
		$this->registerAuthorizationServer();

		$this->registerResourceServer();

		$this->registerStorage();

		$this->registerCommands();
	}

	/**
	 * Register the authorization server.
	 * 
	 * @return void
	 */
	protected function registerAuthorizationServer()
	{
		$this->app['microweber.oauth.authorization'] = $this->app->share(function($app)
		{
			$server = new Authorization($app['microweber.oauth.storage'], $app['request']);

			// Set the access token and refresh token expirations on the server.
			$server->setAccessTokenExpiration($app['config']['oauth.expirations.access']);

			$server->setRefreshTokenExpiration($app['config']['oauth.expirations.refresh']);

			// Spin through each of the grants listed in the configuration file and
			// build an array of grants since some grants can be given options.
			foreach ($app['config']['oauth.grants'] as $key => $value)
			{
				if ( ! is_string($key))
				{
					list ($key, $value) = [$value, []];
				}
				elseif ( ! is_array($value))
				{
					$value = [$value];
				}

				$grants[$key] = $value;
			}

			// We'll create an array of mappings to each of the grants class so that
			// users can use the shorthand name of the grant in the configuration
			// file.
			$mappings = [
				'password'      => 'Microweber\OAuth2\Grant\Password',
				'client'        => 'Microweber\OAuth2\Grant\ClientCredentials',
				'authorization' => 'Microweber\OAuth2\Grant\AuthorizationCode',
				'implicit'      => 'Microweber\OAuth2\Grant\Implicit',
				'refresh'       => 'Microweber\OAuth2\Grant\RefreshToken'
			];

			// Spin through each of the grants and if it isn't set in the mappings
			// then we'll error out. Otherwise we'll get an instance of the
			// grant and register it on the server.
			foreach ($grants as $grant => $options)
			{
				if ( ! isset($mappings[$grant]))
				{
					throw new RuntimeException("Supplied grant [{$grant}] is invalid.");
				}

				$instance = new $mappings[$grant];

				if ($grant == 'password')
				{
					$instance->setAuthenticationCallback(array_pop($options));
				}
				elseif ($grant == 'authorization' and ! empty($options))
				{
					$instance->setAuthorizedCallback(array_pop($options));
				}

				$server->registerGrant($instance);
			}

			return $server;
		});
	}

	/**
	 * Register the resource server.
	 * 
	 * @return void
	 */
	protected function registerResourceServer()
	{
		$this->app['microweber.oauth.resource'] = $this->app->share(function($app)
		{
			$server = new Resource($app['microweber.oauth.storage'], $app['request']);

			$server->setDefaultScopes($app['config']['oauth.scopes']);

			return $server;
		});
	}

	/**
	 * Register the storage.
	 * 
	 * @return void
	 */
	protected function registerStorage()
	{
		$this->app['microweber.oauth.storage'] = $this->app->share(function($app)
		{
			$storage = $app['config']['oauth.storage']($app);

			return $storage->setTables($app['config']['oauth.tables']);
		});
	}

	/**
	 * Register commands.
	 * 
	 * @return void
	 */
	protected function registerCommands()
	{
		$this->app['microweber.oauth.command.install'] = $this->app->bindShared('commands.oauth.install', function($app)
		{
			$builder = new TableBuilder($app['db']->getSchemaBuilder(), $app['config']['oauth.tables']);

			return (new InstallCommand)->setTableBuilder($builder);
		});

		$this->app['microweber.oauth.command.uninstall'] = $this->app->bindShared('commands.oauth.uninstall', function($app)
		{
			$builder = new TableBuilder($app['db']->getSchemaBuilder(), $app['config']['oauth.tables']);

			return (new UninstallCommand)->setTableBuilder($builder);
		});

		$this->app['microweber.oauth.command.new'] = $this->app->bindShared('commands.oauth.new', function($app)
		{
			return (new NewCommand)->setStorage($app['microweber.oauth.storage']);
		});

		$this->app['microweber.oauth.command.delete'] = $this->app->bindShared('commands.oauth.delete', function($app)
		{
			return (new DeleteCommand)->setStorage($app['microweber.oauth.storage']);
		});

		$this->commands('commands.oauth.install');
		$this->commands('commands.oauth.uninstall');
		$this->commands('commands.oauth.new');
		$this->commands('commands.oauth.delete');
	}

}