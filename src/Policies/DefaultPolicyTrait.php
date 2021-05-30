<?php

namespace JDD\Workflow\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * CRUDL permissions
 */
trait DefaultPolicyTrait
{

    /**
     * Determine whether the user can index the models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function list($user)
    {
        return $user->hasPermission($this->modelClass, 'list');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function read($user, Model $model)
    {
        return ($user->getKey() === $model->{$this->ownerField} && $user->hasPermission($this->modelClass, 'read'))
            || $user->hasPermission($this->modelClass, '[*]read');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create($user)
    {
        return $user->hasPermission($this->modelClass, 'create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function update($user, Model $model)
    {
        return ($user->getKey() === $model->{$this->ownerField} && $user->hasPermission($this->modelClass, 'update'))
            || $user->hasPermission($this->modelClass, '[*]update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function delete($user, Model $model)
    {
        return ($user->getKey() === $model->{$this->ownerField} && $user->hasPermission($this->modelClass, 'delete'))
            || $user->hasPermission($this->modelClass, '[*]delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function restore($user, Model $model)
    {
        return ($user->getKey() === $model->{$this->ownerField} && $user->hasPermission($this->modelClass, 'restore'))
            || $user->hasPermission($this->modelClass, '[*]restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function forceDelete($user, Model $model)
    {
        return ($user->getKey() === $model->{$this->ownerField} && $user->hasPermission($this->modelClass, 'forceDelete'))
            || $user->hasPermission($this->modelClass, '[*]forceDelete');
    }

    /**
     * Determine whether the user can call method of the model.
     *
     * @return void
     */
    public function callMethod($user, $model, $method)
    {
        return $user->getKey() === $model->{$this->ownerField} && $user->hasPermission($model, $method)
            || $user->hasPermission($this->modelClass, "[*]$method");
    }

    /**
     * Determine if the user can make a static call to a model method.
     *
     * @return void
     */
    public function callStaticMethod($user, $method)
    {
        return $user->hasPermission($this->modelClass, "&$method");
    }
}
