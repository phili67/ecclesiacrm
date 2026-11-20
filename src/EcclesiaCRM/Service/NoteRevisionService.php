<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\NoteRevision;
use EcclesiaCRM\NoteRevisionQuery;
use EcclesiaCRM\NoteShareQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\ActiveQuery\Criteria;

class NoteRevisionService
{
    public function lock(Note $note, int $personId, ConnectionInterface $connection): void
    {
        NoteQuery::create()
            ->filterById($note->getId())
            ->lockForUpdate()
            ->findOne($connection);
        NoteShareQuery::create()
            ->filterByNoteId($note->getId())
            ->filterBySharePerId($personId)
            ->lockForUpdate()
            ->find($connection);
        $note->reload(true, $connection);
    }

    public function version(Note $note, ConnectionInterface $connection): string
    {
        $latestRevision = NoteRevisionQuery::create()
            ->filterByNoteId($note->getId())
            ->orderById(Criteria::DESC)
            ->findOne($connection);
        return hash('sha256', json_encode([
            $note->getTitle(), $note->getText(), $note->getType(),
            (int)$note->getPrivate(), $latestRevision ? (int)$latestRevision->getId() : 0
        ], JSON_THROW_ON_ERROR));
    }

    public function record(Note $note, int $authorId, \DateTimeInterface $date, ConnectionInterface $connection): void
    {
        $revision = new NoteRevision();
        $revision->setNoteId($note->getId());
        $revision->setAuthorId($authorId);
        $revision->setCreatedAt($date);
        $revision->setTitle($note->getTitle());
        $revision->setType($note->getType());
        $revision->setContent($note->getText());
        $revision->save($connection);
    }

    public function recordInitialIfMissing(Note $note, ConnectionInterface $connection): void
    {
        $revision = NoteRevisionQuery::create()
            ->filterByNoteId($note->getId())
            ->findOne($connection);
        if ($revision === null) {
            $this->record(
                $note,
                (int)($note->getEditedBy() ?: $note->getEnteredBy()),
                $note->getDateLastEdited() ?: $note->getDateEntered(),
                $connection
            );
        }
    }

    public function history(Note $note, ConnectionInterface $connection): array
    {
        $revisions = NoteRevisionQuery::create()
            ->filterByNoteId($note->getId())
            ->orderById(Criteria::DESC)
            ->find($connection);

        $history = [];
        foreach ($revisions as $revision) {
            $history[] = [
                'id' => $revision->getId(),
                'author_id' => $revision->getAuthorId(),
                'created_at' => $revision->getCreatedAt('Y-m-d H:i:s'),
                'title' => $revision->getTitle(),
                'type' => $revision->getType(),
                'content' => $revision->getContent()
            ];
        }

        return $history;
    }

    public function find(Note $note, int $revisionId, ConnectionInterface $connection): ?array
    {
        $revision = NoteRevisionQuery::create()
            ->filterByNoteId($note->getId())
            ->filterById($revisionId)
            ->findOne($connection);

        if ($revision === null) {
            return null;
        }

        return [
            'id' => $revision->getId(),
            'title' => $revision->getTitle(),
            'type' => $revision->getType(),
            'content' => $revision->getContent()
        ];
    }
}
