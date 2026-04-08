<?php
/*******************************************************************************
 *
 *  filename    : querylist.php
 *  last change : 2023-05-30
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                2023 Philippe Logel
 *
 ******************************************************************************/

 use EcclesiaCRM\dto\SystemConfig;
 use EcclesiaCRM\Utils\InputUtils;
 use EcclesiaCRM\Utils\MiscUtils;
 use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\SessionUser;

 //Loops through all the parameters and ensures validation rules have been followed
function ValidateInput($rsParameters, $POST)
{
    //Initialize the validated post array, error text array, and the error flag
    $vPOST = [];
    $aErrorText = [];
    $bError = false;

    //Are there any parameters to loop through?
    if (mysqli_num_rows($rsParameters)) {
        mysqli_data_seek($rsParameters, 0);
    }
    while ($aRow = mysqli_fetch_array($rsParameters)) {
        extract($aRow);

        //Is the value required?
        if ($qrp_Required && strlen(trim($POST[$qrp_Alias])) < 1) {
            $bError = true;
            $aErrorText[$qrp_Alias] = _('This value is required.');
        } //Assuming there was no error above...
        else {
            $ret = '';
            
            //Validate differently depending on the contents of the qrp_Validation field
            switch ($qrp_Validation) {
                //Numeric validation
                case 'n':

                    //Is it a number?
                    if (!is_numeric($POST[$qrp_Alias])) {
                        $bError = true;
                        $aErrorText[$qrp_Alias] = _('This value must be numeric.');
                    } else {
                        //Is it more than the minimum?
                        if ($POST[$qrp_Alias] < $qrp_NumericMin) {
                            $bError = true;
                            $aErrorText[$qrp_Alias] = _('This value must be at least ') . $qrp_NumericMin;
                        } //Is it less than the maximum?
                        elseif ($POST[$qrp_Alias] > $qrp_NumericMax) {
                            $bError = true;
                            $aErrorText[$qrp_Alias] = _('This value cannot be more than ') . $qrp_NumericMax;
                        }
                    }

                    $ret = InputUtils::LegacyFilterInput($POST[$qrp_Alias], 'int');
                    break;

                //Alpha validation
                case 'a':

                    //Is the length less than the maximum?
                    if (strlen($POST[$qrp_Alias]) > $qrp_AlphaMaxLength) {
                        $bError = true;
                        $aErrorText[$qrp_Alias] = _('This value cannot be more than ') . $qrp_AlphaMaxLength . _(' characters long');
                    } //is the length more than the minimum?
                    elseif (strlen($POST[$qrp_Alias]) < $qrp_AlphaMinLength) {
                        $bError = true;
                        $aErrorText[$qrp_Alias] = _('This value cannot be less than ') . $qrp_AlphaMinLength . _(' characters long');
                    }

                    $ret = InputUtils::LegacyFilterInput($POST[$qrp_Alias]);
                    break;

                default:
                    $ret = InputUtils::LegacyFilterInput($POST[$qrp_Alias]);
                    break;
            }

            if (!empty($ret) || $ret === '0') {
                $vPOST[$qrp_Alias] = $ret;
            }
        }
    }

    return [$aErrorText, $vPOST];
}

//Loops through the list of parameters and replaces their alias in the SQL with the value given for the parameter
function ProcessSQL($vPOST, $qry_SQL, $rsParameters)
{
    //Loop through the list of parameters
    if (mysqli_num_rows($rsParameters)) {
        mysqli_data_seek($rsParameters, 0);
    }
    while ($aRow = mysqli_fetch_array($rsParameters)) {
        extract($aRow);

        if (!isset($vPOST[$qrp_Alias])) {
            return "";
        }

        //Debugging code
        if (SessionUser::getUser()->isAdmin()) {
        ?>
        <?= "--" . $qry_SQL ?><br>-- ~<?= $qrp_Alias ?>~<br>--<?= $vPOST[$qrp_Alias] ?><p>
        <?php
        }
        //Replace the placeholder with the parameter value
        $qry_SQL = str_replace('~' . $qrp_Alias . '~', $vPOST[$qrp_Alias], $qry_SQL);
    }

    return $qry_SQL;
}

