<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incorporated in another software without any authorizaion
//
//  Updated : 2018/06/23
//

namespace EcclesiaCRM\MyPDO;

use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\VObject;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Xml\Element\Sharee;

use Sabre\CalDAV\Backend as SabreCalDavBase;

class CalDavPDO extends SabreCalDavBase\PDO {        

    function __construct(\PDO $pdo) {

        parent::__construct($pdo);
        
        $this->calendarObjectTableName = 'events_event';
    }
    
    public function extractCalendarData ($calendarData,$start='2010-01-01 00:00:00',$end='2070-12-31 23:59:59')
    {
        
        // first version*/
        $vObject = VObject\Reader::read($calendarData);
        
        // new version
        $realStartDate = new \DateTime($start);
        $realEndDate   = new \DateTime($end);
        
        /*try {
          $vcalendar = VObject\Reader::read($calendarData);
          $vObject = $vcalendar->expand($realStartDate, $realEndDate);
        
          if (!empty($vObject)) {
            return nil;
          }
        } catch (Exception $e) {
          $vObject = VObject\Reader::read($calendarData);
        }*/


        $title = '';
        $componentType = null;
        $component = null;
        $firstOccurence = '0000-00-00 00:00:00';
        $lastOccurence = '0000-00-00 00:00:00';
        $freqlastOccurence = '0000-00-00 00:00:00';
        $uid = null;
        $freqEvents = [];
        $freq = 'none';
        $location = null;
        $description = null;
        $attentees = null;
        

        foreach ($vObject->getComponents() as $component) {
            if ($component->name !== 'VTIMEZONE') {
                $componentType = $component->name;
                $uid = (string)$component->UID;
                break;
            }
        }
        
        if (!$componentType) {
            throw new \Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
        }
        
        
        if ($componentType === 'VEVENT') {

            $firstOccurence = $component->DTSTART->getDateTime()->format('Y-m-d H:i:s');
            
            if (isset($component->SUMMARY)) {
               $title = $component->SUMMARY->getValue();       
            }
            
            if (isset($component->LOCATION)) {
               $location = $component->LOCATION->getValue();       
            }
            
            if (isset($component->DESCRIPTION)) {
               $description = $component->DESCRIPTION->getValue();       
            }
                        
            // Finding the last occurence is a bit harder
            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $lastOccurence = $component->DTEND->getDateTime()->format('Y-m-d H:i:s');
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate = $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
                    $lastOccurence = $endDate->format('Y-m-d H:i:s');//->getTimeStamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    
                    $endDate = $endDate->modify('+1 day');
                    $lastOccurence = $endDate->format('Y-m-d H:i:s');//->getTimeStamp();
                } else {
                    $lastOccurence = $firstOccurence;
                }
            } else {
                if (isset($component->RRULE)) {
                  $freq = $component->RRULE->getValue();       
                }
                
                if (isset($component->DTEND)) {
                    $lastOccurence = $component->DTEND->getDateTime()->format('Y-m-d H:i:s');
                }

                $it = new VObject\Recur\EventIterator($vObject, (string)$component->UID);
                $maxDate = new \DateTime('2038-01-01');
                if ($it->isInfinite()) {
                    $freqlastOccurence = $maxDate->format('Y-m-d H:i:s');//->getTimeStamp();
                } else {
                    $end = $it->getDtEnd();
                    $i=0;
                    while ($it->valid() && $end < $maxDate) {
                        $componentSubObject = $it->getEventObject();
                        
                        //print_r($componentSubObject->name);
                        if ($componentSubObject->name == 'VEVENT') { 
                           //echo "le nom : ".$componentSubObject->SUMMARY->getValue()."<br>";
                           //echo "le date : ".$componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s')."<br>";
                           //echo "le date : ".$componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s')."<br>";
                           $extras = [];
                                       
                           if (isset($componentSubObject->LOCATION)) {
                             $extras['LOCATION'] = $componentSubObject->LOCATION->getValue(); 
                           }
                           
                           if (isset($componentSubObject->ATTENDEE)) {
                             foreach($vcalendar->VEVENT->ATTENDEE as $attendee) {
                                //echo 'Attendee ', (string)$attendee;
                                array_push($attendees,(string)$attendee);
                             }
                           }

                           if (isset($componentSubObject->DESCRIPTION)) {
                             $extras['DESCRIPTION'] = $componentSubObject->DESCRIPTION->getValue(); 
                           }
                           
                           if (isset($componentSubObject->{'RECURRENCE-ID'})) {
                             $extras['RECURRENCE-ID'] = $componentSubObject->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d H:i:s');
                           }
                           
                           if (isset($componentSubObject->{'UID'})) {
                             $extras['UID'] = $componentSubObject->{'UID'}->getValue();
                           }
                           
                           if (!empty($extras)) {

                             if ( !( $realStartDate <= (new \DateTime($componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s')))
                               && (new \DateTime($componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'))) <= $realEndDate  ) ) {
                               
                               $end = $it->getDtEnd();
                               $it->next();

                               continue;
                             }

                             $subEvent = ['SUMMARY' => $componentSubObject->SUMMARY->getValue(),
                                          'DTSTART' => $componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s'),
                                          'DTEND' => $componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'),
                                          'EVENT' => $componentSubObject->serialize()];
                                          
                                          
                             $subEvent = array_merge($subEvent,$extras);

                           } else {
                               //echo $realStartDate->format('Y-m-d H:i:s')." ".$realEndDate->format('Y-m-d H:i:s')."<br>";
                               //echo "dates = ".$componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s')." ".$componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s')."<br>";
                               
                             if ( !( $realStartDate <= (new \DateTime($componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s')))
                               && (new \DateTime($componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'))) <= $realEndDate  ) ) {
                               
                               $end = $it->getDtEnd();
                               $it->next();
                               continue;
                             }
                             
                             $subEvent = ['SUMMARY' => $componentSubObject->SUMMARY->getValue(),
                                          'DTSTART' => $componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s'),
                                          'DTEND' => $componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'),
                                          'EVENT' => $componentSubObject->serialize()];
                           }
                           
                           if ( isset($subEvent['RECURRENCE-ID']) && !array_search($subEvent['RECURRENCE-ID'],$freqEvents) || !isset($subEvent['RECURRENCE-ID']) ) {
                             array_push($freqEvents,$subEvent);
                           }
                        }

                        $end = $it->getDtEnd();
                        $it->next();

                    }
                    $freqlastOccurence = $end->format('Y-m-d H:i:s');//->getTimeStamp();
                }

            }

            // Ensure Occurence values are positive
            if ($firstOccurence < 0) $firstOccurence = 0;
            if ($lastOccurence < 0) $lastOccurence = 0;
        }

        // Destroy circular references to PHP will GC the object.
        $vObject->destroy();
        
        
        /*if ( !( $realStartDate <= (new \DateTime($firstOccurence))
            && (new \DateTime($lastOccurence)) <= $realEndDate  ) ) {
            // this code has to be finished
        
        } else {*/

          return [
              'freq'           => $freq,
              'title'          => $title,
              'etag'           => md5($calendarData),
              'size'           => strlen($calendarData),
              'componentType'  => $componentType,
              'firstOccurence' => $firstOccurence,
              'lastOccurence'  => $lastOccurence,            
              'freqlastOccurence'  => $freqlastOccurence,
              'freqEvents'     => $freqEvents,
              'uid'            => $uid,
              'location'       => $location,
              'description'    => $description,
              'attentees'      => $attentees
          ];
          
      //}
    }
    
    function getFullCalendar ($calendarId) {
       if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        
        list($calendarId, $instanceId) = $calendarId;
        
        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->calendarInstancesTableName . ' WHERE calendarid = ? AND id = ?');
        $stmt->execute([$calendarId,$instanceId]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;
        
        return [
            'id'           => $row['id'],
            'uri'          => $row['uri'],
            'lastmodified' => (int)$row['lastmodified'],
            'etag'         => '"' . $row['etag'] . '"',
            'size'         => (int)$row['size'],
            'calendardata' => $row['calendardata'],
            'description'  => $row['description'],
            'present'      => $row['present'],
            'visible'      => $row['visible'],
            'grpid'        => $row['grpid'],
            'cal_type'     => $row['cal_type']
         ];
    }
        
    /**
     * Updates the list of shares.
     *
     * @param mixed $calendarId
     * @param \Sabre\DAV\Xml\Element\Sharee[] $sharees
     * @return void
     */
    function updateInvites($calendarId, array $sharees) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        $currentInvites = $this->getInvites($calendarId);
        list($calendarId, $instanceId) = $calendarId;

        $removeStmt = $this->pdo->prepare("DELETE FROM " . $this->calendarInstancesTableName . " WHERE calendarid = ? AND share_href = ? AND access IN (2,3)");
        $updateStmt = $this->pdo->prepare("UPDATE " . $this->calendarInstancesTableName . " SET access = ?, share_displayname = ?, share_invitestatus = ? WHERE calendarid = ? AND share_href = ?");

        $insertStmt = $this->pdo->prepare('
INSERT INTO ' . $this->calendarInstancesTableName . '
    (
        calendarid,
        principaluri,
        access,
        displayname,
        grpid,
        cal_type,
        uri,
        description,
        calendarorder,
        calendarcolor,
        timezone,
        transparent,
        share_href,
        share_displayname,
        share_invitestatus
    )
    SELECT
        ?,
        ?,
        ?,
        displayname,
        grpid,
        cal_type,
        ?,
        description,
        calendarorder,
        calendarcolor,
        timezone,
        1,
        ?,
        ?,
        ?
    FROM ' . $this->calendarInstancesTableName . ' WHERE id = ?');

        foreach ($sharees as $sharee) {

            if ($sharee->access === \Sabre\DAV\Sharing\Plugin::ACCESS_NOACCESS) {
                // if access was set no NOACCESS, it means access for an
                // existing sharee was removed.
                $removeStmt->execute([$calendarId, $sharee->href]);
                continue;
            }

            if (is_null($sharee->principal)) {
                // If the server could not determine the principal automatically,
                // we will mark the invite status as invalid.
                $sharee->inviteStatus = \Sabre\DAV\Sharing\Plugin::INVITE_INVALID;
            } else {
                // Because sabre/dav does not yet have an invitation system,
                // every invite is automatically accepted for now.
                $sharee->inviteStatus = \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED;
            }

            foreach ($currentInvites as $oldSharee) {

                if ($oldSharee->href === $sharee->href) {
                    // This is an update
                    $sharee->properties = array_merge(
                        $oldSharee->properties,
                        $sharee->properties
                    );
                    $updateStmt->execute([
                        $sharee->access,
                        isset($sharee->properties['{DAV:}displayname']) ? $sharee->properties['{DAV:}displayname'] : null,
                        $sharee->inviteStatus ?: $oldSharee->inviteStatus,
                        $calendarId,
                        $sharee->href
                    ]);
                    continue 2;
                }

            }
            // If we got here, it means it was a new sharee
            $insertStmt->execute([
                $calendarId,
                $sharee->principal,
                $sharee->access,
                \Sabre\DAV\UUIDUtil::getUUID(),
                $sharee->href,
                isset($sharee->properties['{DAV:}displayname']) ? $sharee->properties['{DAV:}displayname'] : null,
                $sharee->inviteStatus ?: \Sabre\DAV\Sharing\Plugin::INVITE_NORESPONSE,
                $instanceId
            ]);

        }

    }
    
    /**
     * Delete a calendar and all it's objects
     *
     * @param mixed $calendarId
     * @return void
     */
    function deleteCalendar($calendarId) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT access FROM ' . $this->calendarInstancesTableName . ' where id = ?');
        $stmt->execute([$instanceId]);
        $access = (int)$stmt->fetchColumn();

        if ($access === \Sabre\DAV\Sharing\Plugin::ACCESS_SHAREDOWNER) {

            /**
             * If the user is the owner of the calendar, we delete all data and all
             * instances.
             **/
            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ?');
            $stmt->execute([$calendarId]);

            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarChangesTableName . ' WHERE calendarid = ?');
            $stmt->execute([$calendarId]);

            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarInstancesTableName . ' WHERE calendarid = ?');
            $stmt->execute([$calendarId]);

            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarTableName . ' WHERE id = ?');
            $stmt->execute([$calendarId]);

        } else {

            /**
             * If it was an instance of a shared calendar, we only delete that
             * instance.
             */
            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarInstancesTableName . ' WHERE id = ?');
            $stmt->execute([$instanceId]);

        }
    }
    
    function searchAndDeleteOneEvent ($vcalendar,$reccurenceID) {
      $i=0;
    
      foreach ($vcalendar->VEVENT as $sevent) {
        if ($sevent->{'RECURRENCE-ID'} == (new \DateTime($reccurenceID))->format('Ymd\THis')) {
          $vcalendar->remove($vcalendar->VEVENT[$i]);
          break;
        }
        $i++;
      }
    }
    
    
    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    calendar. This can be the same as the uri or a database key.
     *  * uri. This is just the 'base uri' or 'filename' of the calendar.
     *  * principaluri. The owner of the calendar. Almost always the same as
     *    principalUri passed to this method.
     *
     * Furthermore it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * Many clients also require:
     * {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
     * For this property, you can just return an instance of
     * Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet.
     *
     * If you return {http://sabredav.org/ns}read-only and set the value to 1,
     * ACL will automatically be put in read-only mode.
     *
     * @param string $principalUri
     * @param bool   $all (for all the calendar : order by type)
     * @return array
     */
     function getCalendarsForUser($principalUri,$all=false) {

        $fields = array_values($this->propertyMap);
        $fields[] = 'calendarid';
        $fields[] = 'uri';
        $fields[] = 'synctoken';
        $fields[] = 'components';
        $fields[] = 'principaluri';
        $fields[] = 'transparent';
        $fields[] = 'access';
        $fields[] = 'present';
        $fields[] = 'visible';
        $fields[] = 'grpid';
        $fields[] = 'cal_type';
        $fields[] = 'description';
        
        $ordering = 'displayname';//'calendarorder';
        
        if ($all) {// this is usefull for the calendar popup in the EventEditor window
          $ordering = 'cal_type';  
        }

        // Making fields a comma-delimited list
        $fields = implode(', ', $fields);
        $stmt = $this->pdo->prepare(<<<SQL
SELECT {$this->calendarInstancesTableName}.id as id, $fields FROM {$this->calendarInstancesTableName}
    LEFT JOIN {$this->calendarTableName} ON
        {$this->calendarInstancesTableName}.calendarid = {$this->calendarTableName}.id
WHERE principaluri = ? ORDER BY cal_type ASC,$ordering ASC
SQL
        );
        $stmt->execute([$principalUri]);

        $calendars = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $components = [];
            if ($row['components']) {
                $components = explode(',', $row['components']);
            }

            $calendar = [
                'id'                                                                 => [(int)$row['calendarid'], (int)$row['id']],
                'uri'                                                                => $row['uri'],
                'principaluri'                                                       => $row['principaluri'],
                '{' . CalDAV\Plugin::NS_CALENDARSERVER . '}getctag'                  => 'http://sabre.io/ns/sync/' . ($row['synctoken'] ? $row['synctoken'] : '0'),
                '{http://sabredav.org/ns}sync-token'                                 => $row['synctoken'] ? $row['synctoken'] : '0',
                '{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet($components),
                '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp'         => new CalDAV\Xml\Property\ScheduleCalendarTransp($row['transparent'] ? 'transparent' : 'opaque'),
                'share-resource-uri'                                                 => '/ns/share/' . $row['calendarid'],
                'present'                                                            =>  $row['present'],
                'visible'                                                            =>  $row['visible'],
                'grpid'                                                              =>  $row['grpid'],
                'cal_type'                                                           =>  $row['cal_type'],
                'description'                                                        =>  $row['description'],
            ];

            $calendar['share-access'] = (int)$row['access'];
            // 1 = owner, 2 = readonly, 3 = readwrite
            if ($row['access'] > 1) {
                // We need to find more information about the original owner.
                //$stmt2 = $this->pdo->prepare('SELECT principaluri FROM ' . $this->calendarInstancesTableName . ' WHERE access = 1 AND id = ?');
                //$stmt2->execute([$row['id']]);

                // read-only is for backwards compatbility. Might go away in
                // the future.
                $calendar['read-only'] = (int)$row['access'] === \Sabre\DAV\Sharing\Plugin::ACCESS_READ;
            }

            foreach ($this->propertyMap as $xmlName => $dbName) {
                $calendar[$xmlName] = $row[$dbName];
            }

            $calendars[] = $calendar;

        }

        return $calendars;

    }
    
    /**
     * Returns all calendar objects within a calendar.
     *
     * Every item contains an array with the following keys:
     *   * calendardata - The iCalendar-compatible calendar data
     *   * uri - a unique key which will be used to construct the uri. This can
     *     be any arbitrary string, but making sure it ends with '.ics' is a
     *     good idea. This is only the basename, or filename, not the full
     *     path.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * size - The size of the calendar objects, in bytes.
     *   * component - optional, a string containing the type of object, such
     *     as 'vevent' or 'vtodo'. If specified, this will be used to populate
     *     the Content-Type header.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * If neither etag or size are specified, the calendardata will be
     * used/fetched to determine these numbers. If both are specified the
     * amount of times this is needed is reduced by a great degree.
     *
     * @param mixed $calendarId
     * @return array
     */
    function getCalendarObjects($calendarId) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ?');
        $stmt->execute([$calendarId]);

        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'id'           => $row['event_id'],
                'uri'          => $row['event_uri'],
                'lastmodified' => (int)$row['event_lastmodified'],
                'etag'         => '"' . $row['event_etag'] . '"',
                'size'         => (int)$row['event_size'],
                'location'     => $row['event_location'],
                'component'    => strtolower($row['event_componenttype']),
            ];
        }

        return $result;

    }
    
    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * This method must return null if the object did not exist.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @return array|null
     */
    function getCalendarObjectById($calendarId, $objectId) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_calendardata, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_id = ?');
        $stmt->execute([$calendarId, $objectId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'id'           => $row['event_id'],
            'uri'          => $row['event_uri'],
            'lastmodified' => (int)$row['event_lastmodified'],
            'etag'         => '"' . $row['event_etag'] . '"',
            'size'         => (int)$row['event_size'],
            'location'     => $row['event_location'],
            'calendardata' => $row['event_calendardata'],
            'component'    => strtolower($row['event_componenttype']),
         ];

    }
    
    function getCalendarObject($calendarId, $objectUri) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_calendardata, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_uri = ?');
        $stmt->execute([$calendarId, $objectUri]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'id'           => $row['event_id'],
            'uri'          => $row['event_uri'],
            'lastmodified' => (int)$row['event_lastmodified'],
            'etag'         => '"' . $row['event_etag'] . '"',
            'size'         => (int)$row['event_size'],
            'location'     => $row['event_location'],
            'calendardata' => $row['event_calendardata'],
            'component'    => strtolower($row['event_componenttype']),
         ];

    }
    
