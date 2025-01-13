<?php

require_once 'vendor/autoload.php';

foreach (['i18n', 'data'] as $file) {
    ${$file} = json_decode(file_get_contents("$file.json"), true);
}

// Set up the Twig template loader to look for template files in the './src' directory
$loader = new \Twig\Loader\FilesystemLoader('./src');

// Get the language argument passed from the command line
$language = $argv[1];

// Initialize the Twig environment (template engine)
$twig = new \Twig\Environment($loader);

// Add a global variable 'files' available in all templates
$twig->addGlobal('files', 'sites/default/files');

// Add a custom function 'cached' to generate image cache URLs in templates
$twig->addFunction(new \Twig\TwigFunction('cached', function ($cache, $photo) {
    return "imagecache/{$cache}/sites/all/files/photos/{$photo}";
}));

// Add a custom filter 't' for translations, using the $i18n data for the specified language
$twig->addFilter(new \Twig\TwigFilter('t', function ($s) {
    global $language, $i18n;
    return $i18n[$language][$s] ?? $s; // Return the translated string or the original string if not found
}));

// Add a global variable 'language' for use in templates
$twig->addGlobal('language', $language);

// Render the 'index' and 'contact' pages for the specified language and save as HTML files in the './build' directory
foreach (['index', 'contact'] as $page) {
    file_put_contents("./build/$page-$language.html", ($twig->load("$page.twig"))->render($data));
}
