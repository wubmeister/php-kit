<?php echo "<?";?>xml version="1.0" encoding="UTF-8"<?php echo "?>";?>

Hi,  this is
<?php if ($content == "Hello"): ?>
    <?php echo $variable; ?> content
    literal with { things }  and so
    Tag content
<?php else: ?>
    else content
<?php endif; ?>
with extra content , some  code and a tail and an iteration:
<?php foreach (Range::excl(1,10) as $index => $number): ?>
    <?php if ($index > 0): ?>, <?php endif; ?>Number: <?php echo $number; ?>
<?php endforeach; ?>
so there you go!
