<?php

/*******************************************************************************
 *
 *  filename    : VObjectExtract.php
 *  last change : 2020-01-26
 *  description : manage the full sabre VObject
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2020 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software without authorization
 *  Updated : 2020/01/26
 *
 ******************************************************************************/

namespace EcclesiaCRM\MyPDO;

use Sabre\VObject;

class VObjectExtract {
    static function calendarData($calendarData, $start = '2010-01-01 00:00:00', $end = '2070-12-31 23:59:59')
    {

        // first version*/
        $vObject = VObject\Reader::read($calendarData);

        // new version
        $realStartDate = new \DateTime($start);
        $realEndDate = new \DateTime($end);

        /*try {
          $vcalendar = VObject\Reader::read($calendarData);
          $vObject = $vcalendar->expand($realStartDate, $realEndDate);

          if (!empty($vObject)) {
            return NULL;
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
        $alarm = null;
        $organiser = null;
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

            if (isset($component->VALARM)) {
                $alarm = [
                    'trigger' => $component->VALARM->TRIGGER->getValue(),
                    'DESCRIPTION' => (!is_null($component->VALARM->DESCRIPTION))?$component->VALARM->DESCRIPTION->getValue():"",
                    'ACTION' => (!is_null($component->VALARM->ACTION))?$component->VALARM->ACTION->getValue():"",
                ];
            }

            if (isset($component->ORGANIZER)) {
                $organiser = $component->ORGANIZER->getValue();
            }

            // we check if we've global attendees
            if (isset($component->ATTENDEE)) {
                foreach ($component->ATTENDEE as $attendee) {
                    $attentees[] = (string)$attendee;
                }
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
                    $i = 0;
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

                            // we search the sub attendees
                            $sub_attentees = [];

                            if (isset($componentSubObject->ATTENDEE)) {
                                foreach ($componentSubObject->ATTENDEE as $attendee) {
                                    //echo 'Attendee ', (string)$attendee;
                                    array_push($sub_attentees, (string)$attendee);
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

                                if (!($realStartDate <= (new \DateTime($componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s')))
                                    && (new \DateTime($componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'))) <= $realEndDate)) {

                                    $end = $it->getDtEnd();
                                    $it->next();

                                    continue;
                                }

                                $subEvent = ['SUMMARY' => $componentSubObject->SUMMARY->getValue(),
                                    'DTSTART' => $componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s'),
                                    'DTEND' => $componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'),
                                    'EVENT' => $componentSubObject->serialize(),
                                    'SUBATTENDEES' => $sub_attentees];


                                $subEvent = array_merge($subEvent, $extras);

                            } else {
                                //echo $realStartDate->format('Y-m-d H:i:s')." ".$realEndDate->format('Y-m-d H:i:s')."<br>";
                                //echo "dates = ".$componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s')." ".$componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s')."<br>";

                                if (!($realStartDate <= (new \DateTime($componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s')))
                                    && (new \DateTime($componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'))) <= $realEndDate)) {

                                    $end = $it->getDtEnd();
                                    $it->next();
                                    continue;
                                }

                                $subEvent = ['SUMMARY' => $componentSubObject->SUMMARY->getValue(),
                                    'DTSTART' => $componentSubObject->DTSTART->getDateTime()->format('Y-m-d H:i:s'),
                                    'DTEND' => $componentSubObject->DTEND->getDateTime()->format('Y-m-d H:i:s'),
                                    'EVENT' => $componentSubObject->serialize(),
                                    'SUBATTENDEES' => $sub_attentees];
                            }

                            if (isset($subEvent['RECURRENCE-ID']) && !array_search($subEvent['RECURRENCE-ID'], $freqEvents) || !isset($subEvent['RECURRENCE-ID'])) {
                                array_push($freqEvents, $subEvent);
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
            'freq' => $freq,
            'title' => $title,
            'etag' => md5((string)$calendarData),
            'size' => strlen((string)$calendarData),
            'componentType' => $componentType,
            'firstOccurence' => $firstOccurence,
            'lastOccurence' => $lastOccurence,
            'freqlastOccurence' => $freqlastOccurence,
            'freqEvents' => $freqEvents,
            'uid' => $uid,
            'location' => $location,
            'description' => $description,
            'alarm' => $alarm,
            'organiser' => $organiser,
            'attentees' => $attentees
        ];

        //}
    }
}