//Checks if a count is to be displayed, and displays it if required
function DisplayRecordCount($qry_Count, $rsQueryResults)
{
    //Are we supposed to display a count for this query?
    if ($qry_Count == 1) {
        //Display the count of the recordset
        ?>
        <p align="center">
            <?= mysqli_num_rows($rsQueryResults) . _(' record(s) returned') ?>
        </p>
    <?php
    }
}

//Runs the parameterized SQL and display the results
function DoQuery($cnInfoCentral, $aRowClass, $rsQueryResults, $qry_SQL, $iQueryID, $qry_Name, $qry_Count, $CSPNonce, $sRootPath) 
{
   //Run the SQL
    $rsQueryResults = MiscUtils::RunQuery($qry_SQL); ?>
    <div class="card card-primary card-outline">
        <div class="card-header border-1 d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title mb-0"><i class="fas fa-table mr-1"></i><?= _($qry_Name) ?></h3>
            <div id="cart-buttons" style="display: none;" class="d-flex flex-wrap">
                <button type="button" id="addResultsToCart" class="btn btn-success btn-sm mr-2 mb-2">
                    <i class="fas fa-cart-plus mr-1"></i><?= _('Add To Cart') ?>
                </button>
                <button type="button" id="intersectResultsToCart" class="btn btn-warning btn-sm mr-2 mb-2">
                    <i class="fas fa-cart-shopping mr-1"></i><?= _('Intersect With Cart') ?>
                </button>
                <button type="button" id="removeResultsFromCart" class="btn btn-danger btn-sm mb-2">
                    <i class="fas fa-times mr-1"></i><?= _('Remove From Cart') ?>
                </button>
            </div>
        </div>    
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" id="query-table"
                       style="width:100%">
                    <thead>
                    <?php
                    //Loop through the fields and write the header row
                    for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
                        //If this field is called "AddToCart", provision a headerless column to hold the cart action buttons
                        $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);
                        if ($fieldInfo->name != 'AddToCart' && $fieldInfo->name != 'GDPR') {?>
                            <th><?= _($fieldInfo->name) ?></th>
                        <?php
                        } elseif ($fieldInfo->name == 'AddToCart') {
                            ?>
                            <th>
                                <?= _("Add to Cart") ?>
                            </th>
                            <?php
                        }
                    }
                    ?>
                    </thead>
                    <tbody>
                    <?php
                    $aAddToCartIDs = [];

                    $qry_real_Count = 0;

                    while ($aRow = mysqli_fetch_array($rsQueryResults)) {
                        if (!is_null($aRow['GDPR']) && SystemConfig::getBooleanValue('bGDPR')) continue;

                        $qry_real_Count++;

                        //Alternate the background color of the row
                        ?>
                        <tr>
                        <?php

                        //Loop through the fields and write each one
                        for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
                            // If this field is called "AddToCart", add a cart button to the form
                            $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);

                            if ($fieldInfo->name == 'AddToCart') {
                                if (!Cart::PersonInCart($aRow[$iCount])) {
                                    ?>
                                    <td>
                                        <a class="AddToPeopleCart" data-cartpersonid="<?= $aRow[$iCount] ?>">
                                             <span class="fa-stack">
                                             <i class="fas fa-square fa-stack-2x"></i>
                                             <i class="fas fa-cart-plus fa-stack-1x fa-inverse"></i>
                                             </span>
                                        </a>
                                    </td>
                                    <?php
                                } else {
                                    ?>
                                    <td>
                                        <a class="RemoveFromPeopleCart" data-cartpersonid="<?= $aRow[$iCount] ?>">
                             <span class="fa-stack">
                             <i class="fas fa-square fa-stack-2x"></i>
                             <i class="fas fa-times fa-stack-1x fa-inverse"></i>
                             </span>
                                        </a>
                                    </td>
                                    <?php
                                }
                                $aAddToCartIDs[] = $aRow[$iCount];
                            } //...otherwise just render the field
                            else if ($fieldInfo->name != 'GDPR') {
                                //Write the actual value of this row
                                if ( mb_strpos($aRow[$iCount],"<a href=") !== false) {
                                    $res = str_replace("<a href=", "<a href=".$sRootPath."/", $aRow[$iCount]);
                                    ?>
                                    <td><?= $res ?></td>
                                <?php
                                } else {
                                ?>
                                    <td><?= $aRow[$iCount] ?></td>
                                <?php
                                }

                            }
                        }
                        ?>
                        </tr>
                    <?php
                    } ?>
                    </tbody>
                </table>
            </div>

            <p class="text-right">
                <?= $qry_Count ? $qry_real_Count . _(' record(s) returned') : ''; ?>
            </p>
        </div>

        <div class="card-footer">
            <div class="text-right">
                <?= '<a class="btn btn-outline-secondary" href="' . $sRootPath . '/v2/query/view/' . $iQueryID . '"><i class="fa-solid fa-database mr-1"></i> ' . _('Run Query Again') . '</a>'; ?>
            </div>
        </div>

    </div>

    <?php if (SessionUser::getUser()->isAdmin()) { ?>
    <div class="card card-info card-outline">
        <div class="card-header border-1">
            <div class="card-title"><i class="fas fa-code mr-1"></i>Query</div>
        </div>
        <div class="card-body">
            <code>
            <?php 
                    echo str_replace(chr(13), '<br>', htmlspecialchars($qry_SQL));                 
            ?>
            </code>
        </div>
    </div>
    <?php }
    ?>

    <script nonce="<?= $CSPNonce ?>">
        var aAddToCartIDs = <?= json_encode($aAddToCartIDs) ?>;
        if (aAddToCartIDs.length > 0) {
            $("#cart-buttons").show();
        }
        $("#addResultsToCart").on('click', function () {
            var selectedPersons = <?= json_encode($aAddToCartIDs, JSON_NUMERIC_CHECK) ?>;
            window.CRM.cart.addPerson(selectedPersons, function (data) {
                if (data.status == "success") {
                    // broadcaster
                    $.event.trigger({
                        type: "updateCartMessage",
                        people: data.cart
                    });
                }

                window.CRM.queryTable.rows().every(function (rowIdx, tableLoop, rowLoop) {
                    var personButton = this.data()[0];
                    var personID = $(personButton).data("cartpersonid")

                    var link = "<a class=\"RemoveFromPeopleCart\" data-cartpersonid=\"" + personID + "\">\n" +
                        "                         <span class=\"fa-stack\">\n" +
                        "                         <i class=\"fas fa-square fa-stack-2x\"></i>\n" +
                        "                         <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n" +
                        "                         </span>\n" +
                        "                     </a>"

                    window.CRM.queryTable.cell(rowIdx, 0).data(link);
                });
            });

        });

        $("#intersectResultsToCart").on('click', function () {
            var selectedPersons = <?= json_encode($aAddToCartIDs, JSON_NUMERIC_CHECK) ?>;
            window.CRM.cart.intersectPerson(selectedPersons, function (data) {
                if (data.status == "success") {
                    // broadcaster
                    $.event.trigger({
                        type: "updateCartMessage",
                        people: data.cart
                    });
                }

                var cartPeople = data.cart;

                window.CRM.queryTable.rows().every(function (rowIdx, tableLoop, rowLoop) {
                    var personButton = this.data()[0];
                    var personID = $(personButton).data("cartpersonid")

                    var link = "";

                    if (cartPeople != undefined && cartPeople.length > 0 && cartPeople.includes(personID)) {
                        link = "<a class=\"RemoveFromPeopleCart\" data-cartpersonid=\"" + personID + "\">\n" +
                            "                         <span class=\"fa-stack\">\n" +
                            "                         <i class=\"fas fa-square fa-stack-2x\"></i>\n" +
                            "                         <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n" +
                            "                         </span>\n" +
                            "                     </a>";
                    } else {
                        link = "<a class=\"AddToPeopleCart\" data-cartpersonid=\"" + personID + "\">\n" +
                            "                         <span class=\"fa-stack\">\n" +
                            "                         <i class=\"fas fa-square fa-stack-2x\"></i>\n" +
                            "                         <i class=\"fas fa-cart-plus fa-stack-1x fa-inverse\"></i>\n" +
                            "                         </span>\n" +
                            "                     </a>";
                    }

                    window.CRM.queryTable.cell(rowIdx, 0).data(link);
                });
            });
        });

        $("#removeResultsFromCart").on('click', function () {
            var selectedPersons = <?= json_encode($aAddToCartIDs, JSON_NUMERIC_CHECK) ?>;
            window.CRM.cart.removePerson(selectedPersons, function (data) {
                if (data.status == "success") {
                    // broadcaster
                    $.event.trigger({
                        type: "updateCartMessage",
                        people: data.cart
                    });
                }

                window.CRM.queryTable.rows().every(function (rowIdx, tableLoop, rowLoop) {
                    var personButton = this.data()[0];
                    var personID = $(personButton).data("cartpersonid")

                    var link = "<a class=\"AddToPeopleCart\" data-cartpersonid=\"" + personID + "\">\n" +
                        "                         <span class=\"fa-stack\">\n" +
                        "                         <i class=\"fas fa-square fa-stack-2x\"></i>\n" +
                        "                         <i class=\"fas fa-cart-plus fa-stack-1x fa-inverse\"></i>\n" +
                        "                         </span>\n" +
                        "                     </a>"

                    window.CRM.queryTable.cell(rowIdx, 0).data(link);
                });
            });
        });
    </script>


    <?php

}


