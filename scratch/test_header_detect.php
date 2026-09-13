<?php

$samplePastedNoHeader = "Tgk. Muhammad Danil S.E\tmuhamaddani11032002@gmail.com\nYuke luis adipsah\tyuke.230330003@mhs.unimal.ac.id\nAdrian Reinhard Pelupessy\tadrn21plpssy@gmail.com";

function testDetect($raw) {
    $lines = preg_split('/\r\n|\r|\n/', trim($raw));
    $rows = [];
    foreach ($lines as $line) {
        $r = explode("\t", trim($line));
        $rows[] = array_map('trim', $r);
    }

    $firstRow = $rows[0];
    $firstRowHasEmail = false;
    foreach ($firstRow as $cell) {
        if (filter_var($cell, FILTER_VALIDATE_EMAIL)) {
            $firstRowHasEmail = true;
            break;
        }
    }

    $hasHeader = false;
    if (!$firstRowHasEmail) {
        $headerKeywords = ['nama', 'name', 'peserta', 'email', 'e-mail', 'surel', 'nomor induk', 'no induk', 'nim', 'nis', 'no'];
        foreach ($firstRow as $cell) {
            $c = strtolower(trim((string)$cell));
            foreach ($headerKeywords as $kw) {
                if ($c === $kw || str_contains($c, $kw)) {
                    $hasHeader = true;
                    break 2;
                }
            }
        }
    }

    echo "First row has email: " . ($firstRowHasEmail ? "YES" : "NO") . PHP_EOL;
    echo "Has header: " . ($hasHeader ? "YES" : "NO") . PHP_EOL;

    if ($hasHeader) {
        array_shift($rows);
    }
    echo "Rows remaining: " . count($rows) . " (First: " . $rows[0][0] . ")" . PHP_EOL;
}

echo "--- Test Paste Without Header ---\n";
testDetect($samplePastedNoHeader);

$sampleWithHeader = "Nama\tEmail\nTgk. Muhammad Danil S.E\tmuhamaddani11032002@gmail.com\nYuke luis adipsah\tyuke.230330003@mhs.unimal.ac.id";
echo "\n--- Test Paste With Header ---\n";
testDetect($sampleWithHeader);
