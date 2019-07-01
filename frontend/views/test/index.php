<?php
/**
 * @var array $news
 */
?>

<?php foreach ($news as $item): ?>

    <h1><?= $item['title']?></h1>
    <p><?= $item['content']?></p>
    <hr>

<?php endforeach; ?>