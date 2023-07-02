<?php

/*******************************************************************************
 *
 *  filename    : templates/USISTAddressVerification.php
 *  last change : 2023-06-28
 *  website     : http://www.ecclesiacrm.com
 *                © 2023 Philippe Logel
 *
 ******************************************************************************/

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\ISTAddressLookup;
use EcclesiaCRM\SQLUtils;
use EcclesiaCRM\Utils\MiscUtils;

function XMLparseIST($xmlstr, $xmlfield)
{
    // Function to parse XML data from Intelligent Search Technolgy, Ltd.

    if (!(strpos($xmlstr, "<$xmlfield>") === false) ||
          strpos($xmlstr, "</$xmlfield>" === false)) {
        $startpos = strpos($xmlstr, "<$xmlfield>") + strlen("<$xmlfield>");
        $endpos = strpos($xmlstr, "</$xmlfield>");

        if ($endpos < $startpos) {
            return '';
        }

        return mb_substr($xmlstr, $startpos, $endpos - $startpos);
    }

    return '';
}

require $sRootDocument . '/Include/Header.php';

if (strlen(SystemConfig::getValue('sISTusername')) && strlen(SystemConfig::getValue('sISTpassword'))) {
    $myISTAddressLookup = new ISTAddressLookup();
    $myISTAddressLookup->getAccountInfo(SystemConfig::getValue('sISTusername'), SystemConfig::getValue('sISTpassword'));
    $myISTReturnCode = $myISTAddressLookup->GetReturnCode();
    $myISTSearchesLeft = $myISTAddressLookup->GetSearchesLeft();
} else {
    $myISTReturnCode = '9';
    $myISTSearchesLeft = 'Missing sISTusername or sISTpassword';
}

