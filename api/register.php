<?php

	$conn = new mysqli("localhost", "group16", "welovegroup16", "COP4331_lamp_group_16"); 	

	ini_set('display_errors', 1);
    error_reporting(E_ALL);
	$inData = getRequestInfo();
	
	$id = 0;
	$first_name = "";
	$last_name = "";


	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("INSERT INTO users (first_name,last_name,username,`password`) VALUES(?,?,?,?)");
		$stmt->bind_param("ssss", $inData["first_name"], $inData["last_name"], $inData["username"], $inData["password"]);
		if ($stmt->execute()) {
            echo "User inserted successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

		$stmt->close();
		$conn->close();
	}
	
	function getRequestInfo()
	{
		return $_POST;
    }

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"first_name":"","last_name":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $first_name, $last_name, $id )
	{
		$retValue = '{"id":' . $id . ',"first_name":"' . $first_name . '","last_name":"' . $last_name . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>