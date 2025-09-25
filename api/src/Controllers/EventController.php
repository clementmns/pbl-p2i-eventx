<?php
namespace App\Controllers;

use App\Services\EventService;
use App\Services\UserService;
use App\Utils\Response;
use App\Utils\Auth;

class EventController
{
    private EventService $svc;
    private UserService $userService;

    public function __construct()
    {
        $this->svc = new EventService();
        $this->userService = new UserService();
    }

    /**
     * List all events.
     * @return void
     */
    public function listEvents(): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        Response::json($this->svc->getAllEvents());
    }

    /**
     * Get a single event by ID.
     * @param int $id
     * @return void
     */
    public function getEvent(int $id): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        $ev = $this->svc->getEvent($id);
        if (!$ev) {
            Response::json(['error' => 'not_found'], 404);
            return;
        }
        Response::json($ev);
    }

    /**
     * Get events joined by a user.
     * @param int $userId
     * @return void
     */
    public function getEventsJoinedByUser($userId)
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }
        $events = $this->svc->getEventsJoinedByUser($userId);
        Response::json($events);
    }

    /**
     * Create a new event.
     * @param array $data Must contain userId, name, startDate, endDate, place
     * @return void
     */
    public function createEvent(array $data): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        // Required params for event creation
        $required = ['userId', 'name', 'startDate', 'endDate', 'place'];
        $missing = array_filter($required, fn($k) => empty($data[$k]));
        if ($missing) {
            Response::json(['error' => 'missing_params', 'missing' => $missing], 400);
            return;
        }
        $userId = $data['userId'];
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }
        $res = $this->svc->createEvent($data);
        if (!$res['ok']) {
            Response::json(['error' => $res['error']], 400);
            return;
        }
        Response::json($res, 201);
    }

    /**
     * Update an event.
     * @param int $id
     * @param array $data Must contain userId, name, startDate, endDate, place
     * @return void
     */
    public function updateEvent(int $id, array $data): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        // Required params for event update
        $required = ['userId', 'name', 'startDate', 'endDate', 'place'];
        $missing = array_filter($required, fn($k) => empty($data[$k]));
        if ($missing) {
            Response::json(['error' => 'missing_params', 'missing' => $missing], 400);
            return;
        }
        $userId = $data['userId'];
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }
        $res = $this->svc->updateEvent($id, $data);
        Response::json($res);
    }

    /**
     * Delete an event by ID.
     * @param int $id
     * @return void
     */
    public function deleteEvent(int $id): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        Response::json($this->svc->deleteEvent($id));
    }

    /**
     * User joins an event.
     * @param int $eventId
     * @param int|null $userId
     * @return void
     */
    public function joinEvent(int $eventId, ?int $userId): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if event exists
        if (!$this->svc->getEvent($eventId)) {
            Response::json(['error' => 'event_not_found'], 404);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }

        Response::json($this->svc->joinEvent($eventId, $userId));
    }

    /**
     * User quits an event.
     * @param int $eventId
     * @param int|null $userId
     * @return void
     */
    public function quitEvent(int $eventId, ?int $userId): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if event exists
        if (!$this->svc->getEvent($eventId)) {
            Response::json(['error' => 'event_not_found'], 404);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }

        Response::json($this->svc->quitEvent($eventId, $userId));
    }

    /**
     * List wishlist events for a user.
     * @param int|null $userId
     * @return void
     */
    public function listWishlist(?int $userId): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }

        $events = $this->svc->getWishlist($userId);
        Response::json($events);
    }


    /**
     * Add an event to user's wishlist.
     * @param int $eventId
     * @param int|null $userId
     * @return void
     */
    public function addWishlist(int $eventId, ?int $userId): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }
        // check if event exists
        if (!$this->svc->getEvent($eventId)) {
            Response::json(['error' => 'event_not_found'], 404);
            return;
        }

        Response::json($this->svc->addWishlist($eventId, $userId));
    }

    /**
     * Remove an event from user's wishlist.
     * @param int $eventId
     * @param int|null $userId
     * @return void
     */
    public function removeWishlist(int $eventId, ?int $userId): void
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }
        // check if event exists
        if (!$this->svc->getEvent($eventId)) {
            Response::json(['error' => 'event_not_found'], 404);
            return;
        }
        Response::json($this->svc->removeWishlist($eventId, $userId));
    }
}