    /**
     * Returns a list of calendar objects.
     *
     * This method should work identical to getCalendarObject, but instead
     * return all the calendar objects in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $calendarId
     * @param array $uris
     * @return array
     */
    function getMultipleCalendarObjects($calendarId, array $uris) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $result = [];
        foreach (array_chunk($uris, 900) as $chunk) {
            $query = 'SELECT event_id, event_uri, event_lastmodified, event_etag, event_calendarid, event_size, event_location, event_calendardata, event_componenttype FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_uri IN (';
            // Inserting a whole bunch of question marks
            $query .= implode(',', array_fill(0, count($chunk), '?'));
            $query .= ')';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_merge([$calendarId], $chunk));

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

                $result[] = [
                    'id'           => $row['event_id'],
                    'uri'          => $row['event_uri'],
                    'lastmodified' => (int)$row['event_lastmodified'],
                    'etag'         => '"' . $row['event_etag'] . '"',
                    'size'         => (int)$row['event_size'],
                    'location'     => $row['event_location'],
                    'calendardata' => $row['event_calendardata'],
                    'component'    => strtolower($row['event_componenttype']),
                ];

            }
        }
        return $result;

    }
    
        /**
     * Returns the list of people whom a calendar is shared with.
     *
     * Every item in the returned list must be a Sharee object with at
     * least the following properties set:
     *   $href
     *   $shareAccess
     *   $inviteStatus
     *
     * and optionally:
     *   $properties
     *
     * @param mixed $calendarId
     * @return \Sabre\DAV\Xml\Element\Sharee[]
     */
    function getGroupId ($calendarId) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to getGroupId() is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $query = <<<SQL
