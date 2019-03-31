<?php

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET, POST');
//header("Access-Control-Allow-Headers: X-Requested-With");

include_once 'db.php';
$ajax_request = file_get_contents('php://input');
$request = json_decode($ajax_request);
switch ($request->action) {
    case 'login' :
        login($request->params);
        break;
    case 'registration' :
        registration($request->params);
        break;
    case 'checkToken' :
        checkToken($request->params);
        break;
    case 'getUserByToken' :
        getUserByToken($request->params);
        break;
    case 'getAllUsers' :
        getAllUsers();
        break;
    case 'updateProfileByToken' :
        updateProfileByToken($request->params);
        break;
    case 'getUserById' :
        getUserById($request->params);
        break;
    case 'editUserById' :
        editUserById($request->params);
        break;
    case 'deleteUserById' :
        deleteUserById($request->params);
        break;
    default :
        break;
}

function login($params)
{
    $db = DB::init();
    $pass = md5($params->pass);
    $asset_token = md5(time() . $pass);

    $bulk = new MongoDB\Driver\BulkWrite();
    $filter = [
        'email' => $params->email,
        'pass' => $pass];

    $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
    $cursor = $db->executeQuery('test.users', $query);
    $arr = $cursor->toArray();
    $id_user = (string)$arr[0]->_id;

    if ($id_user) {
        $bulk->update(['_id' => $arr[0]->_id], ['$set' => ['token' => $asset_token]]);
        $db->executeBulkWrite('test.users', $bulk);
        $to_json = ['error' => '', 'data' => ['accessToken' => $asset_token]];

        echo json_encode($to_json);
    } else {
        $to_json = ['error' => 'login or password incorrect'];

        echo json_encode($to_json);
    }
}

function getUserByToken($params)
{
    $db = DB::init();
    $asset_token = $params->token;
    $filter = ['token' => $asset_token];

    $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
    $cursor = $db->executeQuery('test.users', $query);
    $arr = $cursor->toArray();
    $id_user = (string)$arr[0]->_id;

    if ($id_user) {
        $result = $arr[0];
        $result_response = [
            'email' => $result->email,
            'pass' => $result->pass,
            'token' => $result->token,
            'country' => $result->country,
            'username' => $result->username,
            'last_name' => $result->last_name,
            'first_name' => $result->first_name,
            'company' => $result->company,
            'city' => $result->city,
            'about_me' => $result->about_me
        ];
        $to_json = ['error' => '', 'data' => $result_response];
        echo json_encode($to_json);
    } else {
        $to_json = ['error' => 'no data'];
        echo json_encode($to_json);
    }
}

function getAllUsers()
{
    $db = DB::init();
    $filter = [];

    $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
    $cursor = $db->executeQuery('test.users', $query);
    $arr = $cursor->toArray();

    if (!empty($arr)) {
        $result_response = [];
        foreach ($arr as $result) {
            $result_response[] = [
                'id' => (string)$result->_id,
                'email' => $result->email,
                'country' => $result->country,
                'username' => $result->username,
                'last_name' => $result->last_name,
                'first_name' => $result->first_name,
                'company' => $result->company,
                'city' => $result->city,
                'about_me' => $result->about_me
            ];
        }
        $to_json = ['error' => '', 'data' => $result_response];
        echo json_encode($to_json);
    } else {
        $to_json = ['error' => 'no data'];
        echo json_encode($to_json);
    }
}

function updateProfileByToken($params)
{
    $db = DB::init();
    $asset_token = $params->token;
    $filter = ['token' => $asset_token];
    $bulk = new MongoDB\Driver\BulkWrite();

    $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
    $cursor = $db->executeQuery('test.users', $query);
    $arr = $cursor->toArray();
    $id_user = (string)$arr[0]->_id;

    if ($id_user) {
        $bulk->update(['_id' => new \MongoDB\BSON\ObjectId($id_user)], ['$set' => [
            'country' => $params->country,
            'last_name' => $params->last_name,
            'first_name' => $params->first_name,
            'company' => $params->company,
            'city' => $params->city,
            'about_me' => $params->about_me
        ]]);
        $result = $db->executeBulkWrite('test.users', $bulk);
        if (count($result->getWriteErrors()) > 0) {
            $to_json = ['error' => 'error occurred during update'];
            echo json_encode($to_json);
        } else {
            $to_json = ['error' => ''];
            echo json_encode($to_json);
        }
    } else {
        $to_json = ['error' => 'no data'];
        echo json_encode($to_json);
    }
}

