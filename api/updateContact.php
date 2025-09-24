 <?php
	$data = getRequestInfo();
	
	$first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $phone = $data['phone_number'];
    $email = $data['email'];
    $userid = $data['user_id'];
    $contactid = $data['id'];

	$conn = new mysqli("localhost", "group16", "welovegroup16", "COP4331_lamp_group_16");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$stmt = $conn->prepare("UPDATE Contacts SET first_name = ?, last_name=?, phone_number= ?, email= ? WHERE id= ? AND user_id = ?");
        $stmt->bind_param("ssssii", $first_name, $last_name, $phone, $email, $contactid, $userid);
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