<?php

function detectColumns(array $rows) {
    if (empty($rows)) return [];

    $firstRow = $rows[0];
    $firstRowHasEmail = false;
    foreach ($firstRow as $cell) {
        if (filter_var(trim((string)$cell), FILTER_VALIDATE_EMAIL)) {
            $firstRowHasEmail = true;
            break;
        }
    }

    $hasHeader = false;
    $emailColIdx = -1;
    $nameColIdx = -1;
    $niColIdx = -1;

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
        if ($hasHeader) {
            $header = array_shift($rows);
            foreach ($header as $idx => $colName) {
                $cn = strtolower(trim((string)$colName));
                if ($emailColIdx === -1 && (str_contains($cn, 'email') || str_contains($cn, 'mail') || str_contains($cn, 'surel'))) {
                    $emailColIdx = $idx;
                } elseif ($nameColIdx === -1 && (str_contains($cn, 'nama') || str_contains($cn, 'name') || str_contains($cn, 'peserta'))) {
                    $nameColIdx = $idx;
                } elseif ($niColIdx === -1 && (str_contains($cn, 'induk') || str_contains($cn, 'ni') || str_contains($cn, 'nim'))) {
                    $niColIdx = $idx;
                }
            }
        }
    }

    if (empty($rows)) return [];

    // Fallback deteksi email dari data
    if ($emailColIdx === -1) {
        $colEmailCounts = [];
        foreach (array_slice($rows, 0, 20) as $sampleRow) {
            foreach ($sampleRow as $colIdx => $val) {
                if (filter_var(trim((string)$val), FILTER_VALIDATE_EMAIL)) {
                    $colEmailCounts[$colIdx] = ($colEmailCounts[$colIdx] ?? 0) + 1;
                }
            }
        }
        if (!empty($colEmailCounts)) {
            arsort($colEmailCounts);
            $emailColIdx = array_key_first($colEmailCounts);
        }
    }

    // Fallback deteksi nama: pilih kolom yang bukan email dan bukan nomor urut murni
    if ($nameColIdx === -1 && $emailColIdx !== -1) {
        $candidateScores = [];
        foreach (array_keys($rows[0]) as $cIdx) {
            if ($cIdx === $emailColIdx) continue;
            $isNumericCount = 0;
            $textLenSum = 0;
            $sampleSlice = array_slice($rows, 0, 10);
            foreach ($sampleSlice as $sRow) {
                $val = trim((string)($sRow[$cIdx] ?? ''));
                if (is_numeric($val)) $isNumericCount++;
                $textLenSum += strlen($val);
            }
            // Kolom nama biasanya punya teks lebih panjang dan bukan angka murni
            if ($isNumericCount > count($sampleSlice) / 2) {
                $candidateScores[$cIdx] = -100; // Kemungkinan besar nomor urut
            } else {
                $candidateScores[$cIdx] = $textLenSum;
            }
        }
        if (!empty($candidateScores)) {
            arsort($candidateScores);
            $nameColIdx = array_key_first($candidateScores);
        }
    }

    // Fallback deteksi NI
    if ($niColIdx === -1 && $emailColIdx !== -1) {
        foreach (array_keys($rows[0]) as $cIdx) {
            if ($cIdx !== $emailColIdx && $cIdx !== $nameColIdx) {
                $niColIdx = $cIdx;
                break;
            }
        }
    }

    echo "Results: Name Col = $nameColIdx, Email Col = $emailColIdx, NI Col = $niColIdx\n";
    echo "First data row: Name = '{$rows[0][$nameColIdx]}', Email = '{$rows[0][$emailColIdx]}'\n";
    return count($rows);
}

echo "=== Scenario 1: [Nama, Email] without header ===\n";
$rows1 = [
    ['Tgk. Muhammad Danil S.E', 'muhamaddani11032002@gmail.com'],
    ['Yuke luis adipsah', 'yuke.230330003@mhs.unimal.ac.id']
];
detectColumns($rows1);

echo "\n=== Scenario 2: [No, Nama, Email] without header ===\n";
$rows2 = [
    ['1', 'Tgk. Muhammad Danil S.E', 'muhamaddani11032002@gmail.com'],
    ['2', 'Yuke luis adipsah', 'yuke.230330003@mhs.unimal.ac.id']
];
detectColumns($rows2);

echo "\n=== Scenario 3: [Email, Nama] without header ===\n";
$rows3 = [
    ['muhamaddani11032002@gmail.com', 'Tgk. Muhammad Danil S.E'],
    ['yuke.230330003@mhs.unimal.ac.id', 'Yuke luis adipsah']
];
detectColumns($rows3);
