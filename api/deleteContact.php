<?php
	$data = getRequestInfo();
	
    $userid = $data['user_id'];
    $contactid = $data['id'];

	$conn = new mysqli("localhost", "group16", "welovegroup16", "COP4331_lamp_group_16");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$stmt = $conn->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
		$stmt->bind_param("ii", $contactid, $userid);
		$stmt->execute();
        if ($stmt->affected_rows > 0)
        {
            sendResultInfoAsJson('{"success":"Contact deleted"}');
        }
        else
        {
            returnWithError("Delete failed: Contact not found");
        }
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