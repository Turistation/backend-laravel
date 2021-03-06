<?php

namespace App\Http\Controllers\API;

use App\Helpers\CommentException;
use App\Helpers\CommentsPostedVeryOften;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Inspections\Spam;
use App\Models\Comment;
use Error;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    protected $checkComment;
    // make construct and add CommentsPostedVeryOften::class->detect($request->ip());
    public function __construct()
    {
        $this->checkComment = new CommentsPostedVeryOften();
    }
    // post for user
    public function postComment(Request $request)
    {
        // create comment from model and post it to database
        try {
            $rules = [
                'comment' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string'],
                'star' => ['required', 'integer'],
                'blogs_id' => ['required', 'integer'],
                'ip' => ['required', 'string'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->all(), 'Validation Error', 422);
            }

            $data = $request->all();

            $this->checkComment->detect($data['ip'], $data['blogs_id']);

            $data['ip_address'] = $data['ip'];
            $comment = Comment::create($data);

            return ResponseFormatter::success([
                'comment' => $comment,
            ], 'Comment Created');
        } catch (CommentException $e) {
            return ResponseFormatter::error([
                'message' => 'bad request',
                'error' => $e->getMessage(),
            ], 'Bad Request', 400);
        } catch (\Throwable $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Server Error', 500);
        }
    }

    public function showCommentByBlogId(Request $request)
    {
        try {
            $comment = Comment::where('blogs_id', '=', $request->route('blogId'))->get();
            return ResponseFormatter::success([
                'comments' => $comment,
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
