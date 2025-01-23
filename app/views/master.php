<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project</title>
        <link rel="stylesheet" href="<?=  ASSETS_URL . '/css/style4.css' ?>">
        <script src="<?= ASSETS_URL . '/js/script.js' ?>" defer></script>
    </head>
    <body>
        <h1>Users</h1>
        <?php require_once __DIR__ . "/pages/{$page}.php"?>
    </body>
</html>