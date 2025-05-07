<?php

declare(strict_types=1);

namespace Sabre\DAVACL\FS;

use EcclesiaCRM\Auth\BasicAuth;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\MyPDO\WebDavPDO;
use Sabre\DAV\Sharing\ISharedNode;

/**
 * This is an ACL-enabled file node.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class MyFileSharing extends File implements ISharedNode
{
    protected $authBackend;

    /**
     * Owner uri, or null for no owner.
     *
     * @var PrincipalPDO
     */
    protected $principalBackend;

    protected $webDavBackend;

    /**
     * Constructor.
     *
     * @param string      $path  on-disk path
     * @param array       $acl   ACL rules
     * @param string|null $owner principal owner string
     */
    public function __construct(BasicAuth $authBackend, PrincipalPDO $principalBackend,WebDavPDO $webDavBackend,$path, array $acl, $owner = null)
    {
        parent::__construct($path, $acl, $owner);

        $this->authBackend = $authBackend;
        $this->principalBackend = $principalBackend;
        $this->webDavBackend = $webDavBackend;
    }

    // for sharing files directory

    /**
     * Returns the 'access level' for the instance of this shared resource.
     *
     * The value should be one of the Sabre\DAV\Sharing\Plugin::ACCESS_
     * constants.
     *
     * @return int
     */
    public function getShareAccess()
    {
        return $this->webDavBackend->getShareAccess($this);
    }

    /**
     * This function must return a URI that uniquely identifies the shared
     * resource. This URI should be identical across instances, and is
     * also used in several other XML bodies to connect invites to
     * resources.
     *
     * This may simply be a relative reference to the original shared instance,
     * but it could also be a urn. As long as it's a valid URI and unique.
     *
     * @return string
     */
    public function getShareResourceUri()
    {
        return $this->webDavBackend->getShareResourceUri($this);
    }

    /**
     * Updates the list of sharees.
     *
     * Every item must be a Sharee object.
     *
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees
     */
    public function updateInvites(array $sharees)
    {
        $this->webDavBackend->updateInvites($this, $sharees);
    }

    /**
     * Returns the list of people whom this resource is shared with.
     *
     * Every item in the returned array must be a Sharee object with
     * at least the following properties set:
     *
     * * $href
     * * $shareAccess
     * * $inviteStatus
     *
     * and optionally:
     *
     * * $properties
     *
     * @return \Sabre\DAV\Xml\Element\Sharee[]
     */
    public function getInvites()
    {
        return $this->webDavBackend->getInvites($this);
    }
}
