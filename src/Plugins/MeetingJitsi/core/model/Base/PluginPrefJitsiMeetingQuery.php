<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\PluginPrefJitsiMeeting as ChildPluginPrefJitsiMeeting;
use PluginStore\PluginPrefJitsiMeetingQuery as ChildPluginPrefJitsiMeetingQuery;
use PluginStore\Map\PluginPrefJitsiMeetingTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'plugin_pref_jitsimeeting_pjmp' table.
 *
 *
 *
 * @method     ChildPluginPrefJitsiMeetingQuery orderById($order = Criteria::ASC) Order by the jm_pjmp_ID column
 * @method     ChildPluginPrefJitsiMeetingQuery orderByPersonId($order = Criteria::ASC) Order by the jm_pjmp_personmeeting_pm_id column
 * @method     ChildPluginPrefJitsiMeetingQuery orderByDomain($order = Criteria::ASC) Order by the jm_pjmp_domain column
 * @method     ChildPluginPrefJitsiMeetingQuery orderByDomainScriptPath($order = Criteria::ASC) Order by the jm_pjmp_domainscriptpath column
 *
 * @method     ChildPluginPrefJitsiMeetingQuery groupById() Group by the jm_pjmp_ID column
 * @method     ChildPluginPrefJitsiMeetingQuery groupByPersonId() Group by the jm_pjmp_personmeeting_pm_id column
 * @method     ChildPluginPrefJitsiMeetingQuery groupByDomain() Group by the jm_pjmp_domain column
 * @method     ChildPluginPrefJitsiMeetingQuery groupByDomainScriptPath() Group by the jm_pjmp_domainscriptpath column
 *
 * @method     ChildPluginPrefJitsiMeetingQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPluginPrefJitsiMeetingQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPluginPrefJitsiMeetingQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPluginPrefJitsiMeetingQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildPluginPrefJitsiMeetingQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildPluginPrefJitsiMeetingQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildPluginPrefJitsiMeeting|null findOne(ConnectionInterface $con = null) Return the first ChildPluginPrefJitsiMeeting matching the query
 * @method     ChildPluginPrefJitsiMeeting findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPluginPrefJitsiMeeting matching the query, or a new ChildPluginPrefJitsiMeeting object populated from the query conditions when no match is found
 *
 * @method     ChildPluginPrefJitsiMeeting|null findOneById(int $jm_pjmp_ID) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_ID column
 * @method     ChildPluginPrefJitsiMeeting|null findOneByPersonId(int $jm_pjmp_personmeeting_pm_id) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_personmeeting_pm_id column
 * @method     ChildPluginPrefJitsiMeeting|null findOneByDomain(string $jm_pjmp_domain) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_domain column
 * @method     ChildPluginPrefJitsiMeeting|null findOneByDomainScriptPath(string $jm_pjmp_domainscriptpath) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_domainscriptpath column *

 * @method     ChildPluginPrefJitsiMeeting requirePk($key, ConnectionInterface $con = null) Return the ChildPluginPrefJitsiMeeting by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPluginPrefJitsiMeeting requireOne(ConnectionInterface $con = null) Return the first ChildPluginPrefJitsiMeeting matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPluginPrefJitsiMeeting requireOneById(int $jm_pjmp_ID) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_ID column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPluginPrefJitsiMeeting requireOneByPersonId(int $jm_pjmp_personmeeting_pm_id) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_personmeeting_pm_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPluginPrefJitsiMeeting requireOneByDomain(string $jm_pjmp_domain) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_domain column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPluginPrefJitsiMeeting requireOneByDomainScriptPath(string $jm_pjmp_domainscriptpath) Return the first ChildPluginPrefJitsiMeeting filtered by the jm_pjmp_domainscriptpath column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPluginPrefJitsiMeeting[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildPluginPrefJitsiMeeting objects based on current ModelCriteria
 * @psalm-method ObjectCollection&\Traversable<ChildPluginPrefJitsiMeeting> find(ConnectionInterface $con = null) Return ChildPluginPrefJitsiMeeting objects based on current ModelCriteria
 * @method     ChildPluginPrefJitsiMeeting[]|ObjectCollection findById(int $jm_pjmp_ID) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_ID column
 * @psalm-method ObjectCollection&\Traversable<ChildPluginPrefJitsiMeeting> findById(int $jm_pjmp_ID) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_ID column
 * @method     ChildPluginPrefJitsiMeeting[]|ObjectCollection findByPersonId(int $jm_pjmp_personmeeting_pm_id) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_personmeeting_pm_id column
 * @psalm-method ObjectCollection&\Traversable<ChildPluginPrefJitsiMeeting> findByPersonId(int $jm_pjmp_personmeeting_pm_id) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_personmeeting_pm_id column
 * @method     ChildPluginPrefJitsiMeeting[]|ObjectCollection findByDomain(string $jm_pjmp_domain) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_domain column
 * @psalm-method ObjectCollection&\Traversable<ChildPluginPrefJitsiMeeting> findByDomain(string $jm_pjmp_domain) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_domain column
 * @method     ChildPluginPrefJitsiMeeting[]|ObjectCollection findByDomainScriptPath(string $jm_pjmp_domainscriptpath) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_domainscriptpath column
 * @psalm-method ObjectCollection&\Traversable<ChildPluginPrefJitsiMeeting> findByDomainScriptPath(string $jm_pjmp_domainscriptpath) Return ChildPluginPrefJitsiMeeting objects filtered by the jm_pjmp_domainscriptpath column
 * @method     ChildPluginPrefJitsiMeeting[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildPluginPrefJitsiMeeting> paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class PluginPrefJitsiMeetingQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\PluginPrefJitsiMeetingQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\PluginPrefJitsiMeeting', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPluginPrefJitsiMeetingQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPluginPrefJitsiMeetingQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildPluginPrefJitsiMeetingQuery) {
            return $criteria;
        }
        $query = new ChildPluginPrefJitsiMeetingQuery();
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
     * @return ChildPluginPrefJitsiMeeting|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PluginPrefJitsiMeetingTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = PluginPrefJitsiMeetingTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildPluginPrefJitsiMeeting A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT jm_pjmp_ID, jm_pjmp_personmeeting_pm_id, jm_pjmp_domain, jm_pjmp_domainscriptpath FROM plugin_pref_jitsimeeting_pjmp WHERE jm_pjmp_ID = :p0';
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
            /** @var ChildPluginPrefJitsiMeeting $obj */
            $obj = new ChildPluginPrefJitsiMeeting();
            $obj->hydrate($row);
            PluginPrefJitsiMeetingTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildPluginPrefJitsiMeeting|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildPluginPrefJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildPluginPrefJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the jm_pjmp_ID column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE jm_pjmp_ID = 1234
     * $query->filterById(array(12, 34)); // WHERE jm_pjmp_ID IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE jm_pjmp_ID > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPluginPrefJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, $id, $comparison);
    }

    /**
     * Filter the query on the jm_pjmp_personmeeting_pm_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPersonId(1234); // WHERE jm_pjmp_personmeeting_pm_id = 1234
     * $query->filterByPersonId(array(12, 34)); // WHERE jm_pjmp_personmeeting_pm_id IN (12, 34)
     * $query->filterByPersonId(array('min' => 12)); // WHERE jm_pjmp_personmeeting_pm_id > 12
     * </code>
     *
     * @param     mixed $personId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPluginPrefJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByPersonId($personId = null, $comparison = null)
    {
        if (is_array($personId)) {
            $useMinMax = false;
            if (isset($personId['min'])) {
                $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID, $personId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($personId['max'])) {
                $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID, $personId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_PERSONMEETING_PM_ID, $personId, $comparison);
    }

    /**
     * Filter the query on the jm_pjmp_domain column
     *
     * Example usage:
     * <code>
     * $query->filterByDomain('fooValue');   // WHERE jm_pjmp_domain = 'fooValue'
     * $query->filterByDomain('%fooValue%', Criteria::LIKE); // WHERE jm_pjmp_domain LIKE '%fooValue%'
     * $query->filterByDomain(['foo', 'bar']); // WHERE jm_pjmp_domain IN ('foo', 'bar')
     * </code>
     *
     * @param     string|string[] $domain The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPluginPrefJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByDomain($domain = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($domain)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAIN, $domain, $comparison);
    }

    /**
     * Filter the query on the jm_pjmp_domainscriptpath column
     *
     * Example usage:
     * <code>
     * $query->filterByDomainScriptPath('fooValue');   // WHERE jm_pjmp_domainscriptpath = 'fooValue'
     * $query->filterByDomainScriptPath('%fooValue%', Criteria::LIKE); // WHERE jm_pjmp_domainscriptpath LIKE '%fooValue%'
     * $query->filterByDomainScriptPath(['foo', 'bar']); // WHERE jm_pjmp_domainscriptpath IN ('foo', 'bar')
     * </code>
     *
     * @param     string|string[] $domainScriptPath The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPluginPrefJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByDomainScriptPath($domainScriptPath = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($domainScriptPath)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_DOMAINSCRIPTPATH, $domainScriptPath, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPluginPrefJitsiMeeting $pluginPrefJitsiMeeting Object to remove from the list of results
     *
     * @return $this|ChildPluginPrefJitsiMeetingQuery The current query, for fluid interface
     */
    public function prune($pluginPrefJitsiMeeting = null)
    {
        if ($pluginPrefJitsiMeeting) {
            $this->addUsingAlias(PluginPrefJitsiMeetingTableMap::COL_JM_PJMP_ID, $pluginPrefJitsiMeeting->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the plugin_pref_jitsimeeting_pjmp table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PluginPrefJitsiMeetingTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            PluginPrefJitsiMeetingTableMap::clearInstancePool();
            PluginPrefJitsiMeetingTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(PluginPrefJitsiMeetingTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PluginPrefJitsiMeetingTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            PluginPrefJitsiMeetingTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PluginPrefJitsiMeetingTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // PluginPrefJitsiMeetingQuery
