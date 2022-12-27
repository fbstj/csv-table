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
$next_id = 0;
foreach ($lines as $ix => $line) {
	$id = +$line[0];
	if ($id >= $next_id) {
		$next_id = $id + 1;
	}
}

$stamp = date_create('now', new DateTimeZone('UTC'));

// specifically Z for UTC timezone 0
const ISO_8601 = 'Y-m-d\\TH:i:s\\Z';

$_POST["id"] = "$next_id";
$_POST["stamp"] = $stamp->format(ISO_8601);

$new_line = array_map(fn($k) => $_POST[$k], $cols);
$new_line = array_map('json_encode', $new_line);
$new_line = implode(',', $new_line) . "\n";

$resp = file_put_contents(FILE_NAME, $new_line, FILE_APPEND);
if (!$resp) {
	fail('could not write file',[]);
}

header('Location: ./gui.html');
