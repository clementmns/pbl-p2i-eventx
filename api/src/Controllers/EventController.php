<?php
namespace App\Controllers;

use App\Services\EventService;
use App\Utils\Response;
use JsonException;

class EventController {
    private EventService $svc;

    public function __construct() { $this->svc = new EventService(); }

    /**
     * @throws JsonException
     */
    public function listEvents(): void { Response::json($this->svc->getAllEvents()); }

    public function getEvent(int $id): void {
        $ev = $this->svc->getEvent($id);
        if (!$ev) { Response::json(['error'=>'not_found'],404); return; }
        Response::json($ev);
    }

    public function createEvent(array $data): void {
        $res = $this->svc->createEvent($data);
        if (!$res['ok']) { Response::json(['error'=>$res['error']],400); return; }
        Response::json($res,201);
    }

    public function updateEvent(int $id,array $data): void { Response::json($this->svc->updateEvent($id,$data)); }

    public function deleteEvent(int $id): void { Response::json($this->svc->deleteEvent($id)); }

    /**
     * @throws JsonException
     */
    public function joinEvent(int $eventId, ?int $userId): void {
        if (!$userId) { Response::json(['error'=>'userId_required'],400); return; }
        Response::json($this->svc->joinEvent($eventId,$userId));
    }

    /**
     * @throws JsonException
     */
    public function quitEvent(int $eventId, ?int $userId): void {
        if (!$userId) { Response::json(['error'=>'userId_required'],400); return; }
        Response::json($this->svc->quitEvent($eventId,$userId));
    }

    /**
     * @throws JsonException
     */
    public function listWishlist(?int $userId): void {
        if (!$userId) { Response::json(['error'=>'userId_required'],400); return; }
        $events = $this->svc->getWishlist($userId);
        Response::json($events);
    }


    /**
     * @throws JsonException
     */
    public function addWishlist(int $eventId, ?int $userId): void {
        if (!$userId) { Response::json(['error'=>'userId_required'],400); return; }
        Response::json($this->svc->addWishlist($eventId,$userId));
    }

    /**
     * @throws JsonException
     */
    public function removeWishlist(int $eventId, ?int $userId): void {
        if (!$userId) { Response::json(['error'=>'userId_required'],400); return; }
        Response::json($this->svc->removeWishlist($eventId,$userId));
    }
}
