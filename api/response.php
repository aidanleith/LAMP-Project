
<?php

function send_json($data, $status_code = 200) {
	http_response_code($status_code);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
	exit;
}

function send_error($message, $status_code = 400) {
	send_json(['error' => $message], $status_code);
}
