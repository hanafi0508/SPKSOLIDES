<?php

declare(strict_types=1);

const XLSX_MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

function usage(): void
{
    echo "Usage:\n";
    echo "  php database/import_suppliers_from_xlsx.php [--file=data.xlsx] [--dry-run] [--limit=10]\n";
}

function parseOptions(array $argv): array
{
    $options = [
        'file' => __DIR__ . '/../data.xlsx',
        'dry_run' => false,
        'limit' => null,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--dry-run') {
            $options['dry_run'] = true;
            continue;
        }

        if (str_starts_with($arg, '--file=')) {
            $options['file'] = substr($arg, 7);
            continue;
        }

        if (str_starts_with($arg, '--limit=')) {
            $limit = (int) substr($arg, 8);
            $options['limit'] = $limit > 0 ? $limit : null;
            continue;
        }

        if (in_array($arg, ['-h', '--help'], true)) {
            usage();
            exit(0);
        }

        throw new InvalidArgumentException("Argumen tidak dikenali: {$arg}");
    }

    return $options;
}

function loadXmlDocument(ZipArchive $zip, string $entryName): DOMDocument
{
    $xml = $zip->getFromName($entryName);
    if ($xml === false) {
        throw new RuntimeException("File {$entryName} tidak ditemukan di workbook.");
    }

    $document = new DOMDocument();
    $document->loadXML($xml);

    return $document;
}

function getSharedStrings(ZipArchive $zip): array
{
    $sharedStrings = [];
    $document = loadXmlDocument($zip, 'xl/sharedStrings.xml');
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('a', XLSX_MAIN_NS);

    foreach ($xpath->query('//a:si') as $si) {
        $text = '';
        foreach ($xpath->query('.//a:t', $si) as $textNode) {
            $text .= $textNode->textContent;
        }
        $sharedStrings[] = trim($text);
    }

    return $sharedStrings;
}

function cellReferenceToColumn(string $reference): string
{
    if (!preg_match('/^([A-Z]+)[0-9]+$/', $reference, $matches)) {
        throw new RuntimeException("Referensi cell tidak valid: {$reference}");
    }

    return $matches[1];
}

function getSheetRows(string $filePath): array
{
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        throw new RuntimeException("Gagal membuka file XLSX: {$filePath}");
    }

    $sharedStrings = getSharedStrings($zip);
    $document = loadXmlDocument($zip, 'xl/worksheets/sheet1.xml');
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('a', XLSX_MAIN_NS);

    $rows = [];

    foreach ($xpath->query('//a:sheetData/a:row') as $rowNode) {
        $rowNumber = (int) $rowNode->getAttribute('r');
        $row = [
            '__row_number' => $rowNumber,
        ];

        foreach ($xpath->query('./a:c', $rowNode) as $cellNode) {
            $reference = $cellNode->getAttribute('r');
            $column = cellReferenceToColumn($reference);
            $type = $cellNode->getAttribute('t');
            $valueNode = $xpath->query('./a:v', $cellNode)->item(0);
            $value = $valueNode ? trim($valueNode->textContent) : '';

            if ($type === 's' && $value !== '') {
                $value = $sharedStrings[(int) $value] ?? '';
            }

            $row[$column] = trim($value);
        }

        $rows[] = $row;
    }

    $zip->close();

    return $rows;
}

function normalizeWhitespace(?string $value): string
{
    $value = trim((string) $value);
    return preg_replace('/\s+/', ' ', $value) ?? '';
}

function normalizePhone(?string $value): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', '', $value) ?? '';
    return trim($value);
}

