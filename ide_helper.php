<?php

namespace {

    /**
     * Get the available container instance.
     *
     * @param  string|null  $abstract
     * @param  array  $parameters
     * @return mixed|\Illuminate\Contracts\Foundation\Application
     */
    function app($abstract = null, array $parameters = [])
    {
    }

    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
    }

    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
    }

    /**
     * Get the path to the resources folder.
     *
     * @param  string  $path
     * @return string
     */
    function resource_path($path = '')
    {
    }

    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return \Illuminate\Http\Request|string|array|null
     */
    function request($key = null, $default = null)
    {
    }
}

namespace Illuminate\Support {

    use Closure;
    use Illuminate\Console\Application as Artisan;

    abstract class ServiceProvider
    {
        /**
         * The application instance.
         *
         * @var \Illuminate\Contracts\Foundation\Application
         */
        protected $app;

        /**
         * All of the registered booting callbacks.
         *
         * @var array
         */
        protected $bootingCallbacks = [];

        /**
         * All of the registered booted callbacks.
         *
         * @var array
         */
        protected $bootedCallbacks = [];

        /**
         * The paths that should be published.
         *
         * @var array
         */
        public static $publishes = [];

        /**
         * The paths that should be published by group.
         *
         * @var array
         */
        public static $publishGroups = [];

        /**
         * Create a new service provider instance.
         *
         * @param  \Illuminate\Contracts\Foundation\Application  $app
         * @return void
         */
        public function __construct($app)
        {
            $this->app = $app;
        }

        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
        }

        /**
         * Register a booting callback to be run before the "boot" method is called.
         *
         * @param  \Closure  $callback
         * @return void
         */
        public function booting(Closure $callback)
        {
        }

        /**
         * Register a booted callback to be run after the "boot" method is called.
         *
         * @param  \Closure  $callback
         * @return void
         */
        public function booted(Closure $callback)
        {
        }

        /**
         * Call the registered booting callbacks.
         *
         * @return void
         */
        public function callBootingCallbacks()
        {
        }

        /**
         * Call the registered booted callbacks.
         *
         * @return void
         */
        public function callBootedCallbacks()
        {
        }

        /**
         * Merge the given configuration with the existing configuration.
         *
         * @param  string  $path
         * @param  string  $key
         * @return void
         */
        protected function mergeConfigFrom($path, $key)
        {
        }

        /**
         * Load the given routes file if routes are not already cached.
         *
         * @param  string  $path
         * @return void
         */
        protected function loadRoutesFrom($path)
        {
        }

        /**
         * Register a view file namespace.
         *
         * @param  string|array  $path
         * @param  string  $namespace
         * @return void
         */
        protected function loadViewsFrom($path, $namespace)
        {
        }

        /**
         * Register the given view components with a custom prefix.
         *
         * @param  string  $prefix
         * @param  array  $components
         * @return void
         */
        protected function loadViewComponentsAs($prefix, array $components)
        {
        }

        /**
         * Register a translation file namespace.
         *
         * @param  string  $path
         * @param  string  $namespace
         * @return void
         */
        protected function loadTranslationsFrom($path, $namespace)
        {
        }

        /**
         * Register a JSON translation file path.
         *
         * @param  string  $path
         * @return void
         */
        protected function loadJsonTranslationsFrom($path)
        {
        }

        /**
         * Register database migration paths.
         *
         * @param  array|string  $paths
         * @return void
         */
        protected function loadMigrationsFrom($paths)
        {
        }

        /**
         * Register Eloquent model factory paths.
         *
         * @deprecated Will be removed in a future Laravel version.
         *
         * @param  array|string  $paths
         * @return void
         */
        protected function loadFactoriesFrom($paths)
        {
        }

        /**
         * Setup an after resolving listener, or fire immediately if already resolved.
         *
         * @param  string  $name
         * @param  callable  $callback
         * @return void
         */
        protected function callAfterResolving($name, $callback)
        {
        }

