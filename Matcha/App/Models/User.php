<?php
namespace App\Models;

class User
{
    protected $errors = [],
                $id,
                $login,
                $firstName,
                $lastName,
                $age,
                $password,
                $gender,
                $email,
                $hash,
                $bio,
                $sexuality,
                $rating,
                $map,
                $pictures = [],
                $hobbies = [];

    /*
    CONST VARIABLES FOR ERRORS:
    const AUTOR_INVALID = X;
    const CONTENT_INVALID = X;
    ...
    ...
    */
    public function __construct($values) {
        if (!empty($values)) {
            $this->hydrate($values);
        }
    }

    public function hydrate($data) {
        foreach ($data as $piece => $value)
        {
            $method = 'set' . ucfirst($piece);
            if (is_callable([$this, $method]))
                $this->$method($value);
        }
    }

    public function isNew() {
        return empty($this->$id);
    }

    public function isValid() {
        return !(empty($this->$login) || empty($this->$email) || empty($this->$password));
    }

    public function isComplete() {
        return !(empty($this->$bio) || empty($this->$sexuality) || empty($this->$gender) || empty($this->$hobbies));
    }

    public function setId($id) {
        $this->id = (int) $id;
    }

    public function setLogin($login) {
        if (!is_string($login) || empty($login)) {
            $this->errors[] = self::AUTOR_INVALID;
        }
        else {
            $this->login = $login;
        }
    }

    public function setFirstName($firstName) {
        if (!is_string($firstName) || empty($firstName)) {
            $this->errors[] = self::AUTOR_INVALID;
        }
        else {
            $this->firstName = $firstName;
        }
    }

    public function setLastName($lastName) {
        if (!is_strint($lastName) || empty($lastName)) {
            $this->errors[] = self::AUTOR_INVALID;
        }
        else {
            $this->lastName = $lastName;
        }
    }

    public function setBio($bio) {
        if (!is_string($bio) || empty($bio)) {
            $this->errors[] = self::CONTENT_INVALID;
        }
        else {
            $this->bio = $bio;
        }
    }

    public function setSexuality($sexuality) {
        $this->sexuality = $sexuality;
    }

    public function setEmail($email) {
        if (!is_string($email) || empty($email)) {
            $this->errors[] = self::MAIL_INVALID;
        }
        else {
            $this->email = $email;
        }
    }

    public function setPassword($password) {
        if (!is_string($password) || empty($password)) {
            $this->errors[] = self::PASSWORD_INVALID;
        }
        else {
            $this->password = $password;
        }
    }

    public function setMap($map) {
        $this->map = $map;
    }

   public function setHash($hash) {
       if (!is_string($hash) || empty($hash)) {
           $this->errors[] = self::PASSWORD_INVALID;
       }
       else {
           $this->hash = $hash;
       }
   } 

   public function setPictures($src) {
       $this->pictures = $src;
   }

   public function setMainPic($src) {
       $this->mainpic = $src;
   }

   public function setTags($tags) {
       $tmp = array_filter($tags);
       $this->tags = array_keys($tmp);
   }

   public function updateTags($tags) {
       $this->tags = $tags;
   }

   public function setAge($age) {
       $this->age = $age;
   }

   public function setGender($gender) {
       $this->gender = $gender;
   }

   public function setRating($rating) {
    $this->rating = $rating;
   }

   public function errors() {
       return $this->errors;
   }

   public function hobbies() {
        return $this->hobbies;
    }

    public function id() {
        return $this->id;
    }

    public function pictures() {
        return $this->pictures;
    }

    public function mainpic() {
        return $this->mainpic;
    }

    public function rating() {
        return $this->rating;
    }

    public function login() {
        return $this->login;
    }

    public function firstName() {
        return $this->firstName;
    }

    public function lastName() {
        return $this->lastName;
    }

    public function email() {
        return $this->email;
    }

    public function bio() {
        return $this->bio;
    }

    public function sexuality() {
        return $this->sexuality;
    }

    public function gender() {
        return $this->gender;
    }

    public function password() {
        return $this->password;
    }

    public function hash() {
        return $this->hash;
    }

    public function age() {
        return $this->age;
    }

    public function map() {
        return $this->map;
    }
}