SELECT
    grpid
FROM {$this->calendarInstancesTableName}
WHERE
    calendarid = ?
SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$calendarId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return 0;
        
        return $row['grpid'];
    }
    
   /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used
     * to reference this calendar in other methods, such as updateCalendar.
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @param int : 1 = personal, 2 = room, 3 = computer, 4 = video
     * @return string
     */
    function createCalendar($principalUri, $calendarUri, array $properties,$cal_type=1,$desc=null) {

        $fieldNames = [
            'principaluri',
            'uri',
            'transparent',
            'cal_type',
            'description',
            'calendarid',
        ];
        $values = [
            ':principaluri' => $principalUri,
            ':uri'          => $calendarUri,
            ':transparent'  => 0,
            ':cal_type'     => $cal_type,
            ':description'  => $desc,
        ];


        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        if (!isset($properties[$sccs])) {
            // Default value
            $components = 'VEVENT,VTODO';
        } else {
            if (!($properties[$sccs] instanceof CalDAV\Xml\Property\SupportedCalendarComponentSet)) {
                throw new DAV\Exception('The ' . $sccs . ' property must be of type: \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet');
            }
            $components = implode(',', $properties[$sccs]->getValue());
        }
        $transp = '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';
        if (isset($properties[$transp])) {
            $values[':transparent'] = $properties[$transp]->getValue() === 'transparent' ? 1 : 0;
        }
        $stmt = $this->pdo->prepare("INSERT INTO " . $this->calendarTableName . " (synctoken, components) VALUES (1, ?)");
        $stmt->execute([$components]);

        $calendarId = $this->pdo->lastInsertId(
            $this->calendarTableName . '_id_seq'
        );

        $values[':calendarid'] = $calendarId;

        foreach ($this->propertyMap as $xmlName => $dbName) {
            if (isset($properties[$xmlName])) {

                $values[':' . $dbName] = $properties[$xmlName];
                $fieldNames[] = $dbName;
            }
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO " . $this->calendarInstancesTableName . " (" . implode(', ', $fieldNames) . ") VALUES (" . implode(', ', array_keys($values)) . ")");

        $stmt->execute($values);

        return [
            $calendarId,
            $this->pdo->lastInsertId($this->calendarInstancesTableName . '_id_seq')
        ];

    }    
    
    /**
     * Creates a new calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function createCalendarObject($calendarId, $objectUri, $calendarData) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        
        $groupId = $this->getGroupId($calendarId);

        list($calendarId, $instanceId) = $calendarId;
        

        $extraData = $this->extractCalendarData($calendarData);

        $stmt = $this->pdo->prepare('INSERT INTO ' . $this->calendarObjectTableName . ' (event_calendarid, event_uri, event_calendardata, event_lastmodified, event_title, event_desc, event_location, event_last_occurence, event_etag, event_size, event_componenttype, event_start, event_end, event_uid, event_grpid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $calendarId,
            $objectUri,
            $calendarData,
            time(),
            $extraData['title'],
            $extraData['description'],
            $extraData['location'],
            $extraData['freqlastOccurence'],
            $extraData['etag'],
            $extraData['size'],
            $extraData['componentType'],
            $extraData['firstOccurence'],
            $extraData['lastOccurence'],
            $extraData['uid'],
            $groupId,
        ]);
        $this->addChange($calendarId, $objectUri, 1);

        return '"' . $extraData['etag'] . '"';

    }
    
    
     /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function updateCalendarObject($calendarId, $objectUri, $calendarData) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $extraData = $this->extractCalendarData($calendarData);

        $stmt = $this->pdo->prepare('UPDATE ' . $this->calendarObjectTableName . ' SET event_calendardata = ?, event_lastmodified = ?, event_title = ?, event_desc = ?, event_location = ?, event_last_occurence = ?, event_etag = ?, event_size = ?, event_componenttype = ?, event_start = ?, event_end = ?, event_uid = ? WHERE event_calendarid = ? AND event_uri = ?');
        $stmt->execute([$calendarData, time(), $extraData['title'], $extraData['description'], $extraData['location'], $extraData['freqlastOccurence'], $extraData['etag'], $extraData['size'], $extraData['componentType'], $extraData['firstOccurence'], $extraData['lastOccurence'], $extraData['uid'], $calendarId, $objectUri]);
        
        // quand le calendrier est mis à jour on gère la bonne date et la bonne heure
        //error_log("La date est = ".$extraData['firstOccurence']."\n\n", 3, "/var/log/mes-erreurs.log");
        //error_log("Le blob = ".$calendarData."\n\n", 3, "/var/log/mes-erreurs.log");
        
        /*foreach ($extraData as $key => $val) {
           error_log("Key = ".$key." val = ".$val."\n\n", 3, "/var/log/mes-erreurs.log");
        }*/
        

        $this->addChange($calendarId, $objectUri, 2);

        return '"' . $extraData['etag'] . '"';

    }
    
    /**
     * Deletes an existing calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @return void
     */
    function deleteCalendarObject($calendarId, $objectUri) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarObjectTableName . ' WHERE event_calendarid = ? AND event_uri = ?');
        $stmt->execute([$calendarId, $objectUri]);

        $this->addChange($calendarId, $objectUri, 3);

    }

   /**
     * Performs a calendar-query on the contents of this calendar.
     *
     * The calendar-query is defined in RFC4791 : CalDAV. Using the
     * calendar-query it is possible for a client to request a specific set of
     * object, based on contents of iCalendar properties, date-ranges and
     * iCalendar component types (VTODO, VEVENT).
     *
     * This method should just return a list of (relative) urls that match this
     * query.
     *
     * The list of filters are specified as an array. The exact array is
     * documented by \Sabre\CalDAV\CalendarQueryParser.
     *
     * Note that it is extremely likely that getCalendarObject for every path
     * returned from this method will be called almost immediately after. You
     * may want to anticipate this to speed up these requests.
     *
     * This method provides a default implementation, which parses *all* the
     * iCalendar objects in the specified calendar.
     *
     * This default may well be good enough for personal use, and calendars
     * that aren't very large. But if you anticipate high usage, big calendars
     * or high loads, you are strongly adviced to optimize certain paths.
     *
     * The best way to do so is override this method and to optimize
     * specifically for 'common filters'.
     *
     * Requests that are extremely common are:
     *   * requests for just VEVENTS
     *   * requests for just VTODO
     *   * requests with a time-range-filter on a VEVENT.
     *
     * ..and combinations of these requests. It may not be worth it to try to
     * handle every possible situation and just rely on the (relatively
     * easy to use) CalendarQueryValidator to handle the rest.
     *
     * Note that especially time-range-filters may be difficult to parse. A
     * time-range filter specified on a VEVENT must for instance also handle
     * recurrence rules correctly.
     * A good example of how to interpret all these filters can also simply
     * be found in \Sabre\CalDAV\CalendarQueryFilter. This class is as correct
     * as possible, so it gives you a good idea on what type of stuff you need
     * to think of.
     *
     * This specific implementation (for the PDO) backend optimizes filters on
     * specific components, and VEVENT time-ranges.
     *
     * @param mixed $calendarId
     * @param array $filters
     * @return array
     */
    function calendarQuery($calendarId, array $filters) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        $componentType = null;
        $requirePostFilter = true;
        $timeRange = null;

        // if no filters were specified, we don't need to filter after a query
        if (!$filters['prop-filters'] && !$filters['comp-filters']) {
            $requirePostFilter = false;
        }

        // Figuring out if there's a component filter
        if (count($filters['comp-filters']) > 0 && !$filters['comp-filters'][0]['is-not-defined']) {
            $componentType = $filters['comp-filters'][0]['name'];

            // Checking if we need post-filters
            if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['time-range'] && !$filters['comp-filters'][0]['prop-filters']) {
                $requirePostFilter = false;
            }
            // There was a time-range filter
            if ($componentType == 'VEVENT' && isset($filters['comp-filters'][0]['time-range'])) {
                $timeRange = $filters['comp-filters'][0]['time-range'];

                // If start time OR the end time is not specified, we can do a
                // 100% accurate mysql query.
                if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['prop-filters'] && (!$timeRange['start'] || !$timeRange['end'])) {
                    $requirePostFilter = false;
                }
            }

        }

        if ($requirePostFilter) {
            $query = "SELECT event_uri, event_calendardata FROM " . $this->calendarObjectTableName . " WHERE event_calendarid = :calendarid";
        } else {
            $query = "SELECT event_uri FROM " . $this->calendarObjectTableName . " WHERE event_calendarid = :calendarid";
        }

        $values = [
            'calendarid' => $calendarId,
        ];

        if ($componentType) {
            $query .= " AND event_componenttype = :componenttype";
            $values['componenttype'] = $componentType;
        }

        if ($timeRange && $timeRange['start']) {
            $query .= " AND event_end > :startdate";
            $values['startdate'] = $timeRange['start']->getTimeStamp();
        }
        if ($timeRange && $timeRange['end']) {
            $query .= " AND event_start < :enddate";
            $values['enddate'] = $timeRange['end']->getTimeStamp();
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($requirePostFilter) {
                if (!$this->validateFilterForObject($row, $filters)) {
                    continue;
                }
            }
            $result[] = $row['event_uri'];

        }

        return $result;

    }
    
    
   /**
     * Searches through all of a users calendars and calendar objects to find
     * an object with a specific UID.
     *
     * This method should return the path to this object, relative to the
     * calendar home, so this path usually only contains two parts:
     *
     * calendarpath/objectpath.ics
     *
     * If the uid is not found, return null.
     *
     * This method should only consider * objects that the principal owns, so
     * any calendars owned by other principals that also appear in this
     * collection should be ignored.
     *
     * @param string $principalUri
     * @param string $uid
     * @return string|null
     */
    function getCalendarObjectByUID($principalUri, $uid) {

        $query = <<<SQL
SELECT
    calendar_instances.uri AS calendaruri, calendarobjects.event_uri as objecturi
FROM
    $this->calendarObjectTableName AS calendarobjects
LEFT JOIN
    $this->calendarInstancesTableName AS calendar_instances
    ON calendarobjects.event_calendarid = calendar_instances.calendarid
WHERE
    calendar_instances.principaluri = ?
    AND
    calendarobjects.uid = ?
SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$principalUri, $uid]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row['calendaruri'] . '/' . $row['objecturi'];
        }

    }
    

