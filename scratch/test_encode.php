<?php
$data = ['name' => "Sava'Ah", 'title' => 'S.I.Kom', 'quote' => 'hello "world"'];
$encoded = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
file_put_contents('scratch/test_json_out.txt', $encoded);
echo "PHP Encoded: " . $encoded . PHP_EOL;