        /**
         * Register paths to be published by the publish command.
         *
         * @param  array  $paths
         * @param  mixed  $groups
         * @return void
         */
        protected function publishes(array $paths, $groups = null)
        {
        }

        /**
         * Ensure the publish array for the service provider is initialized.
         *
         * @param  string  $class
         * @return void
         */
        protected function ensurePublishArrayInitialized($class)
        {
        }

        /**
         * Add a publish group / tag to the service provider.
         *
         * @param  string  $group
         * @param  array  $paths
         * @return void
         */
        protected function addPublishGroup($group, $paths)
        {
        }

        /**
         * Get the paths to publish.
         *
         * @param  string|null  $provider
         * @param  string|null  $group
         * @return array
         */
        public static function pathsToPublish($provider = null, $group = null)
        {
        }

        /**
         * Get the paths for the provider or group (or both).
         *
         * @param  string|null  $provider
         * @param  string|null  $group
         * @return array
         */
        protected static function pathsForProviderOrGroup($provider, $group)
        {
        }

        /**
         * Get the paths for the provider and group.
         *
         * @param  string  $provider
         * @param  string  $group
         * @return array
         */
        protected static function pathsForProviderAndGroup($provider, $group)
        {
        }

        /**
         * Get the service providers available for publishing.
         *
         * @return array
         */
        public static function publishableProviders()
        {
        }

        /**
         * Get the groups available for publishing.
         *
         * @return array
         */
        public static function publishableGroups()
        {
        }

        /**
         * Register the package's custom Artisan commands.
         *
         * @param  array|mixed  $commands
         * @return void
         */
        public function commands($commands)
        {
        }

        /**
         * Get the services provided by the provider.
         *
         * @return array
         */
        public function provides()
        {
        }

        /**
         * Get the events that trigger this service provider to register.
         *
         * @return array
         */
        public function when()
        {
        }

        /**
         * Determine if the provider is deferred.
         *
         * @return bool
         */
        public function isDeferred()
        {
        }
    }
};

namespace Illuminate\Database\Eloquent {
    abstract class Model
    {
        /**
         * The connection name for the model.
         *
         * @var string|null
         */
        protected $connection;

        /**
         * The table associated with the model.
         *
         * @var string
         */
        protected $table;

        /**
         * The primary key for the model.
         *
         * @var string
         */
        protected $primaryKey = 'id';

        /**
         * The "type" of the primary key ID.
         *
         * @var string
         */
        protected $keyType = 'int';

        /**
         * Indicates if the IDs are auto-incrementing.
         *
         * @var bool
         */
        public $incrementing = true;

        /**
         * The relations to eager load on every query.
         *
         * @var array
         */
        protected $with = [];

        /**
         * The relationship counts that should be eager loaded on every query.
         *
         * @var array
         */
        protected $withCount = [];

        /**
         * The number of models to return for pagination.
         *
         * @var int
         */
        protected $perPage = 15;

        /**
         * Indicates if the model exists.
         *
         * @var bool
         */
        public $exists = false;

        /**
         * Indicates if the model was inserted during the current request lifecycle.
         *
         * @var bool
         */
        public $wasRecentlyCreated = false;

        /**
         * The connection resolver instance.
         *
         * @var \Illuminate\Database\ConnectionResolverInterface
         */
        protected static $resolver;

        /**
         * The event dispatcher instance.
         *
         * @var \Illuminate\Contracts\Events\Dispatcher
         */
        protected static $dispatcher;

        /**
         * The array of booted models.
         *
         * @var array
         */
        protected static $booted = [];

        /**
         * The array of trait initializers that will be called on each new instance.
         *
         * @var array
         */
        protected static $traitInitializers = [];

        /**
         * The array of global scopes on the model.
         *
         * @var array
         */
        protected static $globalScopes = [];

        /**
         * The list of models classes that should not be affected with touch.
         *
         * @var array
         */
        protected static $ignoreOnTouch = [];

