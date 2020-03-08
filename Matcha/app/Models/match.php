<?php


function get_distance($id1, $id2)
{
    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_REQ = $DB_REQ->prepare('
        SELECT longitude FROM users WHERE id = :id1
    ');
    $DB_REQ->bindValue(':id1', $id1, PDO::PARAM_INT);
    $DB_REQ->execute();
    $long11 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    $long1 = (float)$long11[0]['longitude'];
    //print_r($long1);

    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_REQ = $DB_REQ->prepare('
    SELECT latitude FROM users WHERE id = :id1
    ');
    $DB_REQ->bindValue(':id1', $id1, PDO::PARAM_INT);
    $DB_REQ->execute();
    $lat11 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    $lat1 = (float)$lat11[0]['latitude'];
    //print_r($lat1);

    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_REQ = $DB_REQ->prepare('
        SELECT longitude FROM users WHERE id = :id2
    ');
    $DB_REQ->bindValue(':id2', $id2, PDO::PARAM_INT);
    $DB_REQ->execute();
    $long22 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    $long2 = (float)$long22[0]['longitude'];
    //print_r($long2);

    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_REQ = $DB_REQ->prepare('
    SELECT latitude FROM users WHERE id = :id2
    ');
    $DB_REQ->bindValue(':id2', $id2, PDO::PARAM_INT);
    $DB_REQ->execute();
    $lat22 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    $lat2 = (float)$lat22[0]['latitude'];
    //print_r($lat2);
    return (distance($lat1, $long1, $lat2, $long2, "k"));
}

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);
  
    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function sort_by_gs($gender, $sexuality, $id, $ageMin, $ageMax, $scoreMin, $scoreMax)
{
    $res = [];
    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    if ($gender == 'M')
        {
            $genderS = 'F';
        }
        else
        {
            $genderS = 'M';
        }
    if ($sexuality == 'homosexual')
    {
        $DB_REQ = $DB_REQ->prepare('
            SELECT id FROM users WHERE gender = :gender AND sexuality = :sexuality AND id <> :id
            AND age >= :ageMin AND age <= :ageMax AND rating >= :scoreMin AND rating <= :scoreMax
        ');
        $DB_REQ->bindValue(':gender', $gender, PDO::PARAM_STR);
        $DB_REQ->bindValue(':sexuality', $sexuality, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMin', $ageMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMax', $ageMax, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMin', $scoreMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMax', $scoreMax, PDO::PARAM_INT);
        $DB_REQ->execute();
        $ids = $DB_REQ->fetchAll(PDO::FETCH_NUM);
    }
    if ($sexuality == 'heterosexual')
    {
        $DB_REQ = $DB_REQ->prepare('
        SELECT id FROM users WHERE gender = :genderS AND sexuality = :sexuality AND id <> :id
        AND age >= :ageMin AND age <= :ageMax AND rating >= :scoreMin AND rating <= :scoreMax
        ');
        $DB_REQ->bindValue(':genderS', $genderS, PDO::PARAM_STR);
        $DB_REQ->bindValue(':sexuality', $sexuality, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMin', $ageMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMax', $ageMax, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMin', $scoreMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMax', $scoreMax, PDO::PARAM_INT);
        $DB_REQ->execute();
        $ids = $DB_REQ->fetchAll(PDO::FETCH_NUM);
    }
    if ($sexuality == 'bisexual')
    {
        $sexualityS = 'homosexual';
        $DB_REQ = $DB_REQ->prepare('
        SELECT id FROM users
        WHERE sexuality = :sexuality
        AND id <> :id
        AND age >= :ageMin AND age <= :ageMax AND rating >= :scoreMin AND rating <= :scoreMax
        ');
        $DB_REQ->bindValue(':sexuality', $sexuality, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMin', $ageMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMax', $ageMax, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMin', $scoreMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMax', $scoreMax, PDO::PARAM_INT);
        $DB_REQ->execute();
        $ids1 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
        $DB_REQ = $DB_REQ->prepare('
        SELECT id FROM users
        WHERE sexuality = :sexualityS AND gender = :gender AND id <> :id
        AND age >= :ageMin AND age <= :ageMax AND rating >= :scoreMin AND rating <= :scoreMax
        ');
        $DB_REQ->bindValue(':sexualityS', $sexualityS, PDO::PARAM_STR);
        $DB_REQ->bindValue(':gender', $gender, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMin', $ageMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':ageMax', $ageMax, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMin', $scoreMin, PDO::PARAM_INT);
        $DB_REQ->bindValue(':scoreMax', $scoreMax, PDO::PARAM_INT);
        $DB_REQ->execute();
        $ids2 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        $ids = array_merge($ids1, $ids2);
    }
        foreach ($ids as $num => $elem)
        {
            foreach ($elem as $num => $id)
            {
                $res[] = $id;
            }
        }
        return $res;
}

function sort_by_tags($id, $ids, $tagsInCommon)
{
    $res = [];
    if ($ids == NULL)
    {
        return $res;
    }
    foreach ($ids as $elem => $id_to_compare)
    {
        if (array_compare(get_tags($id), get_tags($id_to_compare), $tagsInCommon))
        {
            $res[] = $id_to_compare;
        }
    }
    return $res;
}

function sort_blocked($id, $ids)
{
    $res = [];
    if ($ids == NULL)
    {
        return $res;
    }
    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_REQ = $DB_REQ->prepare('
        SELECT id_blocked FROM blocks WHERE id_belong = :id
    ');
    $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
    $DB_REQ->execute();
    $blocked = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    foreach ($blocked as $block => $value)
    {
        foreach ($value as $b => $id)
        {
            $res[] = $id;
        }
    }
    if ($res == NULL)
    {
        return $ids;
    }
    return array_diff($ids, $res);
}

function sort_liked($id, $ids)
{
    $res = [];
    if ($ids == NULL)
    {
        return $res;
    }
    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_REQ = $DB_REQ->prepare('
        SELECT id_liked FROM likes WHERE id_belong = :id
    ');
    $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
    $DB_REQ->execute();
    $blocked = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    foreach ($blocked as $block => $value)
    {
        foreach ($value as $b => $id)
        {
            $res[] = $id;
        }
    }
    if ($res == NULL)
    {
        return $ids;
    }
    return array_diff($ids, $res);
}

function sort_by_distanse($id, $ids, $locationMax)
{
    $i = 0;
    $res = [];
    foreach ($ids as $ids1 => $id_to_compare)
    {
        if (intval(get_distance($id, $id_to_compare)) < $locationMax)
        {
            $res[] = intval(get_distance($id, $id_to_compare));
        }
        else{
            unset($ids[$ids1]);
        }
    }
    return array_combine($ids, $res);
}

function array_compare($arr1, $arr2, $tags)
{
    $i = 0;
    $count = 0;
    while ($i < 5)
    {
        if ($arr1[$i] == "1" && $arr2[$i] == "1")
        {
            $count++;
        }
        $i++;
    }
    if ($count >= $tags)
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}


function get_tags($id)
{
    $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
    $DB_REQ = $DB_REQ->prepare('
    SELECT algorythm, graphics, unix, sysadmin, web FROM tags WHERE id_belong = :id
    ');
    $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
    $DB_REQ->execute();
    $tags = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tags as $num => $elem)
        {
            foreach ($elem as $num => $id)
            {
                $res[] = $id;
            }
        }
    return $res;
}

function sort_by_age($ids)
{
    $agearr = [];
    foreach ($ids as $id)
    {
        $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
        $DB_REQ = $DB_REQ->prepare('
            SELECT age FROM users WHERE id = :id
        ');
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        $age1 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        $age11 = $age1[0]['age'];
        $agearr[] = $age11;
    }
    $tmp = array_combine($ids, $agearr);
    asort($tmp);
    return array_keys($tmp);
}

function sort_by_rating($ids)
{
    $agearr = [];
    foreach ($ids as $id)
    {
        $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
        $DB_REQ = $DB_REQ->prepare('
            SELECT rating FROM users WHERE id = :id
        ');
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        $age1 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
        $age11 = $age1[0]['rating'];
        $agearr[] = $age11;
    }
    $tmp = array_combine($ids, $agearr);
    asort($tmp);
    return array_keys($tmp);
}


 function hasLiked($id1, $id2)
    {
        $DB_REQ = new PDO('mysql:host=localhost;dbname=rootpokrov_matcha', 'rootpokrov', 'yh3zuey@fGhu');
		$DB_REQ = $DB_REQ->prepare('
        SELECT COUNT(*) FROM likes WHERE id_belong = :id2 AND id_liked = :id1
        ');
        $DB_REQ->bindValue(':id2', $id1, PDO::PARAM_INT);
        $DB_REQ->bindValue(':id1', $id2, PDO::PARAM_INT);
        $DB_REQ->execute();
        if ($DB_REQ->fetchColumn() > 0)
        {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
//$res = sort_by_gs('M', 'homosexual');
//print_r($res);
//var_dump(sort_by_gs('F', 'homosexual', 4));
//print_r(sort_by_distanse(2, sort_blocked(2, sort_by_gs('F', 'bisexual', 2))));
//var_dump(getMatches('F', 2, 'bisexual'));
