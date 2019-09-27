<?php foreach ($articles as $article) : ?>
<h2>id = <?= $article['id'];?></h2>
<h3>subtitle</h3>
    <?= $article['subtitle'];?>
<h3>content</h3>
    <?= $article['content'];?>
<?php endforeach; ?>