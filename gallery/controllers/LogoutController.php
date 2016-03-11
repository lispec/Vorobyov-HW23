<?php

namespace gallery\controllers;

use gallery\components\Router;
use gallery\components\UserSession;

class LogoutController extends BaseController {
    public function execute($arguments = []) {
        if(!UserSession::getInstance()->isGuest) {
            UserSession::getInstance()->logout();
        }
        Router::redirect('/');
    }
}