        /**
         * The name of the "created at" column.
         *
         * @var string|null
         */
        const CREATED_AT = 'created_at';

        /**
         * The name of the "updated at" column.
         *
         * @var string|null
         */
        const UPDATED_AT = 'updated_at';

        /**
         * Create a new Eloquent model instance.
         *
         * @param  array  $attributes
         * @return void
         */
        public function __construct(array $attributes = [])
        {
        }

        /**
         * Check if the model needs to be booted and if so, do it.
         *
         * @return void
         */
        protected function bootIfNotBooted()
        {
        }

        /**
         * The "booting" method of the model.
         *
         * @return void
         */
        protected static function boot()
        {
        }

        /**
         * Boot all of the bootable traits on the model.
         *
         * @return void
         */
        protected static function bootTraits()
        {
        }

        /**
         * Initialize any initializable traits on the model.
         *
         * @return void
         */
        protected function initializeTraits()
        {
        }

        /**
         * Clear the list of booted models so they will be re-booted.
         *
         * @return void
         */
        public static function clearBootedModels()
        {
        }

        /**
         * Disables relationship model touching for the current class during given callback scope.
         *
         * @param  callable  $callback
         * @return void
         */
        public static function withoutTouching(callable $callback)
        {
        }

        /**
         * Disables relationship model touching for the given model classes during given callback scope.
         *
         * @param  array  $models
         * @param  callable  $callback
         * @return void
         */
        public static function withoutTouchingOn(array $models, callable $callback)
        {
        }

        /**
         * Determine if the given model is ignoring touches.
         *
         * @param  string|null  $class
         * @return bool
         */
        public static function isIgnoringTouch($class = null)
        {
        }

        /**
         * Fill the model with an array of attributes.
         *
         * @param  array  $attributes
         * @return $this
         *
         * @throws \Illuminate\Database\Eloquent\MassAssignmentException
         */
        public function fill(array $attributes)
        {
        }

        /**
         * Fill the model with an array of attributes. Force mass assignment.
         *
         * @param  array  $attributes
         * @return $this
         */
        public function forceFill(array $attributes)
        {
        }

        /**
         * Qualify the given column name by the model's table.
         *
         * @param  string  $column
         * @return string
         */
        public function qualifyColumn($column)
        {
        }

        /**
         * Remove the table name from a given key.
         *
         * @param  string  $key
         * @return string
         */
        protected function removeTableFromKey($key)
        {
        }

        /**
         * Create a new instance of the given model.
         *
         * @param  array  $attributes
         * @param  bool  $exists
         * @return static
         */
        public function newInstance($attributes = [], $exists = false)
        {
        }

        /**
         * Create a new model instance that is existing.
         *
         * @param  array  $attributes
         * @param  string|null  $connection
         * @return static
         */
        public function newFromBuilder($attributes = [], $connection = null)
        {
        }

        /**
         * Begin querying the model on a given connection.
         *
         * @param  string|null  $connection
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public static function on($connection = null)
        {
        }

        /**
         * Begin querying the model on the write connection.
         *
         * @return \Illuminate\Database\Query\Builder
         */
        public static function onWriteConnection()
        {
        }

        /**
         * Get all of the models from the database.
         *
         * @param  array|mixed  $columns
         * @return \Illuminate\Database\Eloquent\Collection|static[]
         */
        public static function all($columns = ['*'])
        {
        }

        /**
         * Begin querying a model with eager loading.
         *
         * @param  array|string  $relations
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public static function with($relations)
        {
        }

        /**
         * Eager load relations on the model.
         *
         * @param  array|string  $relations
         * @return $this
         */
        public function load($relations)
        {
        }

        /**
         * Eager load relations on the model if they are not already eager loaded.
         *
         * @param  array|string  $relations
         * @return $this
         */
        public function loadMissing($relations)
        {
        }

