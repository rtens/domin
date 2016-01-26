<?php global $model ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $model['name'] ?> | <?= $model['caption'] ?></title>

    <? if ($model['result']['redirect']) { ?>
    <meta http-equiv="refresh" content="2; URL=<?= $model['result']['redirect'] ?>">
    <? } ?>

    <? foreach ($model['headElements'] as $element) { ?>
    <?= $element ?>

    <? } ?>

</head>
<body>

<?= $model['menu'] ?>

<div class="container">

    <? if ($model['breadcrumbs']) { ?>
    <ol class="breadcrumb">
        <? foreach ($model['breadcrumbs'] as $crumb) { ?>
        <li><a href="<?= $crumb['target'] ?>"><?= $crumb['caption'] ?></a></li>
        <? } ?>
    </ol>
    <? } ?>

    <? if ($model['result']['error']) { ?>
    <div class="alert alert-danger"><?= $model['result']['error'] ?></div>
    <? } ?>

    <? if ($model['result']['missing']) { ?>
    <div class="alert alert-danger">Missing: <?= implode(', ', $model['result']['missing']) ?></div>
    <? } ?>

    <? if ($model['result']['success']) { ?>
    <div class="alert alert-success">Action "<?= $model['caption'] ?>" was successfully executed</div>
    <? } ?>

    <? if ($model['result']['redirect']) { ?>
    <div class="alert alert-info">You are redirected. Please wait or <a href="<?= $model['result']['redirect']?>">click here</a></div>
    <? } else { ?>

    <form id="form" method="post" action="?" enctype="multipart/form-data">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h1 class="panel-title" style="font-size: 22pt">
                    <?= $model['caption'] ?>

                    <? if ($model['executed']) { ?>
                    <div class="pull-right">
                        <span class="show-form glyphicon glyphicon-chevron-down"></span>
                        <span class="hide-form glyphicon glyphicon-chevron-up" style="display: none;"></span>
                    </div>
                    <? } ?>
                </h1>
            </div>

            <? if ($model['action']) { ?>
            <div class="collapsed panel-body">
                <?= $model['action'] ?>
            </div>
            <? } ?>

            <div class="collapsed panel-footer">
                <input type="submit" class="btn btn-primary" value="Execute">

                <? if (!$model['executed']) { ?>
                <a href="javascript:history.back()" class="btn btn-default">Back</a>
                <? } ?>
            </div>
        </div>
    </form>

    <? } ?>

    <? if ($model['result']['output']) { ?>
    <div><?= $model['result']['output'] ?></div>
    <? } ?>

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

    <? if (!$model['executed']) { ?>
    <script>
        document.onkeyup = function () {
            window.onbeforeunload = function () {
                return "If you close this page, you will lose unsaved changes.";
            };
        };
    </script>
    <? } else { ?>
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
    <? } ?>
</div>

</body>
</html>
