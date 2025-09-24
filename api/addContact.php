<?php
	$data = getRequestInfo();
	
	$first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $phone = $data['phone_number'];
    $email = $data['email'];
    $id = $data['user_id'];

	$conn = new mysqli("localhost", "group16", "welovegroup16", "COP4331_lamp_group_16");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$stmt = $conn->prepare("INSERT into contacts (user_id,first_name, last_name, email, phone_number) VALUES(?,?,?,?,?)");
		$stmt->bind_param("issss", $id, $first_name, $last_name, $email, $phone);
		$stmt->execute();
		$stmt->close();
		$conn->close();
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
?>