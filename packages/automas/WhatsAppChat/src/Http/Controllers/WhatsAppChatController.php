<?php

namespace Automas\WhatsAppChat\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Automas\WhatsAppChat\Models\WhatsappContact;

class WhatsAppChatController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-whatsapp-chat')) {
            $contacts = WhatsappContact::leftjoin('whatsapp_chats', 'whatsapp_contacts.id', '=', 'whatsapp_chats.whatsapp_contact_id')
                ->select(
                    'whatsapp_contacts.id',
                    'whatsapp_contacts.contact_no',
                    'whatsapp_contacts.name',
                    'whatsapp_contacts.user_id',
                    'whatsapp_contacts.created_by',
                    DB::raw('COUNT(CASE WHEN whatsapp_chats.is_seen = 0 THEN 1 END) as unseen_count'),
                    DB::raw('(SELECT message FROM whatsapp_chats WHERE whatsapp_contacts.id = whatsapp_chats.whatsapp_contact_id ORDER BY created_at DESC LIMIT 1) as last_message'),
                    DB::raw('(SELECT created_at FROM whatsapp_chats WHERE whatsapp_contacts.id = whatsapp_chats.whatsapp_contact_id ORDER BY created_at DESC LIMIT 1) as last_message_time')
                )
                ->groupBy(
                    'whatsapp_contacts.id',
                    'whatsapp_contacts.contact_no',
                    'whatsapp_contacts.name',
                    'whatsapp_contacts.user_id',
                    'whatsapp_contacts.created_by'
                )
                ->orderByDesc(DB::raw('(SELECT created_at FROM whatsapp_chats WHERE whatsapp_contacts.id = whatsapp_chats.whatsapp_contact_id ORDER BY created_at DESC LIMIT 1)'))
                ->where('whatsapp_contacts.created_by', creatorId())
                ->get();

            // Get users for initialization form
            $users = collect();
            User::where('created_by', creatorId())
                ->get()
                ->map(function ($user) use ($users) {
                    $phoneNo = str_replace(['+', ' ', '-'], '', $user->mobile_no ?? '');
                    $users[$user->id] = "$user->name - $phoneNo";
                });
            

            return Inertia::render('WhatsAppChat/WhatsAppChat/Index', [
                'contacts' => $contacts,
                'users' => $users,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function getChat(Request $request)
    {
        $id = $request->contact_id;
        $whatsAppContact = WhatsappContact::where('created_by', creatorId())
            ->find($id);
        
        if (!$whatsAppContact) {
            return response()->json(['status' => 0, 'message' => __('Invalid Contact!')]);
        }

        $chats = $whatsAppContact->whatsapp_chat()->orderBy('created_at')->get();
        
        return response()->json([
            'status' => 1,
            'contact' => $whatsAppContact,
            'chats' => $chats
        ]);
    }
    
    public function markAsSeen(Request $request)
    {
        $id = $request->contact_id;
        $whatsAppContact = WhatsappContact::where('created_by', creatorId())->find($id);
        
        if ($whatsAppContact) {
            $whatsAppContact->whatsapp_chat()->where('is_seen', 0)->update(['is_seen' => 1]);
        }
        
        return response()->json(['status' => 1]);
    }
    
    public function updateName(Request $request)
    {
        $id = $request->contact_id;
        $name = $request->name;
        
        $whatsAppContact = WhatsappContact::where('created_by', creatorId())->find($id);
        
        if ($whatsAppContact) {
            $whatsAppContact->update(['name' => $name]);
            return response()->json(['status' => 1, 'message' => __('The name are updated successfully!')]);
        }
        
        return response()->json(['status' => 0, 'message' => __('Contact not found!')]);
    }
}
