<?php

namespace EcclesiaCRM\Base;

use \Exception;
use \PDO;
use EcclesiaCRM\KioskAssignment as ChildKioskAssignment;
use EcclesiaCRM\KioskAssignmentQuery as ChildKioskAssignmentQuery;
use EcclesiaCRM\KioskDevice as ChildKioskDevice;
use EcclesiaCRM\KioskDeviceQuery as ChildKioskDeviceQuery;
use EcclesiaCRM\Map\KioskAssignmentTableMap;
use EcclesiaCRM\Map\KioskDeviceTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;

/**
 * Base class that represents a row from the 'kioskdevice_kdev' table.
 *
 * This contains a list of all (un)registered kiosk devices
 *
 * @package    propel.generator.EcclesiaCRM.Base
 */
abstract class KioskDevice implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\EcclesiaCRM\\Map\\KioskDeviceTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the kdev_id field.
     *
     * @var        int
     */
    protected $kdev_id;

    /**
     * The value for the kdev_guidhash field.
     * SHA256 Hash of the GUID stored in the kiosk's cookie
     * @var        string
     */
    protected $kdev_guidhash;

    /**
     * The value for the kdev_name field.
     * Name of the kiosk
     * @var        string
     */
    protected $kdev_name;

    /**
     * The value for the kdev_devicetype field.
     * Kiosk device type
     * @var        string
     */
    protected $kdev_devicetype;

    /**
     * The value for the kdev_lastheartbeat field.
     * Last time the kiosk sent a heartbeat
     * @var        string
     */
    protected $kdev_lastheartbeat;

    /**
     * The value for the kdev_accepted field.
     * Has the admin accepted the kiosk after initial registration?
     * @var        boolean
     */
    protected $kdev_accepted;

    /**
     * The value for the kdev_pendingcommands field.
     * Commands waiting to be sent to the kiosk
     * @var        string
     */
    protected $kdev_pendingcommands;

    /**
     * @var        ObjectCollection|ChildKioskAssignment[] Collection to store aggregation of ChildKioskAssignment objects.
     */
    protected $collKioskAssignments;
    protected $collKioskAssignmentsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildKioskAssignment[]
     */
    protected $kioskAssignmentsScheduledForDeletion = null;

    /**
     * Initializes internal state of EcclesiaCRM\Base\KioskDevice object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>KioskDevice</code> instance.  If
     * <code>obj</code> is an instance of <code>KioskDevice</code>, delegates to
     * <code>equals(KioskDevice)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|KioskDevice The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [kdev_id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->kdev_id;
    }

    /**
     * Get the [kdev_guidhash] column value.
     * SHA256 Hash of the GUID stored in the kiosk's cookie
     * @return string
     */
    public function getGUIDHash()
    {
        return $this->kdev_guidhash;
    }

    /**
     * Get the [kdev_name] column value.
     * Name of the kiosk
     * @return string
     */
    public function getName()
    {
        return $this->kdev_name;
    }

    /**
     * Get the [kdev_devicetype] column value.
     * Kiosk device type
     * @return string
     */
    public function getDeviceType()
    {
        return $this->kdev_devicetype;
    }

    /**
     * Get the [kdev_lastheartbeat] column value.
     * Last time the kiosk sent a heartbeat
     * @return string
     */
    public function getLastHeartbeat()
    {
        return $this->kdev_lastheartbeat;
    }

    /**
     * Get the [kdev_accepted] column value.
     * Has the admin accepted the kiosk after initial registration?
     * @return boolean
     */
    public function getAccepted()
    {
        return $this->kdev_accepted;
    }

    /**
     * Get the [kdev_accepted] column value.
     * Has the admin accepted the kiosk after initial registration?
     * @return boolean
     */
    public function isAccepted()
    {
        return $this->getAccepted();
    }

    /**
     * Get the [kdev_pendingcommands] column value.
     * Commands waiting to be sent to the kiosk
     * @return string
     */
    public function getPendingCommands()
    {
        return $this->kdev_pendingcommands;
    }

    /**
     * Set the value of [kdev_id] column.
     *
     * @param int $v new value
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->kdev_id !== $v) {
            $this->kdev_id = $v;
            $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [kdev_guidhash] column.
     * SHA256 Hash of the GUID stored in the kiosk's cookie
     * @param string $v new value
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function setGUIDHash($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->kdev_guidhash !== $v) {
            $this->kdev_guidhash = $v;
            $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_GUIDHASH] = true;
        }

        return $this;
    } // setGUIDHash()

    /**
     * Set the value of [kdev_name] column.
     * Name of the kiosk
     * @param string $v new value
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->kdev_name !== $v) {
            $this->kdev_name = $v;
            $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_NAME] = true;
        }

        return $this;
    } // setName()

    /**
     * Set the value of [kdev_devicetype] column.
     * Kiosk device type
     * @param string $v new value
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function setDeviceType($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->kdev_devicetype !== $v) {
            $this->kdev_devicetype = $v;
            $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_DEVICETYPE] = true;
        }

        return $this;
    } // setDeviceType()

    /**
     * Set the value of [kdev_lastheartbeat] column.
     * Last time the kiosk sent a heartbeat
     * @param string $v new value
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function setLastHeartbeat($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->kdev_lastheartbeat !== $v) {
            $this->kdev_lastheartbeat = $v;
            $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT] = true;
        }

        return $this;
    } // setLastHeartbeat()

    /**
     * Sets the value of the [kdev_accepted] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Has the admin accepted the kiosk after initial registration?
     * @param  boolean|integer|string $v The new value
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function setAccepted($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->kdev_accepted !== $v) {
            $this->kdev_accepted = $v;
            $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_ACCEPTED] = true;
        }

        return $this;
    } // setAccepted()

    /**
     * Set the value of [kdev_pendingcommands] column.
     * Commands waiting to be sent to the kiosk
     * @param string $v new value
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function setPendingCommands($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->kdev_pendingcommands !== $v) {
            $this->kdev_pendingcommands = $v;
            $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS] = true;
        }

        return $this;
    } // setPendingCommands()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : KioskDeviceTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->kdev_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : KioskDeviceTableMap::translateFieldName('GUIDHash', TableMap::TYPE_PHPNAME, $indexType)];
            $this->kdev_guidhash = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : KioskDeviceTableMap::translateFieldName('Name', TableMap::TYPE_PHPNAME, $indexType)];
            $this->kdev_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : KioskDeviceTableMap::translateFieldName('DeviceType', TableMap::TYPE_PHPNAME, $indexType)];
            $this->kdev_devicetype = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : KioskDeviceTableMap::translateFieldName('LastHeartbeat', TableMap::TYPE_PHPNAME, $indexType)];
            $this->kdev_lastheartbeat = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : KioskDeviceTableMap::translateFieldName('Accepted', TableMap::TYPE_PHPNAME, $indexType)];
            $this->kdev_accepted = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : KioskDeviceTableMap::translateFieldName('PendingCommands', TableMap::TYPE_PHPNAME, $indexType)];
            $this->kdev_pendingcommands = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 7; // 7 = KioskDeviceTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\EcclesiaCRM\\KioskDevice'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildKioskDeviceQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collKioskAssignments = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see KioskDevice::setDeleted()
     * @see KioskDevice::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildKioskDeviceQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(KioskDeviceTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                KioskDeviceTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->kioskAssignmentsScheduledForDeletion !== null) {
                if (!$this->kioskAssignmentsScheduledForDeletion->isEmpty()) {
                    foreach ($this->kioskAssignmentsScheduledForDeletion as $kioskAssignment) {
                        // need to save related object because we set the relation to null
                        $kioskAssignment->save($con);
                    }
                    $this->kioskAssignmentsScheduledForDeletion = null;
                }
            }

            if ($this->collKioskAssignments !== null) {
                foreach ($this->collKioskAssignments as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[KioskDeviceTableMap::COL_KDEV_ID] = true;
        if (null !== $this->kdev_id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . KioskDeviceTableMap::COL_KDEV_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_ID)) {
            $modifiedColumns[':p' . $index++]  = 'kdev_ID';
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_GUIDHASH)) {
            $modifiedColumns[':p' . $index++]  = 'kdev_GUIDHash';
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'kdev_Name';
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_DEVICETYPE)) {
            $modifiedColumns[':p' . $index++]  = 'kdev_deviceType';
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT)) {
            $modifiedColumns[':p' . $index++]  = 'kdev_lastHeartbeat';
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_ACCEPTED)) {
            $modifiedColumns[':p' . $index++]  = 'kdev_Accepted';
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS)) {
            $modifiedColumns[':p' . $index++]  = 'kdev_PendingCommands';
        }

        $sql = sprintf(
            'INSERT INTO kioskdevice_kdev (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'kdev_ID':
                        $stmt->bindValue($identifier, $this->kdev_id, PDO::PARAM_INT);
                        break;
                    case 'kdev_GUIDHash':
                        $stmt->bindValue($identifier, $this->kdev_guidhash, PDO::PARAM_STR);
                        break;
                    case 'kdev_Name':
                        $stmt->bindValue($identifier, $this->kdev_name, PDO::PARAM_STR);
                        break;
                    case 'kdev_deviceType':
                        $stmt->bindValue($identifier, $this->kdev_devicetype, PDO::PARAM_STR);
                        break;
                    case 'kdev_lastHeartbeat':
                        $stmt->bindValue($identifier, $this->kdev_lastheartbeat, PDO::PARAM_STR);
                        break;
                    case 'kdev_Accepted':
                        $stmt->bindValue($identifier, (int) $this->kdev_accepted, PDO::PARAM_INT);
                        break;
                    case 'kdev_PendingCommands':
                        $stmt->bindValue($identifier, $this->kdev_pendingcommands, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = KioskDeviceTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getGUIDHash();
                break;
            case 2:
                return $this->getName();
                break;
            case 3:
                return $this->getDeviceType();
                break;
            case 4:
                return $this->getLastHeartbeat();
                break;
            case 5:
                return $this->getAccepted();
                break;
            case 6:
                return $this->getPendingCommands();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['KioskDevice'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['KioskDevice'][$this->hashCode()] = true;
        $keys = KioskDeviceTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getGUIDHash(),
            $keys[2] => $this->getName(),
            $keys[3] => $this->getDeviceType(),
            $keys[4] => $this->getLastHeartbeat(),
            $keys[5] => $this->getAccepted(),
            $keys[6] => $this->getPendingCommands(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collKioskAssignments) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'kioskAssignments';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'kioskassginment_kasms';
                        break;
                    default:
                        $key = 'KioskAssignments';
                }

                $result[$key] = $this->collKioskAssignments->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\EcclesiaCRM\KioskDevice
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = KioskDeviceTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\EcclesiaCRM\KioskDevice
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setGUIDHash($value);
                break;
            case 2:
                $this->setName($value);
                break;
            case 3:
                $this->setDeviceType($value);
                break;
            case 4:
                $this->setLastHeartbeat($value);
                break;
            case 5:
                $this->setAccepted($value);
                break;
            case 6:
                $this->setPendingCommands($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = KioskDeviceTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setGUIDHash($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setName($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setDeviceType($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setLastHeartbeat($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setAccepted($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setPendingCommands($arr[$keys[6]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\EcclesiaCRM\KioskDevice The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(KioskDeviceTableMap::DATABASE_NAME);

        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_ID)) {
            $criteria->add(KioskDeviceTableMap::COL_KDEV_ID, $this->kdev_id);
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_GUIDHASH)) {
            $criteria->add(KioskDeviceTableMap::COL_KDEV_GUIDHASH, $this->kdev_guidhash);
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_NAME)) {
            $criteria->add(KioskDeviceTableMap::COL_KDEV_NAME, $this->kdev_name);
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_DEVICETYPE)) {
            $criteria->add(KioskDeviceTableMap::COL_KDEV_DEVICETYPE, $this->kdev_devicetype);
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT)) {
            $criteria->add(KioskDeviceTableMap::COL_KDEV_LASTHEARTBEAT, $this->kdev_lastheartbeat);
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_ACCEPTED)) {
            $criteria->add(KioskDeviceTableMap::COL_KDEV_ACCEPTED, $this->kdev_accepted);
        }
        if ($this->isColumnModified(KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS)) {
            $criteria->add(KioskDeviceTableMap::COL_KDEV_PENDINGCOMMANDS, $this->kdev_pendingcommands);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildKioskDeviceQuery::create();
        $criteria->add(KioskDeviceTableMap::COL_KDEV_ID, $this->kdev_id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (kdev_id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \EcclesiaCRM\KioskDevice (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setGUIDHash($this->getGUIDHash());
        $copyObj->setName($this->getName());
        $copyObj->setDeviceType($this->getDeviceType());
        $copyObj->setLastHeartbeat($this->getLastHeartbeat());
        $copyObj->setAccepted($this->getAccepted());
        $copyObj->setPendingCommands($this->getPendingCommands());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getKioskAssignments() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addKioskAssignment($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \EcclesiaCRM\KioskDevice Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('KioskAssignment' == $relationName) {
            $this->initKioskAssignments();
            return;
        }
    }

    /**
     * Clears out the collKioskAssignments collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addKioskAssignments()
     */
    public function clearKioskAssignments()
    {
        $this->collKioskAssignments = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collKioskAssignments collection loaded partially.
     */
    public function resetPartialKioskAssignments($v = true)
    {
        $this->collKioskAssignmentsPartial = $v;
    }

    /**
     * Initializes the collKioskAssignments collection.
     *
     * By default this just sets the collKioskAssignments collection to an empty array (like clearcollKioskAssignments());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initKioskAssignments($overrideExisting = true)
    {
        if (null !== $this->collKioskAssignments && !$overrideExisting) {
            return;
        }

        $collectionClassName = KioskAssignmentTableMap::getTableMap()->getCollectionClassName();

        $this->collKioskAssignments = new $collectionClassName;
        $this->collKioskAssignments->setModel('\EcclesiaCRM\KioskAssignment');
    }

    /**
     * Gets an array of ChildKioskAssignment objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildKioskDevice is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildKioskAssignment[] List of ChildKioskAssignment objects
     * @throws PropelException
     */
    public function getKioskAssignments(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collKioskAssignmentsPartial && !$this->isNew();
        if (null === $this->collKioskAssignments || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collKioskAssignments) {
                // return empty collection
                $this->initKioskAssignments();
            } else {
                $collKioskAssignments = ChildKioskAssignmentQuery::create(null, $criteria)
                    ->filterByKioskDevice($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collKioskAssignmentsPartial && count($collKioskAssignments)) {
                        $this->initKioskAssignments(false);

                        foreach ($collKioskAssignments as $obj) {
                            if (false == $this->collKioskAssignments->contains($obj)) {
                                $this->collKioskAssignments->append($obj);
                            }
                        }

                        $this->collKioskAssignmentsPartial = true;
                    }

                    return $collKioskAssignments;
                }

                if ($partial && $this->collKioskAssignments) {
                    foreach ($this->collKioskAssignments as $obj) {
                        if ($obj->isNew()) {
                            $collKioskAssignments[] = $obj;
                        }
                    }
                }

                $this->collKioskAssignments = $collKioskAssignments;
                $this->collKioskAssignmentsPartial = false;
            }
        }

        return $this->collKioskAssignments;
    }

    /**
     * Sets a collection of ChildKioskAssignment objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $kioskAssignments A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildKioskDevice The current object (for fluent API support)
     */
    public function setKioskAssignments(Collection $kioskAssignments, ConnectionInterface $con = null)
    {
        /** @var ChildKioskAssignment[] $kioskAssignmentsToDelete */
        $kioskAssignmentsToDelete = $this->getKioskAssignments(new Criteria(), $con)->diff($kioskAssignments);


        $this->kioskAssignmentsScheduledForDeletion = $kioskAssignmentsToDelete;

        foreach ($kioskAssignmentsToDelete as $kioskAssignmentRemoved) {
            $kioskAssignmentRemoved->setKioskDevice(null);
        }

        $this->collKioskAssignments = null;
        foreach ($kioskAssignments as $kioskAssignment) {
            $this->addKioskAssignment($kioskAssignment);
        }

        $this->collKioskAssignments = $kioskAssignments;
        $this->collKioskAssignmentsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related KioskAssignment objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related KioskAssignment objects.
     * @throws PropelException
     */
    public function countKioskAssignments(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collKioskAssignmentsPartial && !$this->isNew();
        if (null === $this->collKioskAssignments || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collKioskAssignments) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getKioskAssignments());
            }

            $query = ChildKioskAssignmentQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByKioskDevice($this)
                ->count($con);
        }

        return count($this->collKioskAssignments);
    }

    /**
     * Method called to associate a ChildKioskAssignment object to this object
     * through the ChildKioskAssignment foreign key attribute.
     *
     * @param  ChildKioskAssignment $l ChildKioskAssignment
     * @return $this|\EcclesiaCRM\KioskDevice The current object (for fluent API support)
     */
    public function addKioskAssignment(ChildKioskAssignment $l)
    {
        if ($this->collKioskAssignments === null) {
            $this->initKioskAssignments();
            $this->collKioskAssignmentsPartial = true;
        }

        if (!$this->collKioskAssignments->contains($l)) {
            $this->doAddKioskAssignment($l);

            if ($this->kioskAssignmentsScheduledForDeletion and $this->kioskAssignmentsScheduledForDeletion->contains($l)) {
                $this->kioskAssignmentsScheduledForDeletion->remove($this->kioskAssignmentsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildKioskAssignment $kioskAssignment The ChildKioskAssignment object to add.
     */
    protected function doAddKioskAssignment(ChildKioskAssignment $kioskAssignment)
    {
        $this->collKioskAssignments[]= $kioskAssignment;
        $kioskAssignment->setKioskDevice($this);
    }

    /**
     * @param  ChildKioskAssignment $kioskAssignment The ChildKioskAssignment object to remove.
     * @return $this|ChildKioskDevice The current object (for fluent API support)
     */
    public function removeKioskAssignment(ChildKioskAssignment $kioskAssignment)
    {
        if ($this->getKioskAssignments()->contains($kioskAssignment)) {
            $pos = $this->collKioskAssignments->search($kioskAssignment);
            $this->collKioskAssignments->remove($pos);
            if (null === $this->kioskAssignmentsScheduledForDeletion) {
                $this->kioskAssignmentsScheduledForDeletion = clone $this->collKioskAssignments;
                $this->kioskAssignmentsScheduledForDeletion->clear();
            }
            $this->kioskAssignmentsScheduledForDeletion[]= $kioskAssignment;
            $kioskAssignment->setKioskDevice(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this KioskDevice is new, it will return
     * an empty collection; or if this KioskDevice has previously
     * been saved, it will retrieve related KioskAssignments from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in KioskDevice.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildKioskAssignment[] List of ChildKioskAssignment objects
     */
    public function getKioskAssignmentsJoinEvent(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildKioskAssignmentQuery::create(null, $criteria);
        $query->joinWith('Event', $joinBehavior);

        return $this->getKioskAssignments($query, $con);
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        $this->kdev_id = null;
        $this->kdev_guidhash = null;
        $this->kdev_name = null;
        $this->kdev_devicetype = null;
        $this->kdev_lastheartbeat = null;
        $this->kdev_accepted = null;
        $this->kdev_pendingcommands = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collKioskAssignments) {
                foreach ($this->collKioskAssignments as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collKioskAssignments = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(KioskDeviceTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postInsert')) {
            parent::postInsert($con);
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preUpdate')) {
            return parent::preUpdate($con);
        }
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postUpdate')) {
            parent::postUpdate($con);
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preDelete')) {
            return parent::preDelete($con);
        }
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postDelete')) {
            parent::postDelete($con);
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
