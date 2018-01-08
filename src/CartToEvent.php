<?php
/*******************************************************************************
 *
 *  filename    : CartToEvent.php
 *  last change : 2018-01-08
 *  description : Add cart records to an event
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2003 Phillip Hullquist, Deane Barker, Chris Gebhardt
 *  Copyright 2005 Todd Pillars
 *  Copyright 2012 Michael Wilt
 *            2018 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\EventQuery;

// Security: User must have Manage Groups & Roles permission
if (!$_SESSION['bManageGroups']) {
    Redirect('Menu.php');
    exit;
}

// Was the form submitted?
if (isset($_POST['Submit']) && count($_SESSION['aPeopleCart']) > 0 && isset($_POST['EventID'])) {

        // Get the PersonID
    $iEventID = InputUtils::LegacyFilterInput($_POST['EventID'], 'int');
    
    // Loop through the session array
    $iCount = 0;
    while ($element = each($_SESSION['aPeopleCart'])) {
        // Enter ID into event
        try {
            $eventAttent = new EventAttend();
        
            $eventAttent->setEventId($iEventID);
            $eventAttent->setPersonId($_SESSION['aPeopleCart'][$element['key']]);
            $eventAttent->save();
        } catch (\Exception $ex) {
           $errorMessage = $ex->getMessage();
        }
        
        $iCount++;
    }
    
    $sGlobalMessage = $iCount.' records(s) successfully added to selected Event.';

    Redirect('CartView.php?Action=EmptyCart&Message=aMessage&iCount='.$iCount.'&iEID='.$iEventID);
}

// Set the page title and include HTML header
$sPageTitle = gettext('Add Cart to Event');
require 'Include/Header.php';

if (count($_SESSION['aPeopleCart']) > 0) {
    $ormEvents = EventQuery::Create()->find();
    ?>
<div class="box">
<p align="center"><?= gettext('Select the event to which you would like to add your cart') ?>:</p>
<form name="CartToEvent" action="CartToEvent.php" method="POST">
<table align="center">
        <?php if ($sGlobalMessage) {
        ?>
        <tr>
          <td colspan="2"><?= $sGlobalMessage ?></td>
        </tr>
        <?php
    } ?>
        <tr>
                <td class="LabelColumn"><?= gettext('Select Event') ?>:</td>
                <td class="TextColumn">
                   <select name="EventID"  class="form-control">
                        <?php
                        // Create the group select drop-down
                        foreach ($ormEvents as $ormEvent) {
                          ?>
                          <option value="<?= $ormEvent->getId() ?>"><?= $ormEvent->getTitle() ?></option>
                          <?php
                          
                        }
                     ?>
                 </select>
           </td>
        </tr>
</table>
<p align="center">
<BR>
<input type="submit" name="Submit" value=<?= '"'.gettext('Add Cart to Event').'"' ?> class="btn btn-primary">
<BR><BR>--<?= gettext('OR') ?>--<BR><BR>
<a href="EventEditor.php" class="btn btn-info"><?= gettext('Add New Event') ?></a>
<BR><BR>
</p>
</form>
</div>
<?php
} else {
        echo '<p align="center" class="callout callout-warning">'.gettext('Your cart is empty!').'</p>';
    }

require 'Include/Footer.php';
?>
