<?php
function createFileFromBase64ChunksWithCRC32($inputFile, $outputFile) {
    // Check if input file exists
    if (!file_exists($inputFile)) {
        return 'Input file does not exist.';
    }

    // Open the input file
    $handle = fopen($inputFile, 'rb');
    if (!$handle) {
        return 'Error opening input file.';
    }

    // Initialize variables for reading content and computing hash
    $chunkSize = 512;
    $hashContext = hash_init('sha256');
    $chunkCounter = 0;

    // Start generating the PHP code
    ob_start();
    echo "<?php\n";
    echo "// Generated PHP code to assemble and verify the file content\n";
    echo "\$outputContent = '';\n";
    echo "\$chunkCRC32Errors = [];\n";

    // Read and process each chunk
    while (!feof($handle)) {
        $chunk = fread($handle, $chunkSize);
        hash_update($hashContext, $chunk);
        $base64Chunk = base64_encode($chunk);
        $crc32 = crc32($chunk);
        echo "// Decode chunk and verify CRC32\n";
        echo "\$decodedChunk = base64_decode('" . $base64Chunk . "');\n";
        echo "if (crc32(\$decodedChunk) !== $crc32) {\n";
        echo "    \$chunkCRC32Errors[] = $chunkCounter;\n";
        echo "}\n";
        echo "\$outputContent .= \$decodedChunk;\n";
        $chunkCounter++;
    }

    // Close the input file
    fclose($handle);

    // Finalize the hash of the entire file content
    $hash = hash_final($hashContext);

    // Continue generating the PHP code for hash and CRC32 verification
    echo "// Original SHA-256 hash for verification\n";
    echo "\$originalHash = '" . $hash . "';\n";
    echo "\$computedHash = hash('sha256', \$outputContent);\n";
    echo "if (\$computedHash !== \$originalHash || count(\$chunkCRC32Errors) > 0) {\n";
    echo "    echo 'Hash mismatch or CRC32 errors in chunks: ' . implode(', ', \$chunkCRC32Errors) . '. The file may have been tampered with.';\n";
    echo "} else {\n";
    echo "    \$newFileName = __DIR__ . '/' . basename(__FILE__, '.php');\n";
    echo "    file_put_contents(\$newFileName, \$outputContent);\n";
    echo "}\n";
    echo "?>";

    // Get the generated PHP code
    $phpCode = ob_get_clean();

    // Write the generated PHP code to the specified output file
    if (file_put_contents($outputFile, $phpCode) === false) {
        return 'Error writing to output file.';
    }

    return 'Success';
}
?>