//Displays the name and description of the query
function DisplayQueryInfo($qry_Name, $qry_Description)
{
?>
    <div class="card card-info card-outline">
        <div class="card-header border-1">
            <h3 class="card-title"><i class="fas fa-circle-info mr-1"></i><?= _($qry_Name); ?></h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0"><?= _($qry_Description); ?></p>
        </div>
    </div>
    <?php
}


function getQueryFormInput($queryParameters)
{
    extract($queryParameters);

    $input = '';
    $label = '<label>' . _($qrp_Name) . '</label>';
    $helpMsg = '<div>' . _($qrp_Description) . '</div>';

    switch ($qrp_Type) {
        //Standard INPUT box
        case 0:
            $input = '<input size="' . $qrp_InputBoxSize . '" name="' . $qrp_Alias . '" type="text" value="' . $qrp_Default . '" class= "form-control form-control-sm">';
            break;

        //SELECT box with OPTION tags supplied in the queryparameteroptions_qpo table
        case 1:
            //Get the query parameter options for this parameter
            $sSQL = 'SELECT * FROM queryparameteroptions_qpo WHERE qpo_qrp_ID = ' . $qrp_ID;
            $rsParameterOptions = MiscUtils::RunQuery($sSQL);

            $input = '<select name="' . $qrp_Alias . '" class= "form-control form-control-sm">';
            $input .= '<option disabled selected value> -- ' . _("select an option") . ' -- </option>';

            //Loop through the parameter options
            while ($ThisRow = mysqli_fetch_array($rsParameterOptions)) {
                extract($ThisRow);
                $input .= '<option value="' . $qpo_Value . '">' . _($qpo_Display) . '</option>';
            }

            $input .= '</select>';
            break;

        //SELECT box with OPTION tags provided via a SQL query
        case 2:
            //Run the SQL to get the options
            $rsParameterOptions = MiscUtils::RunQuery($qrp_OptionSQL);

            $input .= '<select name="' . $qrp_Alias . '" class= "form-control form-control-sm">';
            $input .= '<option disabled selected value> -- ' . _('select an option') . ' -- </option>';

            while ($ThisRow = mysqli_fetch_array($rsParameterOptions)) {
                extract($ThisRow);
                $input .= '<option value="' . $Value . '">' . $Display . '</option>';
            }

            $input .= '</select>';
            break;
    }

    $helpBlock = '<div class="help-block">' . $helpMsg . '</div>';

    if ($aErrorText[$qrp_Alias]) {
        $errorMsg = '<div>' . $aErrorText[$qrp_Alias] . '</div>';
        $helpBlock = '<div class="help-block">' . $helpMsg . $errorMsg . '</div>';
        return [$aErrorText, '<div class="form-group has-error">' . $label . $input . $helpBlock . '</div>'];
    }

    return [$aErrorText, '<div class="form-group">' . $label . $input . $helpBlock . '</div>'];
}

