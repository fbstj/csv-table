<?php

function done($data) {
	header('Content-Type: application/json');
	die(json_encode($data));
}
function fail($reason, $extra) {
	http_response_code(400);
	done([ 'reason' => $reason, 'extra' => $extra ]);
}

const FILE_NAME = './data.csv';

$file = file_get_contents(FILE_NAME);
if (!$file) {
	fail('could not open file',[]);
}
$lines = explode("\n", trim($file));
$lines = array_map(fn($line) => json_decode('['.$line.']'), $lines);
$cols = array_shift($lines);

// determine next ID
$next_id = 1;
foreach ($lines as $ix => $line) {
	$id = +$line[0];
	if ($id >= $next_id) {
		$next_id = $id + 1;
	}
}


// specifically Z for UTC timezone 0
const ISO_8601 = 'Y-m-d\\TH:i:s\\Z';
$stamp = date_create('now', new DateTimeZone('UTC'));
$_POST["stamp"] = $stamp->format(ISO_8601);
// set new line ID
$_POST["id"] = "$next_id";

// build new line
$new_line = array_map(fn($k) => $_POST[$k], $cols);
$new_line = array_map('json_encode', $new_line);
$new_line = implode(',', $new_line) . "\n";

// append line to file
$resp = file_put_contents(FILE_NAME, $new_line, FILE_APPEND);
if (!$resp) {
	fail('could not write file',[]);
}

header('Location: '.FILE_NAME);

