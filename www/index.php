<?php
require_once("../controller.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $response->getTitle(); ?></title>
        <link rel="stylesheet" href="main.css" type="text/css" />
        <link rel="stylesheet" href="jquery-ui.css" type="text/css" />

        <script type="text/javascript" rel="script" src="jquery-3.2.1.min.js"></script>
        <script type="text/javascript" rel="script" src="jquery.canvasjs.min.js"></script>
        <script type="text/javascript" rel="script" src="jquery-ui.js"></script>
    </head>

    <?php flush(); ?>

    <body class="main">
        <div id="bootstrap">
            <div id="content-tabs">
                <div id="tab-table" class="content-tab hyperlink">Table</div>
                <div id="tab-bargraph" class="content-tab hyperlink">Bar Graph</div>
                <div id="tab-export" class="content-tab hyperlink">Export</div>
            </div>
        </div>

        <div id="view">
            <noscript>

            </noscript>

            <div id="time-filter">
                <div id="time-filter-start-container" class="container">Start: <input type="text" id="date-picker-start" class="date-picker" /></div>
                <div id="time-filter-end-container" class="container">End: <input type="text" id="date-picker-end" class="date-picker" /></div>
            </div>

            <?php

            include("../views/table.php");
            include("../views/bargraph.php");

            ?>

            <div id="view-spinner" class="spinner inactive"></div>
        </div>

        <div id="dialog-alert" class="dialog inactive">
        </div>

        <?php flush(); ?>

        <script type="text/javascript" rel="script">
            (function(domWindow)
            {
                domWindow.Defaults =
                {
                    PageActiveTab: "<?php echo $response->getActiveContentTabName(); ?>",
                    PageFilterDateStart: "<?php echo $response->getFilterStartDateDefault(); ?>",
                    PageFilterDateEnd: "<?php echo $response->getFilterEndDateDefault(); ?>"
                };
            })(window);
        </script>

        <script type="text/javascript" rel="script" src="main.js"></script>
    </body>

</html>