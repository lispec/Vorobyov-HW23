<?php

namespace gallery\controllers;

use gallery\components\Router;
use gallery\components\UserSession;
use gallery\models\MySQLDB;

class DeletePhotoController extends BaseController
{

    public function execute($arguments = [])
    {
        if (UserSession::getInstance()->isGuest) {
            Router::redirect('/');
        }

        MySQLDB::getInstance()->deletePhoto(UserSession::getInstance()->username, $arguments[1]);
        Router::redirect('/user/' . UserSession::getInstance()->username);
    }

}