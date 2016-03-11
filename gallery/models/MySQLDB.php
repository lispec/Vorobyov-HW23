<?php

namespace gallery\models;

class MySQLDB
{
    private $pdo;
    private static $instance;

    public static function init($dbName, $host, $user, $password)
    {
        if (!self::$instance) {
            self::$instance = new self($dbName, $host, $user, $password);
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            throw new \Exception('MySQL DB not onit, use init method.');
        }
        return self::$instance;
    }

    private function __construct($dbName, $host, $user)
    {
        try {
            $this->pdo = new \PDO("mysql:dbname=$dbName;host=$host", $user);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function findUser($username, $password)
    {
        $statement = $this->pdo->prepare('SELECT id FROM user WHERE username= :u AND password = :p');
        $statement->bindValue(':u', $username);
        $statement->bindValue(':p', sha1($password));
        if ($statement->execute()) {
            if ($user = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return $user;
            }
        }
        return false;
    }

    public function findUserName($username)
    {
        $s = $this->pdo->prepare('SELECT id FROM user WHERE username=:u');
        $s->bindValue(':u', $username);
        if ($s->execute()) {
            if ($user = $s->fetch(\PDO::FETCH_ASSOC)) {
                return true;
            }
        }
        return false;
    }

    public function addUser($username, $password)
    {
        $s = $this->pdo->prepare('INSERT INTO `user` (username, password, regDate) VALUES (:u, :p, NOW())');
        $s->bindValue(':u', $username);
        $s->bindValue(':p', sha1($password));
        $s->execute();
        return $this->pdo->lastInsertId();
    }

    public
    function addPhoto($username, $photoURI, $description)
    {
        $s = $this->pdo->prepare('SELECT id FROM user WHERE username= :u');
        $s->bindValue(':u', $username);
        if ($s->execute()) {
            if ($user = $s->fetch(\PDO::FETCH_ASSOC)) {
                $s = $this->pdo->prepare('INSERT INTO picture (userId, uri, description, date) VALUES (:uId, :uri, :desc, NOW())');
                $s->bindValue(':uId', $user['id']);
                $s->bindValue('uri', $photoURI);
                $s->bindValue(':desc', $description);
                $s->execute();
                return $this->pdo->lastInsertId();
            }
        }
    }

    public function addPhotoByUserId($userId, $photoURI, $description)
    {
        $s = $this->pdo->prepare('INSERT INTO `picture` (userId, uri, description, date) VALUES (:uId,:uri,:desc,NOW())');
        $s->bindValue(':uId', $userId);
        $s->bindValue(':uri', $photoURI);
        $s->bindValue(':desc', $description);
        $s->execute();
        return $this->pdo->lastInsertId();
    }

    public function getPhoto($username, $photoId)
    {
        $s = $this->pdo->prepare('SELECT * FROM comment LEFT JOIN picture ON comment.pictureId=picture.id WHERE comment.pictureId=:photoId');
        $s->bindValue(':photoId', $photoId);
        if ($s->execute()) {
            if ($picWithComments = $s->fetchAll(\PDO::FETCH_ASSOC)) {
                $pic = $picWithComments[0];
                $pic['comment'] = $picWithComments;
                return $pic;
                return $pic['comment'];
            } else {
                return $this->getPhotoByPhotoId($photoId);
            }
        }
        return false;
    }

    public function getPhotoByPhotoId($photoId)
    {
        $s = $this->pdo->prepare('SELECT * FROM picture WHERE id = :id');
        $s->bindValue(':id', $photoId);
        if ($s->execute()) {
            if ($pic = $s->fetch(\PDO::FETCH_ASSOC)) {
                return $pic;
            }
        }
        return false;
    }

    public function getPhotos($username, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;

        $s = $this->pdo->prepare('SELECT picture.id as id, picture.userId, description, picture.uri as photoURI, picture.date FROM picture INNER JOIN `user` ON picture.userId = user.id WHERE user.username = :user ORDER BY `date` DESC LIMIT :limit OFFSET :offset ');

        $s->bindValue(':user', $username);
        $s->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $s->bindValue(':offset', $offset, \PDO::PARAM_INT);

        if ($s->execute()) {
            if ($photos = $s->fetchAll(\PDO::FETCH_ASSOC)) {
                return $photos;
            }
        } else {
            var_dump($s->queryString);
            var_dump($s->errorInfo());
            die;
        }
        return [];
    }

    public function getPhotosByUserId($userId, $page, $perPage)
    {
        $offset = $page - 1 * $perPage;

        $s = $this->pdo->prepare('SELECT * FROM picture WHERE userId = :u LIMIT :l OFFSET :o');
        $s->bindValue(':l', $perPage);
        $s->bindValue(':o', $offset);
        $s->bindValue(':u', $userId);
        if ($s->execute()) {
            if ($photos = $s->fetchAll(\PDO::FETCH_ASSOC)) {
                return $photos;
            }
        }
        return [];
    }


    public function deletePhoto($username, $id)
    {
        return $this->deletePhotoById($id);
    }

    public function deletePhotoById($id)
    {
        $s = $this->pdo->prepare('DELETE FROM picture WHERE id = :id');
        $s->bindValue(':id', $id);
        if (!$s->execute()) {
            var_dump($s->errorInfo());
            die;
        }
        return true;
    }


    public function PostCount($username)
    {
        $s = $this->pdo->prepare('SELECT count(*) as c FROM picture INNER JOIN user ON user.id = picture.userId WHERE user.username = :u');
        $s->bindValue(':u', $username);
        if ($s->execute()) {
            if ($c = $s->fetch(\PDO::FETCH_ASSOC)) {
                return intval($c['c']);
            }
        }
        return false;
    }

    public function addComment($name, $text, $pictureId)
    {
        $s = $this->pdo->prepare('INSERT INTO `comment` (`name`, `text`, `createdAt`, `pictureId`) VALUES (:name, :text, NOW(), :pictureId);');
        $s->bindValue(':name', $name);
        $s->bindValue(':text', $text);
        $s->bindValue(':pictureId', $pictureId);

        if ($s->execute()) {
            return true;
        }
        return false;
    }
}


