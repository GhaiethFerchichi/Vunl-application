<?php
/**
 * File operations with vulnerabilities
 */

include 'config.php';

// VULNERABILITY: LFI (Local File Inclusion)
$page = $_GET['page'] ?? 'home';
if (isset($_GET['page'])) {
    // VULNERABILITY: Directory traversal in include
    include $page . '.php';
}

// VULNERABILITY: Arbitrary file download
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    // VULNERABILITY: No path validation
    if (file_exists($file)) {
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
    }
}

// VULNERABILITY: Race condition in file operations
if (isset($_POST['file_content'])) {
    $filename = 'uploads/' . time() . '.txt';
    file_put_contents($filename, $_POST['file_content']);
    echo "File saved to: " . $filename;
}

// VULNERABILITY: XXE (XML External Entity)
if (isset($_POST['xml_data'])) {
    $xml = simplexml_load_string($_POST['xml_data']);
    echo $xml->asXML();
}

?>
