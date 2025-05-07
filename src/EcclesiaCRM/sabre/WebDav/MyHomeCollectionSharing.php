<?php

declare(strict_types=1);

namespace Sabre\DAVACL\FS;

use EcclesiaCRM\MyPDO\WebDavPDO;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\FSExt\Directory as BaseCollection;
use Sabre\DAVACL\ACLTrait;
use Sabre\DAVACL\IACL;
use Sabre\DAV\Sharing\ISharedNode;

use EcclesiaCRM\Auth\BasicAuth;

use EcclesiaCRM\MyPDO\PrincipalPDO;

/**
 * This is an ACL-enabled collection.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class MyHomeCollectionSharing extends BaseCollection implements IACL, ISharedNode
{
    use ACLTrait;

    /**
     * A list of ACL rules.
     *
     * @var array
     */
    protected $acl;

    /**
     * Owner uri, or null for no owner.
     *
     * @var string|null
     */
    protected $owner;

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
     * @param PrincipalPDO   $principalBackend
     * @param string         $path  on-disk path
     * @param array          $acl   ACL rules
     * @param string|null    $owner principal owner string
     */
    public function __construct(BasicAuth $authBackend, PrincipalPDO $principalBackend,WebDavPDO $webDavBackend, $path, array $acl, $owner = null)
    {
        parent::__construct($path);
        $this->acl = $acl;
        $this->owner = $owner;

        $this->authBackend = $authBackend;
        $this->principalBackend = $principalBackend;
        $this->webDavBackend = $webDavBackend;
    }

    /**
     * Returns a specific child node, referenced by its name.
     *
     * This method must throw Sabre\DAV\Exception\NotFound if the node does not
     * exist.
     *
     * @param string $name
     *
     * @throws NotFound
     *
     * @return \Sabre\DAV\INode
     */
    public function getChild($name)
    {
        $path = $this->path.'/'.$name;

        if (!file_exists($path)) {
            throw new NotFound('File could not be located');
        }
        if ('.' == $name || '..' == $name) {
            throw new Forbidden('Permission denied to . and ..');
        }
        if (is_dir($path)) {
            return new self($this->authBackend, $this->principalBackend, $this->webDavBackend, $path, $this->acl, $this->owner);
        } else {
            return new MyFileSharing($this->authBackend, $this->principalBackend, $this->webDavBackend, $path, $this->acl, $this->owner);
        }
    }

    /**
     * Returns the owner principal.
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Returns a list of ACE's for this node.
     *
     * Each ACE has the following properties:
     *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
     *     currently the only supported privileges
     *   * 'principal', a url to the principal who owns the node
     *   * 'protected' (optional), indicating that this ACE is not allowed to
     *      be updated.
     *
     * @return array
     */
    public function getACL()
    {
        return $this->acl;
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
