<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    // post for user
    public function postComment(Request $request)
    {
        // create comment from model and post it to database
        try {
            $rules = [
                'comment' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string'],
                'star' => ['required', 'integer'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
            }

            $data = $request->all();
            $comment = Comment::create([
                'comment' => $data['comment'],
                'name' => $data['name'],
                'star' => $data['star'],
                'blog_id' => $request->route('id'),
            ]);

            return ResponseFormatter::success([
                'comment' => $comment,
            ], 'Comment Created');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function showCommentByBlogId(Request $request)
    {
        try {
            $comment = Comment::where('blogs_id', '=', $request->route('blogId'))->get();
            return ResponseFormatter::success([
                'comment' => $comment,
            ], 'Comment Found');
        } catch (ModelNotFoundException $e) {
            return ResponseFormatter::error([
                'message' => 'Comment not found',
                'error' => $e,
            ], 'Comment Not Found', 404);
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Internal Server Error', 500);
        }
    }

    // admin side
    public function showAllComment()
    {
        try {
            $comments = Comment::with(['blog'])->get();
            return ResponseFormatter::success([
                'comments' => $comments,
            ], 'Comments Found');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }

    public function deleteComment(Request $request)
    {
        try {
            $comment = Comment::findOrFail($request->route('id'));
            $comment->delete();
            return ResponseFormatter::success([
                'message' => 'Comment deleted',
            ], 'Comment Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Error', 500);
        }
    }
}
