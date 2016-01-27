<?php global $model ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $model['name'] ?> | <?php echo $model['caption'] ?></title>

    <?php if ($model['result']['redirect']) { ?>
    <meta http-equiv="refresh" content="2; URL=<?php echo $model['result']['redirect'] ?>">
    <?php } ?>

    <?php foreach ($model['headElements'] as $element) { ?>
    <?php echo $element ?>

    <?php } ?>

</head>
<body>

<?php echo $model['menu'] ?>

<div class="container">

    <?php if ($model['breadcrumbs']) { ?>
    <ol class="breadcrumb">
        <?php foreach ($model['breadcrumbs'] as $crumb) { ?>
        <li><a href="<?php echo $crumb['target'] ?>"><?php echo $crumb['caption'] ?></a></li>
        <?php } ?>
    </ol>
    <?php } ?>

    <?php if ($model['result']['error']) { ?>
    <div class="alert alert-danger"><?php echo $model['result']['error'] ?></div>
    <?php } ?>

    <?php if ($model['result']['missing']) { ?>
    <div class="alert alert-danger">Missing: <?php echo implode(', ', $model['result']['missing']) ?></div>
    <?php } ?>

    <?php if ($model['result']['success']) { ?>
    <div class="alert alert-success">Action "<?php echo $model['caption'] ?>" was successfully executed</div>
    <?php } ?>

    <?php if ($model['result']['redirect']) { ?>
    <div class="alert alert-info">You are redirected. Please wait or <a href="<?php echo $model['result']['redirect']?>">click here</a></div>
    <?php } else { ?>

    <?php if ($model['confirmationRequired']) { ?>
    <div class="alert alert-info">Please confirm the execution of this action.</div>
    <?php } ?>

    <form id="form" method="post" action="?" enctype="multipart/form-data">
        <?php if ($model['token']) { ?>
            <input type="hidden" name="<?php echo $model['token']['name'] ?>" value="<?php echo $model['token']['value'] ?>"/>
        <?php } ?>

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h1 class="panel-title" style="font-size: 22pt">
                    <?php echo $model['caption'] ?>

                    <?php if ($model['executed']) { ?>
                    <div class="pull-right">
                        <span class="show-form glyphicon glyphicon-chevron-down"></span>
                        <span class="hide-form glyphicon glyphicon-chevron-up" style="display: none;"></span>
                    </div>
                    <?php } ?>
                </h1>
            </div>

            <?php if ($model['action']) { ?>
            <div class="collapsed panel-body">
                <?php echo $model['action'] ?>
            </div>
            <?php } ?>

            <div class="collapsed panel-footer">
                <input type="submit" class="btn btn-primary" value="Execute">

                <?php if (!$model['executed']) { ?>
                <a href="javascript:history.back()" class="btn btn-default">Back</a>
                <?php } ?>
            </div>
        </div>
    </form>

    <?php } ?>

    <?php if ($model['result']['output']) { ?>
    <div><?php echo $model['result']['output'] ?></div>
    <?php } ?>

    <script>
        document.onsubmit = function () {
            window.onbeforeunload = function () {
            };

            document.getElementById('form').style.opacity = 0.5;
            document.onsubmit = function () {
                return false;
            };
            return true;
        };
    </script>

    <?php if (!$model['executed']) { ?>
    <script>
        document.onkeyup = function () {
            window.onbeforeunload = function () {
                return "If you close this page, you will lose unsaved changes.";
            };
        };
    </script>
    <?php } else { ?>
    <script>
        var panel = $('form > .panel');
        var showForm = panel.find('.show-form');
        var hideForm = panel.find('.hide-form');
        var collapsed = panel.find('.collapsed');

        var hidden = true;
        collapsed.hide();

        panel.css('cursor', 'pointer');

        panel.find('.panel-heading').click(function () {
            collapsed.toggle();
            hideForm.toggle();
            showForm.toggle();
            hidden = !hidden;
        });
    </script>
    <?php } ?>
</div>

</body>
</html>
