<?php
namespace Models;
use Models\User;

abstract class UserManager
{
    abstract protected function add(User $user);
    //return void -> a way to add a user
    abstract public function count();
    // return int of total amount of users
    abstract public function delete($id);
    // return void - method to delete a user(id)
    abstract public function getList($first = -1, $limit = -1);
    // return arr(users) from first to limited
    abstract public function getUser($id);
    // return a unique user
    public function save(User $user) {
        if ($user->isValid()) {
            $user->isNew() ? $this->add($user) : $this->update($user);
        }
        else {
            throw new \RuntimeException('At least 1 field is empty');
        }
    }
    abstract protected function update(User $user);
    //return void -> a way to update user's data
}



?>