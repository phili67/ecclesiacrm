<?php
/***********************************************************************
 *
 * This source code can only be called through :
 * http://www.mydomain.com/v2/people/family/verify/274
 *
 ***********************************************************************/

use EcclesiaCRM\FamilyQuery;

$family =  FamilyQuery::create()
    ->findOneById($iFamilyID);

$family->verify();

header('Location: '.$family->getViewURI());
exit;
