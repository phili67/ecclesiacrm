<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\PersonJitsiMeeting as ChildPersonJitsiMeeting;
use PluginStore\PersonJitsiMeetingQuery as ChildPersonJitsiMeetingQuery;
use PluginStore\Map\PersonJitsiMeetingTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'personjitsimeeting_pm' table.
 *
 *
 *
 * @method     ChildPersonJitsiMeetingQuery orderById($order = Criteria::ASC) Order by the jm_pm_ID column
 * @method     ChildPersonJitsiMeetingQuery orderByPersonId($order = Criteria::ASC) Order by the jm_pm_person_id column
 * @method     ChildPersonJitsiMeetingQuery orderByCode($order = Criteria::ASC) Order by the jm_pm_code column
 * @method     ChildPersonJitsiMeetingQuery orderByCreationDate($order = Criteria::ASC) Order by the jm_pm_cr_date column
 *
 * @method     ChildPersonJitsiMeetingQuery groupById() Group by the jm_pm_ID column
 * @method     ChildPersonJitsiMeetingQuery groupByPersonId() Group by the jm_pm_person_id column
 * @method     ChildPersonJitsiMeetingQuery groupByCode() Group by the jm_pm_code column
 * @method     ChildPersonJitsiMeetingQuery groupByCreationDate() Group by the jm_pm_cr_date column
 *
 * @method     ChildPersonJitsiMeetingQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPersonJitsiMeetingQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPersonJitsiMeetingQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPersonJitsiMeetingQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildPersonJitsiMeetingQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildPersonJitsiMeetingQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildPersonJitsiMeeting|null findOne(ConnectionInterface $con = null) Return the first ChildPersonJitsiMeeting matching the query
 * @method     ChildPersonJitsiMeeting findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPersonJitsiMeeting matching the query, or a new ChildPersonJitsiMeeting object populated from the query conditions when no match is found
 *
 * @method     ChildPersonJitsiMeeting|null findOneById(int $jm_pm_ID) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_ID column
 * @method     ChildPersonJitsiMeeting|null findOneByPersonId(int $jm_pm_person_id) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_person_id column
 * @method     ChildPersonJitsiMeeting|null findOneByCode(string $jm_pm_code) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_code column
 * @method     ChildPersonJitsiMeeting|null findOneByCreationDate(string $jm_pm_cr_date) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_cr_date column *

 * @method     ChildPersonJitsiMeeting requirePk($key, ConnectionInterface $con = null) Return the ChildPersonJitsiMeeting by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonJitsiMeeting requireOne(ConnectionInterface $con = null) Return the first ChildPersonJitsiMeeting matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPersonJitsiMeeting requireOneById(int $jm_pm_ID) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_ID column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonJitsiMeeting requireOneByPersonId(int $jm_pm_person_id) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_person_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonJitsiMeeting requireOneByCode(string $jm_pm_code) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_code column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPersonJitsiMeeting requireOneByCreationDate(string $jm_pm_cr_date) Return the first ChildPersonJitsiMeeting filtered by the jm_pm_cr_date column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPersonJitsiMeeting[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildPersonJitsiMeeting objects based on current ModelCriteria
 * @psalm-method ObjectCollection&\Traversable<ChildPersonJitsiMeeting> find(ConnectionInterface $con = null) Return ChildPersonJitsiMeeting objects based on current ModelCriteria
 * @method     ChildPersonJitsiMeeting[]|ObjectCollection findById(int $jm_pm_ID) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_ID column
 * @psalm-method ObjectCollection&\Traversable<ChildPersonJitsiMeeting> findById(int $jm_pm_ID) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_ID column
 * @method     ChildPersonJitsiMeeting[]|ObjectCollection findByPersonId(int $jm_pm_person_id) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_person_id column
 * @psalm-method ObjectCollection&\Traversable<ChildPersonJitsiMeeting> findByPersonId(int $jm_pm_person_id) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_person_id column
 * @method     ChildPersonJitsiMeeting[]|ObjectCollection findByCode(string $jm_pm_code) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_code column
 * @psalm-method ObjectCollection&\Traversable<ChildPersonJitsiMeeting> findByCode(string $jm_pm_code) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_code column
 * @method     ChildPersonJitsiMeeting[]|ObjectCollection findByCreationDate(string $jm_pm_cr_date) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_cr_date column
 * @psalm-method ObjectCollection&\Traversable<ChildPersonJitsiMeeting> findByCreationDate(string $jm_pm_cr_date) Return ChildPersonJitsiMeeting objects filtered by the jm_pm_cr_date column
 * @method     ChildPersonJitsiMeeting[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildPersonJitsiMeeting> paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class PersonJitsiMeetingQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\PersonJitsiMeetingQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\PersonJitsiMeeting', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPersonJitsiMeetingQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPersonJitsiMeetingQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildPersonJitsiMeetingQuery) {
            return $criteria;
        }
        $query = new ChildPersonJitsiMeetingQuery();
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
     * @return ChildPersonJitsiMeeting|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PersonJitsiMeetingTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = PersonJitsiMeetingTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildPersonJitsiMeeting A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT jm_pm_ID, jm_pm_person_id, jm_pm_code, jm_pm_cr_date FROM personjitsimeeting_pm WHERE jm_pm_ID = :p0';
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
            /** @var ChildPersonJitsiMeeting $obj */
            $obj = new ChildPersonJitsiMeeting();
            $obj->hydrate($row);
            PersonJitsiMeetingTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildPersonJitsiMeeting|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildPersonJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildPersonJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the jm_pm_ID column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE jm_pm_ID = 1234
     * $query->filterById(array(12, 34)); // WHERE jm_pm_ID IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE jm_pm_ID > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_ID, $id, $comparison);
    }

    /**
     * Filter the query on the jm_pm_person_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPersonId(1234); // WHERE jm_pm_person_id = 1234
     * $query->filterByPersonId(array(12, 34)); // WHERE jm_pm_person_id IN (12, 34)
     * $query->filterByPersonId(array('min' => 12)); // WHERE jm_pm_person_id > 12
     * </code>
     *
     * @param     mixed $personId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByPersonId($personId = null, $comparison = null)
    {
        if (is_array($personId)) {
            $useMinMax = false;
            if (isset($personId['min'])) {
                $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID, $personId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($personId['max'])) {
                $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID, $personId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_PERSON_ID, $personId, $comparison);
    }

    /**
     * Filter the query on the jm_pm_code column
     *
     * Example usage:
     * <code>
     * $query->filterByCode('fooValue');   // WHERE jm_pm_code = 'fooValue'
     * $query->filterByCode('%fooValue%', Criteria::LIKE); // WHERE jm_pm_code LIKE '%fooValue%'
     * $query->filterByCode(['foo', 'bar']); // WHERE jm_pm_code IN ('foo', 'bar')
     * </code>
     *
     * @param     string|string[] $code The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByCode($code = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($code)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_CODE, $code, $comparison);
    }

    /**
     * Filter the query on the jm_pm_cr_date column
     *
     * Example usage:
     * <code>
     * $query->filterByCreationDate('2011-03-14'); // WHERE jm_pm_cr_date = '2011-03-14'
     * $query->filterByCreationDate('now'); // WHERE jm_pm_cr_date = '2011-03-14'
     * $query->filterByCreationDate(array('max' => 'yesterday')); // WHERE jm_pm_cr_date > '2011-03-13'
     * </code>
     *
     * @param     mixed $creationDate The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPersonJitsiMeetingQuery The current query, for fluid interface
     */
    public function filterByCreationDate($creationDate = null, $comparison = null)
    {
        if (is_array($creationDate)) {
            $useMinMax = false;
            if (isset($creationDate['min'])) {
                $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE, $creationDate['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($creationDate['max'])) {
                $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE, $creationDate['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_CR_DATE, $creationDate, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPersonJitsiMeeting $personJitsiMeeting Object to remove from the list of results
     *
     * @return $this|ChildPersonJitsiMeetingQuery The current query, for fluid interface
     */
    public function prune($personJitsiMeeting = null)
    {
        if ($personJitsiMeeting) {
            $this->addUsingAlias(PersonJitsiMeetingTableMap::COL_JM_PM_ID, $personJitsiMeeting->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the personjitsimeeting_pm table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PersonJitsiMeetingTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            PersonJitsiMeetingTableMap::clearInstancePool();
            PersonJitsiMeetingTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(PersonJitsiMeetingTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PersonJitsiMeetingTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            PersonJitsiMeetingTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PersonJitsiMeetingTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // PersonJitsiMeetingQuery