function splitProjectNames(string $value): array
{
    $value = normalizeWhitespace($value);
    if ($value === '') {
        return [];
    }

    $parts = [];
    $buffer = '';
    $depth = 0;
    $length = mb_strlen($value);

    for ($i = 0; $i < $length; $i++) {
        $char = mb_substr($value, $i, 1);

        if ($char === '(') {
            $depth++;
        } elseif ($char === ')' && $depth > 0) {
            $depth--;
        }

        if ($char === ',' && $depth === 0) {
            $part = normalizeWhitespace($buffer);
            if ($part !== '') {
                $parts[] = $part;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    $tail = normalizeWhitespace($buffer);
    if ($tail !== '') {
        $parts[] = $tail;
    }

    return array_values(array_unique($parts));
}

function canonicalizeProjectName(string $projectName): string
{
    $projectName = normalizeWhitespace($projectName);

    return match (mb_strtolower($projectName)) {
        'rumah suka cita' => 'Rumah Sukacita',
        'cibubur' => 'Citaville Cibubur',
        'cikarang (warehouse delta)' => 'Warehouse Delta',
        default => $projectName,
    };
}

function expandProjectNames(string $value): array
{
    $projects = [];

    foreach (splitProjectNames($value) as $projectName) {
        if (preg_match('/^Jabodetabek\s*\((.+)\)$/i', $projectName, $matches)) {
            foreach (splitProjectNames($matches[1]) as $innerProject) {
                $projects[] = canonicalizeProjectName($innerProject);
            }
            continue;
        }

        $projects[] = canonicalizeProjectName($projectName);
    }

    return array_values(array_unique(array_filter($projects)));
}

function applyCanonicalSupplierRules(array $supplier): array
{
    $phone = preg_replace('/[^0-9]/', '', (string) $supplier['no_telepon']);

    switch ($phone) {
        case '08158335041':
            $supplier['nama_supplier'] = 'Sahabat Sampora Jaya';
            $supplier['jenis_material'] = 'Material Alam - Pasir, Spilt, dll';
            break;

        case '08161124555':
            $supplier['nama_supplier'] = 'PT. Karya Baru Jaya Perkasa';
            $supplier['jenis_material'] = 'Arsitektur Atap - Atap Baja Ringan; Pasang Baja Ringan Atap';
            break;

        case '0895384223512':
            $supplier['nama_supplier'] = 'Rohman / Komeng';
            $supplier['status'] = 'tidak_aktif';
            break;
    }

    return $supplier;
}

function inferSupplierType(string $kategori): string
{
    $kategori = mb_strtolower($kategori);

    if (str_contains($kategori, 'supplier')) {
        return 'barang';
    }

    if (
        str_contains($kategori, 'vendor') ||
        str_contains($kategori, 'subkon') ||
        str_contains($kategori, 'mandor') ||
        str_contains($kategori, 'tukang')
    ) {
        return 'jasa';
    }

    return 'jasa';
}

function extractCategoryContext(string $kategori): string
{
    $kategori = normalizeWhitespace($kategori);

    if (str_contains($kategori, ';')) {
        $parts = array_map('trim', explode(';', $kategori, 2));
        return $parts[1] ?? '';
    }

    if (preg_match('/^(Mandor\/Tukang)\s+(.+)$/i', $kategori, $matches)) {
        return trim($matches[2]);
    }

    if (preg_match('/^(Supplier|Vendor|Subkon)\s+(.+)$/i', $kategori, $matches)) {
        return trim($matches[2]);
    }

    return '';
}

function buildItemLabel(string $kategori, string $keterangan): string
{
    $parts = [];
    $context = extractCategoryContext($kategori);
    $keterangan = normalizeWhitespace($keterangan);

    if ($context !== '') {
        $parts[] = $context;
    }

    if ($keterangan !== '' && !in_array(mb_strtolower($keterangan), array_map('mb_strtolower', $parts), true)) {
        $parts[] = $keterangan;
    }

    if (count($parts) === 0) {
        return $kategori !== '' ? $kategori : '-';
    }

    return implode(' - ', $parts);
}

function mapWorkbookRowsToSuppliers(array $rows): array
{
    $mapped = [];

    foreach ($rows as $row) {
        $rowNumber = $row['__row_number'] ?? 0;

        if ($rowNumber === 1) {
            continue;
        }

        $nama = normalizeWhitespace($row['A'] ?? '');
        $kategori = normalizeWhitespace($row['B'] ?? '');
        $keterangan = normalizeWhitespace($row['C'] ?? '');
        $proyek = normalizeWhitespace($row['D'] ?? '');
        $kontak = normalizePhone($row['E'] ?? '');

        if ($nama === '') {
            continue;
        }

        $mapped[] = applyCanonicalSupplierRules([
            'source_row' => $rowNumber,
            'nama_supplier' => $nama,
            'tipe_supplier' => inferSupplierType($kategori),
            'alamat' => '',
            'no_telepon' => $kontak,
            'email' => null,
            'jenis_material' => buildItemLabel($kategori, $keterangan),
            'status' => 'aktif',
            'projects' => expandProjectNames($proyek),
            'raw_kategori' => $kategori,
            'raw_keterangan' => $keterangan,
            'raw_project' => $proyek,
        ]);
    }

    return $mapped;
}

function findExistingSupplier(mysqli $conn, array $supplier): ?int
{
    $stmt = mysqli_prepare($conn, "
        SELECT id_supplier
        FROM supplier
        WHERE nama_supplier = ?
          AND tipe_supplier = ?
          AND COALESCE(no_telepon, '') = ?
        LIMIT 1
    ");

    $phone = $supplier['no_telepon'] ?? '';
    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $supplier['nama_supplier'],
        $supplier['tipe_supplier'],
        $phone
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        return (int) $row['id_supplier'];
    }

    $stmtFallback = mysqli_prepare($conn, "
        SELECT id_supplier
        FROM supplier
        WHERE nama_supplier = ?
          AND tipe_supplier = ?
          AND COALESCE(jenis_material, '') = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param(
        $stmtFallback,
        "sss",
        $supplier['nama_supplier'],
        $supplier['tipe_supplier'],
        $supplier['jenis_material']
    );
    mysqli_stmt_execute($stmtFallback);
    $fallbackResult = mysqli_stmt_get_result($stmtFallback);
    $fallbackRow = mysqli_fetch_assoc($fallbackResult);

    return $fallbackRow ? (int) $fallbackRow['id_supplier'] : null;
}

function importSuppliers(?mysqli $conn, array $suppliers, bool $dryRun = false, ?int $limit = null): array
{
    $stats = [
        'processed' => 0,
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'projects_created' => 0,
        'alternatif_created' => 0,
    ];

    if ($dryRun) {
        foreach ($suppliers as $index => $supplier) {
            if ($limit !== null && $index >= $limit) {
                break;
            }

            $stats['processed']++;
            $stats['inserted']++;
        }

        return $stats;
    }

    if (!$conn instanceof mysqli) {
        throw new RuntimeException('Koneksi database tidak tersedia untuk proses import.');
    }

    $insertStmt = mysqli_prepare($conn, "
        INSERT INTO supplier (nama_supplier, tipe_supplier, alamat, no_telepon, email, jenis_material, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $updateStmt = mysqli_prepare($conn, "
        UPDATE supplier
        SET alamat = ?, no_telepon = ?, email = ?, jenis_material = ?, status = ?
        WHERE id_supplier = ?
    ");

    $projectSelectStmt = mysqli_prepare($conn, "
        SELECT id_proyek
        FROM proyek
        WHERE LOWER(TRIM(nama_proyek)) = LOWER(TRIM(?))
        LIMIT 1
    ");

    $projectInsertStmt = mysqli_prepare($conn, "
        INSERT INTO proyek (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, keterangan)
        VALUES (?, ?, NULL, NULL, ?)
    ");

    $alternatifSelectStmt = mysqli_prepare($conn, "
        SELECT id_alternatif
        FROM alternatif
        WHERE id_proyek = ? AND id_supplier = ?
        LIMIT 1
    ");

    $alternatifInsertStmt = mysqli_prepare($conn, "
        INSERT INTO alternatif (id_proyek, id_supplier)
        VALUES (?, ?)
    ");

    foreach ($suppliers as $index => $supplier) {
        if ($limit !== null && $index >= $limit) {
            break;
        }

        $stats['processed']++;
        $existingId = findExistingSupplier($conn, $supplier);

        if ($existingId !== null) {
            mysqli_stmt_bind_param(
                $updateStmt,
                "sssssi",
                $supplier['alamat'],
                $supplier['no_telepon'],
                $supplier['email'],
                $supplier['jenis_material'],
                $supplier['status'],
                $existingId
            );
            mysqli_stmt_execute($updateStmt);
            $stats['updated']++;
            $supplierId = $existingId;
        } else {
            mysqli_stmt_bind_param(
                $insertStmt,
                "sssssss",
                $supplier['nama_supplier'],
                $supplier['tipe_supplier'],
                $supplier['alamat'],
                $supplier['no_telepon'],
                $supplier['email'],
                $supplier['jenis_material'],
                $supplier['status']
            );
            mysqli_stmt_execute($insertStmt);
            $stats['inserted']++;
            $supplierId = (int) mysqli_insert_id($conn);
        }

        foreach ($supplier['projects'] as $projectName) {
            mysqli_stmt_bind_param($projectSelectStmt, "s", $projectName);
            mysqli_stmt_execute($projectSelectStmt);
            $projectResult = mysqli_stmt_get_result($projectSelectStmt);
            $projectRow = mysqli_fetch_assoc($projectResult);

            if ($projectRow) {
                $projectId = (int) $projectRow['id_proyek'];
            } else {
                $projectDescription = 'Import otomatis dari data.xlsx';
                mysqli_stmt_bind_param($projectInsertStmt, "sss", $projectName, $projectName, $projectDescription);
                mysqli_stmt_execute($projectInsertStmt);
                $projectId = (int) mysqli_insert_id($conn);
                $stats['projects_created']++;
            }

            mysqli_stmt_bind_param($alternatifSelectStmt, "ii", $projectId, $supplierId);
            mysqli_stmt_execute($alternatifSelectStmt);
            $alternatifResult = mysqli_stmt_get_result($alternatifSelectStmt);
            $alternatifRow = mysqli_fetch_assoc($alternatifResult);

            if ($alternatifRow) {
                continue;
            }

            mysqli_stmt_bind_param($alternatifInsertStmt, "ii", $projectId, $supplierId);
            mysqli_stmt_execute($alternatifInsertStmt);
            $stats['alternatif_created']++;
        }
    }

    return $stats;
}

function printPreview(array $suppliers, ?int $limit = null): void
{
    $rows = $limit !== null ? array_slice($suppliers, 0, $limit) : $suppliers;

    foreach ($rows as $supplier) {
        echo sprintf(
            "[row %d] %s | %s | %s | proyek: %s | kontak: %s\n",
            $supplier['source_row'],
            $supplier['nama_supplier'],
            $supplier['tipe_supplier'],
            $supplier['jenis_material'],
            implode(', ', $supplier['projects']),
            $supplier['no_telepon']
        );
    }
}

function runImporter(array $argv): int
{
    global $conn;

    $transactionStarted = false;

    try {
    $options = parseOptions($argv);
    $filePath = $options['file'];

    if (!is_file($filePath)) {
        throw new RuntimeException("File XLSX tidak ditemukan: {$filePath}");
    }

    $rows = getSheetRows($filePath);
    $suppliers = mapWorkbookRowsToSuppliers($rows);

    echo "File: {$filePath}\n";
    echo "Total row supplier terdeteksi: " . count($suppliers) . "\n";

    if ($options['dry_run']) {
        echo "Mode: DRY RUN\n";
    }

    printPreview($suppliers, $options['limit'] !== null ? min($options['limit'], 10) : 10);

    $conn = null;

    if (!$options['dry_run']) {
        require __DIR__ . '/../config/database.php';
        mysqli_begin_transaction($conn);
        $transactionStarted = true;
    }

    $stats = importSuppliers($conn, $suppliers, $options['dry_run'], $options['limit']);

    if (!$options['dry_run']) {
        mysqli_commit($conn);
        $transactionStarted = false;
    }

    echo "\nRingkasan:\n";
    echo "- processed: {$stats['processed']}\n";
    echo "- inserted: {$stats['inserted']}\n";
    echo "- updated: {$stats['updated']}\n";
    echo "- skipped: {$stats['skipped']}\n";
    echo "- projects_created: {$stats['projects_created']}\n";
    echo "- alternatif_created: {$stats['alternatif_created']}\n";

        return 0;
    } catch (Throwable $th) {
        if ($transactionStarted && isset($conn) && $conn instanceof mysqli) {
            mysqli_rollback($conn);
        }

        fwrite(STDERR, "Error: " . $th->getMessage() . PHP_EOL);
        return 1;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit(runImporter($argv));
}
