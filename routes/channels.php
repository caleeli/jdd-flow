<?php

Broadcast::channel('Process.{id}.Token.{token}', function ($user, $id, $token) {
    return true;
});
Broadcast::channel('Process.{id}', function ($user, $id) {
    return true;
});
Broadcast::channel('User.{id}', function ($user, $id) {
    return $user->getKey() == $id;
});
Broadcast::channel('Bpmn', function ($user) {
    return true;
});
