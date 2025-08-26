<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) . ' - ' : ''; echo APP_NAME; ?></title>
    <link href="<?php echo APP_URL; ?>/assets/css/bundle.admin.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
        }
    </style>
</head>
<body>
    <div class="container">