<?php

namespace App\Controllers;

class StatusController
{
    /**
     * Get the current status of the application.
     */
    public function getStatus(): array
    {
        return [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get the available routes in the application.
     */
    public function getRoutes(): array
    {
        return [
            // Status Routes
            ['method' => 'GET', 'path' => '/status', 'description' => 'Get the current status of the application'],
            ['method' => 'GET', 'path' => '/', 'description' => 'Get all available routes'],

            // User Routes
            ['method' => 'GET', 'path' => '/users', 'description' => 'List all users', 'requires_auth' => true],
            ['method' => 'GET', 'path' => '/users/{id}', 'description' => 'Get a specific user by ID', 'requires_auth' => true],
            ['method' => 'PUT', 'path' => '/users/{id}', 'description' => 'Update a specific user by ID', 'requires_auth' => true, 'body_params' => ['mail', 'password', 'roleId']],
            ['method' => 'DELETE', 'path' => '/users/{id}', 'description' => 'Delete a specific user by ID', 'requires_auth' => true, 'requires_admin' => true],

            // Auth Routes
            ['method' => 'POST', 'path' => '/auth/register', 'description' => 'Register a new user', 'body_params' => ['mail', 'password']],
            ['method' => 'POST', 'path' => '/auth/login', 'description' => 'Authenticate a user and return a token', 'body_params' => ['mail', 'password']],

            // Event Routes
            ['method' => 'GET', 'path' => '/events', 'description' => 'List all events', 'requires_auth' => true],
            ['method' => 'POST', 'path' => '/events', 'description' => 'Create a new event', 'requires_auth' => true, 'body_params' => ['userId', 'name', 'startDate', 'endDate', 'place', 'description', 'maxParticipants']],
            ['method' => 'GET', 'path' => '/events/{id}', 'description' => 'Get a specific event by ID', 'requires_auth' => true],
            ['method' => 'PUT', 'path' => '/events/{id}', 'description' => 'Update a specific event by ID', 'requires_auth' => true, 'body_params' => ['userId', 'name', 'startDate', 'endDate', 'place', 'description', 'maxParticipants']],
            ['method' => 'DELETE', 'path' => '/events/{id}', 'description' => 'Delete a specific event by ID', 'requires_auth' => true],
            ['method' => 'GET', 'path' => '/events/user/{id}', 'description' => 'Get events joined by a specific user', 'requires_auth' => true],

            // Event Join/Quit Routes
            ['method' => 'POST', 'path' => '/events/{id}/join', 'description' => 'Join a specific event', 'requires_auth' => true, 'body_params' => ['userId']],
            ['method' => 'POST', 'path' => '/events/{id}/quit', 'description' => 'Quit a specific event', 'requires_auth' => true, 'body_params' => ['userId']],

            // Wishlist Routes
            ['method' => 'POST', 'path' => '/events/{id}/wishlist/add', 'description' => 'Add an event to user wishlist', 'requires_auth' => true, 'body_params' => ['userId']],
            ['method' => 'POST', 'path' => '/events/{id}/wishlist/remove', 'description' => 'Remove an event from user wishlist', 'requires_auth' => true, 'body_params' => ['userId']],
            ['method' => 'GET', 'path' => '/events/wishlist', 'description' => 'List events in user wishlist', 'requires_auth' => true, 'query_params' => ['userId']],

            // Profile Routes
            ['method' => 'GET', 'path' => '/profiles/user/{id}', 'description' => 'Get profile for a specific user', 'requires_auth' => true],
            ['method' => 'PUT', 'path' => '/profiles/user/{id}', 'description' => 'Update or create profile for a specific user', 'requires_auth' => true, 'body_params' => ['firstName', 'lastName', 'bio', 'birthDate', 'avatarUrl']]
        ];
    }
}
