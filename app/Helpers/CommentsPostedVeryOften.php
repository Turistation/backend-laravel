<?php

namespace App\Helpers;

use App\Models\Comment;
use App\User;
use Carbon\Carbon;
use Exception;

class CommentsPostedVeryOften
{
    /**
     * Detect spam
     *
     * @param  string $body
     * @throws \Exception
     */
    public function detect($requestedIp, $userAgent)
    {
        // where ip address and user agent
        $comment = Comment::where('ip_address', $requestedIp)
            ->orWhere('user_agent', $userAgent)
            ->latest()
            ->first();

        if ($comment) {
            $data = $this->prepareCommonData($comment);
            if ($comment->canUserPostComment($data)) {
                throw new Exception("You can post only once in {$data["userCommentFrequency"]} seconds.");
            }
        }
    }

    /**
     * Prepare common data
     *
     * @param  Collection $latestComment
     * @return array
     */
    public function prepareCommonData($latestComment)
    {
        return [
            'latestCommentCreated' => new Carbon($latestComment->created_at),
            'userCommentFrequency' => config('app.spam_detection.user_can_comment_once_in'),
        ];
    }
}
