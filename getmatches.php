#!/usr/bin/php
<?php



function getMatches($gender, $id, $sexuality)
{
    $res = sort_by_distanse($id, sort_by_tags($id, sort_blocked($id, sort_by_gs($gender, $sexuality, $id))));
    asort($res);
    return $res;
}

function get_distance($id1, $id2)
{
    $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
    $DB_REQ = $DB_REQ->prepare('
        SELECT longitude FROM users WHERE id = :id1
    ');
    $DB_REQ->bindValue(':id1', $id1, PDO::PARAM_INT);
    $DB_REQ->execute();
    $long11 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    $long1 = (float)$long11[0]['longitude'];
    //print_r($long1);

    $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
    $DB_REQ = $DB_REQ->prepare('
    SELECT latitude FROM users WHERE id = :id1
    ');
    $DB_REQ->bindValue(':id1', $id1, PDO::PARAM_INT);
    $DB_REQ->execute();
    $lat11 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    $lat1 = (float)$lat11[0]['latitude'];
    //print_r($lat1);

    $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
    $DB_REQ = $DB_REQ->prepare('
        SELECT longitude FROM users WHERE id = :id2
    ');
    $DB_REQ->bindValue(':id2', $id2, PDO::PARAM_INT);
    $DB_REQ->execute();
    $long22 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);
    $long2 = (float)$long22[0]['longitude'];
    //print_r($long2);

    $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
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

function sort_by_gs($gender, $sexuality, $id)
{
    $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
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
        ');
        $DB_REQ->bindValue(':gender', $gender, PDO::PARAM_STR);
        $DB_REQ->bindValue(':sexuality', $sexuality, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        $ids = $DB_REQ->fetchAll(PDO::FETCH_NUM);
    }
    if ($sexuality == 'heterosexual')
    {
        $DB_REQ = $DB_REQ->prepare('
        SELECT id FROM users WHERE gender = :genderS AND sexuality = :sexuality AND id <> :id
        ');
        $DB_REQ->bindValue(':genderS', $genderS, PDO::PARAM_STR);
        $DB_REQ->bindValue(':sexuality', $sexuality, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
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
        ');
        $DB_REQ->bindValue(':sexuality', $sexuality, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
        $DB_REQ->execute();
        $ids1 = $DB_REQ->fetchAll(PDO::FETCH_ASSOC);

        $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
        $DB_REQ = $DB_REQ->prepare('
        SELECT id FROM users
        WHERE sexuality = :sexualityS AND gender = :gender AND id <> :id
        ');
        $DB_REQ->bindValue(':sexualityS', $sexualityS, PDO::PARAM_STR);
        $DB_REQ->bindValue(':gender', $gender, PDO::PARAM_STR);
        $DB_REQ->bindValue(':id', $id, PDO::PARAM_INT);
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

function sort_by_tags($id, $ids)
{
    foreach ($ids as $elem => $id_to_compare)
    {
        if (array_compare(get_tags($id), get_tags($id_to_compare)))
        {
            $res[] = $id_to_compare;
        }
    }
    return $res;
}

function sort_blocked($id, $ids)
{
    $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
    $DB_REQ = $DB_REQ->prepare('
        SELECT id_blocker FROM blocks WHERE id_belong = :id
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
    return array_diff($ids, $res);
}

function sort_by_distanse($id, $ids)
{
    $i = 0;
    foreach ($ids as $ids1 => $id_to_compare)
    {
        $res[] = intval(get_distance($id, $id_to_compare));
    }
    return array_combine($ids, $res);
}

function array_compare($arr1, $arr2)
{
    $i = 0;
    while ($i < 5)
    {
        if ($arr1[$i] == $arr2[$i])
        {
            return TRUE;
        }
        $i++;
    }
    return FALSE;
}


function get_tags($id)
{
    $DB_REQ = new PDO('mysql:host=localhost;dbname=matcha', 'root', 'root');
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

//$res = sort_by_gs('M', 'homosexual');
//print_r($res);
//var_dump(sort_by_gs('F', 'homosexual', 4));
//print_r(sort_by_distanse(2, sort_blocked(2, sort_by_gs('F', 'bisexual', 2))));
var_dump(getMatches('F', 2, 'bisexual'));
