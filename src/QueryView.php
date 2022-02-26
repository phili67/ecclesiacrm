<?php
/*******************************************************************************
 *
 *  filename    : QueryView.php
 *  last change : 2012-07-22
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
 *                Copyright 2004-2012 Michael Wilt
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\RedirectUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;


// Security
if (!(SessionUser::getUser()->isShowMenuQueryEnabled())) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

//Set the page title
$sPageTitle = _('Query View');

//Get the QueryID from the querystring
$iQueryID = InputUtils::LegacyFilterInput($_GET['QueryID'], 'int');

$aFinanceQueries = explode(',', SystemConfig::getValue('aFinanceQueries'));

if (!(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance')) && in_array($iQueryID, $aFinanceQueries)) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

//Include the header
require 'Include/Header.php';

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
    ValidateInput();

    //Any errors?
    if (count($aErrorText) == 0) {
        //No errors; process the SQL, run the query, and display the results
        DisplayQueryInfo();
        ProcessSQL();
        DoQuery();
    } else {
        //Yes, there were errors; re-display the parameter form (the DisplayParameterForm function will
        //pick up and display any error messages)
        DisplayQueryInfo();
        DisplayParameterForm();
    }
} else {
    //Display the parameter form
    DisplayQueryInfo();
    DisplayParameterForm();
}

//Loops through all the parameters and ensures validation rules have been followed
function ValidateInput()
{
    global $rsParameters;
    global $_POST;
    global $vPOST;
    global $aErrorText;

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
        if ($qrp_Required && strlen(trim($_POST[$qrp_Alias])) < 1) {
            $bError = true;
            $aErrorText[$qrp_Alias] = _('This value is required.');
        } //Assuming there was no error above...
        else {
            //Validate differently depending on the contents of the qrp_Validation field
            switch ($qrp_Validation) {
                //Numeric validation
                case 'n':

                    //Is it a number?
                    if (!is_numeric($_POST[$qrp_Alias])) {
                        $bError = true;
                        $aErrorText[$qrp_Alias] = _('This value must be numeric.');
                    } else {
                        //Is it more than the minimum?
                        if ($_POST[$qrp_Alias] < $qrp_NumericMin) {
                            $bError = true;
                            $aErrorText[$qrp_Alias] = _('This value must be at least ') . $qrp_NumericMin;
                        } //Is it less than the maximum?
                        elseif ($_POST[$qrp_Alias] > $qrp_NumericMax) {
                            $bError = true;
                            $aErrorText[$qrp_Alias] = _('This value cannot be more than ') . $qrp_NumericMax;
                        }
                    }

                    $vPOST[$qrp_Alias] = InputUtils::LegacyFilterInput($_POST[$qrp_Alias], 'int');
                    break;

                //Alpha validation
                case 'a':

                    //Is the length less than the maximum?
                    if (strlen($_POST[$qrp_Alias]) > $qrp_AlphaMaxLength) {
                        $bError = true;
                        $aErrorText[$qrp_Alias] = _('This value cannot be more than ') . $qrp_AlphaMaxLength . _(' characters long');
                    } //is the length more than the minimum?
                    elseif (strlen($_POST[$qrp_Alias]) < $qrp_AlphaMinLength) {
                        $bError = true;
                        $aErrorText[$qrp_Alias] = _('This value cannot be less than ') . $qrp_AlphaMinLength . _(' characters long');
                    }

                    $vPOST[$qrp_Alias] = InputUtils::LegacyFilterInput($_POST[$qrp_Alias]);
                    break;

                default:
                    $vPOST[$qrp_Alias] = $_POST[$qrp_Alias];
                    break;
            }
        }
    }
}

//Loops through the list of parameters and replaces their alias in the SQL with the value given for the parameter
function ProcessSQL()
{
    global $vPOST;
    global $qry_SQL;
    global $rsParameters;

    //Loop through the list of parameters
    if (mysqli_num_rows($rsParameters)) {
        mysqli_data_seek($rsParameters, 0);
    }
    while ($aRow = mysqli_fetch_array($rsParameters)) {
        extract($aRow);

        //Debugging code
        //echo "--" . $qry_SQL . "<br>--" . "~" . $qrp_Alias . "~" . "<br>--" . $vPOST[$qrp_Alias] . "<p>";

        //Replace the placeholder with the parameter value
        $qry_SQL = str_replace('~' . $qrp_Alias . '~', $vPOST[$qrp_Alias], $qry_SQL);
    }
}

//Checks if a count is to be displayed, and displays it if required
function DisplayRecordCount()
{
    global $qry_Count;
    global $rsQueryResults;

    //Are we supposed to display a count for this query?
    if ($qry_Count == 1) {
        //Display the count of the recordset
        echo '<p align="center">';
        echo mysqli_num_rows($rsQueryResults) . _(' record(s) returned');
        echo '</p>';
    }
}

//Runs the parameterized SQL and display the results
function DoQuery()
{
    global $cnInfoCentral;
    global $aRowClass;
    global $rsQueryResults;
    global $qry_SQL;
    global $iQueryID;
    global $qry_Name;
    global $qry_Count;

    //Run the SQL
    $rsQueryResults = MiscUtils::RunQuery($qry_SQL); ?>
    <div class="card card-primary">

        <div class="card-body">
            <table class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" id="query-table"
                   style="width:100%">
                <thead>
                <?php
                //Loop through the fields and write the header row
                for ($iCount = 0; $iCount < mysqli_num_fields($rsQueryResults); $iCount++) {
                    //If this field is called "AddToCart", provision a headerless column to hold the cart action buttons
                    $fieldInfo = mysqli_fetch_field_direct($rsQueryResults, $iCount);
                    if ($fieldInfo->name != 'AddToCart' && $fieldInfo->name != 'GDPR') {
                        echo '<th>' . _($fieldInfo->name) . '</th>';
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
                    echo '<tr>';

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
                            echo '<td>' . $aRow[$iCount] . '</td>';
                        }
                    }

                    echo '</tr>';
                } ?>
                </tbody>
            </table>

            <p class="text-right">
                <?= $qry_Count ? $qry_real_Count . _(' record(s) returned') : ''; ?>
            </p>
        </div>

        <div class="card-footer">
            <p>
                <?php if (count($aAddToCartIDs)) { ?>
            <div class="col-sm-offset-1">
                <button type="button" id="addResultsToCart"
                        class="btn btn-success btn-sm"> <?= _('Add To Cart') ?></button>
                <button type="button" id="intersectResultsToCart"
                        class="btn btn-warning btn-sm"><?= _('Intersect With Cart') ?></button>
                <button type="button" id="removeResultsFromCart"
                        class="btn btn-danger btn-sm"> <?= _('Remove From Cart') ?></button>
            </div>
            </p>
            <?php } ?>
            <p class="text-right">
                <?= '<a href="QueryView.php?QueryID=' . $iQueryID . '">' . _('Run Query Again') . '</a>'; ?>
            </p>
        </div>

    </div>

    <div class="card card-info">
        <div class="card-header border-0">
            <div class="card-title">Query</div>
        </div>
        <div class="card-body">
            <code><?= str_replace(chr(13), '<br>', htmlspecialchars($qry_SQL)); ?></code>
        </div>
    </div>

    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        $("#addResultsToCart").click(function () {
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

        $("#intersectResultsToCart").click(function () {
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

        $("#removeResultsFromCart").click(function () {
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
function DisplayQueryInfo()
{
    global $qry_Name;
    global $qry_Description; ?>
    <div class="card card-info">
        <div class="card-body">
            <p><strong><?= _($qry_Name); ?></strong></p>
            <p><?= _($qry_Description); ?></p>
        </div>
    </div>
    <?php
}


function getQueryFormInput($queryParameters)
{
    global $aErrorText;

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
        return '<div class="form-group has-error">' . $label . $input . $helpBlock . '</div>';
    }

    return '<div class="form-group">' . $label . $input . $helpBlock . '</div>';
}

//Displays a form to enter values for each parameter, creating INPUT boxes and SELECT drop-downs as necessary
function DisplayParameterForm()
{
    global $rsParameters;
    global $iQueryID; ?>
    <div class="row">
        <div class="col-md-8">

            <div class="card card-primary">

                <div class="card-body">

                    <form method="post" action="QueryView.php?QueryID=<?= $iQueryID ?>">
                        <?php
                        //Loop through the parameters and display an entry box for each one
                        if (mysqli_num_rows($rsParameters)) {
                            mysqli_data_seek($rsParameters, 0);
                        }
                        while ($aRow = mysqli_fetch_array($rsParameters)) {
                            echo getQueryFormInput($aRow);
                        } ?>

                        <div class="form-group text-right">
                            <input class="btn btn-primary" type="Submit" value="<?= _("Execute Query") ?>"
                                   name="Submit">
                        </div>
                    </form>

                </div>
            </div> <!-- box -->

        </div>

    </div>

    <?php
}

?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/people/AddRemoveCart.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        window.CRM.queryTable = $("#query-table").DataTable(window.CRM.plugin.dataTable);
    });
</script>

<?php
require 'Include/Footer.php';
?>