        /**
         * Eager load relation counts on the model.
         *
         * @param  array|string  $relations
         * @return $this
         */
        public function loadCount($relations)
        {
        }

        /**
         * Increment a column's value by a given amount.
         *
         * @param  string  $column
         * @param  float|int  $amount
         * @param  array  $extra
         * @return int
         */
        protected function increment($column, $amount = 1, array $extra = [])
        {
        }

        /**
         * Decrement a column's value by a given amount.
         *
         * @param  string  $column
         * @param  float|int  $amount
         * @param  array  $extra
         * @return int
         */
        protected function decrement($column, $amount = 1, array $extra = [])
        {
        }

        /**
         * Run the increment or decrement method on the model.
         *
         * @param  string  $column
         * @param  float|int  $amount
         * @param  array  $extra
         * @param  string  $method
         * @return int
         */
        protected function incrementOrDecrement($column, $amount, $extra, $method)
        {
        }

        /**
         * Increment the underlying attribute value and sync with original.
         *
         * @param  string  $column
         * @param  float|int  $amount
         * @param  array  $extra
         * @param  string  $method
         * @return void
         */
        protected function incrementOrDecrementAttributeValue($column, $amount, $extra, $method)
        {
        }

        /**
         * Update the model in the database.
         *
         * @param  array  $attributes
         * @param  array  $options
         * @return bool
         */
        public function update(array $attributes = [], array $options = [])
        {
        }

        /**
         * Save the model and all of its relationships.
         *
         * @return bool
         */
        public function push()
        {
        }

        /**
         * Save the model to the database.
         *
         * @param  array  $options
         * @return bool
         */
        public function save(array $options = [])
        {
        }

        /**
         * Save the model to the database using transaction.
         *
         * @param  array  $options
         * @return bool
         *
         * @throws \Throwable
         */
        public function saveOrFail(array $options = [])
        {
        }

        /**
         * Perform any actions that are necessary after the model is saved.
         *
         * @param  array  $options
         * @return void
         */
        protected function finishSave(array $options)
        {
        }

        /**
         * Perform a model update operation.
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $query
         * @return bool
         */
        protected function performUpdate(Builder $query)
        {
        }

        /**
         * Set the keys for a save update query.
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $query
         * @return \Illuminate\Database\Eloquent\Builder
         */
        protected function setKeysForSaveQuery(Builder $query)
        {
        }

        /**
         * Get the primary key value for a save query.
         *
         * @return mixed
         */
        protected function getKeyForSaveQuery()
        {
        }

        /**
         * Perform a model insert operation.
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $query
         * @return bool
         */
        protected function performInsert(Builder $query)
        {
        }

        /**
         * Insert the given attributes and set the ID on the model.
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $query
         * @param  array  $attributes
         * @return void
         */
        protected function insertAndSetId(Builder $query, $attributes)
        {
        }

        /**
         * Destroy the models for the given IDs.
         *
         * @param  \Illuminate\Support\Collection|array|int  $ids
         * @return int
         */
        public static function destroy($ids)
        {
        }

        /**
         * Delete the model from the database.
         *
         * @return bool|null
         *
         * @throws \Exception
         */
        public function delete()
        {
        }

        /**
         * Force a hard delete on a soft deleted model.
         *
         * This method protects developers from running forceDelete when trait is missing.
         *
         * @return bool|null
         */
        public function forceDelete()
        {
        }

        /**
         * Perform the actual delete query on this model instance.
         *
         * @return void
         */
        protected function performDeleteOnModel()
        {
        }