/**
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified calendar.
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'modified.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the calendar, as reported in the {http://sabredav.org/ns}sync-token
     * property this is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param mixed $calendarId
     * @param string $syncToken
     * @param int $syncLevel
     * @param int $limit
     * @return array
     */
    function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {

        if (!is_array($calendarId)) {
            throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
        }
        list($calendarId, $instanceId) = $calendarId;

        // Current synctoken
        $stmt = $this->pdo->prepare('SELECT synctoken FROM ' . $this->calendarTableName . ' WHERE id = ?');
        $stmt->execute([$calendarId]);
        $currentToken = $stmt->fetchColumn(0);

        if (is_null($currentToken)) return null;

        $result = [
            'syncToken' => $currentToken,
            'added'     => [],
            'modified'  => [],
            'deleted'   => [],
        ];

        if ($syncToken) {

            $query = "SELECT uri, operation FROM " . $this->calendarChangesTableName . " WHERE synctoken >= ? AND synctoken < ? AND calendarid = ? ORDER BY synctoken";
            if ($limit > 0) $query .= " LIMIT " . (int)$limit;

            // Fetching all changes
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$syncToken, $currentToken, $calendarId]);

            $changes = [];

            // This loop ensures that any duplicates are overwritten, only the
            // last change on a node is relevant.
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

                $changes[$row['uri']] = $row['operation'];

            }

            foreach ($changes as $uri => $operation) {

                switch ($operation) {
                    case 1 :
                        $result['added'][] = $uri;
                        break;
                    case 2 :
                        $result['modified'][] = $uri;
                        break;
                    case 3 :
                        $result['deleted'][] = $uri;
                        break;
                }

            }
        } else {
            // No synctoken supplied, this is the initial sync.
            $query = "SELECT event_uri FROM " . $this->calendarObjectTableName . " WHERE event_calendarid = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$calendarId]);

            $result['added'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
        return $result;

    }
    
   /**
     * The moveCalendarToNewPrincipal
     *
     *
     * @param int $new_principaluri
     * @return nothing
     */    
    public function moveCalendarToNewPrincipal ($old_principaluri,$new_principaluri)
    {    

        $stmt = $this->pdo->prepare('UPDATE ' . $this->calendarInstancesTableName . ' SET principaluri = ? WHERE principaluri = ?');
        $stmt->execute([$new_principaluri,$old_principaluri]);

    }

}