<?php
    require_once(__DIR__ . '/vendor/autoload.php');
    $document = \WikiParserDemo\Application::getInstance()->runSearch();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <link href="css/screen.css" rel="stylesheet"/>
    </script>
</head>
<body>
    <form method="post">
        <fieldset>
            Dotaz: <input type="search" name="search" placeholder="Hledat" required value="<?php echo \WikiParserDemo\Application::getSearchString(); ?>"/>
        </fieldset>
        <?php if($document): ?>
            <h4>Nalezený text:</h4>
            <div><?php echo $document->getContent(); ?></div>

            <h4>Počet různých slov v tomto textu:</h4>
            <div><?php echo $document->getWordCount(); ?></div>

            <?php if(!$document->isDirty()): ?>
                <h4>Výsledek z cache.</h4>
            <?php endif; ?>
        <?php elseif($document!==false): ?>
            <h4>Nenalezeno</h4>
        <?php endif; ?>
    </form>
</body>
</html>