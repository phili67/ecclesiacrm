<?php
/*******************************************************************************
 *
 *  filename    : templates/autoPaymentClearAccount.php
 *
 *  http://www.ecclesiacrm.com/
 *
 *  2023 Philippe Logel
 *
 ******************************************************************************/

use Propel\Runtime\Propel;

$connection = Propel::getConnection();

$sSQL = 'UPDATE autopayment_aut SET ';
$sSQL .= 'aut_CreditCard=CONCAT("************",SUBSTR(aut_CreditCard,LENGTH(aut_CreditCard)-3,4))';
$sSQL .= ', aut_Account=CONCAT("*****",SUBSTR(aut_Account,LENGTH(aut_Account)-3, 4))';
$sSQL .= " WHERE aut_ID=$iAutID";

$raOpps = $connection->prepare($sSQL);

try {
    $raOpps->execute();
    header('Content-type: application/json');
    echo json_encode(['Success'=>true, 'ErrStr'=>$errStr]);
} catch (PDOException $e) {
    return gettext('Cannot execute query.')."<p>$sSQL<p>".$e->getMessage();
}

