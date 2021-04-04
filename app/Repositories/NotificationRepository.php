<?php

namespace App\Repositories;

use App\Models\Eloquent\Notification;

/**
 * Class NotificationRepository
 * @package App\Repositories
 *
 *
 * TODO: add multi-tenancy
 */
class NotificationRepository extends EloquentRepository
{
    public function __construct(Notification $notification)
    {
        parent::__construct($notification);
    }

    /**
     * Get notifications by user ID
     *
     * @param integer $userId
     * @param boolean $read
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function getForUser($userId, $read, $limit = 20, $offset = 0)
    {
        $query = $this->model->select('url', 'message', 'id', 'read')->where('user_id', $userId);

        if (!is_null($read)) {
            $query->where('read', $read);
        }

        $notifications = $query->take($limit)->skip($offset)->orderBy('created_at', 'desc')->get();

        $unreadCount = $this->model->where('user_id', $userId)->where('read', 0)->count();

        if (!empty($notifications)) {
            return ['unread' => $unreadCount, 'data' => $notifications->toArray()];
        }

        return ['unread' => 0, 'data' => []];
    }

    /**
     * Mark a specific notification as read/unread
     *
     * @param integer $notificationId
     * @param string $status
     *
     * @return boolean
     */
    public function setRead($notificationId, $status)
    {
        $notification = $this->getById($notificationId);

        if (empty($notification)) {
            return false;
        }

        $notification = $notification->toArray();
        $notification['read'] = $status;

        return $this->update($notificationId, $notification);
    }

    /**
     * Get the latest notification with a certain uri
     * for a specific user
     *
     * @param string $uri
     * @param integer $userId
     *
     * @return array
     */
    public function getLatestByUri($uri, $userId)
    {
        $notification = $this->model->whereRaw(
            "created_at = (select max(created_at) from notifications where user_id= ? AND url= ?)",
            [$userId, $uri]
        )->first();

        if (!empty($notification)) {
            return $notification->toArray();
        }

        return [];
    }
}