        /**
         * Begin querying the model.
         *
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public static function query()
        {
        }

        /**
         * Get a new query builder for the model's table.
         *
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function newQuery()
        {
        }

        /**
         * Get a new query builder that doesn't have any global scopes or eager loading.
         *
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public function newModelQuery()
        {
        }

        /**
         * Get a new query builder with no relationships loaded.
         *
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function newQueryWithoutRelationships()
        {
        }

        /**
         * Register the global scopes for this builder instance.
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $builder
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function registerGlobalScopes($builder)
        {
        }

        /**
         * Get a new query builder that doesn't have any global scopes.
         *
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public function newQueryWithoutScopes()
        {
        }

        /**
         * Get a new query instance without a given scope.
         *
         * @param  \Illuminate\Database\Eloquent\Scope|string  $scope
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function newQueryWithoutScope($scope)
        {
        }

        /**
         * Get a new query to restore one or more models by their queueable IDs.
         *
         * @param  array|int  $ids
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function newQueryForRestoration($ids)
        {
        }

        /**
         * Create a new Eloquent query builder for the model.
         *
         * @param  \Illuminate\Database\Query\Builder  $query
         * @return \Illuminate\Database\Eloquent\Builder|static
         */
        public function newEloquentBuilder($query)
        {
        }

        /**
         * Get a new query builder instance for the connection.
         *
         * @return \Illuminate\Database\Query\Builder
         */
        protected function newBaseQueryBuilder()
        {
        }

        /**
         * Create a new Eloquent Collection instance.
         *
         * @param  array  $models
         * @return \Illuminate\Database\Eloquent\Collection
         */
        public function newCollection(array $models = [])
        {
        }

        /**
         * Create a new pivot model instance.
         *
         * @param  \Illuminate\Database\Eloquent\Model  $parent
         * @param  array  $attributes
         * @param  string  $table
         * @param  bool  $exists
         * @param  string|null  $using
         * @return \Illuminate\Database\Eloquent\Relations\Pivot
         */
        public function newPivot(self $parent, array $attributes, $table, $exists, $using = null)
        {
        }

        /**
         * Convert the model instance to an array.
         *
         * @return array
         */
        public function toArray()
        {
        }

        /**
         * Convert the model instance to JSON.
         *
         * @param  int  $options
         * @return string
         *
         * @throws \Illuminate\Database\Eloquent\JsonEncodingException
         */
        public function toJson($options = 0)
        {
        }

        /**
         * Convert the object into something JSON serializable.
         *
         * @return array
         */
        public function jsonSerialize()
        {
        }

        /**
         * Reload a fresh model instance from the database.
         *
         * @param  array|string  $with
         * @return static|null
         */
        public function fresh($with = [])
        {
        }

        /**
         * Reload the current model instance with fresh attributes from the database.
         *
         * @return $this
         */
        public function refresh()
        {
        }

        /**
         * Clone the model into a new, non-existing instance.
         *
         * @param  array|null  $except
         * @return static
         */
        public function replicate(array $except = null)
        {
        }

        /**
         * Determine if two models have the same ID and belong to the same table.
         *
         * @param  \Illuminate\Database\Eloquent\Model|null  $model
         * @return bool
         */
        public function is($model)
        {
        }

        /**
         * Determine if two models are not the same.
         *
         * @param  \Illuminate\Database\Eloquent\Model|null  $model
         * @return bool
         */
        public function isNot($model)
        {
        }

        /**
         * Get the database connection for the model.
         *
         * @return \Illuminate\Database\Connection
         */
        public function getConnection()
        {
        }

        /**
         * Get the current connection name for the model.
         *
         * @return string|null
         */
        public function getConnectionName()
        {
        }

        /**
         * Set the connection associated with the model.
         *
         * @param  string|null  $name
         * @return $this
         */
        public function setConnection($name)
        {
        }

        /**
         * Resolve a connection instance.
         *
         * @param  string|null  $connection
         * @return \Illuminate\Database\Connection
         */
        public static function resolveConnection($connection = null)
        {
        }

        /**
         * Get the connection resolver instance.
         *
         * @return \Illuminate\Database\ConnectionResolverInterface
         */
        public static function getConnectionResolver()
        {
        }

        /**
         * Set the connection resolver instance.
         *
         * @param  \Illuminate\Database\ConnectionResolverInterface  $resolver
         * @return void
         */
        public static function setConnectionResolver(Resolver $resolver)
        {
        }

