<?php global $model ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $model['name'] ?> | actions</title>

<?php foreach ($model['headElements'] as $element) { ?>
    <?php echo $element ?>

<?php } ?>

</head>
<body>

<?php echo $model['menu'] ?>

<div class="container">

<div class="list-group">
    <?php foreach ($model['action'] as $anAction) { ?>
        <a href="<?php echo $anAction['link']['href'] ?>" class="list-group-item">
            <?php echo $anAction['caption'] ?>
            <?php if ($anAction['description']) { ?>
            <small class="text-muted">- <?php echo $anAction['description'] ?></small>
            <?php } ?>
        </a>
    <?php } ?>
</div>

</div>

</body>
</html>
