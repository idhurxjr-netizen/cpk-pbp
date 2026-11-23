<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.local.php';

require_once __DIR__ . '/../src/Lib/Ulid.php';
require_once __DIR__ . '/../src/Lib/Jwt.php';
require_once __DIR__ . '/../src/Lib/Response.php';
require_once __DIR__ . '/../src/Lib/Router.php';
require_once __DIR__ . '/../src/Lib/DB.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Lib/Auth.php';

$router = new Router();

require __DIR__ . '/../src/Routes/api.php';

return $router;