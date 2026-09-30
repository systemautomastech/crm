<?php

namespace Automas\WhatsAppChat\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;
use Automas\WhatsAppChat\Models\WhatsappChat;
use Automas\WhatsAppChat\Models\WhatsappContact;

class WhatsAppController extends Controller
{
    public $accessToken;
    public $phoneNumberId;

    public function sendWhatsappChatMessage(Request $request)
    {
        $setting = getCompanyAllSetting();

        if (!isset($setting['whatsappchat_phone_number_id'], $setting['whatsappchat_access_token']) || empty($setting['whatsappchat_phone_number_id']) || empty($setting['whatsappchat_access_token'])) {
            return redirect()->back()->with('error', __('Please add the first credential in Settings.'));
        }

        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'required|in:choose_from_users,custom',
                'user_id' => ['required_if:type,choose_from_users', function ($attribute, $value, $fail) {
                    $user = User::where('created_by', creatorId())->find($value);
                    if (!$user) {
                        $fail("Please select Valid User!");
                    }
                }],
                'name' => 'nullable|max:255',
                'contact_no' => 'required',
                'message' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $this->phoneNumberId = $setting['whatsappchat_phone_number_id'] ?? '';
        $this->accessToken = $setting['whatsappchat_access_token'] ?? '';

        $response = $this->sendMessage($request->contact_no, $request->message);
        if ($response['status'] == 'success') {
            $whatsappContact = WhatsappContact::where('contact_no', $request->contact_no)->first();
            if (!$whatsappContact) {
                // Get user name if type is choose_from_users
                $userName = null;
                if ($request->type == 'choose_from_users' && $request->user_id) {
                    $user = User::find($request->user_id);
                    $userName = $user ? $user->name : null;
                }

                $whatsappContact = WhatsappContact::create([
                    'contact_no' => $request->contact_no,
                    'user_id' => $request->type == 'choose_from_users' ? $request->user_id : null,
                    'name' => $request->type == 'custom' ? $request->name : $userName,
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId(),
                ]);
            }

            WhatsappChat::create([
                'whatsapp_contact_id' => $whatsappContact->id,
                'is_send' => 1,
                'is_seen' => 1,
                'message' => $request->message,
                'creator_id' => Auth::id(),
                'created_by' => creatorId(),
            ]);

            return redirect()->back()->with('success', __('Message Send Successfully!'));
        } else {
            return redirect()->back()->with('error', $response['data']);
        }
    }

