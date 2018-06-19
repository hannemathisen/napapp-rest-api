<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Connect to database
$connection = mysqli_connect('host','user','password','db');

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {
  case 'GET':
    // Retrive
    $id=0;
    $car_id=0;
    $user_id=0;
    $start_time=0;
    $end_time=0;
    if (!empty($_GET["id"])) {
      $id = intval($_GET["id"]);
      get_rides($id, $car_id, $user_id, $start_time, $end_time);
      break;
    } if (!empty($_GET["car_id"])) {
      $car_id = intval($_GET["car_id"]);
    } if (!empty($_GET["user_id"])) {
      $user_id = intval($_GET["user_id"]);
    } if (!empty($_GET["start_time"])) {
      $start_time = intval($_GET["start_time"]);
    } if (!empty($_GET["end_time"])) {
      $end_time = intval($_GET["end_time"]);
    }
    get_rides($id, $car_id, $user_id, $start_time, $end_time);
    break;
  case 'POST':
    // Insert
    insert_ride();
    break;
  case 'PUT':
    // Update
    $id = intval($_GET["id"]);
    update_ride($id);
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

function get_rides($id, $car_id, $user_id, $start_time, $end_time) {
  global $connection;
  $query = "SELECT * FROM rides as r INNER JOIN cars as c ON r.car_id = c.car_id INNER JOIN users as u on r.user_id = u.user_id";

  if ($id != 0) {
    $query .= " WHERE r.ride_id=".$id." LIMIT 1";
  } else if ($car_id != 0 || $user_id != 0 || $start_time != 0 || $end_time != 0){
    $where = false;
    if ($car_id != 0) {
      $query .= " WHERE r.car_id=".$car_id;
      $where = true;
    }
    if ($user_id != 0) {
      if ($where) $query .= " AND r.user_id=".$user_id;
      else {
        $query .= " WHERE r.user_id=".$user_id;
        $where = true;
      }
    }
    if ($start_time != 0) {
      if ($where)  $query .= " AND start_time>=FROM_UNIXTIME(".$start_time.")";
      else {
        $query .= " WHERE start_time>=FROM_UNIXTIME(".$start_time.")";
        $where = true;
      }
    }
    if ($end_time != 0) {
      if ($where) $query .= " AND end_time<=FROM_UNIXTIME(".$end_time.")";
      else {
        $query .= " WHERE end_time<=FROM_UNIXTIME(".$end_time.")";
      }
    }
  }
  //
    // if ($car_id != 0) {
    //   $query .= " WHERE r.car_id=".$car_id;
    //   if ($user_id != 0) {
    //     $query .= " AND r.user_id=".$user_id;
    //   } if ($start_time != 0) {
    //     $query .= " AND start_time>=FROM_UNIXTIME(".$start_time.")";
    //   } if ($end_time != 0) {
    //     $query .= " AND end_time<=FROM_UNIXTIME(".$end_time.")";
    //   }
    // }
    //
    // else if ($user_id != 0) {
		// 	$query .= " WHERE user_id=".$user_id;
    //   if ($start_time != 0) {
    //    $query .= " AND start_time>=FROM_UNIXTIME(".$start_time.")";
    //  } if ($end_time != 0) {
    //    $query .= " AND end_time<=FROM_UNIXTIME(".$end_time.")";
    //  }
		// }
    //
    // else if ($start_time != 0) {
    //   $query .= " WHERE start_time>=FROM_UNIXTIME(".$start_time.")";
    //   if ($end_time != 0) {
    //     $query .= " AND end_time<=FROM_UNIXTIME(".$end_time.")";
    //   }
    // }
    //
    // else if ($end_time != 0) {
    //   $query .= " WHERE end_time<=FROM_UNIXTIME(".$end_time.")";
    // }

		$response = array();
		$result = mysqli_query($connection, $query);

    foreach ($result as $row) {
      extract($row);
      $ride_item = array(
        "ride_id" => $ride_id,
        "car_id" => $car_id,
        "reg_number" => $reg_number,
        "user_id" => $user_id,
        "name" => $name,
        "start_latitude" => $start_latitude,
        "start_longitude" => $start_longitude,
        "start_time" => $start_time,
        "via_latitude" => $via_latitude,
        "via_longitude" => $via_longitude,
        "via_time" => $via_time,
        "end_latitude" => $end_latitude,
        "end_longitude" => $end_longitude,
        "end_time" => $end_time,
      );
      array_push($response, $ride_item);
    }

		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function insert_ride() {
		global $connection;

    $data = json_decode(file_get_contents("php://input"));

    $car_id = $data->car_id;
    $user_id = $data->user_id;
    $start_latitude = $data->start_latitude;
    $start_longitude = $data->start_longitude;
    $start_time = $data->start_time;
    $via_latitude = $data->via_latitude;
    $via_longitude = $data->via_longitude;
    $via_time = $data->via_time;
    $end_latitude = $data->end_latitude;
    $end_longitude = $data->end_longitude;
    $end_time = $data->end_time;

    // $car_id = $_POST["car_id"];
    // $user_id = $_POST["user_id"];
		// $start_latitude = $_POST["start_latitude"];
		// $start_longitude = $_POST["start_longitude"];
    // $start_time = $_POST["start_time"];
    // $via_latitude = $_POST["via_latitude"];
    // $via_longitude = $_POST["via_longitude"];
    // $via_time = $_POST["via_time"];
    // $end_latitude = $_POST["end_latitude"];
    // $end_longitude = $_POST["end_longitude"];
    // $end_time = $_POST["end_time"];

    $query = sprintf(
      "INSERT INTO rides (car_id, user_id, start_latitude, start_longitude, start_time, via_latitude, via_longitude, via_time, end_latitude, end_longitude, end_time)
      VALUES ('%s', '%s', '%s', '%s', FROM_UNIXTIME('%s'), '%s', '%s', FROM_UNIXTIME('%s'), '%s', '%s', FROM_UNIXTIME('%s'))",
      $car_id, $user_id, $start_latitude, $start_longitude, $start_time, $via_latitude, $via_longitude, $via_time, $end_latitude, $end_longitude, $end_time);
      // mysqli_real_escape_string($conn, $car_id),
      // mysqli_real_escape_string($conn, $user_id),
      // mysqli_real_escape_string($conn, $start_latitude),
      // mysqli_real_escape_string($conn, $start_longitude),
      // mysqli_real_escape_string($conn, $start_time),
      // mysqli_real_escape_string($conn, $via_latitude),
      // mysqli_real_escape_string($conn, $via_longitude),
      // mysqli_real_escape_string($conn, $via_time),
      // mysqli_real_escape_string($conn, $end_latitude),
      // mysqli_real_escape_string($conn, $end_longitude),
      // mysqli_real_escape_string($conn, $end_time));

		// $query = "INSERT INTO rides SET reg_number='{$reg_number}', latitude={$latitude}, longitude={$longitude}, booked='{$booked}'";

    if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'Ride Added Successfully.'
			);
		}	else {
			$response=array(
				'status' => 0,
				'status_message' => 'Ride Addition Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function update_ride($id)
	{
		global $connection;

    // old code
    $data = json_decode(file_get_contents("php://input"));

    $startQuery = sprintf("UPDATE rides SET");

    foreach ($data as $key => $value) {
      $startQuery .= sprintf(" %s=%s,", $key, htmlspecialchars($value));
    }

    $query = rtrim($startQuery, ",") . sprintf(" WHERE ride_id=%s", $id);

    if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'Ride Updated Successfully.'
			);
		}	else {
			$response = array(
				'status' => 0,
				'status_message' => 'Ride Updation Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  function delete_ride($id)
	{
		global $connection;
		$query = "DELETE FROM rides WHERE ride_id=" .$id;
		if (mysqli_query($connection, $query)) {
			$response = array(
				'status' => 1,
				'status_message' => 'Ride Deleted Successfully.'
			);
		}	else {
			$response = array(
				'status' => 0,
				'status_message' => 'Ride Deletion Failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

  mysqli_close($connection);

?>
