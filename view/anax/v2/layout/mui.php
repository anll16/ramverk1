<?php

namespace Anax\View;

use Anax\StyleChooser\StyleChooserController;

/**
 * A layout rendering views in defined regions.
 */

// Show incoming variables and view helper functions
//echo showEnvironment(get_defined_vars(), get_defined_functions());

$htmlClass = $htmlClass ?? [];
$lang = $lang ?? "sv";
$charset = $charset ?? "utf-8";
$title = ($title ?? "No title") . ($baseTitle ?? " | No base title defined");
$bodyClass = $bodyClass ?? null;

// Set active stylesheet
$request = $di->get("request");
$session = $di->get("session");
if ($request->getGet("style")) {
    $session->set("redirect", currentUrl());
    redirect("style/update/" . rawurlencode($_GET["style"]));
}

// Get the active stylesheet, if any.
$activeStyle = $session->get(StyleChooserController::getSessionKey(), null);
if ($activeStyle) {
    $stylesheets = [];
    $stylesheets[] = $activeStyle;
}

// Get hgrid & vgrid
if ($request->hasGet("hgrid")) {
    $htmlClass[] = "hgrid";
}
if ($request->hasGet("vgrid")) {
    $htmlClass[] = "vgrid";
}

// Show regions
if ($request->hasGet("regions")) {
    $htmlClass[] = "regions";
}

// Get flash message if any and add to region flash-message
$flashMessage = $session->getOnce("flashmessage");
if ($flashMessage) {
    $di->get("view")->add(__DIR__ . "/../flashmessage/default", ["message" => $flashMessage], "flash-message");
}

// Get current route to make as body class
$route = "route-" . str_replace("/", "-", $di->get("request")->getRoute());


?>

<!doctype html>
<html <?= classList($htmlClass) ?> lang="<?= $lang ?>">
<head>

    <meta charset="<?= $charset ?>">
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="//cdn.muicss.com/mui-0.10.0/js/mui.min.js"></script>
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php if (isset($favicon)) : ?>
        <link rel="icon" href="<?= $favicon ?>">
    <?php endif; ?>

    <?php if (isset($stylesheets)) : ?>
        <?php foreach ($stylesheets as $stylesheet) : ?>
            <link rel="stylesheet" type="text/css" href="<?= asset($stylesheet) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <script>
        jQuery(function($) {
            var $bodyEl = $('body'),
                $sidedrawerEl = $('#sidedrawer');
            console.log(document.cookie);


            function showSidedrawer() {
                // show overlay
                var options = {
                    onclose: function() {
                        $sidedrawerEl
                            .removeClass('active')
                            .appendTo(document.body);
                    }
                };

                var $overlayEl = $(mui.overlay('on', options));

                // show element
                $sidedrawerEl.appendTo($overlayEl);
                setTimeout(function() {
                    $sidedrawerEl.addClass('active');
                }, 20);
            }


            function hideSidedrawer() {
                $bodyEl.toggleClass('hide-sidedrawer');
                document.cookie = "hidedrawer=yes; expires=Thu, 18 Dec 2022 12:00:00 UTC; path=/";

            }


            $('.js-show-sidedrawer').on('click', showSidedrawer);
            $('.js-hide-sidedrawer').on('click', hideSidedrawer);
        });
    </script>
</head>
<body <?= classList($bodyClass, $route) ?>>

<!-- siteheader with optional columns -->
<?php if (regionHasContent("header") || regionHasContent("header-col-1")) : ?>
    <div id="sidedrawer" class="mui--no-user-select">
        <!-- Side drawer content goes here -->
        <!-- header-col-2 -->
        <?php if (regionHasContent("header-col-2")) : ?>
            <div class="region-header-col-2">
                <?php renderRegion("header-col-2") ?>
            </div>
        <?php endif; ?>
    </div>
    <header id="header">
        <div class="mui-appbar mui--appbar-line-height" style="height: 45px">
            <div class="mui-container-fluid">
                <a class="sidedrawer-toggle mui--visible-xs-inline-block mui--visible-sm-inline-block js-show-sidedrawer">☰</a>
                <a class="sidedrawer-toggle mui--hidden-xs mui--hidden-sm js-hide-sidedrawer">☰</a>
                                <!-- header -->
                    <?php if (regionHasContent("header")) : ?>
                        <div class="g">
                            <?php renderRegion("header") ?>
                        </div>
                    <?php endif; ?>

                    <!-- header-col-1 -->
                    <?php if (regionHasContent("header-col-1")) : ?>
                        <div class="wtf mui--text-title">
                            <?php renderRegion("header-col-1") ?>
                        </div>
                    <?php endif; ?>
                <div class="mui-row">


                    <!-- header-col-3 -->
                    <?php if (regionHasContent("header-col-3")) : ?>
                        <div class="mui-col-md-2">
                            <?php renderRegion("header-col-3") ?>
                        </div>
                    <?php endif; ?>

                </div>



            </div>
        </div>
    </header>
<?php endif; ?>


<div id="content-wrapper">
    <div class="mui--appbar-height"></div>
    <div class="mui-container-fluid">
        <!-- columns-above -->
        <?php if (regionHasContent("columns-above")) : ?>
            <div class="outer-wrap outer-wrap-columns-above">
                <div class="inner-wrap inner-wrap-columns-above">
                    <div class="row">
                        <div class="region-columns-above">
                            <?php renderRegion("columns-above") ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>



        <!-- main -->
        <div class="outer-wrap outer-wrap-main">
            <div class="inner-wrap inner-wrap-main">
                <div class="row">

                    <?php
                    $sidebarLeft  = regionHasContent("sidebar-left");
                    $sidebarRight = regionHasContent("sidebar-right");
                    $class = "";
                    $class .= $sidebarLeft  ? "has-sidebar-left "  : "";
                    $class .= $sidebarRight ? "has-sidebar-right " : "";
                    $class .= empty($class) ? "" : "has-sidebar";
                    ?>

                    <?php if ($sidebarLeft) : ?>
                        <div class="wrap-sidebar region-sidebar-left <?= $class ?>" role="complementary">
                            <?php renderRegion("sidebar-left") ?>
                        </div>
                    <?php endif; ?>

                    <?php if (regionHasContent("main")) : ?>
                        <main class="region-main <?= $class ?>" role="main">
                            <?php renderRegion("main") ?>
                        </main>
                    <?php endif; ?>

                    <?php if ($sidebarRight) : ?>
                        <div class="wrap-sidebar region-sidebar-right <?= $class ?>" role="complementary">
                            <?php renderRegion("sidebar-right") ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>



        <!-- after-main -->
        <?php if (regionHasContent("after-main")) : ?>
            <div class="outer-wrap outer-wrap-after-main">
                <div class="inner-wrap inner-wrap-after-main">
                    <div class="row">
                        <div class="region-after-main">
                            <?php renderRegion("after-main") ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- columns-below -->
        <?php if (regionHasContent("columns-below")) : ?>
            <div class="outer-wrap outer-wrap-columns-below">
                <div class="inner-wrap inner-wrap-columns-below">
                    <div class="row">
                        <div class="region-columns-below">
                            <?php renderRegion("columns-below") ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<footer id="footer">
    <div class="mui-container-fluid">
        <!-- sitefooter -->
        <?php if (regionHasContent("footer")) : ?>
                    <div class="mui-row">
                        <div class="region-footer">
                            <?php renderRegion("footer") ?>
                        </div>
                    </div>
        <?php endif; ?>
    </div>
</footer>
</body>
</html>
