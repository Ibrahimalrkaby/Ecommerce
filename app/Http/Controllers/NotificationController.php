<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.notification.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $notification = Auth()->user()->notifications()->where('id', $request->id)->first();
        if ($notification) {
            $notification->markAsRead();
            return redirect($notification->data['actionURL']);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $notification = Notification::find($id);
        if ($notification) {
            $status = $notification->delete();
            if ($status) {
                request()->session()->flash('success', 'Notification successfully deleted');
                return back();
            } else {
                request()->session()->flash('error', 'Error please try again');
                return back();
            }
        } else {
            request()->session()->flash('error', 'Notification not found');
            return back();
        }
    }
}
