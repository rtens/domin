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

<?php foreach ($model['actions'] as $group => $actions) { ?>
<div class="action-group">
    <?php if (count($model['actions']) > 1) { ?>
        <h2 class="group-name">
            <small>
                <span class="toggle-group glyphicon glyphicon-chevron-right"></span>
                <span class="toggle-group glyphicon glyphicon-chevron-down" style="display: none;"></span>
            </small>
            <?php echo $group ?>
        </h2>
    <?php } ?>

    <div class="list-group">
        <?php foreach ($actions as $action) { ?>
            <a href="<?php echo $action['link']['href'] ?>" class="list-group-item">
                <?php echo $action['caption'] ?>
                <?php if ($action['description']) { ?>
                <small class="text-muted">- <?php echo $action['description'] ?></small>
                <?php } ?>
            </a>
        <?php } ?>
    </div>
</div>
<?php } ?>

</div>

<?php if (count($model['actions']) > 1) { ?>
<script>
    $('.list-group').hide();

    var groupName = $('.group-name');
    groupName.css('cursor', 'pointer');
    groupName.click(function () {
        $(this).closest('.action-group').find('.list-group').toggle();
        $(this).find('.toggle-group').toggle();
    });

    groupName[0].click();
</script>
<?php } ?>

</body>
</html>
