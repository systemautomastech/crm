<?php

// Get command line arguments
$packageName = $argv[1] ?? null;

// Define the base directories to scan (excluding packages for now)
$baseDirectories = [
    __DIR__ . '/resources/js',
    __DIR__ . '/resources/views',
    __DIR__ . '/app'
];

$outputFile = __DIR__ . '/resources/lang/en.json';
$packagesPath = __DIR__ . '/packages/automas';

// Initialize arrays to store translations
$translations = [];
$packageTranslations = [];

// Function to recursively scan directories
function scanDirectory($dir, &$translations, $packageName = null) {
    $realDir = realpath($dir);
    if (!$realDir || !is_dir($realDir)) {
        return;
    }

    $files = scandir($realDir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $realDir . '/' . $file;

        if (is_dir($path)) {
            scanDirectory($path, $translations, $packageName);
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $isBladeTemplate = str_ends_with($path, '.blade.php');
            if (in_array($extension, ['tsx', 'jsx','ts', 'php']) || $isBladeTemplate) {
                extractTranslations($path, $translations);
            }
        }
    }
}

// Function to extract translations from a file
function extractTranslations($file, &$translations) {
    $content = @file_get_contents($file);
    if ($content === false) {
        return;
    }

    // Match t("...") - double-quoted strings (apostrophes allowed inside)
    preg_match_all('/(?<![a-zA-Z0-9_])t\("((?:[^"\\\\]|\\\\.)*)"\)/', $content, $tDoubleMatches);

    // Match t('...') - single-quoted strings (handles escaped apostrophes like \')
    preg_match_all('/(?<![a-zA-Z0-9_])t\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/', $content, $tSingleMatches);

    // Match __("...") - double-quoted strings (apostrophes allowed inside)
    preg_match_all('/__\("((?:[^"\\\\]|\\\\.)*)"\)/', $content, $underscoreDoubleMatches);

    // Match __('...') - single-quoted strings (handles escaped apostrophes like \')
    preg_match_all('/__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/', $content, $underscoreSingleMatches);

    // Combine all matches
    $allMatches = array_merge(
        $tDoubleMatches[1],
        $tSingleMatches[1],
        $underscoreDoubleMatches[1],
        $underscoreSingleMatches[1]
    );

    foreach ($allMatches as $match) {
        // Unescape escaped quotes (e.g., \' -> ' and \" -> ")
        $key = str_replace(["\\'", '\\"'], ["'", '"'], $match);
        $translations[$key] = $key;
    }
}

// Function to process a specific package
function processPackage($packageDir, $packageName) {
    $packageTranslations = [];
    $packageLangFile = $packageDir . '/src/Resources/lang/en.json';

    scanDirectory($packageDir, $packageTranslations, $packageName);

    // Sort and save package translations
    ksort($packageTranslations);

    $packageLangDir = dirname($packageLangFile);
    if (!is_dir($packageLangDir)) {
        mkdir($packageLangDir, 0755, true);
    }

    file_put_contents($packageLangFile, json_encode($packageTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Package '$packageName': Found " . count($packageTranslations) . " strings.\n";

    return $packageTranslations;
}

// Handle package extraction based on arguments
if ($packageName) {
    // Extract specific package only
    $specificPackageDir = $packagesPath . '/' . $packageName;
    if (is_dir($specificPackageDir)) {
        echo "Extracting translations for package: $packageName\n";
        processPackage($specificPackageDir, $packageName);
    } else {
        echo "Error: Package '$packageName' not found in $packagesPath\n";
        exit(1);
    }
} else {
    // Extract main translations only
    foreach ($baseDirectories as $directory) {
        scanDirectory($directory, $translations);
    }

    // Sort main translations alphabetically
    ksort($translations);

    // Create directory if it doesn't exist
    $outputDir = dirname($outputFile);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    // Write main translations file
    file_put_contents($outputFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "Main translation extraction complete. Found " . count($translations) . " strings.\n";
}
echo "\nUsage: php extract-translations.php [package_name]\n";
echo "  - No arguments: Extract main translations\n";
echo "  - package_name: Extract specific package only\n";
echo "  Example: php extract-translations.php Test\n";
