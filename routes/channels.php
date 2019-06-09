<?php

Broadcast::channel('Process.{id}.Token.{token}', function ($user, $id, $token) {
    return true;
});
