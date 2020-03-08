<?php
namespace App\Models;
class ProfileLikedNotification extends Notification
{
    public function messageForNotification(Notification $notification) : string
    {
        return $this->sender->getName() . 'has liked your profile:.';
    }
    public function messageForNotifications(array $notifications, int $realCount = 0) : string
    {
        if ($realCount === 0) {
            $realCount = count($notifications);
        }
        if ($realCount === 2) {
            $names = $this->messageForTwoNotifications($notifications);
        }
        elseif ($realCount < 5) {
            $names = $this->messageForManyNotifications($notifications);
        }
        else {
            $names = $this->messageForManyManyNotifications($notifications, $realCount);
        }
        return $names . ' liked your profile: ';
    }
    protected function messageForTwoNotifications(array $notifications) : string
    {
        list($first, $second) = $notifications;
        return $first->getName() . ' and ' . $second->getName();
    }
    protected function messageForManyNotifications(array $notifications) : string
    {
        $last = array_pop($notifications);
        foreach($notifications as $notification) {
            $names .= $notification->getName() . ', ';
        }
        return substr($names, 0, -2) . ' and ' . $last->getName();
    }
    protected function messageForManyManyNotifications(array $notifications, int $realCount) : string
    {
        list($first, $second) = array_slice($notifications, 0, 2);
        return $first->getName() . ', ' . $second->getName() . ' and ' .  $realCount . ' others';
    }
}