<?php
namespace App\Models;
use App\Models\User;
use \PDO;
include "match.php";

class UserManagerPDO extends UserManager
{
    protected $DB_REQ;

    public function __construct(PDO $DB_REQ)
    {
        $this->DB_REQ = $DB_REQ;
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
                    $values[$choice] = "1";
                }
                else {
                    $values[$choice] = "0";
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

	public function getUnique($id /*getIdFromLogin($_SESSION['login']*/)
    {
        if (isset($id) && !empty($id)) {
            $DB_REQ = $this->DB_REQ->prepare('
                SELECT id, firstName, lastName, email, age, rating, gender, sexuality, bio, updated_at
                FROM users
                WHERE id = :id
                ');
            $DB_REQ->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $DB_REQ->execute();
            $DB_REQ->setFetchMode(PDO::FETCH_CLASS, 'App\Models\User');
            $user = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            $DB_REQ->closeCursor();
            $DB_REQ = $this->DB_REQ->prepare('
                SELECT algorythm, graphics, unix, sysadmin, web
                FROM tags
                WHERE id_belong = :id_belong
                ');
            $DB_REQ->bindValue(':id_belong', $id, PDO::PARAM_INT);
            $DB_REQ->execute();
            $tags = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            if ($tags) {
                $user['tags'] = $tags;
            }
            $DB_REQ->closeCursor();
            $DB_REQ = $this->DB_REQ->prepare('
                SELECT source FROM pictures
                WHERE id_belong = :id_belong
                    AND mainpic = :mainpic
                ');
            $DB_REQ->bindValue(':id_belong', $id, PDO::PARAM_INT);
            $DB_REQ->bindValue(':mainpic', 0, PDO::PARAM_INT);
            $DB_REQ->execute();
            $pictures= $DB_REQ->fetchAll(PDO::FETCH_COLUMN);
            if ($pictures) {
                $user['pictures'] = $pictures;
            }
            $DB_REQ->closeCursor();
            $DB_REQ = $this->DB_REQ->prepare('
                SELECT source FROM pictures
                WHERE id_belong = :id_belong
                    AND mainpic = :mainpic
            ');
            $DB_REQ->bindValue(':id_belong', $id, PDO::PARAM_INT);
            $DB_REQ->bindValue(':mainpic', 1, PDO::PARAM_INT);
            $DB_REQ->execute();
            $mainpicture= $DB_REQ->fetch(PDO::FETCH_ASSOC);
            if ($mainpicture) {
                $user['mainpicture'] = $mainpicture['source'];
            }
            $DB_REQ->closeCursor();
            //PLACEHOLDER POPULARITY+MAP
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
            return "Wrong confirmation link.";
        }
        else if ($find[0]['isActive'] == '1')
        {
            $this->userActive($hash);
			return "Thank you for confirming your email address. Your account has been activated.";
        }
        else if ($find[0]['isActive'] == '0')
        {
			return "This user is already activated.";
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

    public function countPictures($id)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        SELECT COUNT(*) as count FROM pictures WHERE id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id);
        $DB_REQ->execute();
        $result = $DB_REQ->fetch(PDO::FETCH_ASSOC);
        return intval($result['count']);
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
        UPDATE users SET rating = rating + 1 WHERE id = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id_liked, PDO::PARAM_INT);
        $DB_REQ->execute();
        return $lastId;
    }

    public function dislike($id_disliker, $id_disliked)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        DELETE FROM likes WHERE id_belong = :id_disliked AND id_liked = :id_disliker
        ');
        $DB_REQ->bindValue(':id_disliker', $id_disliker, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id_disliked', $id_disliked, PDO::PARAM_INT);
        $DB_REQ->execute();
		$DB_REQ->closeCursor();
		$DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET rating = rating - 1 WHERE id = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id_disliker, PDO::PARAM_INT);
        $DB_REQ->execute();
        $DB_REQ->closeCursor();
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

    public function listLikes($id)
    {
		$result = [];
        $hasLike = [];
        $DB_REQ = $this->DB_REQ->prepare('
        SELECT id_liked FROM likes WHERE id_belong = :id
        ');
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        $res = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $num => $value)
        {
            foreach ($value as $bl => $id1)
            {
                $result[] = (int)$id1;
            }
        }
        foreach ($result as $key => $value)
        {
            if (hasLiked($value, (int)$id))
            {
                $hasLike[] = 1;
            }
            else {
                $hasLike[] = 0;
            }
        }
        $superResult = array_combine($result, $hasLike);
        return $superResult;
    }

    public function block($id_blocker, $id_blocked)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO blocks (id_belong, id_blocked)
        VALUES (:id_blocker, :id_blocked)
        ');
        $DB_REQ->bindValue(':id_blocked', $id_blocked, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id_blocker', $id_blocker, PDO::PARAM_INT);
        $DB_REQ->execute();
		
		$DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET rating = rating - 1 WHERE id = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id_blocked, PDO::PARAM_INT);
        $DB_REQ->execute();
    }

    public function unblock($id_unblocker, $id_unblocked)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        DELETE FROM blocks
        WHERE id_belong = :id_unblocker AND id_blocked = :id_unblocked
        ');
        $DB_REQ->bindValue(':id_unblocked', $id_unblocked, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id_unblocker', $id_unblocker, PDO::PARAM_INT);
        $DB_REQ->execute();
		$DB_REQ->closeCursor();
		
		$DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET rating = rating + 1 WHERE id = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id_unblocked, PDO::PARAM_INT);
        $DB_REQ->execute();
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
	
	public function getUserFromLogin($login) {
        if ($login) {
            $DB_REQ = $this->DB_REQ->prepare('
                SELECT id
                FROM users
                WHERE login = :login');
            $DB_REQ->bindValue(':login', $login);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            return ($data['id']);
        }
        return NULL;
    }
	
	public function addTotal(User $user)
    {
        $DB_REQ = $this->DB_REQ->prepare('UPDATE users SET bio = :bio, sexuality = :sexuality, gender = :gender WHERE id = :id;');
        $DB_REQ->bindValue(':gender', $user->gender());
        $DB_REQ->bindValue(':bio', $user->bio());
        $DB_REQ->bindValue(':sexuality', $user->sexuality());
        $DB_REQ->bindValue(':id', $user->id());
        $DB_REQ->execute();
    }
	
	public function addExtras(User $user, $tags)
    {
        $values = [];
		if (in_array('algorythm', $tags))
        {
            $values['algorythm'] = 1;
        }
        else{
            $values['algorythm'] = 0;
        }
        if (in_array('graphics', $tags))
        {
            $values['graphics'] = 1;
        }
        else{
            $values['graphics'] = 0;
        }
        if (in_array('sysadmin', $tags))
        {
            $values['sysadmin'] = 1;
        }
        else{
            $values['sysadmin'] = 0;
        }
        if (in_array('unix', $tags))
        {
            $values['unix'] = 1;
        }
        else{
            $values['unix'] = 0;
        }
        if (in_array('web', $tags))
        {
            $values['web'] = 1;
        }
        else{
            $values['web'] = 0;
        }
        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO tags (id_belong, algorythm, graphics, unix, sysadmin, web)
      VALUES (:id_belong, :algorythm, :graphics, :unix, :sysadmin, :web)
      ON DUPLICATE KEY UPDATE
        algorythm = :algorythm,
        graphics = :graphics,
        unix = :unix,
        sysadmin = :sysadmin,
        web = :web;');
        $DB_REQ->bindValue(':id_belong', $user->id());
        $DB_REQ->bindValue(':algorythm', $values['algorythm']);
        $DB_REQ->bindValue(':graphics', $values['graphics']);
        $DB_REQ->bindValue(':unix', $values['unix']);
        $DB_REQ->bindValue(':sysadmin', $values['sysadmin']);
        $DB_REQ->bindValue(':web', $values['web']);
        $DB_REQ->execute();
    }
	
	public function addPicture($source, $id)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        SELECT COUNT(*) as count FROM pictures WHERE id_belong = :id_belong
        ');
        $DB_REQ->bindValue(':id_belong', $id);
        $DB_REQ->execute();
        $result = $DB_REQ->fetch(PDO::FETCH_ASSOC);
        if (intval($result['count']) < 5)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT COUNT(mainpic) as count FROM pictures WHERE id_belong = :id_belong AND mainpic = 1
            ');
            $DB_REQ->bindValue(':id_belong', $id);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            if ($data['count'] == 1)
            {$mainpic = 0;}
            else
            {$mainpic = 1;}
            $DB_REQ = $this->DB_REQ->prepare('
            INSERT INTO pictures (id_belong, source, mainpic) VALUES (:id_belong, :source, :mainpic)
            ');
            $DB_REQ->bindValue(':id_belong', $id);
            $DB_REQ->bindValue(':source', $source);
            $DB_REQ->bindValue(':mainpic', $mainpic);
            $DB_REQ->execute();
        }
        return NULL;
    }
	
	public function getGenderFromLogin ($login)
    {
        if ($login)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT gender FROM users WHERE login = :login
            ');
            $DB_REQ->bindValue(':login', $login);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            return $data['gender'];
        }
        return NULL;
    }
    public function getSexualityFromLogin ($login)
    {
        if ($login)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT sexuality FROM users WHERE login = :login
            ');
            $DB_REQ->bindValue(':login', $login);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            return $data['sexuality'];
        }
        return NULL;
    }
	
	public function getMatches($id, $gender, $sexuality, $sort, $ageMin, $ageMax, $scoreMin, $scoreMax, $locationMax, $tagsInCommon)
	{
		$res1 = sort_by_tags($id, sort_liked($id, sort_blocked($id, sort_by_gs($gender, $sexuality, $id, $ageMin, $ageMax, $scoreMin, $scoreMax))), $tagsInCommon);
		//asort($res);
		if ($sort == 1)
		{
			$res = sort_by_distanse($id, $res1, $locationMax);
			asort($res);
		}
		if ($sort == 2)
		{
			$res2 = sort_by_age($res1);
			$res = sort_by_distanse($id, $res2, $locationMax);
		}
		if ($sort == 3)
		{
			$res2 = sort_by_rating($res1);
			$res = sort_by_distanse($id, $res2, $locationMax);
		}
		return $res;
	}
	
	public function deletePicture($source, $id)
    {
        $DB_REQ = $this->prepare('
        DELETE FROM pictures WHERE source = :source AND id_belong = :id
        ');
        $DB_REQ->bindValue(':id', $id);
        $DB_REQ->bindValue(':source', $source);
        $DB_REQ->execute();
    }
	
	public function changeFirstName($id, $firstName)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET firstname = :firstname WHERE id = :id
        ');
        $DB_REQ->bindValue(':firstname', $firstName, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        return 1;
    }
    public function changeLastName($id, $lastName)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET lastname = :lastname WHERE id = :id
        ');
        $DB_REQ->bindValue(':lastname', $lastName, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        return 1;
    }
    public function changeMail($id, $email)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET email = :email WHERE id = :id
        ');
        $DB_REQ->bindValue(':email', $email, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        return 1;
    }
    public function changePass($id, $pass)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        UPDATE users SET password = :password WHERE id = :id
        ');
        $DB_REQ->bindValue(':password', $pass, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        return 1;
    }
	
	public function setCoords($longitude, $latitude, $id)
    {
        $DB_REQ = $this->DB_REQ->prepare('
            UPDATE users SET longitude = :longitude, latitude = :latitude WHERE id = :id
        ');
        $DB_REQ->bindValue(':longitude', $longitude);
        $DB_REQ->bindValue(':latitude', $latitude);
        $DB_REQ->bindValue(':id', $id);
        $DB_REQ->execute();
    }
	
	public function listBlocks($id)
    {
        $result = [];
        $DB_REQ = $this->DB_REQ->prepare('
        SELECT id_blocked FROM blocks WHERE id_belong = :id
        ');
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        $res = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $num => $value)
        {
            foreach ($value as $bl => $id)
            {
                $result[] = $id;
            }
        }
        return $result;
    }
	
	public function saveMsg($poster, $receptor, $message) {
        $DB_REQ = $this->DB_REQ->prepare('
            INSERT INTO chat (id_poster, id_receptor, message, date_message)
            VALUES (:id_poster, :id_receptor, :message, NOW())
            ');
        $DB_REQ->bindValue(':id_poster', $poster);
        $DB_REQ->bindValue(':id_receptor', $receptor);
        $DB_REQ->bindValue(':message', $message);
        $DB_REQ->execute();
    }
    public function getChatMsg($user1, $user2) {
        $DB_REQ = $this->DB_REQ->prepare('
            SELECT *
            FROM chat
            WHERE (id_poster = :user1 AND id_receptor = :user2)
            OR (id_poster = :user2 AND id_receptor = :user1)
            ORDER BY date_message
        ');
        $DB_REQ->bindValue(':user1', $user1);
        $DB_REQ->bindValue(':user2', $user2);
        $DB_REQ->execute();
        $data = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
	
    public function updateLastSeen($userID) {
		$DB_REQ = $this->DB_REQ->prepare('
			UPDATE users
			SET updated_at = NOW()
			WHERE id = :id
		');
	
		$DB_REQ->bindValue(':id', $userID, PDO::PARAM_INT);
	
		$DB_REQ->execute();

		$DB_REQ->closeCursor();
    }
   
    public function isOnline($userID) {
		$DB_REQ = $this->DB_REQ->prepare('
			SELECT updated_at
			FROM users
			WHERE id = :id
		');
		$DB_REQ->bindValue(':id', $userID, PDO::_PARAM_INT);
		$DB_REQ->execute();

		$data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
			
		date_default_timezone_set('Europe/Moscow');
		$date = strtotime($data['updated_at']);
		$now = time();

		if (round(abs($date - $now) / 60) > 20) {
			return FALSE;
		}
		return TRUE;
	}
}