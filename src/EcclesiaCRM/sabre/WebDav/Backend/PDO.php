<?php

declare(strict_types=1);

namespace Sabre\WebDAV\Backend;

/**
 * PDO CalDAV backend.
 *
 * This backend is used to store calendar-data in a PDO database, such as
 * sqlite or MySQL
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class PDO extends AbstractBackend implements  SharingSupport
{
    /**
     * We need to specify a max date, because we need to stop *somewhere*.
     *
     * On 32 bit system the maximum for a signed integer is 2147483647, so
     * MAX_DATE cannot be higher than date('Y-m-d', 2147483647) which results
     * in 2038-01-19 to avoid problems when the date is converted
     * to a unix timestamp.
     */
    const MAX_DATE = '2038-01-01';

    /**
     * pdo.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The table name that will be used for calendars.
     *
     * @var string
     */
    public $collectionsTableName = 'collections';

    public $collectionsinstancesTableName = 'collectionsinstances';

    /**
     * Creates the backend.
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Updates the list of sharees.
     *
     * Every item must be a Sharee object.
     *
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees
     */
    public function updateInvites($mycol, array $sharees){}

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
    public function getInvites($mycol)
    {
        return [];
    }
}
