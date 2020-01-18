<?php
namespace Models;
use User;
use \PDO;

class UserManagePDO extends UserManager
{
    protected $DB_REQ;

    public function __construct(PDO $DB_REQ)
    {
        $this->DB_REQ = $DB_REQ;
    }

    protected function add(User $user)
    {
        $DB_REQ = $this->DB_REQ->prepare('
        INSERT INTO users(login, firstName, lastName, age, password, email)
        VALUES(:login, :firstName, :lastName, :age, :password, :email)
        ');
        $DB_REQ->bindValue(':login', $user->login());
        $DB_REQ->bindValue(':email', $user->email());
        $DB_REQ->bindValue(':firstName', $user->firstName());
        $DB_REQ->bindValue(':lastName', $user->lastName());
        $DB_REQ->bindValue(':age', $user->age());
        $DB_REQ->bindValue(':password', $user->password());
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

            $DB_REQ = $this->DB_REQ->prepare('
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

            $user->setPopularity($data['score']);

            return $user;
        }
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

    public function getUserFromEmail($email)
    {
        if ($email)
        {
            $DB_REQ = $this->DB_REQ->prepare('
            SELECT id FROM users WHERE email = :email
            ');
            $DB_REQ->bindValue(':email', $email);
            $DB_REQ->execute();
            $data = $DB_REQ->fetch(PDO::FETCH_ASSOC);
            return (self::getUnique($data['id']));
        }
        return NULL;
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

    




}

?>