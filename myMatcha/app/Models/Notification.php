<?php
namespace App\Models;
use \Datetime;
use App\Models\UsermanagerPDO;
class Notification
{
    protected $id;
    protected $id_belong;
    protected $id_sender;
    protected $loginSender;
    protected $unread;
    protected $type;
    protected $dateNotif;
    protected $pictureSender;
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
    public function message() : string
    {
        return $this->messageForNotification($this);
    }
    public function setId($id)
    {
        $this->id = (int) $id;
    }
    public function setbelong($belong)
    {
        $this->belong = (int) $belong;
    }
    public function setpictureSender($pictureSender)
    {
        $this->pictureSender = $pictureSender;
    }
    public function setSender($sender)
    {
        $this->sender = (int) $sender;
    }
    public function setloginSender($login_sender)
    {
        $this->login_sender = $login_sender;
    }
    public function setUnread($unread)
    {
        $this->unread = (int) $unread;
    }
    public function setType($type)
    {
        $this->type =$type;
    }
    public function setReferenceId($referenceId)
    {
        $this->referenceId = (int) $referenceId;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }
    public function id() {
        return $this->id;
    }
    public function belong() {
        return $this->belong;
    }
    public function sender() {
        return $this->sender;
    }
    public function loginSender() {
        return $this->loginSender;
    }
    public function unread() {
        return $this->unread;
    }
    public function type() {
        return $this->type;
    }
    public function referenceId() {
        return $this->referenceId;
    }
    public function pictureSender() {
        return $this->pictureSender;
    }
    public function createdAt() {
        return $this->created_at;
    }
}