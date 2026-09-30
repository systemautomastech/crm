<?php

namespace App\Http\Controllers;

use App\Events\CreateHelpdeskReply;
use App\Events\DestroyHelpdeskReply;
use App\Http\Requests\StoreHelpdeskReplyRequest;
use App\Models\EmailTemplate;
use App\Models\HelpdeskReply;
use App\Models\HelpdeskTicket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HelpdeskReplyController extends Controller
{
    public function store(StoreHelpdeskReplyRequest $request, HelpdeskTicket $ticket)
    {
        if(Auth::user()->can('create-helpdesk-replies')){
            $validated = $request->validated();

            $reply = new HelpdeskReply();
            $reply->ticket_id = $ticket->id;
            $reply->message = $validated['message'];

            // Handle multiple attachments
            if (isset($validated['attachments']) && $validated['attachments']) {
                $attachmentPaths = is_array($validated['attachments']) ? $validated['attachments'] : [$validated['attachments']];
                $filenames = array_map('basename', array_filter($attachmentPaths));
                $reply->attachments = !empty($filenames) ? json_encode($filenames) : null;
            }

            $reply->is_internal = $request->boolean('is_internal', false);
            $reply->created_by = Auth::id();
            $reply->save();

            CreateHelpdeskReply::dispatch($request, $reply);

            // Ensure attachments is always an array for frontend
            $replyData = $reply->load('creator')->toArray();
            if (isset($replyData['attachments']) && is_string($replyData['attachments'])) {
                $replyData['attachments'] = json_decode($replyData['attachments'], true) ?: [];
            }
            try {
                $adminUser = User::where('type', 'superadmin')->first();

                if(admin_setting('Helpdesk Ticket Reply') == 'on') {
                    if(Auth::user()->type === 'superadmin') {
                        $ticketCreator = User::find($ticket->created_by);
                        $toEmail = company_setting('company_email', $ticket->created_by) ?: $ticketCreator?->email;
                        $name = company_setting('company_name', $ticket->created_by) ?: $ticketCreator?->name;
                    } else {
                        $toEmail = $adminUser->email;
                        $name = $adminUser->name;
                    }

                    $emailData = [
                        'company_name' => $name,                         
                        'ticket_name' => $ticket->title ?? null,
                        'ticket_id' => $reply->ticket_id ?? null,
                        'ticket_url' => route('helpdesk-tickets.show', $ticket->id) ?? null,
                        'ticket_description' => strip_tags($reply->message ?? ''),
                    ];
                    
                    $message = EmailTemplate::sendEmailTemplate('Helpdesk Ticket Reply', [$toEmail], $emailData, $adminUser->id);
                    if($message['is_success'] == false && !empty($message['error'])) {
                        return back()
                            ->with('success', __('Reply added successfully.'))
                            ->with('error', $message['error']);
                    }
                }
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => __('Reply added successfully'),
                'reply' => $replyData
            ]);
        }
        else{
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }

    public function destroy($id)
    {
        if(Auth::user()->can('delete-helpdesk-replies')){
            $helpdeskReply = HelpdeskReply::find($id);
            DestroyHelpdeskReply::dispatch($helpdeskReply);
            $helpdeskReply->delete();

            session()->flash('success', __('Reply deleted successfully'));

            return response()->json([
                'success' => true,
                'message' => __('Reply deleted successfully')
            ]);
        }
        else{
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }
}
