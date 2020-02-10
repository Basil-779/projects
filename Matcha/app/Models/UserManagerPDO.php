<?php
namespace App\Models;
use App\Models\User;
use \PDO;

class UserManagerPDO extends UserManager
{
    protected $DB_REQ;

    public function __construct(PDO $DB_REQ)
    {
        $this->DB_REQ = $DB_REQ;
    }

    protected function add(User $user)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO users(login, firstName, lastName, age, password, email, hash)
        VALUES(:login, :firstName, :lastName, :age, :password, :email, :hash)
        ');
        $DB_REQ->bindValue(':login', $user->login());
        $DB_REQ->bindValue(':email', $user->email());
        $DB_REQ->bindValue(':firstName', $user->firstName());
        $DB_REQ->bindValue(':lastName', $user->lastName());
        $DB_REQ->bindValue(':age', $user->age());
        $DB_REQ->bindValue(':password', $user->password());
		$DB_REQ->bindValue(':hash', $user->hash());
        $DB_REQ->execute();
    }
    protected function addExtras(User $user, $tags)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO scores(id_belong) VALUES(:id_owner)
        ');
        $DB_REQ->bindValue(':id_belong', $user->id());
        $DB_REQ->execute();
        $DB_REQ->closeCursor();

        $tagsArray = array('algorythm', 'graphics', 'uxix', 'sysadmin', 'web');

        if (isset($tags)) 
        {
            $values = array();
            foreach ($tags as $choice)
            {
                if (in_array($choice, $tagsArray))
                {
                    $values[$choice] = 1;
                }
                else {
                    $values[$choice] = 0;
                }
            }
        }

        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO tags(
            id_belong, algorythm, graphics, unix, sysadmin, web
        )
        VALUES(
            :id_belong, :algorythm, :graphics, :unix, :sysadmin, :web
        )
        ');
        $DB_REQ->bindValue(':id_belong', $user->id());
        $DB_REQ->bindValue(':algorythm', $values['algorythm']);
        $DB_REQ->bindValue(':graphics', $values['graphics']);
        $DB_REQ->bindValue(':unix', $values['unix']);
        $DB_REQ->bindValue(':sysadmin', $values['sysadmin']);
        $DB_REQ->bindValue(':web', $values['web']);

        $DB_REQ->execute();
    }

    protected function updateTags(User $user, $tags)
    {
        $tagsArray = array('algorythm', 'graphics', 'uxix', 'sysadmin', 'web');

        if (isset($tags)) 
        {
            $values = array();
            foreach ($tags as $choice)
            {
                if (in_array($choice, $tagsArray))
                {
                    $values[$choice] = 1;
                }
                else {
                    $values[$choice] = NULL;
                }
            }
        }

        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE TAGS
        SET
            algorythm = :algorythm,
            graphics = :graphics,
            unix = :unix,
            sysadmin = :sysadmin,
            web = :web
        WHERE
            id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $user->id());
        $DB_REQ->bindValue(':algorythm', $values['algorythm']);
        $DB_REQ->bindValue(':graphics', $values['graphics']);
        $DB_REQ->bindValue(':unix', $values['unix']);
        $DB_REQ->bindValue(':sysadmin', $values['sysadmin']);
        $DB_REQ->bindValue(':web', $values['web']);

        $DB_REQ->execute();
    }

    public function count()
    {
        return $this->DB_REQ->query('
        SELECT COUNT(*) FROM users
        ')->fetchColumn();
    }
    public function delete($id)
    {
        $this->DB_REQ->exec('
        DELETE FROM users WHERE id = '.(int) $id);
    }
    
    public function getList($first = -1, $limit = -1)
    {
        $sql = '
        SELECT id, login, email, password
        FROM users
        ORDER BY id DESC
        ';
        if ($first != -1 || $limit != -1)
        {
            $sql .= ' LIMIT '.(int) $limit.' OFFSET '.(int) $first;
        }

        $DB_REQ = $this->DB_REQ->query($sql);
        $DB_REQ->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Models\User');

        $listUser = $DB_REQ->fetchALL();

        $DB_REQ->closeCursor();

        return $listUser;
    }

    public function getUnique($id)
    {
        if(isset($id) && !empty($id))
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id, login, firstName, lastName, age, password, gender, email, hash, bio, sexuality, rating, map
            FROM users
            WHERE id = :id
            ');
            $DB_REQ->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $DB_REQ->execute();

            $DB_REQ->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Models\User');
            $user = $DB_REQ->fetch();

            $DB_REQ->closeCursor();

            /*$DB_REQ = $this->DB_REQ->prepare('
            SELECT algorythm, graphics, unix, sysadmin, web
            FROM tags
            WHERE id_belong = :id_belong
            ');
            $DB_REQ->bindValue(':id_belong', $id, PDO::PARAM_INT);
            $DB_REQ->execute();
            $tags = $DB_REQ->fetch(PDO::FETCH_ASSOC);

            if ($tags) 
            {
                $user->setTags($tags);
            }

            $DB_REQ->closeCursor();

            $DB_REQ = $this->DB_REQ->prepare('
            SELECT source FROM pictures
            WHERE id_belong = :id_belong AND mainpic = :mainpic
            ');

            $DB_REQ->bindValue(':id_belong', $id, PDO::PARAM_INT);
            $DB_REQ->bindValue(':mainpic', 0, PDO::PARAM_INT);
            $DB_REQ->execute();
            $pictures = $DB_REQ->fetchAll(PDO::FETCH_COLUMN);

            if ($pictures) 
            {
                $user->setPictures($pictures);
            }

            $DB_REQ->closeCursor();

            $DB_REQ = $this->DB_REQ->prepare('
            SELECT source FROM pictures
            WHERE id_belong = :id_belong AND mainpic = :mainpic
            ');

            $DB_REQ->bindValue(':id_belong', $id, PDO::PARAM_INT);
            $DB_REQ->bindValue(':mainpic', 1, PDO::PARAM_INT);
            $DB_REQ->execute();
            $mainpicture = $DB_REQ->fetch(PDO::FETCH_ASSOC);

            if ($mainpicture)
            {
                $user->setMainPicture($mainpicture['source']);
            }

            $DB_REQ->closeCursor();

            $DB_REQ = $this->DB_REQ->prepare('
            SELECT score FROM scores
            WHERE id_belong = :id_belong
            ');
            $DB_REQ->bindValue(':id_belong', $id, PDO::PARAM_INT);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);

            //$user->setPopularity($data['score']);*/

            return $user;
        }
    }
	
	public function newHash($email)
    {
        $hash = hash(md5, $email);
		$DB_REQ = $this->DB_REQ->prepare('
        UPDATE users
        SET hash = :hash
        WHERE email = :email
        ');
        $DB_REQ->bindValue(':hash', $hash);
        $DB_REQ->bindValue(':email', $email);
        $DB_REQ->execute();
		return $hash;
    }

    public function getIdFromLogin ($login)
    {
        if ($login)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id FROM users WHERE login = :login
            ');
            $DB_REQ->bindValue(':login', $login);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            return $data['id'];
        }
        return NULL;
    }

    public function getLoginFromId($id)
    {
        if ($id)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT login FROM users WHERE id = :id
            ');
            $DB_REQ->bindValue(':id', $id);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            return $data['login'];
        }
        return NULL;
    }

    public function getUserFromEmail($email) {
        if ($email) {
            $DB_REQ = $this->DB_REQ->prepare('
                SELECT id
                FROM users
                WHERE email = :email');
            $DB_REQ->bindValue(':email', $email);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            return (self::getUnique($data['id']));
        }
        return NULL;
    }
	
	public function activateUser($hash) {
		$DB_REQ = $this->DB_REQ->prepare('
        SELECT isActive FROM users
        WHERE hash = :hash
        ');
        $DB_REQ->bindValue(':hash', $hash);
        $DB_REQ->execute();
		$find = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        if ($find == NULL)
        {
            return NO_SUCH_USER;
        }
        else if ($find[0]['isActive'] == '1')
        {
            $this->userActive($hash);
			return USER_ACTIVATED;
        }
        else if ($find[0]['isActive'] == '0')
        {
			//return var_dump($find);
			return USER_ALREADY_ACTIVATED;
        }
	}
	
	public function userActive($hash)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE users
        SET isActive = 0
        WHERE hash = :hash
        ');
        $DB_REQ->bindValue(':hash', $hash);
        $DB_REQ->execute();
    }

    public function setNewPassword($hash, $newpass)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE users
        SET password = :newpassword
        WHERE hash = :hash
        ');
        $DB_REQ->bindValue(':hash', $hash);
        $DB_REQ->bindValue(':newpassword', $newpass);
        $DB_REQ->execute();
    }

    protected function update(User $user)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET 
        login = :login
        firstName = :firstName
        lastName = :lastName
        age = :age
        password = :password
        gender = :gender
        email = :email
        hash = :hash
        bio = :bio
        sexuality = :sexuality
        rating = :rating
        map = :map
        WHERE id = :id
        ');

        $DB_REQ->bindValue(':id', $user->id(), PDO::PARAM_INT);
        $DB_REQ->bindValue(':login', $user->login(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':firstName', $user->firstName(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':lastName', $user->lastName(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':age', $user->age(), PDO::PARAM_INT);
        $DB_REQ->bindValue(':password', $user->password(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':gender', $user->gender());
        $DB_REQ->bindValue(':email', $user->email(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':hash', $user->hash(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':bio', $user->bio(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':sexuality', $user->sexuality(), PDO::PARAM_STR);
        $DB_REQ->bindValue(':map', $user->map());

        $DB_REQ->execute();
    }

    public function countPictures(User $user)
    {
        $DB_REQ = $this->prepare('
        SELECT COUNT(*) as count FROM pictures WHERE id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $user->id());
        $DB_REQ->execute();
        $result = $DB_REQ->fetch(PDO::FETCH_ASSOC);
        return intval($result['count']);
    }

    public function addPicture($source, User $user)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        SELECT COUNT(*) as count FROM pictures WHERE id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $user->id());
        $DB_REQ->execute();
        $result = $DB_REQ->fetch(PDO::FETCH_ASSOC);

        if (intval($result['count']) < 5)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT COUNT(mainpic) as count FROM pictures WHERE id_belong = :id_belong AND mainpic = 1
            ');
            $DB_REQ->bindValue(':id_belong', $user->id());
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);

            if ($data['count'] == 1)
            {$mainpic = 0;}
            else
            {$mainpic = 1;}
            $DB_REQ = $this->DB_REQ->prepare('
            INSERT INTO pictures (id_belong, source, mainpic) VALUES (:id_belong, :source, :mainpic
            ');

            $DB_REQ->bindValue(':id_belong', $user->id());
            $DB_REQ->bindValue(':source', $source);
            $DB_REQ->bindValue(':mainpic', $mainpic);
            $DB_REQ->execute();
        }
        return NULL;
    }

    public function deletePicture($idPic, User $user)
    {
        $DB_REQ = $this->prepare('
        DELETE FROM pictures WHERE id = :id AND id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $user->id());
        $DB_REQ->bindValue(':id', $idPic);
        $DB_REQ->execute();
    }

    public function getIdFromPicSrc($source)
    {
        if ($source) {
			$DB_REQ = $this->DB_REQ->prepare('
				SELECT id 
				FROM pictures
				WHERE source = :source
				');
			$DB_REQ->bindValue(':source', $source);
			$DB_REQ->execute();
			$data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
			return $data['id'];
		}
		return NULL;
    }

    public function setMainPicture($idPic, User $user)
    {
        if ($idPic){
			$DB_REQ = $this->DB_REQ->prepare('
				UPDATE pictures
				SET mainpic = :value
				WHERE id_belong = :id_belong
				');
			$DB_REQ->bindValue(':value', 0, PDO::PARAM_INT);
			$DB_REQ->bindValue(':id_belong', $user->id());
			$DB_REQ->execute();

			$DB_REQ->closeCursor();

			$DB_REQ = $this->DB_REQ->prepare('
				UPDATE pictures
				SET mainpic = :value
				WHERE id = :id
				');
			$DB_REQ->bindValue(':value', 1, PDO::PARAM_INT);
			$DB_REQ->bindValue(':id', $idPic);
			$DB_REQ->execute();
		}
    }

    public function addVisit($idVisitor, $idVisited)
    {
        if (!empty($idVisitor) && !empty($idVisited))
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id_visitor FROM visitors WHERE id_belong = :id_belong AND id_visitor = :id_visitor
            ');
            $DB_REQ->bindValue(':id_belong', $idVisited, PDO::PARAM_INT);
            $DB_REQ->bindValue(':id_visitor', $idVisitor, PDO::PARAM_INT);
            $DB_REQ->execute();

            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            $DB_REQ->closeCursor();

            if (!$data['id_visitor'])
            {
                $DB_REQ = $this->DB_REQ->prepare('
                INSERT INTO visitors (id_belong, id_visitor) VALUES (:id_belong, :id_visitor)
                ');
                $DB_REQ->bindValue(':id_belong', $idVisited, PDO::PARAM_INT);
                $DB_REQ->bindValue(':id_visitor', $idVisitor, PDO::PARAM_INT);
                $DB_REQ->execute();

                $DB_REQ = $this->DB_REQ->query('SELECT LAST_INSERT_ID()');
                $lastId = $DB_REQ->fetch(PDO::FETCH_ASSOC);

                $DB_REQ->closeCursor();

                $DB_REQ = $this->DB_REQ->prepare('
                UPDATE scores SET score = score + 1 WHERE id_belong = :id_belong
                ');
                $DB_REQ->bindValue(':id_belong', $idVisited, PDO::PARAM_INT);
                $DB_REQ->execute();
                return $lastId;
            }

        }

        return NULL;
    }
    public function getVisits($id_belong)
    {
        if (!empty($id_belong))
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id_belong, users.login FROM visitors INNER JOIN users ON
            users.id = visitors.id_visitor WHERE id_belong = :id_belong ORDER BY DESC LIMIT 5;
            ');
            $DB_REQ->bindValue(':id_belong', $id_belong, PDO::PARAM_INT);
            $DB_REQ->execute();
            $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $key => $value)
            {
                $data[$key] = array_merge($value, array("origin" => "visit"));
            }
            return $data;
        }
        return NULL;
    }

    public function like($id_liker, $id_liked)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO likes (id_belong, id_liked)
        VALUES (:id_liker, :id_liked)
        ');
        $DB_REQ->bindValue(':id_liker', $id_liker, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id_liked', $id_liked, PDO::PARAM_INT);

        $DB_REQ->execute();
        $DB_REQ = $this->DB_REQ->query('SELECT LAST_INSERT_ID()');
        $lastId = $DB_REQ->fetch(PDO::FETCH_ASSOC);
        $DB_REQ->closeCursor();

        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE scores SET score = score + 2 WHERE id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id_liked, PDO::PARAM_INT);
        $DB_REQ->execute();
        return $lastId;
    }

    public function dislike($id_disliker, $id_disliked)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        DELETE FROM likes WHERE id_belong = :id_disliked AND id_liker = :id_disliker
        ');
        $DB_REQ->bindValue(':id_disliker', $id_disliker, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id_disliked', $id_disliked, PDO::PARAM_INT);
        $DB_REQ->execute();

        $DB_REQ->closeCursor();

        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE scores SET score = score - 2 WHERE id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id_belong, PDO::PARAM_INT);
        $DB_REQ->execute();
    }

    public function getLikes($id_belong)
    {
        if(!empty($id_belong))
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id_belong, users.login FROM likes INNER JOIN users ON
            users.id = likes.id_belong WHERE id_belong = :id_belong
            ORDER BY DESC LIMIT 5;
            ');
            $DB_REQ->bindValue(':id_belong', $id_belong, PDO::PARAM_INT);
            $DB_REQ->execute();
            $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $key => $value)
            {
                $data[$key] = array_merge($value, array("origin" => "like"));
            }
            return $data;
        }
        return NULL;
    }

    public function mutualFriendlist(User $user, User $userprofile)
    {
        if ($user)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT a.id_liker, a.id-belong FROM likes AS a 
            INNER JOIN likes AS b
            ON a.id_liker = b.id_belong AND b.id_liker = a.id_belong
            ');
            $DB_REQ->bindValue(':id_liker', (int)$user->id(), PDO::PARAM_INT);
            $DB_REQ->bindValue(':if_belong', (int)$userprofile->id(), PDO::PARAM_INT);
            $DB_REQ->execute();
            $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $value)
            {
                if($value['id_liker'] == $user->id() && $value['id_belong'] == $userprofile->id())
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function hasLiked(User $user1, User $user2)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        SELECT COUNT(*) AS count FROM likes 
        WHERE id_liker = :user2 AND id_belong = :user1;
        ');
        $DB_REQ->bindValue(':user2', $user2->id());
        $DB_REQ->bindValue(':user1', $user1->id());
        $DB_REQ->execute();
        $result = $DB_REQ->fetch(PDO::FETCH_ASSOC);

        if (intval($result['count']) == 0)
        {
            return FALSE;
        }
        return TRUE;
    }

    public function block($id_blocker, $id_blocked)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO blocks (id_belong, id_blocker)
        VALUES (:id_blocked, :id_blocker)
        ');
        $DB_REQ->bindValue(':id_blocked', $id_blocked, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id_blocker', $id_blocker, PDO::PARAM_INT);
        $DB_REQ->execute();
    }

    public function unblock($id_unblocker, $id_unblocked)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        DELETE FROM blocks
        WHERE id_belong = :id_unblocked AND id_blocker = :id_unblocker
        ');
        $DB_REQ->bindValue(':id_unblocked', $id_unblocked, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id_unblocker', $id_unblocker, PDO::PARAM_INT);
        $DB_REQ->execute();

        if ($DB_REQ)
            {return TRUE;}
        return FALSE;
    }

    public function canLike($id_liker, $id_liked)
    {
        if (!empty($id_liker) && !empty($id_liked))
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id_liker FROM likes
            WHERE id_belong = :id_belong AND id_liker = :id_liker
            ');
            $DB_REQ->bindValue(':id_belong', $id_liked, PDO::PARAM_INT);
            $DB_REQ->bindValue(':id_liker', $id_liker, PDO::PARAM_INT);
            $DB_REQ->execute();

            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            if ($data['id_liker'])
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }

        }
        return NULL;
    }

    public function canBlock($id_blocker, $id_blocked)
    {
        if (!empty($id_blocker) && !empty($id_blocked))
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id_blocker FROM blocks WHERE id_belong = :id_belong 
            AND id_blocker = :id_blocker
            ');
            $DB_REQ->bindValue(':id_blocker', $id_blocker, PDO::PARAM_INT);
            $DB_REQ->bindValue(':id_belong', $id_blocked, PDO::PARAM_INT);
            $DB_REQ->execute();

            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);

            if($data['id_blocker'])
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        return NULL;
    }

    public function getBlockedUsers($id_blocker)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        SELECT id_belong FROM blocks WHERE id_blocker = :id_blocker
        ');
        $DB_REQ->bindValue(':id_blocker', $id_blocker, PDO::PARAM_INT);
        $DB_REQ->execute();
        $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function countSimilarTags(User $user, User $user_to_compare)
    {
        $tags = $user->tags();
        $tags_to_compare = $user_to_compare->tags();

        $count = count(array_intersect($tags, $tags_to_compare));
        return $count;
    }

    public function getMatches(User $user, $distance)
    {
        if (!empty($user))
        {
            $gender = $user->gender();
            $sexuality = $user->sexuality();

            $DB_REQ = $this->DB_REQ->prepare('
            SELECT * FROM
            (
                SELECT a.login AS from_user, b.login AS to_user, b.id AS to_user_id, b.gender, pictures.src AS to_user_pic, b.sexuality AS to_user_sexuality, b.age as to_user_age, scores.score AS popularity,
                    ROUND(111.1111 * 
                    DEGREES(ACOS(COS(RADIANS(a.Latitude))
                    * COS(RADIANS(b.Latitude))
                    * COS(RADIANS(a.Latitude - b.Longitude))
                    + SIN(RADIANS(a.Latitude))
                    * SIN(RADIANS(b.Latitude)))), 2) AS distance_in_km
            FROM users AS a
            JOIN users AS b ON a.id <> b.id

            INNER JOIN pictures
            ON pictures.id_belong = b.id AND mainpic = 1

            INNER JOIN scores
            ON scores.id_belong = b.id

            WHERE a.login = :from_user AND CASE
                WHEN a.sexuality = "hetero" AND a.gender = "m" THEN b.gender = "f" AND b.sexuality NOT IN ("homo")
                WHEN a.sexuality = "hetero" AND a.gender = "f" THEN b.gender = "m" AND b.sexuality NOT IN ("homo")

                WHEN a.sexuality = "homo" AND a.gender = "m" THEN b.gender = "m" AND b.sexuality NOT IN ("hetero")
                WHEN a.sexuality = "homo" AND a.gender = "f" THEN b.gender = "f" AND b.sexuality NOT IN ("hetero")

                WHEN a.sexuality = "bisexual" AND a.gender = "m" THEN (b.gender = "f" AND b.sexuality NOT IN ("homo")) OR (b.gender = "m" AND b.sexuality NOT IN ("hetero"))
                WHEN a.sexuality = "bisexual" AND a.gender = "f" THEN (b.gender = "f" AND b.sexuality NOT IN ("hetero")) OR (b.gender = "m" AND b.sexuality NOT IN ("homo"))
                END
            AND EXISTS(SELECT * FROM pictures WHERE mainpic = 1)
            AND NOT EXISTS(SELECT * FROM blocks WHERE
                blocks.id_belong = b.id AND blocks.id_blocker = a.id 
                OR
                blocks.id_belong = a.id AND blocks.id_blocker = b.id)
            ORDER BY distance_in_km, cast(scores AS UNSIGNED) DESC
            )
            AS inner_table
            WHERE distance_in_km < :distance
            ');
            $DB_REQ->bindValue(':from_user', $user->login());
            $DB_REQ->bindValue(':distance', $distance, PDO::PARAM_INT);
            $DB_REQ->execute();
            $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        }
    }
}