function checkToken($params)
{
    $db = DB::init();
    $filter = ['token' => $params->token];

    $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
    $cursor = $db->executeQuery('test.users', $query);
    $result = $cursor->toArray();
    $result = $result[0]->token;

    if ($result === $params->token && $params->token != null) {
        $to_json = ['error' => ''];
        echo json_encode($to_json);
    } else {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
}

function checkEmail(MongoDB\Driver\Manager $db, string $email)
{
    $filter = ['email' => $email];

    $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
    $cursor = $db->executeQuery('test.users', $query);
    $result = $cursor->toArray();
    $result = $result[0]->email;

    if ($result !== $email && $email != null) {
        return true;
    } else {
        return false;
    }
}

function checkUsername(MongoDB\Driver\Manager $db, string $login)
{
    $filter = ['username' => $login];

    $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
    $cursor = $db->executeQuery('test.users', $query);
    $result = $cursor->toArray();
    $result = $result[0]->username;

    if ($result !== $login && $login != null) {
        return true;
    } else {
        return false;
    }
}

function registration($params)
{
    $db = DB::init();

    if (checkEmail($db, $params->email)){
        if (checkUsername($db, $params->username)){
            $pass = md5($params->pass);
            $bulk = new MongoDB\Driver\BulkWrite();
            $asset_token = md5(time() . $pass);

            $bulk->insert([
                'email' => $params->email,
                'pass' => $pass,
                'token' => $asset_token,
                'country' => $params->country,
                'username' => $params->username,
                'last_name' => $params->last_name,
                'first_name' => $params->first_name,
                'company' => $params->company,
                'city' => $params->city,
                'about_me' => ''
            ]);
            $result = $db->executeBulkWrite('test.users', $bulk);

            if ($result->getInsertedCount()) {
                $to_json = ['error' => '', 'data' => ['accessToken' => $asset_token]];
                echo json_encode($to_json);
            } else {
                $to_json = ['error' => 'Add failed'];
                echo json_encode($to_json);
            }
        } else {
            $to_json = ['error' => 'This username already exists', 'username' => '1'];
            echo json_encode($to_json);
        }
    } else {
        $to_json = ['error' => 'This email already exists', 'email' => '1'];
        echo json_encode($to_json);
    }

}

function getUserById($params)
{
    if ($params->id) {
        $db = DB::init();
        $id_user = new \MongoDB\BSON\ObjectId($params->id);

        $filter = ['_id' => $id_user];

        $query = new MongoDB\Driver\Query($filter, DB::default_options_search());
        $cursor = $db->executeQuery('test.users', $query);
        $arr = $cursor->toArray();
        $result = $arr[0];
        $result_response = [
            'email' => $result->email,
            'country' => $result->country,
            'username' => $result->username,
            'last_name' => $result->last_name,
            'first_name' => $result->first_name,
            'company' => $result->company,
            'city' => $result->city,
            'about_me' => $result->about_me,
            'id' => $params->id
        ];
        $to_json = ['error' => '', 'data' => $result_response];
        echo json_encode($to_json);
    } else {
        $to_json = ['error' => 'no data'];
        echo json_encode($to_json);
    }
}

function editUserById ($params)
{
    if ($params->id) {
        $db = DB::init();
        $bulk = new MongoDB\Driver\BulkWrite();
        $id_user = new \MongoDB\BSON\ObjectId($params->id);

        $bulk->update(['_id' => $id_user], ['$set' => [
            'country' => $params->country,
            'last_name' => $params->last_name,
            'first_name' => $params->first_name,
            'company' => $params->company,
            'city' => $params->city,
            'about_me' => $params->about_me
        ]]);
        $result = $db->executeBulkWrite('test.users', $bulk);
        if (count($result->getWriteErrors()) > 0) {
            $to_json = ['error' => 'error occurred during update'];
            echo json_encode($to_json);
        } else {
            $to_json = ['error' => ''];
            echo json_encode($to_json);
        }
    } else {
        $to_json = ['error' => 'no data'];
        echo json_encode($to_json);
    }
}

function deleteUserById ($params)
{
    if ($params->id) {
        $db = DB::init();
        $bulk = new MongoDB\Driver\BulkWrite();
        $id_user = new \MongoDB\BSON\ObjectId($params->id);

        $bulk->delete(['_id' => $id_user]);
        $result = $db->executeBulkWrite('test.users', $bulk);
        if ($result->getDeletedCount() > 0) {
            $to_json = ['error' => ''];
            echo json_encode($to_json);
        } else {
            $to_json = ['error' => 'no delete'];
            echo json_encode($to_json);
        }
    } else {
        $to_json = ['error' => 'no data'];
        echo json_encode($to_json);
    }
}
