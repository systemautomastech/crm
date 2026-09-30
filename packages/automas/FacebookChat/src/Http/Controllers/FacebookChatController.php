<?php

namespace Automas\FacebookChat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Automas\FacebookChat\Models\FacebookContact;

class FacebookChatController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-facebook-chat')) {
            $contacts = FacebookContact::leftjoin('facebook_chats', 'facebook_contacts.id', '=', 'facebook_chats.facebook_contact_id')
                ->select(
                    'facebook_contacts.id',
                    'facebook_contacts.name',
                    'facebook_contacts.user_name',
                    'facebook_contacts.sender_id',
                    'facebook_contacts.created_by',
                    DB::raw('COUNT(CASE WHEN facebook_chats.is_seen = 0 THEN 1 END) as unseen_count'),
                    DB::raw('(SELECT message FROM facebook_chats WHERE facebook_contacts.id = facebook_chats.facebook_contact_id ORDER BY created_at DESC LIMIT 1) as last_message'),
                    DB::raw('(SELECT created_at FROM facebook_chats WHERE facebook_contacts.id = facebook_chats.facebook_contact_id ORDER BY created_at DESC LIMIT 1) as last_message_time')
                )
                ->groupBy(
                    'facebook_contacts.id',
                    'facebook_contacts.name',
                    'facebook_contacts.user_name',
                    'facebook_contacts.sender_id',
                    'facebook_contacts.created_by'
                )
                ->orderByDesc(DB::raw('(SELECT created_at FROM facebook_chats WHERE facebook_contacts.id = facebook_chats.facebook_contact_id ORDER BY created_at DESC LIMIT 1)'))
                ->where('facebook_contacts.created_by', creatorId())
                ->get();

            return Inertia::render('FacebookChat/FacebookChat/Index', [
                'contacts' => $contacts,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function getChat(Request $request)
    {
        $id = $request->contact_id;
        $facebookContact = FacebookContact::where('created_by', creatorId())
            ->find($id);
        
        if (!$facebookContact) {
            return response()->json(['status' => 0, 'message' => __('Invalid Contact!')]);
        }

        $chats = $facebookContact->chats()->orderBy('created_at')->get();
        
        return response()->json([
            'status' => 1,
            'contact' => $facebookContact,
            'chats' => $chats
        ]);
    }
    
    public function markAsSeen(Request $request)
    {
        $id = $request->contact_id;
        $facebookContact = FacebookContact::where('created_by', creatorId())->find($id);
        
        if ($facebookContact) {
            $facebookContact->chats()->where('is_seen', 0)->update(['is_seen' => 1]);
        }
        
        return response()->json(['status' => 1]);
    }
}