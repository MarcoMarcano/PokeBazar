<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

session_unset();
session_destroy();
session_start();
setFlash('success', 'Sesion finalizada.');
redirectTo('index.php');
