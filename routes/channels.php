<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tournament.{id}', function ($user = null, $id) {
    // Public channel: anyone can listen, even if not logged in.
    return true;
});
