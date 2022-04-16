<?php

namespace App\Helpers;

use App\Models\Comment;
use Carbon\Carbon;

class CommentsPostedVeryOften
{
    /**
     * Detect spam
     *
     * @param  string $body
     * @throws \CommentException
     */
    public function detect($requestedIp, $blogId)
    {
        // where ip address and user agent
        $comment = Comment::where('ip_address', $requestedIp)->where('blogs_id', $blogId)
            ->latest()
            ->first();

        if ($comment) {
            $data = $this->prepareCommonData($comment);
            if ($comment->canUserPostComment($data)) {
                throw new CommentException("You can post only once in {$data["userCommentFrequency"]} seconds.");
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