//Displays a form to enter values for each parameter, creating INPUT boxes and SELECT drop-downs as necessary
function DisplayParameterForm($rsParameters, $iQueryID, $sRootPath)
{ ?>
    <div class="row">
        <div class="col-lg-8 col-xl-7">
            <div class="card card-primary card-outline">
                <div class="card-header border-1">
                    <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i><?= _("Query Parameters") ?></h3>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= $sRootPath ?>/v2/query/view/<?= $iQueryID ?>">
                        <?php
                        //Loop through the parameters and display an entry box for each one
                        if (mysqli_num_rows($rsParameters)) {
                            mysqli_data_seek($rsParameters, 0);
                        }
                        while ($aRow = mysqli_fetch_array($rsParameters)) {
                            $res = getQueryFormInput($aRow);
                            ?>
                            <?= $res[1] ?>
                        <?php                            
                        } ?>

                        <div class="form-group text-right mb-0">
                            <button class="btn btn-success" type="submit" name="Submit">
                                <i class="fas fa-play mr-1"></i> <?= _("Execute Query") ?>
                            </button>
                        </div>
                    </form>

                </div>
            </div> <!-- box -->

        </div>

    </div>

    <?php
}

require $sRootDocument . '/Include/Header.php';

?>
<div class="d-flex justify-content-end mb-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= $sRootPath ?>/v2/query/list">
        <i class="fas fa-arrow-left mr-1"></i><?= _('Back to Query List') ?>
    </a>