if ($myISTReturnCode == '4') {
    ?>
  <div class="row">
    <div class="col-lg-12 col-md-7 col-sm-3">
      <div class="card card-body">
        <div class="alert alert-danger alert-dismissible">
          <h4><i class="icon fas fa-ban"></i>The Intelligent Search Technology, Ltd. XML web service is temporarily unavailable.</h4>
            <?= 'getAccountInfo ReturnCode = '.$myISTReturnCode ?>
                Please try again in 30 minutes.
                You may follow the URL below to log in and manage your Intelligent Search ';
                  Technology account settings.  This link may also provide information pertaining to ';
                this service disruption.<br><br>
          <a href="https://www.intelligentsearch.com/Hosted/User/">https://www.intelligentsearch.com/Hosted/User/</a>';
        </div>
      </div>
    </div>
  </div>
  <?php
} elseif ($myISTReturnCode != '0') {
        ?>
  <div class="row">
    <div class="col-lg-12 col-md-7 col-sm-3">
      <div class="card card-body">
        <div class="alert alert-danger alert-dismissible">
          <h4><i class="icon fas fa-ban"></i>The Intelligent Search Technology, Ltd. XML web service is temporarily unavailable.</h4>
          <p><?='getAccountInfo ReturnCode = '.$myISTReturnCode ?></p>
          <p><?= $myISTSearchesLeft ?></p>
          <p>Please verify that your Intelligent Search Technology, Ltd. username and password are correct</p>
          <p><i>Admin -> Edit General Settings -> sISTusername</i></p>
          <p><i>Admin -> Edit General Settings -> sISTpassword</i></p>
          <p>Follow the URL below to log in and manage your Intelligent Search Technology account settings.  If you do not already have an account you may establish an account at this URL. This software was written to work best with the service CorrectAddress(R) with Addons</p>
          <a href="https://www.intelligentsearch.com/Hosted/User/">https://www.intelligentsearch.com/Hosted/User/</a>
          <br><br>
          If you are sure that your account username and password are correct and that your
          account is in good standing it is possible that the server is currently unavailable
          but may be back online if you try again later.<br><br>
          EcclesiaCRM uses XML web services provided by Intelligent
          Search Technology, Ltd.  For information about CorrectAddress(R) Online Address
          Verification Service visit the following URL. This software was written to work
          best with the service CorrectAddress(R) with Addons. <br><br>
          <a href="http://www.intelligentsearch.com/address_verification/verify_address.html"> <?= _('http://www.intelligentsearch.com/address_verification/verify_address.html') ?></a>
        </div>
      </div>
    </div>
  </div>
  <?php
    } elseif ($myISTSearchesLeft == 'X') {
        ?>
        <br>
        Searches Left = <?=$myISTSearchesLeft ?><br><br>
        Follow the URL below to log in and manage your Intelligent Search Technology account settings.<br>

        <a href="https://www.intelligentsearch.com/Hosted/User/">
            https://www.intelligentsearch.com/Hosted/User/</a><br><br><br>

        This software was written to work best with the service CorrectAddress(R)
        with Addons. <br><br><br>
    <?php
    } else {
        // IST account is valid and working.  Time to get to work.
    ?>        
        <h3>
        To conserve funds the following rules are used to determine if
        an address lookup should be performed.<br>
        1) The family record has been added since the last lookup<br>
        2) The family record has been edited since the last lookup<br>
        3) It's been more than two years since the family record has been verified<br>
        4) The address must be a US address (Country = United States)<br><br>
        </h3>

        <?php
        // Housekeeping ... Delete families from the table istlookup_lu that
        // do not exist in the table family_fam.  This happens whenever
        // a family is deleted from family_fam.  (Or, more rarely, if a family
        // moves to another country)

        $sSQL = 'SELECT lu_fam_ID FROM istlookup_lu ';
        $rsIST = MiscUtils::RunQuery($sSQL);
        $iOrphanCount = 0;
        while ($aRow = mysqli_fetch_array($rsIST)) {
            extract($aRow);
            // verify that this ID exists in family_fam with
            // fam_Country = 'United States'
            $sSQL = 'SELECT count(fam_ID) as idexists FROM family_fam ';
            $sSQL .= "WHERE fam_ID='$lu_fam_ID' ";
            $sSQL .= "AND fam_Country='United States'";
            $rsExists = MiscUtils::RunQuery($sSQL);
            extract(mysqli_fetch_array($rsExists));
            if ($idexists == '0') {
                $sSQL = "DELETE FROM istlookup_lu WHERE lu_fam_ID='$lu_fam_ID'";
                MiscUtils::RunQuery($sSQL);
                $iOrphanCount++;
            }
        }
        ?>
        
        <h4>
        <?php
        if ($iOrphanCount) {
            ?>
            <?= $iOrphanCount ?> Orphaned IDs deleted.<br>
        <?php    
        }

        // More Housekeeping ... Delete families from the table istlookup_lu that
        // have had their family_fam records edited since the last lookup
        //
        // Note: If the address matches the information from the previous
        // lookup the delete is not necessary.  Perform this check to determine
        // if a delete is really needed.  This avoids the problem of having to do
        // a lookup AFTER the address has been corrected.

        $sSQL = 'SELECT * FROM family_fam INNER JOIN istlookup_lu ';
        $sSQL .= 'ON family_fam.fam_ID = istlookup_lu.lu_fam_ID ';
        $sSQL .= 'WHERE fam_DateLastEdited > lu_LookupDateTime ';
        $sSQL .= 'AND fam_DateLastEdited IS NOT NULL';
        $rsUpdated = MiscUtils::RunQuery($sSQL);
        $iUpdatedCount = 0;
        while ($aRow = mysqli_fetch_array($rsUpdated)) {
            extract($aRow);

            $sFamilyAddress = $fam_Address1.$fam_Address2.$fam_City.
            $fam_State.$fam_Zip;
            $sLookupAddress = $lu_DeliveryLine1.$lu_DeliveryLine2.$lu_City.
            $lu_State.$lu_ZipAddon;

            // compare addresses
            if (strtoupper($sFamilyAddress) != strtoupper($sLookupAddress)) {
                // only delete mismatches from lookup table
                $sSQL = "DELETE FROM istlookup_lu WHERE lu_fam_ID='$fam_ID'";
                MiscUtils::RunQuery($sSQL);
                $iUpdatedCount++;
            }
        }
        if ($iUpdatedCount) {
        ?>
            <?= $iUpdatedCount ?> Updated IDs deleted.<br>
        <?php
        }

        // More Housekeeping ... Delete families from the table istlookup_lu that
        // have not had a lookup performed in more than one year.  Zip codes and street
        // names occasionally change so a verification every two years is a good idea.

        $twoYearsAgo = date('Y-m-d H:i:s', strtotime('-24 months'));

        $sSQL = 'SELECT lu_fam_ID FROM istlookup_lu ';
        $sSQL .= "WHERE '$twoYearsAgo' > lu_LookupDateTime";
        $rsResult = MiscUtils::RunQuery($sSQL);
        $iOutdatedCount = 0;
        while ($aRow = mysqli_fetch_array($rsResult)) {
            extract($aRow);
            $sSQL = "DELETE FROM istlookup_lu WHERE lu_fam_ID='$lu_fam_ID'";
            MiscUtils::RunQuery($sSQL);
            $iOutdatedCount++;
        }
        if ($iOutdatedCount) {
        ?>
            <?= $iOutdatedCount ?> Outdated IDs deleted.<br>
        <?php
        }

        // All housekeeping is finished !!!
        // Get count of non-US addresses
        $sSQL = 'SELECT count(fam_ID) AS nonustotal FROM family_fam ';
        $sSQL .= "WHERE fam_Country NOT IN ('United States')";
        $rsResult = MiscUtils::RunQuery($sSQL);
        extract(mysqli_fetch_array($rsResult));
        $iNonUSCount = intval($nonustotal);
        if ($iNonUSCount) {
            ?>
            <?= $iNonUSCount ?> Non US addresses in database will not be verified.<br>
        <?php
        }

        // Get count of US addresses
        $sSQL = 'SELECT count(fam_ID) AS ustotal FROM family_fam ';
        $sSQL .= "WHERE fam_Country IN ('United States')";
        $rsResult = MiscUtils::RunQuery($sSQL);
        extract(mysqli_fetch_array($rsResult));
        $iUSCount = intval($ustotal);
        if ($iUSCount) {
        ?>
            <?= $iUSCount ?> Total US addresses in database.<br>
        <?php
        }

        // Get count of US addresses that do not require a fresh lookup
        $sSQL = 'SELECT count(lu_fam_ID) AS usokay FROM istlookup_lu';
        $rsResult = MiscUtils::RunQuery($sSQL);
        extract(mysqli_fetch_array($rsResult));
        $iUSOkay = intval($usokay);
        if ($iUSOkay) {
        ?>
            <?= $iUSOkay ?> US addresses have had lookups performed.<br>
        <?php
        }

        // Get count of US addresses ready for lookup
        $sSQL = 'SELECT count(fam_ID) AS newcount FROM family_fam ';
        $sSQL .= "WHERE fam_Country IN ('United States') AND fam_ID NOT IN (";
        $sSQL .= 'SELECT lu_fam_ID from istlookup_lu)';
        $rs = MiscUtils::RunQuery($sSQL);
        extract(mysqli_fetch_array($rs));
        $iEligible = intval($newcount);
        if ($iEligible) {
        ?>
          <?= $iEligible ?> US addresses are eligible for lookup.<br>
        <?php
        } else {
        ?>
           There are no US addresses eligible for lookup.<br>
        <?php
        }
        ?>
        </h4>
        <?php
        if (!empty($DoLookup)) {
            $startTime = time();  // keep tabs on how long this runs to avoid server timeouts

            ?>
            Lookups in process, screen refresh scheduled every 20 seconds.<br>

            <table>
                <tr>
                    <td>
                        <form method="POST" action="<?= $sRootPath ?>/v2/sytem/USISTAddress/Verification">
                            <input type=submit class=btn name=StopLookup value="Stop Lookups">
                        </form>
                    </td>
                </tr>
            </table>
    <?php
    // Get list of fam_ID that do not exist in table istlookup_lu
    $sSQL = 'SELECT fam_ID, fam_Address1, fam_Address2, fam_City, fam_State ';
            $sSQL .= 'FROM family_fam LEFT JOIN istlookup_lu ';
            $sSQL .= 'ON fam_id = lu_fam_id ';
            $sSQL .= 'WHERE lu_fam_id IS NULL ';
            $rsResult = MiscUtils::RunQuery($sSQL);

            $bNormalFinish = true;
            while ($aRow = mysqli_fetch_array($rsResult)) {
                extract($aRow);
                if (strlen($fam_Address2)) {
                    $fam_Address1 = $fam_Address2;
                    $fam_Address2 = '';
                }
                ?>
                Sent: <?= $fam_Address1?> <?= $fam_Address2 ?>
                <?= $fam_City ?> <?= $fam_State ?>
                <br>

                <?php
                $myISTAddressLookup = new ISTAddressLookup();
                $myISTAddressLookup->SetAddress($fam_Address1, $fam_Address2, $fam_City, $fam_State);

                $ret = $myISTAddressLookup->wsCorrectA(SystemConfig::getValue('sISTusername'), SystemConfig::getValue('sISTpassword'));

                $lu_fam_ID = SQLUtils::MySQLquote(addslashes($fam_ID));
                $lu_LookupDateTime = SQLUtils::MySQLquote(addslashes(date('Y-m-d H:i:s')));
                $lu_DeliveryLine1 = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetAddress1()));
                $lu_DeliveryLine2 = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetAddress2()));
                $lu_City = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetCity()));
                $lu_State = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetState()));
                $lu_ZipAddon = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetZip()));
                $lu_Zip = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetZip5()));
                $lu_Addon = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetZip4()));
                $lu_LOTNumber = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetLOTNumber()));
                $lu_DPCCheckdigit = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetDPCCheckdigit()));
                $lu_RecordType = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetRecordType()));
                $lu_LastLine = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetLastLine()));
                $lu_CarrierRoute = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetCarrierRoute()));
                $lu_ReturnCodes = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetReturnCodes()));
                $lu_ErrorCodes = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetErrorCodes()));
                $lu_ErrorDesc = SQLUtils::MySQLquote(addslashes($myISTAddressLookup->GetErrorDesc()));

                //echo "<br>" . $lu_ErrorCodes;

                $iSearchesLeft = $myISTAddressLookup->GetSearchesLeft();
                if (!is_numeric($iSearchesLeft)) {
                    $iSearchesLeft = 0;
                } else {
                    $iSearchesLeft = intval($iSearchesLeft);
                }
                ?>
                Received:  <?= $myISTAddressLookup->GetAddress1() ?>
                <?= $myISTAddressLookup->GetAddress2() ?> 
                <?= $myISTAddressLookup->GetLastLine() ?> <?= $iSearchesLeft ?>

                <?php
                if ($lu_ErrorDesc != 'NULL') {
                ?>
                    <?= $myISTAddressLookup->GetErrorDesc() ?>
                <?php
                }
                ?>
                
                <br><br>

                <?php
                if ($lu_ErrorCodes != "'xx'") {
                    // Error code xx is one of the following
                    // 1) Connection failure 2) Invalid username or password 3) No searches left
                    //
                    // Insert data into istlookup_lu table
                    //
                    $sSQL = 'INSERT INTO istlookup_lu (';
                    $sSQL .= '  lu_fam_ID,  lu_LookupDateTime,  lu_DeliveryLine1, ';
                    $sSQL .= '  lu_DeliveryLine2,  lu_City,  lu_State,  lu_ZipAddon, ';
                    $sSQL .= '  lu_Zip,  lu_Addon,  lu_LOTNumber,  lu_DPCCheckdigit,  lu_RecordType, ';
                    $sSQL .= '  lu_LastLine,  lu_CarrierRoute,  lu_ReturnCodes,  lu_ErrorCodes, ';
                    $sSQL .= '  lu_ErrorDesc) ';
                    $sSQL .= 'VALUES( ';
                    $sSQL .= " $lu_fam_ID, $lu_LookupDateTime, $lu_DeliveryLine1, ";
                    $sSQL .= " $lu_DeliveryLine2, $lu_City, $lu_State, $lu_ZipAddon, ";
                    $sSQL .= " $lu_Zip, $lu_Addon, $lu_LOTNumber, $lu_DPCCheckdigit, $lu_RecordType, ";
                    $sSQL .= " $lu_LastLine, $lu_CarrierRoute, $lu_ReturnCodes, $lu_ErrorCodes, ";
                    $sSQL .= " $lu_ErrorDesc) ";

                    //echo $sSQL . "<br>";

                    MiscUtils::RunQuery($sSQL);
                }

                if ($iSearchesLeft < 30) {
                    if ($lu_ErrorCodes != "'xx'") {
                    ?>
                        <h3>There are <?= $iSearchesLeft ?> searches remaining 
                            in your account.  Searches will be performed one at a time until 
                            your account balance is zero.  To enable bulk lookups you will 
                            need to add funds to your Intelligent Search Technology account 
                            at the following link.<br>
                            <a href="https://www.intelligentsearch.com/Hosted/User/">
                                https://www.intelligentsearch.com/Hosted/User/</a><br>
                        </h3>
                    <?php
                    } else {
                    ?>
                        <h4>Lookup failed.  There is a problem with the connection or with your account.</h4><br>
                        Please verify that your Intelligent Search Technology, Ltd. username and password 
                        are correct.<br><br>
                        Admin -> Edit General Settings -> sISTusername<br>
                        Admin -> Edit General Settings -> sISTpassword<br><br>
                        Follow the URL below to log in and manage your Intelligent Search Technology account 
                        settings.  If you do not already have an account you may establish an account at this 
                        URL. This software was written to work best with the service CorrectAddress(R) 
                        with Addons. <br><br><br>

                        <a href="https://www.intelligentsearch.com/Hosted/User/">https://www.intelligentsearch.com/Hosted/User/</a><br><br>

                        If you are sure that your account username and password are correct and that your 
                        account is in good standing it is possible that the server is currently unavailable 
                        but may be back online if you try again later.<br><br>
                    <?php    
                    }

                    if ($iSearchesLeft) {
                        ?>
                        <form method="GET" action="<?= $sRootPath ?>/v2/sytem/USISTAddress/Verification/DoLookup">
                            <input type=submit class=btn name=DoLookup value="Perform Next Lookup">
                        </form><br><br>
          <?php
                    }
                    $bNormalFinish = false;
                    break;
                }

                $now = time();    // This code used to prevent browser and server timeouts
      // Keep doing fresh reloads of this page until complete.
      if ($now - $startTime > 17) {  // run for 17 seconds, then reload page
        // total cycle is about 20 seconds per page reload
        ?><meta http-equiv="refresh" content="2;URL=USISTAddressVerification.php?DoLookup=Perform+Lookups" /><?php
        $bNormalFinish = false;
          break;
      }
            }
            if ($bNormalFinish) {
                ?><meta http-equiv="refresh" content="2;URL=USISTAddressVerification.php" /><?php
            }
        } ?>
  <table><tr>
  <?php
  if (!empty($DoLookup) && $iEligible) {
      ?>
        <td>
          <form method="GET" action="<?= $sRootPath ?>/v2/sytem/USISTAddress/Verification/DoLookup">
            <input type=submit class=btn name=DoLookup value="Perform Lookups">
          </form>
        </td>
  <?php
  } ?>

  <?php if ($iUSOkay) {
      ?>
        <td>
            <form method="POST" action="<?= $sRootPath ?>/Reports/USISTAddressReport.php">
                <input type=submit class=btn name=MismatchReport value="View Mismatch Report">
            </form>
        </td>
  <?php
  } ?>

  <?php if ($iNonUSCount) {
      ?>
        <td>
            <form method="POST" action="<?= $sRootPath ?>/Reports/USISTAddressReport.php">
                <input type=submit class=btn name=NonUSReport value="View Non-US Address Report">
            </form>
        </td>
  <?php
  } ?>

    </tr></table>

  <?php
    }
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
