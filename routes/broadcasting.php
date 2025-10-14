<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

require base_path('routes/channels.php');
require base_path('routes/web.php');
require base_path('routes/api.php');
require base_path('routes/console.php');

?>