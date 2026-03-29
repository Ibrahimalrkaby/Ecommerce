<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Notifications\StatusNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PostCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = PostComment::getAllComments();
        return view('backend.comment.index')->with('comments', $comments);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.comment.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation: We validate the 'post_slug' instead of 'post_id'
        $request->validate([
            'post_slug' => 'required|exists:posts,slug',
            'comment'   => 'required|string',
        ]);

        // 2. Use your custom function to get the post info
        $post_info = Post::getPostBySlug($request->post_slug);

        // 3. Prepare the data
        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['post_id'] = $post_info->id; // Assign the ID found via the slug
        $data['status']  = 'active';

        // 4. Generate the unique slug for the COMMENT
        // We create the slug from the first 30 characters of the comment
        $slug = Str::slug(Str::limit($request->comment, 30));

        // Check if this comment slug already exists in the PostComment table
        $count = PostComment::where('slug', $slug)->count();

        if ($count > 0) {
            // If it exists, append timestamp and random number for uniqueness
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }

        $data['slug'] = $slug;

        // 5. Save the comment
        $status = PostComment::create($data);
        $user = User::where('role', 'admin')->get();
        $details = [
            'title' => "New Comment created",
            'actionURL' => route('blog.detail', $post_info->slug),
            'fas' => 'fas fa-comment'
        ];
        Notification::send($user, new StatusNotification($details));

        // 6. Response
        if ($status) {
            request()->session()->flash('success', 'Thank you for your comment');
        } else {
            request()->session()->flash('error', 'Something went wrong! Please try again!!');
        }

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(PostComment $postComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $comments = PostComment::find($id);
        if ($comments) {
            return view('backend.comment.edit')->with('comment', $comments);
        } else {
            request()->session()->flash('error', 'Comment not found');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $comment = PostComment::find($id);

        if ($comment) {
            // 1. Validation Logic
            $request->validate([
                'comment' => 'required|string|min:5',
                'status'  => 'required|in:active,inactive',
                // If you are updating the slug as well:
                // 'slug' => ['nullable', Rule::unique('post_comments')->ignore($id)],
            ]);

            $data = $request->all();

            // 2. Slug Logic: Only update slug if the comment text has changed
            if ($request->has('comment') && $request->comment !== $comment->comment) {
                $slug = Str::slug(Str::limit($request->comment, 30));

                // Check if this slug exists elsewhere
                $count = PostComment::where('slug', $slug)->where('id', '!=', $id)->count();
                if ($count > 0) {
                    $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
                }
                $data['slug'] = $slug;
            }

            // 3. Update the record
            $status = $comment->fill($data)->save(); // Use save() or update()

            if ($status) {
                request()->session()->flash('success', 'Comment successfully updated');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!!');
            }

            return redirect()->route('comment.index');
        } else {
            request()->session()->flash('error', 'Comment not found');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $comment = PostComment::find($id);
        if ($comment) {
            $status = $comment->delete();
            if ($status) {
                request()->session()->flash('success', 'Post Comment successfully deleted');
            } else {
                request()->session()->flash('error', 'Error occurred please try again');
            }
            return back();
        } else {
            request()->session()->flash('error', 'Post Comment not found');
            return redirect()->back();
        }
    }
}
