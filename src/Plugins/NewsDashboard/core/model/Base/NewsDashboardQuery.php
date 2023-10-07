<?php

namespace PluginStore\Base;

use \Exception;
use \PDO;
use PluginStore\NewsDashboard as ChildNewsDashboard;
use PluginStore\NewsDashboardQuery as ChildNewsDashboardQuery;
use PluginStore\Map\NewsDashboardTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the `news_nw` table.
 *
 * @method     ChildNewsDashboardQuery orderById($order = Criteria::ASC) Order by the news_nw_id column
 * @method     ChildNewsDashboardQuery orderByUserId($order = Criteria::ASC) Order by the news_nw_user_id column
 * @method     ChildNewsDashboardQuery orderByTitle($order = Criteria::ASC) Order by the news_nw_title column
 * @method     ChildNewsDashboardQuery orderByText($order = Criteria::ASC) Order by the news_nw_Text column
 * @method     ChildNewsDashboardQuery orderByType($order = Criteria::ASC) Order by the news_nw_type column
 * @method     ChildNewsDashboardQuery orderByDateentered($order = Criteria::ASC) Order by the news_nw_DateEntered column
 * @method     ChildNewsDashboardQuery orderByDatelastedited($order = Criteria::ASC) Order by the news_nw_DateLastEdited column
 *
 * @method     ChildNewsDashboardQuery groupById() Group by the news_nw_id column
 * @method     ChildNewsDashboardQuery groupByUserId() Group by the news_nw_user_id column
 * @method     ChildNewsDashboardQuery groupByTitle() Group by the news_nw_title column
 * @method     ChildNewsDashboardQuery groupByText() Group by the news_nw_Text column
 * @method     ChildNewsDashboardQuery groupByType() Group by the news_nw_type column
 * @method     ChildNewsDashboardQuery groupByDateentered() Group by the news_nw_DateEntered column
 * @method     ChildNewsDashboardQuery groupByDatelastedited() Group by the news_nw_DateLastEdited column
 *
 * @method     ChildNewsDashboardQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildNewsDashboardQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildNewsDashboardQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildNewsDashboardQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildNewsDashboardQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildNewsDashboardQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildNewsDashboard|null findOne(?ConnectionInterface $con = null) Return the first ChildNewsDashboard matching the query
 * @method     ChildNewsDashboard findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildNewsDashboard matching the query, or a new ChildNewsDashboard object populated from the query conditions when no match is found
 *
 * @method     ChildNewsDashboard|null findOneById(int $news_nw_id) Return the first ChildNewsDashboard filtered by the news_nw_id column
 * @method     ChildNewsDashboard|null findOneByUserId(int $news_nw_user_id) Return the first ChildNewsDashboard filtered by the news_nw_user_id column
 * @method     ChildNewsDashboard|null findOneByTitle(string $news_nw_title) Return the first ChildNewsDashboard filtered by the news_nw_title column
 * @method     ChildNewsDashboard|null findOneByText(string $news_nw_Text) Return the first ChildNewsDashboard filtered by the news_nw_Text column
 * @method     ChildNewsDashboard|null findOneByType(string $news_nw_type) Return the first ChildNewsDashboard filtered by the news_nw_type column
 * @method     ChildNewsDashboard|null findOneByDateentered(string $news_nw_DateEntered) Return the first ChildNewsDashboard filtered by the news_nw_DateEntered column
 * @method     ChildNewsDashboard|null findOneByDatelastedited(string $news_nw_DateLastEdited) Return the first ChildNewsDashboard filtered by the news_nw_DateLastEdited column
 *
 * @method     ChildNewsDashboard requirePk($key, ?ConnectionInterface $con = null) Return the ChildNewsDashboard by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNewsDashboard requireOne(?ConnectionInterface $con = null) Return the first ChildNewsDashboard matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildNewsDashboard requireOneById(int $news_nw_id) Return the first ChildNewsDashboard filtered by the news_nw_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNewsDashboard requireOneByUserId(int $news_nw_user_id) Return the first ChildNewsDashboard filtered by the news_nw_user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNewsDashboard requireOneByTitle(string $news_nw_title) Return the first ChildNewsDashboard filtered by the news_nw_title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNewsDashboard requireOneByText(string $news_nw_Text) Return the first ChildNewsDashboard filtered by the news_nw_Text column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNewsDashboard requireOneByType(string $news_nw_type) Return the first ChildNewsDashboard filtered by the news_nw_type column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNewsDashboard requireOneByDateentered(string $news_nw_DateEntered) Return the first ChildNewsDashboard filtered by the news_nw_DateEntered column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildNewsDashboard requireOneByDatelastedited(string $news_nw_DateLastEdited) Return the first ChildNewsDashboard filtered by the news_nw_DateLastEdited column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildNewsDashboard[]|Collection find(?ConnectionInterface $con = null) Return ChildNewsDashboard objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> find(?ConnectionInterface $con = null) Return ChildNewsDashboard objects based on current ModelCriteria
 *
 * @method     ChildNewsDashboard[]|Collection findById(int|array<int> $news_nw_id) Return ChildNewsDashboard objects filtered by the news_nw_id column
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> findById(int|array<int> $news_nw_id) Return ChildNewsDashboard objects filtered by the news_nw_id column
 * @method     ChildNewsDashboard[]|Collection findByUserId(int|array<int> $news_nw_user_id) Return ChildNewsDashboard objects filtered by the news_nw_user_id column
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> findByUserId(int|array<int> $news_nw_user_id) Return ChildNewsDashboard objects filtered by the news_nw_user_id column
 * @method     ChildNewsDashboard[]|Collection findByTitle(string|array<string> $news_nw_title) Return ChildNewsDashboard objects filtered by the news_nw_title column
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> findByTitle(string|array<string> $news_nw_title) Return ChildNewsDashboard objects filtered by the news_nw_title column
 * @method     ChildNewsDashboard[]|Collection findByText(string|array<string> $news_nw_Text) Return ChildNewsDashboard objects filtered by the news_nw_Text column
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> findByText(string|array<string> $news_nw_Text) Return ChildNewsDashboard objects filtered by the news_nw_Text column
 * @method     ChildNewsDashboard[]|Collection findByType(string|array<string> $news_nw_type) Return ChildNewsDashboard objects filtered by the news_nw_type column
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> findByType(string|array<string> $news_nw_type) Return ChildNewsDashboard objects filtered by the news_nw_type column
 * @method     ChildNewsDashboard[]|Collection findByDateentered(string|array<string> $news_nw_DateEntered) Return ChildNewsDashboard objects filtered by the news_nw_DateEntered column
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> findByDateentered(string|array<string> $news_nw_DateEntered) Return ChildNewsDashboard objects filtered by the news_nw_DateEntered column
 * @method     ChildNewsDashboard[]|Collection findByDatelastedited(string|array<string> $news_nw_DateLastEdited) Return ChildNewsDashboard objects filtered by the news_nw_DateLastEdited column
 * @psalm-method Collection&\Traversable<ChildNewsDashboard> findByDatelastedited(string|array<string> $news_nw_DateLastEdited) Return ChildNewsDashboard objects filtered by the news_nw_DateLastEdited column
 *
 * @method     ChildNewsDashboard[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildNewsDashboard> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class NewsDashboardQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \PluginStore\Base\NewsDashboardQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'pluginstore', $modelName = '\\PluginStore\\NewsDashboard', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildNewsDashboardQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildNewsDashboardQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildNewsDashboardQuery) {
            return $criteria;
        }
        $query = new ChildNewsDashboardQuery();
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
     * @return ChildNewsDashboard|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(NewsDashboardTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = NewsDashboardTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildNewsDashboard A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT news_nw_id, news_nw_user_id, news_nw_title, news_nw_Text, news_nw_type, news_nw_DateEntered, news_nw_DateLastEdited FROM news_nw WHERE news_nw_id = :p0';
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
            /** @var ChildNewsDashboard $obj */
            $obj = new ChildNewsDashboard();
            $obj->hydrate($row);
            NewsDashboardTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @return ChildNewsDashboard|array|mixed the result, formatted by the current formatter
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
     * @param array $keys Primary keys to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return Collection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ?ConnectionInterface $con = null)
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
     * @param mixed $key Primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_ID, $key, Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array|int $keys The list of primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the news_nw_id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE news_nw_id = 1234
     * $query->filterById(array(12, 34)); // WHERE news_nw_id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE news_nw_id > 12
     * </code>
     *
     * @param mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterById($id = null, ?string $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the news_nw_user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE news_nw_user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE news_nw_user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE news_nw_user_id > 12
     * </code>
     *
     * @param mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByUserId($userId = null, ?string $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_USER_ID, $userId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the news_nw_title column
     *
     * Example usage:
     * <code>
     * $query->filterByTitle('fooValue');   // WHERE news_nw_title = 'fooValue'
     * $query->filterByTitle('%fooValue%', Criteria::LIKE); // WHERE news_nw_title LIKE '%fooValue%'
     * $query->filterByTitle(['foo', 'bar']); // WHERE news_nw_title IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $title The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTitle($title = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($title)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the news_nw_Text column
     *
     * Example usage:
     * <code>
     * $query->filterByText('fooValue');   // WHERE news_nw_Text = 'fooValue'
     * $query->filterByText('%fooValue%', Criteria::LIKE); // WHERE news_nw_Text LIKE '%fooValue%'
     * $query->filterByText(['foo', 'bar']); // WHERE news_nw_Text IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $text The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByText($text = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($text)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_TEXT, $text, $comparison);

        return $this;
    }

    /**
     * Filter the query on the news_nw_type column
     *
     * Example usage:
     * <code>
     * $query->filterByType('fooValue');   // WHERE news_nw_type = 'fooValue'
     * $query->filterByType('%fooValue%', Criteria::LIKE); // WHERE news_nw_type LIKE '%fooValue%'
     * $query->filterByType(['foo', 'bar']); // WHERE news_nw_type IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $type The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByType($type = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($type)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_TYPE, $type, $comparison);

        return $this;
    }

    /**
     * Filter the query on the news_nw_DateEntered column
     *
     * Example usage:
     * <code>
     * $query->filterByDateentered('2011-03-14'); // WHERE news_nw_DateEntered = '2011-03-14'
     * $query->filterByDateentered('now'); // WHERE news_nw_DateEntered = '2011-03-14'
     * $query->filterByDateentered(array('max' => 'yesterday')); // WHERE news_nw_DateEntered > '2011-03-13'
     * </code>
     *
     * @param mixed $dateentered The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByDateentered($dateentered = null, ?string $comparison = null)
    {
        if (is_array($dateentered)) {
            $useMinMax = false;
            if (isset($dateentered['min'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED, $dateentered['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateentered['max'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED, $dateentered['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_DATEENTERED, $dateentered, $comparison);

        return $this;
    }

    /**
     * Filter the query on the news_nw_DateLastEdited column
     *
     * Example usage:
     * <code>
     * $query->filterByDatelastedited('2011-03-14'); // WHERE news_nw_DateLastEdited = '2011-03-14'
     * $query->filterByDatelastedited('now'); // WHERE news_nw_DateLastEdited = '2011-03-14'
     * $query->filterByDatelastedited(array('max' => 'yesterday')); // WHERE news_nw_DateLastEdited > '2011-03-13'
     * </code>
     *
     * @param mixed $datelastedited The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByDatelastedited($datelastedited = null, ?string $comparison = null)
    {
        if (is_array($datelastedited)) {
            $useMinMax = false;
            if (isset($datelastedited['min'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED, $datelastedited['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($datelastedited['max'])) {
                $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED, $datelastedited['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_DATELASTEDITED, $datelastedited, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildNewsDashboard $newsDashboard Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($newsDashboard = null)
    {
        if ($newsDashboard) {
            $this->addUsingAlias(NewsDashboardTableMap::COL_NEWS_NW_ID, $newsDashboard->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the news_nw table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(NewsDashboardTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            NewsDashboardTableMap::clearInstancePool();
            NewsDashboardTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(NewsDashboardTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(NewsDashboardTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            NewsDashboardTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            NewsDashboardTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
