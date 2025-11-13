<?php
// Force download certificate with no cache headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$pdfPath = __DIR__ . '/../storage/app/public/certificates/CERT-2025-JVISYFAG.pdf';

if (!file_exists($pdfPath)) {
    die('File not found!');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Sertifikat_FRESH_' . time() . '.pdf"');
header('Content-Length: ' . filesize($pdfPath));

readfile($pdfPath);
exit;
