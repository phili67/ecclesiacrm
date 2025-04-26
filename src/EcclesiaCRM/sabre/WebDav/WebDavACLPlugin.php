<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated     : 2018/05/13
//

namespace EcclesiaCRM\WebDav;

use Sabre\DAV;
use Sabre\DAVACL\Plugin;
use Sabre\DAVACL\IACL;

use EcclesiaCRM\Bootstrapper;

use EcclesiaCRM\UserQuery;

class WebDavACLPlugin extends Plugin {
    /**
     * Returns a list of privileges the current user has
     * on a particular node.
     *
     * Either a uri or a DAV\INode may be passed.
     *
     * null will be returned if the node doesn't support ACLs.
     *
     * @param string|DAV\INode $node
     *
     * @return array
     */
    /**
     * Returns a list of privileges the current user has
     * on a particular node.
     *
     * Either a uri or a DAV\INode may be passed.
     *
     * null will be returned if the node doesn't support ACLs.
     *
     * @param string|DAV\INode $node
     *
     * @return array
     */
    public function getCurrentUserPrivilegeSet($node)
    {
        if (is_string($node)) {
            $node = $this->server->tree->getNodeForPath($node);
        }

        $acl = $this->getACL($node);

        $collected = [];

        $isAuthenticated = null !== $this->getCurrentUserPrincipal();

        foreach ($acl as $ace) {
            $principal = $ace['principal'];

            switch ($principal) {
                case '{DAV:}owner':
                    $owner = $node->getOwner();
                    if ($owner && $this->principalMatchesPrincipal($owner)) {
                        $collected[] = $ace;
                    }
                    break;

                // 'all' matches for every user
                case '{DAV:}all':
                    $collected[] = $ace;
                    break;

                case '{DAV:}authenticated':
                    // Authenticated users only
                    if ($isAuthenticated) {
                        $collected[] = $ace;
                    }
                    break;

                case '{DAV:}unauthenticated':
                    // Unauthenticated users only
                    if (!$isAuthenticated) {
                        $collected[] = $ace;
                    }
                    break;

                default:
                    if ($this->principalMatchesPrincipal($ace['principal'])) {
                        $collected[] = $ace;
                    }
                    break;
            }
        }

        // Now we deduct all aggregated privileges.
        $flat = $this->getFlatPrivilegeSet($node);

        $collected2 = [];
        while (count($collected)) {
            $current = array_pop($collected);
            $collected2[] = $current['privilege'];

            if (!isset($flat[$current['privilege']])) {
                // Ignoring privileges that are not in the supported-privileges list.
                $this->server->getLogger()->debug('A node has the "'.$current['privilege'].'" in its ACL list, but this privilege was not reported in the supportedPrivilegeSet list. This will be ignored.');
                continue;
            }
            foreach ($flat[$current['privilege']]['aggregates'] as $subPriv) {
                $collected2[] = $subPriv;
                $collected[] = $flat[$subPriv];
            }
        }

        return array_values(array_unique($collected2));
    }

    /**
     * Returns the full ACL list.
     *
     * Either a uri or a INode may be passed.
     *
     * null will be returned if the node doesn't support ACLs.
     *
     * @param string|DAV\INode $node
     *
     * @return array
     */
    public function getAcl($node)
    {
        if (is_string($node)) {
            $node = $this->server->tree->getNodeForPath($node);
        }
        if (!$node instanceof IACL) {
            return $this->getDefaultAcl();
        }
        $acl = $node->getACL();
        foreach ($this->adminPrincipals as $adminPrincipal) {
            $acl[] = [
                'principal' => $adminPrincipal,
                'privilege' => '{DAV:}all',
                'protected' => true,
            ];
        }

        return $acl;
    }
}