    public function receiveMessage(Request $request, $slug)
    {

        if (isset($request->hub_mode) && $request->hub_mode == 'subscribe') {
            Log::info('WhatsApp Webhook: Verification request');
            return response($request->hub_challenge, 200);
        }

        $user = User::where('slug', $slug)->first();
        if (!$user) {
            Log::error('WhatsApp Webhook: User not found', ['slug' => $slug]);
            return response()->json(['status' => 0, 'message' => 'Failed']);
        }

        Log::info('WhatsApp Webhook: User found', ['user_id' => $user->id, 'user_name' => $user->name]);

        $setting = Setting::where('key', 'whatsappchat_access_token')->where('created_by', $user->id)->first();
        if (!$setting) {
            return;
        }

        $this->accessToken = $setting->value;
        $data = $request->all();

        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
            Log::info('WhatsApp Webhook: Processing message', ['message' => $message]);

            $sender = $message['from'];
            $type = $message['type'];

            // Check if mobile number exists in users table
            $cleanSender = str_replace(['+', ' ', '-'], '', $sender);
            $existingUser = User::where('created_by', $user->id)
                ->where(function ($query) use ($cleanSender, $sender) {
                    $query->where('mobile_no', $sender)
                        ->orWhere('mobile_no', $cleanSender)
                        ->orWhere('mobile_no', '+' . $cleanSender);
                })
                ->first();

            $whatsappContact = WhatsappContact::where('contact_no', $sender)->where('created_by', $user->id)->first();
            if (!$whatsappContact) {
                $whatsappContact = WhatsappContact::create([
                    'contact_no' => $sender,
                    'user_id' => $existingUser ? $existingUser->id : null,
                    'name' => $existingUser ? $existingUser->name : null,
                    'created_by' => $user->id,
                ]);
            } else {
                Log::info('WhatsApp Webhook: Using existing contact', ['contact_id' => $whatsappContact->id]);
            }

            if (in_array($type, ['image', 'document', 'video'])) {
                $caption = $message['caption'] ?? '';
                $this->handleMediaMessage($whatsappContact, $message, $caption);
            } elseif ($type === 'text') {
                $text = $message['text']['body'];
                $this->handleTextMessage($text, $whatsappContact);
            }

            return response()->json(['status' => 1, 'message' => 'Message received successfully']);
        } else {
            Log::warning('WhatsApp Webhook: No message found in request data');
        }
    }

    private function handleMediaMessage($whatsappContact, $message, $caption = '')
    {
        try {
            $mediaType = null;
            if (isset($message['image'])) {
                $mediaType = 'image';
            } elseif (isset($message['document'])) {
                $mediaType = 'document';
            } elseif (isset($message['video'])) {
                $mediaType = 'video';
            }

            if ($mediaType) {
                $mediaId = $message[$mediaType]['id'];
                Log::info('WhatsApp: Downloading media', ['media_id' => $mediaId, 'type' => $mediaType]);

                $uploadedFile = $this->downloadMedia($mediaId, $mediaType);

                if ($uploadedFile) {
                    // Create a proper UploadedFile with correct MIME type
                    $mimeType = $message[$mediaType]['mime_type'] ?? '';
                    $extension = $this->getExtensionFromMimeType($mimeType, $mediaType);
                    $fileName = uniqid() . '.' . $extension;

                    // Create new UploadedFile with correct MIME type
                    $properFile = new \Illuminate\Http\UploadedFile(
                        $uploadedFile->getPathname(),
                        $fileName,
                        $mimeType,
                        null,
                        true
                    );

                    $request = new \Illuminate\Http\Request();
                    $request->files->set('file', $properFile);

                    $path = "whatsapp_images";
                    $uploadResult = upload_file($request, 'file', $fileName, $path);

                    if ($uploadResult['flag'] == 1) {
                        $fileUrl = $uploadResult['url'];
                        Log::info('WhatsApp: Media uploaded successfully', ['file_url' => $fileUrl]);
                        $this->processAttachment($whatsappContact, $fileUrl, $caption);
                    } else {
                        Log::error('WhatsApp: Media upload failed', ['upload_result' => $uploadResult]);
                    }
                } else {
                    Log::error('WhatsApp: Media download failed', ['media_id' => $mediaId]);
                }
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp: Media handling failed', ['error' => $e->getMessage()]);
        }
    }

    private function downloadMedia($mediaId, $mediaType)
    {
        try {
            $url = "https://graph.facebook.com/v21.0/{$mediaId}";
            $response = Http::withToken($this->accessToken)->get($url);

            if ($response->successful()) {
                $mediaUrl = $response->json()['url'];
                Log::info('WhatsApp: Got media URL', ['media_url' => $mediaUrl]);

                $mediaResponse = Http::withToken($this->accessToken)->get($mediaUrl);

                if ($mediaResponse->successful()) {
                    $tempPath = sys_get_temp_dir() . '/temp_' . uniqid() . '.' . $mediaType;
                    file_put_contents($tempPath, $mediaResponse->body());

                    if (file_exists($tempPath) && filesize($tempPath) > 0) {
                        return new \Illuminate\Http\UploadedFile(
                            $tempPath,
                            basename($tempPath),
                            mime_content_type($tempPath),
                            null,
                            true
                        );
                    } else {
                        Log::error('WhatsApp: Downloaded file is empty or missing', ['temp_path' => $tempPath]);
                    }
                } else {
                    Log::error('WhatsApp: Media download failed', ['status' => $mediaResponse->status(), 'body' => $mediaResponse->body()]);
                }
            } else {
                Log::error('WhatsApp: Media URL fetch failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp: Media download exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function processAttachment($whatsappContact, $fileUrl, $caption = '')
    {
        $whatsappChat = WhatsappChat::create([
            'whatsapp_contact_id' => $whatsappContact->id,
            'is_send' => 0,
            'is_seen' => 0,
            'message' => $caption,
            'image_path' => $fileUrl,
            'creator_id' => $whatsappContact->created_by,
            'created_by' => $whatsappContact->created_by,
        ]);

        $this->broadcastMessage($whatsappContact, $whatsappChat);
    }

    private function handleTextMessage($text, $whatsappContact)
    {
        $whatsappChat = WhatsappChat::create([
            'whatsapp_contact_id' => $whatsappContact->id,
            'is_send' => 0,
            'is_seen' => 0,
            'message' => $text,
            'creator_id' => $whatsappContact->created_by,
            'created_by' => $whatsappContact->created_by,
        ]);

        $this->broadcastMessage($whatsappContact, $whatsappChat);
    }

    public function sendMessage($recipientPhone, $message, $attachmentUrl = null)
    {
        $url = "https://graph.facebook.com/v21.0/{$this->phoneNumberId}/messages";
        $client = new Client();
        $responses = [];

        if ($attachmentUrl) {
            $fullPath = rtrim(getImageUrlPrefix(), '/') . '/' . ltrim($attachmentUrl, '/');
            $extension = pathinfo($attachmentUrl, PATHINFO_EXTENSION);
            $fileType = $this->getMimeTypeFromExtension($extension);

            try {
                $mediaType = $this->getMediaType($fileType);
                $uploadUrl = "https://graph.facebook.com/v21.0/{$this->phoneNumberId}/media";

                $fileContent = file_get_contents($fullPath);
                $tempFileName = 'temp_' . uniqid() . '.' . pathinfo($attachmentUrl, PATHINFO_EXTENSION);
                $tempPath = sys_get_temp_dir() . '/' . $tempFileName;
                file_put_contents($tempPath, $fileContent);

                    $uploadResponse = $client->post($uploadUrl, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->accessToken,
                        ],
                        'multipart' => [
                            [
                                'name' => 'messaging_product',
                                'contents' => 'whatsapp',
                            ],
                            [
                                'name' => 'file',
                                'contents' => fopen($tempPath, 'r'),
                            ],
                            [
                                'name' => 'type',
                                'contents' => $mediaType,
                            ]
                        ],
                    ]);
                    
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }                

                $uploadData = json_decode($uploadResponse->getBody()->getContents(), true);
                $mediaId = $uploadData['id'];
            } catch (\Exception $e) {
                return ['status' => 'error', 'data' => 'Attachment upload failed: ' . $e->getMessage()];
            }

            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientPhone,
                'type' => $mediaType,
                $mediaType => [
                    'id' => $mediaId,
                ],
            ];

            if ($mediaType === 'document' && !empty($message)) {
                $data[$mediaType]['caption'] = $message;
            }

            try {
                $mediaResponse = $client->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $data,
                ]);
                $responses['media'] = json_decode($mediaResponse->getBody()->getContents(), true);
            } catch (\Exception $e) {
                return ['status' => 'error', 'data' => 'Media sending failed: ' . $e->getMessage()];
            }
        }

        if ($message && empty($attachmentUrl)) {
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientPhone,
                'type' => 'text',
                'text' => ['body' => $message],
            ];

            try {
                $textResponse = $client->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $data,
                ]);
                $responses['text'] = json_decode($textResponse->getBody()->getContents(), true);
            } catch (\Exception $e) {
                return ['status' => 'error', 'data' => 'Text sending failed: ' . $e->getMessage()];
            }
        }

        return ['status' => 'success', 'data' => $responses];
    }

    private function getMediaType($mimeType)
    {
        if (strpos($mimeType, 'image') !== false) {
            return 'image';
        } elseif (strpos($mimeType, 'video') !== false) {
            return 'video';
        } elseif (strpos($mimeType, 'pdf') !== false || strpos($mimeType, 'msword') !== false || strpos($mimeType, 'spreadsheetml') !== false) {
            return 'document';
        } else {
            return 'document';
        }
    }

    public function sendMessageToContact(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'attachment' => 'file|mimes:png,jpeg,gif,aac,m4a,wav,mp4,ogg,avi,mov,webm',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $id = $request->contact_id;
        $whatsAppContact = WhatsappContact::where('created_by', creatorId())->find($id);

        if (!$whatsAppContact) {
            return response()->json(['status' => 0, 'message' => __('Invalid Contact!')]);
        }

        $setting = getCompanyAllSetting();

        $this->phoneNumberId = $setting['whatsappchat_phone_number_id'] ?? '';
        $this->accessToken = $setting['whatsappchat_access_token'] ?? '';
        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $path = "whatsapp_images";
            $fileName = uniqid() . '.' . $attachment->getClientOriginalExtension();

            $uploadResult = upload_file($request, 'attachment', $fileName, $path);

            if ($uploadResult['flag'] == 1) {
                $attachmentUrl = $uploadResult['url'];
            }
        }

        if (empty($request->message) && is_null($attachmentUrl)) {
            return response()->json(['status' => 0, 'message' => __('Image or Message required!')]);
        }
        $response = $this->sendMessage($whatsAppContact->contact_no, $request->message, $attachmentUrl);
        if ($response['status'] == 'success') {
            $whatsappChat = WhatsappChat::create([
                'whatsapp_contact_id' => $whatsAppContact->id,
                'is_send' => 1,
                'is_seen' => 1,
                'message' => $request->message,
                'image_path' => $attachmentUrl,
                'creator_id' => Auth::id(),
                'created_by' => creatorId(),
            ]);

            // Broadcast message via Pusher
            $this->broadcastMessage($whatsAppContact, $whatsappChat);

            return response()->json(['status' => 1, 'message' => __('Message sent successfully!')]);
        } else {
            if (!is_null($attachmentUrl)) {
                delete_file($attachmentUrl);
            }
            return response()->json(['status' => 0, 'message' => $response['data']]);
        }
    }

    private function broadcastMessage($whatsAppContact, $whatsappChat)
    {
        try {
            $setting = getAdminAllSetting();

            // Check if Pusher settings exist
            if (empty($setting['pusher_app_key']) || empty($setting['pusher_app_secret']) || empty($setting['pusher_app_id']) || empty($setting['pusher_app_cluster'])) {
                return;
            }

            $options = [
                'cluster' => $setting['pusher_app_cluster'],
                'useTLS' => true,
            ];
            $pusher = new Pusher(
                $setting['pusher_app_key'],
                $setting['pusher_app_secret'],
                $setting['pusher_app_id'],
                $options
            );

            $channel = "whatsapp-chat-" . $whatsAppContact->created_by;
            $event = 'whatsapp-chat-event-' . $whatsAppContact->created_by;

            $data = [
                'id' => $whatsAppContact->id,
                'whatsapp_contact_no' => $whatsAppContact->contact_no,
                'user_name' => is_null($whatsAppContact->name) ? optional($whatsAppContact->user)->name ?? $whatsAppContact->contact_no : $whatsAppContact->name,
                'message' => $whatsappChat->message,
                'avatar' => optional($whatsAppContact->user)->avatar ? getImageUrlPrefix() . '/' . optional($whatsAppContact->user)->avatar : getImageUrlPrefix() . '/' . 'avatar.png',
                'time' => \Carbon\Carbon::parse($whatsappChat->created_at)->format('l h:i A'),
                'message_id' => $whatsappChat->id,
                'nav_time' => \Carbon\Carbon::parse($whatsappChat->created_at)->format('h:i A'),
                'nav_date' => \Carbon\Carbon::parse($whatsappChat->created_at)->format('d M'),
                'image_path' => $whatsappChat->image_path ? getImageUrlPrefix() . '/' . $whatsappChat->image_path : null,
                'image_path_type' => $this->getImageType($whatsappChat->image_path),
                'is_send' => $whatsappChat->is_send
            ];

            $result = $pusher->trigger($channel, $event, $data);
            Log::info('WhatsApp Pusher: Message sent successfully', [
                'channel' => $channel,
                'event' => $event,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp Pusher: Broadcast failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function getImageType($image_path)
    {
        if (is_null($image_path)) {
            return [
                'is_image' => false,
                'is_video' => false,
                'is_document' => false
            ];
        }

        $extension = pathinfo($image_path, PATHINFO_EXTENSION);
        $fileMimeType = $this->getMimeTypeFromExtension($extension);

        $isImage = str_starts_with($fileMimeType, 'image/');
        $isVideo = str_starts_with($fileMimeType, 'video/');
        $isDocument = !$isImage && !$isVideo;

        return [
            'is_image' => $isImage,
            'is_video' => $isVideo,
            'is_document' => $isDocument
        ];
    }

    private function getMimeTypeFromExtension($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'avi' => 'video/avi',
            'mov' => 'video/mov',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    private function getExtensionFromMimeType($mimeType, $mediaType)
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            'video/avi' => 'avi',
            'video/mov' => 'mov'
        ];

        return $extensions[$mimeType] ?? $mediaType;
    }
}
