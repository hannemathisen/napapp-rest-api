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
      get_cars($id);
    } else {
      get_cars();
    }
    break;
  case 'POST':
    // Insert
    insert_car();
    break;
  case 'PUT':
    // Update
    $id = intval($_GET["id"]);
    update_car($id);
    break;
  case 'DELETE':
    // Delete
    $id = intval($_GET["id"]);
    delete_product($id);
    break;
  default:
    // Invalid Request Method
    header("HTTP/1.0 405 Method Not Allowed");
    break;
}

function get_cars($id=0) {
		global $connection;
		$query = "SELECT * FROM cars";
		if ($id != 0) {
			$query .= " WHERE car_id=".$id." LIMIT 1";
		}
		$response = array();
		$result = mysqli_query($connection, $query);

    // retrieve table contents
    foreach ($result as $row) {
      extract($row);
      $car_item = array(
        "id" => $car_id,
        "reg_number" => $reg_number,
        "longitude" => $longitude,
        "latitude" => $latitude,
        "booked" => $booked,
        "brand" => $brand,
        "type" => $type,
        "color" => $color,
        "year" => $year
      );

      if ($booked == 0) {
        array_push($response, $car_item);
      }
    }

		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function insert_car() {
		global $connection;
		$reg_number = $_POST["reg_number"];
		$latitude = $_POST["latitude"];
		$longitude = $_POST["longitude"];
    $booked = $_POST["booked"];
		$brand = $_POST["brand"];
    $type = $_POST["type"];
    $color = $_POST["color"];
    $year = $_POST["year"];

    $query = sprintf(
      "INSERT INTO cars (reg_number, latitude, longitude, booked, brand, type, color, year)
      VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
      mysqli_real_escape_string($conn, $reg_number),
      mysqli_real_escape_string($conn, $latitude),
      mysqli_real_escape_string($conn, $longitude),
      mysqli_real_escape_string($conn, $booked),
      mysqli_real_escape_string($conn, $brand),
      mysqli_real_escape_string($conn, $type),
      mysqli_real_escape_string($conn, $color),
      mysqli_real_escape_string($conn, $year));

    if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'Car Added Successfully.'
			);
		}	else {
			$response=array(
				'status' => 0,
				'status_message' => 'Car Addition Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function update_car($id)
	{
		global $connection;

    // old code
    $data = json_decode(file_get_contents("php://input"));

    $startQuery = sprintf("UPDATE cars SET");

    foreach ($data as $key => $value) {
      $startQuery .= sprintf(" %s=%s,", $key, htmlspecialchars($value));
    }

    $query = rtrim($startQuery, ",") . sprintf(" WHERE car_id=%s", $id);

    if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'Car Updated Successfully.'
			);
		}	else {
			$response = array(
				'status' => 0,
				'status_message' => 'Car Updation Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function delete_car($id)
	{
		global $connection;
		$query = "DELETE FROM cars WHERE car_id=" .$id;
		if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'Car Deleted Successfully.'
			);
		}	else {
			$response = array(
				'status' => 0,
				'status_message' => 'Car Deletion Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  mysqli_close($connection);

?>
