<?php
namespace App\Models;
use \PDO;
use \Datetime;
use App\Models\Notification;
class NotificationManager
{
    protected $notificationAdapter;
    protected $DB_REQ;
    public function __construct(PDO $DB_REQ)
    {
        $this->DB_REQ = $DB_REQ;
    }
    public function isDoublicate(Notification $notification) {
        $DB_REQ = $this->DB_REQ->prepare('
            SELECT COUNT(*) AS count
            FROM notifications
            WHERE id_belong = :id_belong AND id_sender = :id_sender AND type = :type
            ');
        $DB_REQ->bindValue(':id_belong', $notification->belong());
        $DB_REQ->bindValue(':id_sender', $notification->sender());
        $DB_REQ->bindValue(':type', $notification->type());
        $DB_REQ->execute();
        $result = $DB_REQ->fetch(PDO::FETCH_ASSOC);
        if (intval($result['count']) > 0) {
            return TRUE;
        }
        return FALSE;
    }
    public function add(Notification $notification) {
        //if (!(self::isDoublicate($notification))) {
            $DB_REQ = $this->DB_REQ->prepare('
                INSERT INTO notifications(id_belong, id_sender, unread, type, date_notif)
                VALUES (:id_belong, :id_sender, :unread, :type, NOW())
                ');
            $DB_REQ->bindValue(':id_belong', $notification->belong());
            $DB_REQ->bindValue(':id_sender', $notification->sender());
            $DB_REQ->bindValue(':unread', $notification->unread());
            $DB_REQ->bindValue(':type', $notification->type());
            $DB_REQ->execute();
        /*}
        else {
            $DB_REQ = $this->DB_REQ->prepare('
                SELECT * FROM notifications
                WHERE date_notif = (SELECT max(date_notif) FROM notifications WHERE id_belong = :id_belong AND id_sender = :id_sender)
                ');
            $DB_REQ->bindValue(':id_belong', $notification->belong());
            $DB_REQ->bindValue(':id_sender', $notification->sender());
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            if ($data['type'] === "visit" && $notification->type() === 'visit'){
                return NULL;
            }
            else {
                $notification->setUnread(TRUE);
                self::update($notification);
            }
        }*/
    }
    public function countUnread($userId) {
        $DB_REQ = $this->DB_REQ->prepare('
            SELECT COUNT(*) AS count
            FROM notifications
            WHERE id_belong = :id_belong AND unread = 1
            ');
        $DB_REQ->bindValue(':id_belong', $userId);
        $DB_REQ->execute();
        $result = $DB_REQ->fetch(PDO::FETCH_ASSOC);
        return(($result['count']));
    }
    public function setAllNotifsAsRead($userId) {
        $DB_REQ = $this->DB_REQ->prepare('
            UPDATE notifications
            SET
                unread = 0
            WHERE id_belong = :id_belong
            ');
        $DB_REQ->bindValue(':id_belong', $userId);
        $DB_REQ->execute();
    }
	public function setNotifAsRead($userId, $notifId) {
        $DB_REQ = $this->DB_REQ->prepare('
            UPDATE notifications
            SET
                unread = 0
            WHERE id_belong = :id_belong
			AND   id = :id
            ');
        $DB_REQ->bindValue(':id_belong', $userId);
		$DB_REQ->bindValue(':id', $notifId);
        $DB_REQ->execute();
    }
    public function update(Notification $notification) {
        $DB_REQ = $this->DB_REQ->prepare('
            UPDATE notifications
            SET
                unread = :unread,
                date_notif = NOW()
            WHERE id_belong = :id_belong AND id_sender = :id_sender AND type = :type
            ');
        $DB_REQ->bindValue(':id_belong', $notification->belong());
        $DB_REQ->bindValue(':id_sender', $notification->sender());
        $DB_REQ->bindValue(':type', $notification->type());
        $DB_REQ->bindValue(':unread', 1);
        $DB_REQ->execute();
    }
	
	
    public function getNotifs($Userid) {
        $DB_REQ = $this->DB_REQ->prepare('SELECT * FROM notifications WHERE id_belong = :id_belong AND delivered = 0; UPDATE notifications SET delivered = 1 WHERE id_belong = :id_belong');
        $DB_REQ->bindValue(':id_belong', (int) $Userid, PDO::PARAM_INT);
        $DB_REQ->execute();
        $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
	
	public function get($Userid, $max = 20, $offset = 0) {
        $DB_REQ = $this->DB_REQ->prepare('
            SELECT notifications.id, notifications.id_belong, id_sender, unread, type, date_notif, users.login AS loginSender, pictures.source AS pictureSender
            FROM notifications
            INNER JOIN users
            ON users.id = id_sender
            INNER JOIN pictures
            ON pictures.id_belong = id_sender
            WHERE notifications.id_belong = :id_belong
			AND notifications.unread = 1
            AND pictures.mainpic = 1
            ORDER BY date_notif DESC, type
            LIMIT :max OFFSET :offset
            ;');
        $DB_REQ->bindValue(':id_belong', (int) $Userid, PDO::PARAM_INT);
		$DB_REQ->bindValue(':max', (int) $max, PDO::PARAM_INT);
        $DB_REQ->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $DB_REQ->execute();
        $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
}