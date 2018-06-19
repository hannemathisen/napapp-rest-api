<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Connect to database
$connection = mysqli_connect('host','user','password','db');

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
  case 'GET':
    // Retrive
    if (!empty($_GET["id"])) {
      $id = intval($_GET["id"]);
      get_users($id);
    } else {
      get_users();
    }
    break;
  case 'POST':
    // Insert
    insert_user();
    break;
  case 'PUT':
    // Update
    $id = intval($_GET["id"]);
    update_user($id);
    break;
  case 'DELETE':
    // Delete
    $id = intval($_GET["id"]);
    delete_product($id);
    break;
  default:
    // Invalid Request Method
    header("HTTP/1.0 405 Method Not Allowed");
    // echo "Not allowed"
    break;
}

function get_users($id=0) {
		global $connection;
		$query = "SELECT * FROM users";
		if ($id != 0) {
			$query .= " WHERE user_id=".$id." LIMIT 1";
		}
		$response = array();
		$result = mysqli_query($connection, $query);

    // retrieve table contents
    foreach ($result as $row) {
      extract($row);
      $user_item = array(
        "id" => $user_id,
        "email" => $email,
        "name" => $name,
        "password" => $password,
        "home_address" => $home_address,
      );
      // echo $user_item;
      array_push($response, $user_item);
    }

		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function insert_user() {
		global $connection;
    echo "hello";
		$password = $_POST["password"];
		$name = $_POST["name"];
    $home_address = $_POST["home_address"];
    $email = $_POST["email"];

    $query = sprintf(
      "INSERT INTO users (email,password, name, home_address)
      VALUES ('%s', 'PASSWORD(%s)', '%s', '%s')",
      mysqli_real_escape_string($conn, $email),
      mysqli_real_escape_string($conn, $password),
      mysqli_real_escape_string($conn, $name),
      mysqli_real_escape_string($conn, $home_address));

    if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'User Added Successfully.'
			);
		}	else {
			$response=array(
				'status' => 0,
				'status_message' => 'User Addition Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function update_user($id)
	{
		global $connection;

    // old code
    $data = json_decode(file_get_contents("php://input"));

    $startQuery = sprintf("UPDATE users SET");

    foreach ($data as $key => $value) {
      $startQuery .= sprintf(" %s=%s,", $key, htmlspecialchars($value));
    }

    $query = rtrim($startQuery, ",") . sprintf(" WHERE user_id=%s", $id);

    if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'User Updated Successfully.'
			);
		}	else {
			$response = array(
				'status' => 0,
				'status_message' => 'User Updation Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function delete_user($id)
	{
		global $connection;
		$query = "DELETE FROM users WHERE user_id=" .$id;
		if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'User Deleted Successfully.'
			);
		}	else {
			$response = array(
				'status' => 0,
				'status_message' => 'User Deletion Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  mysqli_close($connection);

?>