</div>
<?php

//Get the query information
$sSQL = 'SELECT * FROM query_qry WHERE qry_ID = ' . $iQueryID;
$rsSQL = MiscUtils::RunQuery($sSQL);
extract(mysqli_fetch_array($rsSQL));

//Get the parameters for this query
$sSQL = 'SELECT * FROM queryparameters_qrp WHERE qrp_qry_ID = ' . $iQueryID . ' ORDER BY qrp_ID';
$rsParameters = MiscUtils::RunQuery($sSQL);

//If the form was submitted or there are no parameters, run the query
if (isset($_POST['Submit']) || mysqli_num_rows($rsParameters) == 0) {
    //Check that all validation rules were followed
    $res = ValidateInput($rsParameters, $_POST);
    $aErrorText = $res[0];
    $vPOST = $res[1];

    //Any errors?
    if (count($aErrorText) == 0) {
        //No errors; process the SQL, run the query, and display the results
        DisplayQueryInfo($qry_Name, $qry_Description);
        $qry_SQL = ProcessSQL($vPOST, $qry_SQL, $rsParameters);
        if (empty($qry_SQL)) {
            echo '<div class="alert alert-danger">' . _('An error occurred while processing the SQL for this query. Please check your parameter values and try again.') . '</div>';
            DisplayParameterForm($rsParameters, $iQueryID, $sRootPath);
            require $sRootDocument . '/Include/Footer.php';
            exit;
        } else {
            DoQuery($cnInfoCentral, $aRowClass, $rsQueryResults, $qry_SQL, $iQueryID, $qry_Name, $qry_Count, $CSPNonce, $sRootPath);
        }
    } else {
        //Yes, there were errors; re-display the parameter form (the DisplayParameterForm function will
        //pick up and display any error messages)
        DisplayQueryInfo($qry_Name, $qry_Description);
        DisplayParameterForm($rsParameters, $iQueryID, $sRootPath);
    }
} else {
    //Display the parameter form
    DisplayQueryInfo($qry_Name, $qry_Description);
    DisplayParameterForm($rsParameters, $iQueryID, $sRootPath);
}
?>

<script src="<?= $sRootPath ?>/skin/js/people/AddRemoveCart.js"></script>

<script nonce="<?= $CSPNonce ?>">
    $(function() {
        window.CRM.queryTable = $("#query-table").DataTable(window.CRM.plugin.dataTable);
    });
</script>


<?php require $sRootDocument . '/Include/Footer.php'; ?>