        /**
         * Unset the connection resolver for models.
         *
         * @return void
         */
        public static function unsetConnectionResolver()
        {
        }

        /**
         * Get the table associated with the model.
         *
         * @return string
         */
        public function getTable()
        {
        }

        /**
         * Set the table associated with the model.
         *
         * @param  string  $table
         * @return $this
         */
        public function setTable($table)
        {
        }

        /**
         * Get the primary key for the model.
         *
         * @return string
         */
        public function getKeyName()
        {
        }

        /**
         * Set the primary key for the model.
         *
         * @param  string  $key
         * @return $this
         */
        public function setKeyName($key)
        {
        }

        /**
         * Get the table qualified key name.
         *
         * @return string
         */
        public function getQualifiedKeyName()
        {
        }

        /**
         * Get the auto-incrementing key type.
         *
         * @return string
         */
        public function getKeyType()
        {
        }

        /**
         * Set the data type for the primary key.
         *
         * @param  string  $type
         * @return $this
         */
        public function setKeyType($type)
        {
        }

        /**
         * Get the value indicating whether the IDs are incrementing.
         *
         * @return bool
         */
        public function getIncrementing()
        {
        }

        /**
         * Set whether IDs are incrementing.
         *
         * @param  bool  $value
         * @return $this
         */
        public function setIncrementing($value)
        {
        }

        /**
         * Get the value of the model's primary key.
         *
         * @return mixed
         */
        public function getKey()
        {
        }

        /**
         * Get the queueable identity for the entity.
         *
         * @return mixed
         */
        public function getQueueableId()
        {
        }

        /**
         * Get the queueable relationships for the entity.
         *
         * @return array
         */
        public function getQueueableRelations()
        {
        }

        /**
         * Get the queueable connection for the entity.
         *
         * @return string|null
         */
        public function getQueueableConnection()
        {
        }

        /**
         * Get the value of the model's route key.
         *
         * @return mixed
         */
        public function getRouteKey()
        {
        }

        /**
         * Get the route key for the model.
         *
         * @return string
         */
        public function getRouteKeyName()
        {
        }

        /**
         * Retrieve the model for a bound value.
         *
         * @param  mixed  $value
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        public function resolveRouteBinding($value)
        {
        }

        /**
         * Get the default foreign key name for the model.
         *
         * @return string
         */
        public function getForeignKey()
        {
        }

        /**
         * Get the number of models to return per page.
         *
         * @return int
         */
        public function getPerPage()
        {
        }

        /**
         * Set the number of models to return per page.
         *
         * @param  int  $perPage
         * @return $this
         */
        public function setPerPage($perPage)
        {
        }

        /**
         * Dynamically retrieve attributes on the model.
         *
         * @param  string  $key
         * @return mixed
         */
        public function __get($key)
        {
        }

        /**
         * Dynamically set attributes on the model.
         *
         * @param  string  $key
         * @param  mixed  $value
         * @return void
         */
        public function __set($key, $value)
        {
        }

        /**
         * Determine if the given attribute exists.
         *
         * @param  mixed  $offset
         * @return bool
         */
        public function offsetExists($offset)
        {
        }

        /**
         * Get the value for a given offset.
         *
         * @param  mixed  $offset
         * @return mixed
         */
        public function offsetGet($offset)
        {
        }

        /**
         * Set the value for a given offset.
         *
         * @param  mixed  $offset
         * @param  mixed  $value
         * @return void
         */
        public function offsetSet($offset, $value)
        {
        }

        /**
         * Unset the value for a given offset.
         *
         * @param  mixed  $offset
         * @return void
         */
        public function offsetUnset($offset)
        {
        }

        /**
         * Determine if an attribute or relation exists on the model.
         *
         * @param  string  $key
         * @return bool
         */
        public function __isset($key)
        {
        }

