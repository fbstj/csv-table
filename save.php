<?php

function done($data) {
	header('Content-Type: application/json');
	die(json_encode($data));
}
function fail($reason, $extra) {
	http_response_code(400);
	done([ 'reason' => $reason, 'extra' => $extra ]);
}

if (!$_GET['id']) {
	fail('needs `?id=` param', []);
}

const FILE_NAME = './data.csv';

$file = file_get_contents(FILE_NAME);
if (!$file) {
	fail('could not open file',[]);
}
$lines = explode("\n", trim($file));
$lines = array_map(fn($line) => json_decode('['.$line.']'), $lines);
$cols = array_shift($lines);
$cols = array_flip($cols);

// find ID row
function find_row($id, $lines, $col) {
	foreach ($lines as $ix => $row) {
		if ($row[$col] == $id) {
			return $ix;
		}
	}
	return false;
}
$row_ix = find_row($_GET['id'], $lines, $cols['id']);
if ($row_ix === false) {
	fail('could not find row', []);
}
$row = $lines[$row_ix];

// update columns
$changed = [];
foreach ($cols as $col => $ix) {
	if ($col == 'id' || $col == 'stamp') {
		continue;
	}
	if ($row[$ix] == $_POST[$col]) {
		continue;
	}
	$row[$ix] = $_POST[$col];
	$changed[] = $col;
}

if (count($changed) < 1) {
	fail('no changes made',[]);
}

// update stamp
$stamp = date_create('now', new DateTimeZone('UTC'));
// specifically Z for UTC timezone 0
const ISO_8601 = 'Y-m-d\\TH:i:s\\Z';
$row[$cols['stamp']] = $stamp->format(ISO_8601);

$lines[$row_ix] = $row;

$cols = array_flip($cols);
array_unshift($lines, $cols);

$lines = array_map(fn($line) => implode(',', array_map('json_encode', $line)), $lines);

$file = implode("\n", $lines)."\n";

$resp = file_put_contents(FILE_NAME, $file);
if (!$resp) {
	fail('could not write file',[]);
}

header('Location: ./gui.html');
