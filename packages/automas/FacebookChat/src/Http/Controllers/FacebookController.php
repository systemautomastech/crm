<?php

namespace Automas\FacebookChat\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Automas\FacebookChat\Models\FacebookChat;
use Automas\FacebookChat\Models\FacebookContact;

class FacebookController extends Controller
{
    protected $accessToken;

    public function sendFacebookMessageToContact(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'attachment' => [
                    'nullable',
                    'file',
                    'mimes:aac,m4a,wav,mp3,mp4,ogg,avi,mov,webm,png,jpeg,gif,pdf,doc,docx,xls,xlsx',
                    function ($attribute, $value, $fail) {
                        if ($value->isValid()) {
                            $extension = $value->getClientOriginalExtension();
                            $sizeInMB = $value->getSize() / 1024 / 1024;

                            $maxSizeLimits = [
                                'aac' => 25,
                                'm4a' => 25,
                                'wav' => 25,
                                'mp3' => 25,
                                'mp4' => 25,
                                'ogg' => 25,
                                'avi' => 25,
                                'mov' => 25,
                                'webm' => 25,
                                'png' => 8,
                                'jpeg' => 8,
                                'jpg' => 8,
                                'gif' => 8,
                            ];

                            if (isset($maxSizeLimits[$extension]) && $sizeInMB > $maxSizeLimits[$extension]) {
                                $fail("The {$attribute} must not be greater than {$maxSizeLimits[$extension]} MB.");
                            }
                        }
                    }
                ],
                'message' => 'required_without:attachment|max:999'
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $id = $request->contact_id;
        $facebookContact = FacebookContact::where('created_by', creatorId())->find($id);

        if (!$facebookContact) {
            return response()->json(['status' => 0, 'message' => __('Invalid Contact!')]);
        }

        $setting = getCompanyAllSetting();
        $this->accessToken = $setting['facebook_access_token'] ?? null;
        $attachmentUrl = null;

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $path = "facebook_files";
            $fileName = uniqid() . '.' . $attachment->getClientOriginalExtension();
            $uploadResult = upload_file($request, 'attachment', $fileName, $path);

            if ($uploadResult['flag'] == 1) {
                $attachmentUrl = $uploadResult['url'];
            }
        }


        $response = $this->sendMessage(
            $facebookContact->sender_id,
            $request->message,
            $request->file('attachment')
        );

        if ($response['status'] == 'success') {
            FacebookChat::create([
                'facebook_contact_id' => $facebookContact->id,
                'is_send' => 1,
                'is_seen' => 1,
                'message' => $request->message,
                'image_path' => $attachmentUrl
            ]);

            return response()->json(['status' => 1, 'message' => __('Message sent successfully')]);
        } else {
            if (!is_null($attachmentUrl)) {
                delete_file($attachmentUrl);
            }
            return response()->json(['status' => 0, 'message' => $response['data']]);
        }
    }

    public function sendMessage($recipient_id, $message, $attachment = null)
    {

        try {

            /* ================================
         | 1. ATTACHMENT FLOW (Upload API)
         ================================= */
            if (!is_null($attachment)) {

                // Detect attachment type
                $extension = strtolower(pathinfo($attachment->getClientOriginalName(), PATHINFO_EXTENSION));

                $typeMap = [
                    'jpg' => 'image',
                    'jpeg' => 'image',
                    'png' => 'image',
                    'gif' => 'image',
                    'mp4' => 'video',
                    'mov' => 'video',
                    'avi' => 'video',
                    'pdf' => 'file',
                    'doc' => 'file',
                    'docx' => 'file',
                    'xls' => 'file',
                    'xlsx' => 'file'
                ];

                $attachmentType = $typeMap[$extension] ?? 'file';

                /* Upload attachment to Facebook */
                $uploadResponse = Http::attach(
                    'filedata',
                    file_get_contents($attachment->getRealPath()),
                    $attachment->getClientOriginalName()
                )->post('https://graph.facebook.com/v21.0/me/message_attachments', [
                    'message' => json_encode([
                        'attachment' => [
                            'type' => $attachmentType,
                            'payload' => [
                                'is_reusable' => true
                            ]
                        ]
                    ]),
                    'access_token' => $this->accessToken
                ]);

                $uploadData = $uploadResponse->json();

                if (!isset($uploadData['attachment_id'])) {
                    return [
                        'status' => 'error',
                        'data' => $uploadData['error']['message'] ?? 'Attachment upload failed'
                    ];
                }

                $attachmentId = $uploadData['attachment_id'];

                /* Send attachment message */
                $sendResponse = Http::post('https://graph.facebook.com/v21.0/me/messages', [
                    'recipient' => ['id' => $recipient_id],
                    'message' => [
                        'attachment' => [
                            'type' => $attachmentType,
                            'payload' => [
                                'attachment_id' => $attachmentId
                            ]
                        ]
                    ],
                    'messaging_type' => 'RESPONSE',
                    'access_token' => $this->accessToken
                ]);

                return [
                    'status' => 'success',
                    'data' => $sendResponse->json()
                ];
            }

            /* ================================
         | 2. TEXT MESSAGE ONLY
         ================================= */
            if (!empty($message)) {

                $response = Http::post('https://graph.facebook.com/v21.0/me/messages', [
                    'recipient' => ['id' => $recipient_id],
                    'message' => ['text' => $message],
                    'messaging_type' => 'RESPONSE',
                    'access_token' => $this->accessToken
                ]);

                return [
                    'status' => 'success',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => 'error',
                'data' => __('Nothing to send')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'data' => $e->getMessage()
            ];
        }
    }
}