        /**
         * Unset an attribute on the model.
         *
         * @param  string  $key
         * @return void
         */
        public function __unset($key)
        {
        }

        /**
         * Handle dynamic method calls into the model.
         *
         * @param  string  $method
         * @param  array  $parameters
         * @return mixed
         */
        public function __call($method, $parameters)
        {
        }

        /**
         * Handle dynamic static method calls into the method.
         *
         * @param  string  $method
         * @param  array  $parameters
         * @return mixed
         */
        public static function __callStatic($method, $parameters)
        {
        }

        /**
         * Convert the model to its string representation.
         *
         * @return string
         */
        public function __toString()
        {
        }

        /**
         * When a model is being unserialized, check if it needs to be booted.
         *
         * @return void
         */
        public function __wakeup()
        {
        }
    }
}

namespace Illuminate\Support\Facades {

    /**
     * @method static \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard guard(string|null $name = null)
     * @method static void shouldUse(string $name);
     * @method static bool check()
     * @method static bool guest()
     * @method static \Illuminate\Contracts\Auth\Authenticatable|null user()
     * @method static int|null id()
     * @method static bool validate(array $credentials = [])
     * @method static void setUser(\Illuminate\Contracts\Auth\Authenticatable $user)
     * @method static bool attempt(array $credentials = [], bool $remember = false)
     * @method static bool once(array $credentials = [])
     * @method static void login(\Illuminate\Contracts\Auth\Authenticatable $user, bool $remember = false)
     * @method static \Illuminate\Contracts\Auth\Authenticatable loginUsingId(mixed $id, bool $remember = false)
     * @method static bool onceUsingId(mixed $id)
     * @method static bool viaRemember()
     * @method static void logout()
     * @method static \Symfony\Component\HttpFoundation\Response|null onceBasic(string $field = 'email',array $extraConditions = [])
     * @method static bool|null logoutOtherDevices(string $password, string $attribute = 'password')
     * @method static \Illuminate\Contracts\Auth\UserProvider|null createUserProvider(string $provider = null)
     * @method static \Illuminate\Auth\AuthManager extend(string $driver, \Closure $callback)
     * @method static \Illuminate\Auth\AuthManager provider(string $name, \Closure $callback)
     *
     * @see \Illuminate\Auth\AuthManager
     * @see \Illuminate\Contracts\Auth\Factory
     * @see \Illuminate\Contracts\Auth\Guard
     * @see \Illuminate\Contracts\Auth\StatefulGuard
     */
    class Auth
    {
    }

    /**
     * @method static \Illuminate\Database\ConnectionInterface connection(string $name = null)
     * @method static string getDefaultConnection()
     * @method static void setDefaultConnection(string $name)
     * @method static \Illuminate\Database\Query\Builder table(string $table)
     * @method static \Illuminate\Database\Query\Expression raw($value)
     * @method static mixed selectOne(string $query, array $bindings = [])
     * @method static array select(string $query, array $bindings = [])
     * @method static bool insert(string $query, array $bindings = [])
     * @method static int update(string $query, array $bindings = [])
     * @method static int delete(string $query, array $bindings = [])
     * @method static bool statement(string $query, array $bindings = [])
     * @method static int affectingStatement(string $query, array $bindings = [])
     * @method static bool unprepared(string $query)
     * @method static array prepareBindings(array $bindings)
     * @method static mixed transaction(\Closure $callback, int $attempts = 1)
     * @method static void beginTransaction()
     * @method static void commit()
     * @method static void rollBack()
     * @method static int transactionLevel()
     * @method static array pretend(\Closure $callback)
     * @method static void listen(\Closure $callback)
     * @method static void enableQueryLog()
     * @method static void disableQueryLog()
     * @method static bool logging()
     * @method static array getQueryLog()
     * @method static void flushQueryLog()
     *
     * @see \Illuminate\Database\DatabaseManager
     * @see \Illuminate\Database\Connection
     */
    class DB
    {
    }
}
