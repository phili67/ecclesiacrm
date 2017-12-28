<?php

namespace EcclesiaCRM\Base;

use \Exception;
use \PDO;
use EcclesiaCRM\KioskDevice as ChildKioskDevice;
use EcclesiaCRM\KioskDeviceQuery as ChildKioskDeviceQuery;
use EcclesiaCRM\Map\KioskDeviceTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'kioskdevice_kdev' table.
 *
 * This contains a list of all (un)registered kiosk devices
 *
 * @method     ChildKioskDeviceQuery orderById($order = Criteria::ASC) Order by the kdev_ID column
 * @method     ChildKioskDeviceQuery orderByGUIDHash($order = Criteria::ASC) Order by the kdev_GUIDHash column
 * @method     ChildKioskDeviceQuery orderByName($order = Criteria::ASC) Order by the kdev_Name column
 * @method     ChildKioskDeviceQuery orderByDeviceType($order = Criteria::ASC) Order by the kdev_deviceType column
 * @method     ChildKioskDeviceQuery orderByLastHeartbeat($order = Criteria::ASC) Order by the kdev_lastHeartbeat column
 * @method     ChildKioskDeviceQuery orderByAccepted($order = Criteria::ASC) Order by the kdev_Accepted column
 * @method     ChildKioskDeviceQuery orderByPendingCommands($order = Criteria::ASC) Order by the kdev_PendingCommands column
 *
 * @method     ChildKioskDeviceQuery groupById() Group by the kdev_ID column
 * @method     ChildKioskDeviceQuery groupByGUIDHash() Group by the kdev_GUIDHash column
 * @method     ChildKioskDeviceQuery groupByName() Group by the kdev_Name column
 * @method     ChildKioskDeviceQuery groupByDeviceType() Group by the kdev_deviceType column
 * @method     ChildKioskDeviceQuery groupByLastHeartbeat() Group by the kdev_lastHeartbeat column
 * @method     ChildKioskDeviceQuery groupByAccepted() Group by the kdev_Accepted column
 * @method     ChildKioskDeviceQuery groupByPendingCommands() Group by the kdev_PendingCommands column
 *
 * @method     ChildKioskDeviceQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildKioskDeviceQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildKioskDeviceQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildKioskDeviceQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildKioskDeviceQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildKioskDeviceQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildKioskDeviceQuery leftJoinKioskAssignment($relationAlias = null) Adds a LEFT JOIN clause to the query using the KioskAssignment relation
 * @method     ChildKioskDeviceQuery rightJoinKioskAssignment($relationAlias = null) Adds a RIGHT JOIN clause to the query using the KioskAssignment relation
 * @method     ChildKioskDeviceQuery innerJoinKioskAssignment($relationAlias = null) Adds a INNER JOIN clause to the query using the KioskAssignment relation
 *
 * @method     ChildKioskDeviceQuery joinWithKioskAssignment($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the KioskAssignment relation
 *
 * @method     ChildKioskDeviceQuery leftJoinWithKioskAssignment() Adds a LEFT JOIN clause and with to the query using the KioskAssignment relation
 * @method     ChildKioskDeviceQuery rightJoinWithKioskAssignment() Adds a RIGHT JOIN clause and with to the query using the KioskAssignment relation
 * @method     ChildKioskDeviceQuery innerJoinWithKioskAssignment() Adds a INNER JOIN clause and with to the query using the KioskAssignment relation
 *
 * @method     \EcclesiaCRM\KioskAssignmentQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildKioskDevice findOne(ConnectionInterface $con = null) Return the first ChildKioskDevice matching the query
 * @method     ChildKioskDevice findOneOrCreate(ConnectionInterface $con = null) Return the first ChildKioskDevice matching the query, or a new ChildKioskDevice object populated from the query conditions when no match is found
 *
 * @method     ChildKioskDevice findOneById(int $kdev_ID) Return the first ChildKioskDevice filtered by the kdev_ID column
 * @method     ChildKioskDevice findOneByGUIDHash(string $kdev_GUIDHash) Return the first ChildKioskDevice filtered by the kdev_GUIDHash column
 * @method     ChildKioskDevice findOneByName(string $kdev_Name) Return the first ChildKioskDevice filtered by the kdev_Name column
 * @method     ChildKioskDevice findOneByDeviceType(string $kdev_deviceType) Return the first ChildKioskDevice filtered by the kdev_deviceType column
 * @method     ChildKioskDevice findOneByLastHeartbeat(string $kdev_lastHeartbeat) Return the first ChildKioskDevice filtered by the kdev_lastHeartbeat column
 * @method     ChildKioskDevice findOneByAccepted(boolean $kdev_Accepted) Return the first ChildKioskDevice filtered by the kdev_Accepted column
 * @method     ChildKioskDevice findOneByPendingCommands(string $kdev_PendingCommands) Return the first ChildKioskDevice filtered by the kdev_PendingCommands column *

 * @method     ChildKioskDevice requirePk($key, ConnectionInterface $con = null) Return the ChildKioskDevice by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildKioskDevice requireOne(ConnectionInterface $con = null) Return the first ChildKioskDevice matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildKioskDevice requireOneById(int $kdev_ID) Return the first ChildKioskDevice filtered by the kdev_ID column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildKioskDevice requireOneByGUIDHash(string $kdev_GUIDHash) Return the first ChildKioskDevice filtered by the kdev_GUIDHash column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildKioskDevice requireOneByName(string $kdev_Name) Return the first ChildKioskDevice filtered by the kdev_Name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildKioskDevice requireOneByDeviceType(string $kdev_deviceType) Return the first ChildKioskDevice filtered by the kdev_deviceType column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildKioskDevice requireOneByLastHeartbeat(string $kdev_lastHeartbeat) Return the first ChildKioskDevice filtered by the kdev_lastHeartbeat column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildKioskDevice requireOneByAccepted(boolean $kdev_Accepted) Return the first ChildKioskDevice filtered by the kdev_Accepted column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildKioskDevice requireOneByPendingCommands(string $kdev_PendingCommands) Return the first ChildKioskDevice filtered by the kdev_PendingCommands column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildKioskDevice[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildKioskDevice objects based on current ModelCriteria
 * @method     ChildKioskDevice[]|ObjectCollection findById(int $kdev_ID) Return ChildKioskDevice objects filtered by the kdev_ID column
 * @method     ChildKioskDevice[]|ObjectCollection findByGUIDHash(string $kdev_GUIDHash) Return ChildKioskDevice objects filtered by the kdev_GUIDHash column
 * @method     ChildKioskDevice[]|ObjectCollection findByName(string $kdev_Name) Return ChildKioskDevice objects filtered by the kdev_Name column
 * @method     ChildKioskDevice[]|ObjectCollection findByDeviceType(string $kdev_deviceType) Return ChildKioskDevice objects filtered by the kdev_deviceType column
 * @method     ChildKioskDevice[]|ObjectCollection findByLastHeartbeat(string $kdev_lastHeartbeat) Return ChildKioskDevice objects filtered by the kdev_lastHeartbeat column
 * @method     ChildKioskDevice[]|ObjectCollection findByAccepted(boolean $kdev_Accepted) Return ChildKioskDevice objects filtered by the kdev_Accepted column
 * @method     ChildKioskDevice[]|ObjectCollection findByPendingCommands(string $kdev_PendingCommands) Return ChildKioskDevice objects filtered by the kdev_PendingCommands column
 * @method     ChildKioskDevice[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class KioskDeviceQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \EcclesiaCRM\Base\KioskDeviceQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\EcclesiaCRM\\KioskDevice', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildKioskDeviceQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildKioskDeviceQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildKioskDeviceQuery) {
            return $criteria;
        }
        $query = new ChildKioskDeviceQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildKioskDevice|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = KioskDeviceTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildKioskDevice A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT kdev_ID, kdev_GUIDHash, kdev_Name, kdev_deviceType, kdev_lastHeartbeat, kdev_Accepted, kdev_PendingCommands FROM kioskdevice_kdev WHERE kdev_ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildKioskDevice $obj */
            $obj = new ChildKioskDevice();
            $obj->hydrate($row);
            KioskDeviceTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildKioskDevice|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the kdev_ID column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE kdev_ID = 1234
     * $query->filterById(array(12, 34)); // WHERE kdev_ID IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE kdev_ID > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ID, $id, $comparison);
    }

    /**
     * Filter the query on the kdev_GUIDHash column
     *
     * Example usage:
     * <code>
     * $query->filterByGUIDHash('fooValue');   // WHERE kdev_GUIDHash = 'fooValue'
     * $query->filterByGUIDHash('%fooValue%', Criteria::LIKE); // WHERE kdev_GUIDHash LIKE '%fooValue%'
     * </code>
     *
     * @param     string $gUIDHash The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByGUIDHash($gUIDHash = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($gUIDHash)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_GUIDHASH, $gUIDHash, $comparison);
    }

    /**
     * Filter the query on the kdev_Name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE kdev_Name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE kdev_Name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the kdev_deviceType column
     *
     * Example usage:
     * <code>
     * $query->filterByDeviceType('fooValue');   // WHERE kdev_deviceType = 'fooValue'
     * $query->filterByDeviceType('%fooValue%', Criteria::LIKE); // WHERE kdev_deviceType LIKE '%fooValue%'
     * </code>
     *
     * @param     string $deviceType The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByDeviceType($deviceType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($deviceType)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_DEVICETYPE, $deviceType, $comparison);
    }

    /**
     * Filter the query on the kdev_lastHeartbeat column
     *
     * Example usage:
     * <code>
     * $query->filterByLastHeartbeat('fooValue');   // WHERE kdev_lastHeartbeat = 'fooValue'
     * $query->filterByLastHeartbeat('%fooValue%', Criteria::LIKE); // WHERE kdev_lastHeartbeat LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lastHeartbeat The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByLastHeartbeat($lastHeartbeat = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastHeartbeat)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT, $lastHeartbeat, $comparison);
    }

    /**
     * Filter the query on the kdev_Accepted column
     *
     * Example usage:
     * <code>
     * $query->filterByAccepted(true); // WHERE kdev_Accepted = true
     * $query->filterByAccepted('yes'); // WHERE kdev_Accepted = true
     * </code>
     *
     * @param     boolean|string $accepted The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByAccepted($accepted = null, $comparison = null)
    {
        if (is_string($accepted)) {
            $accepted = in_array(strtolower($accepted), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ACCEPTED, $accepted, $comparison);
    }

    /**
     * Filter the query on the kdev_PendingCommands column
     *
     * Example usage:
     * <code>
     * $query->filterByPendingCommands('fooValue');   // WHERE kdev_PendingCommands = 'fooValue'
     * $query->filterByPendingCommands('%fooValue%', Criteria::LIKE); // WHERE kdev_PendingCommands LIKE '%fooValue%'
     * </code>
     *
     * @param     string $pendingCommands The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByPendingCommands($pendingCommands = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($pendingCommands)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS, $pendingCommands, $comparison);
    }

    /**
     * Filter the query by a related \EcclesiaCRM\KioskAssignment object
     *
     * @param \EcclesiaCRM\KioskAssignment|ObjectCollection $kioskAssignment the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function filterByKioskAssignment($kioskAssignment, $comparison = null)
    {
        if ($kioskAssignment instanceof \EcclesiaCRM\KioskAssignment) {
            return $this
                ->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ID, $kioskAssignment->getKioskId(), $comparison);
        } elseif ($kioskAssignment instanceof ObjectCollection) {
            return $this
                ->useKioskAssignmentQuery()
                ->filterByPrimaryKeys($kioskAssignment->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByKioskAssignment() only accepts arguments of type \EcclesiaCRM\KioskAssignment or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the KioskAssignment relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function joinKioskAssignment($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('KioskAssignment');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'KioskAssignment');
        }

        return $this;
    }

    /**
     * Use the KioskAssignment relation KioskAssignment object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \EcclesiaCRM\KioskAssignmentQuery A secondary query class using the current class as primary query
     */
    public function useKioskAssignmentQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinKioskAssignment($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'KioskAssignment', '\EcclesiaCRM\KioskAssignmentQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildKioskDevice $kioskDevice Object to remove from the list of results
     *
     * @return $this|ChildKioskDeviceQuery The current query, for fluid interface
     */
    public function prune($kioskDevice = null)
    {
        if ($kioskDevice) {
            $this->addUsingAlias(KioskDeviceTableMap::COL_KDEV_ID, $kioskDevice->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the kioskdevice_kdev table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            KioskDeviceTableMap::clearInstancePool();
            KioskDeviceTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(KioskDeviceTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            KioskDeviceTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            KioskDeviceTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // KioskDeviceQuery
