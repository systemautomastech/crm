<?php

namespace Automas\FacebookChat\Listeners;

use App\Events\CreateMetaWebhook;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Pusher\Pusher;
use Automas\FacebookChat\Models\FacebookChat;
use Automas\FacebookChat\Models\FacebookContact;
use Illuminate\Support\Facades\Log;

class CreateMetaWebhookLis
{
    public function handle(CreateMetaWebhook $event)
    {
        $payload = $event->payload;
        $entries = $payload['entry'];

        if ($payload['object'] == 'page') {
            foreach ($entries as $entry) {
                if (!isset($entry['id']) || empty($entry['messaging'])) {
                    continue;
                }
                $requestClientId = $entry['id'];
                foreach ($entry['messaging'] as $messaging) {
                    $this->processMessage($messaging, $requestClientId);
                }
            }
        }
    }

    private function processMessage($messaging, $requestClientId)
    {
        $senderId = $messaging['sender']['id'] ?? null;
        $recipientId = $messaging['recipient']['id'] ?? null;
        $messageText = $messaging['message']['text'] ?? null;
        $attachments = $messaging['message']['attachments'] ?? [];

        if (!$senderId || !$requestClientId) {
            return;
        }

        $setting = Setting::where('key', 'facebook_client_id')
            ->where('value', $requestClientId)
            ->first();

        if (!$setting) {
            return;
        }

        $tokenSetting = company_setting('facebook_access_token', $setting->created_by);
        if (!$tokenSetting) {
            return;
        }

        $isOutGoing = ($senderId == $requestClientId);
        if ($isOutGoing) {
            return;
        }

        $facebookContact = FacebookContact::where([
            ['sender_id', $isOutGoing ? $recipientId : $senderId],
            ['created_by', $setting->created_by],
        ])->first();

        if (!$facebookContact) {
            $profileDetail = $this->getFacebookProfileDetails($senderId, $tokenSetting);

            $facebookContact = FacebookContact::create([
                'name' => $profileDetail['status'] == 1 ? $profileDetail['data']['name'] : 'User Name',
                'profile_image' => $profileDetail['status'] == 1 ? $profileDetail['data']['profile_pic'] : null,
                'user_name' => $profileDetail['status'] == 1 ? $profileDetail['data']['name'] : 'User Name',
                'sender_id' => $senderId,
                'creator_id' => $setting->created_by,
                'created_by' => $setting->created_by,
            ]);
        }

        if ($messageText) {
            $facebookChat = FacebookChat::create([
                'facebook_contact_id' => $facebookContact->id,
                'is_send' => $isOutGoing ? 1 : 0,
                'is_seen' => $isOutGoing ? 1 : 0,
                'message' => $messageText,
                'image_path' => null,
            ]);

            $this->broadcastMessage($facebookContact, $facebookChat, $isOutGoing);
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (!isset($attachment['type']) || !isset($attachment['payload']['url'])) {
                    continue;
                }
                $attachmentUrl = $attachment['payload']['url'];
                $this->handleImageAttachment($attachmentUrl, $facebookContact, $isOutGoing, $setting->created_by);
            }
        }
    }

    private function getFacebookProfileDetails($senderId, $token)
    {
        $url = "https://graph.facebook.com/{$senderId}?fields=name,profile_pic&access_token={$token}";
        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return ['status' => 1, 'data' => $data];
        } else {
            return ['status' => 0];
        }
    }

    private function handleImageAttachment($attachmentUrl, $facebookContact, $isOutGoing, $createdBy)
    {
        try {
            $fileContents = Http::get($attachmentUrl);

            if ($fileContents->successful()) {
                // Get content type from headers
                $contentType = $fileContents->header('Content-Type');

                // Extract extension from URL
                $urlExtension = pathinfo($attachmentUrl, PATHINFO_EXTENSION);

                // Determine proper extension
                $fileExtension = $this->getExtensionFromContentType($contentType) ?: ($urlExtension ?: 'jpg');
                $fileName = uniqid() . '.' . $fileExtension;

                $temporaryFile = UploadedFile::fake()->createWithContent($fileName, $fileContents->body());

                $path = "facebook_files";
                $request = new Request();
                $request->files->set('attachment', $temporaryFile);

                $uploadResult = upload_file($request, 'attachment', $fileName, $path);

                if ($uploadResult['flag'] == 1) {
                    $attachmentUrl = $uploadResult['url'];

                    $facebookChat = FacebookChat::create([
                        'facebook_contact_id' => $facebookContact->id,
                        'is_send' => $isOutGoing ? 1 : 0,
                        'is_seen' => $isOutGoing ? 1 : 0,
                        'message' => null,
                        'image_path' => $attachmentUrl,
                    ]);

                    $this->broadcastMessage($facebookContact, $facebookChat, $isOutGoing);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Facebook: Media handling failed', ['error' => $e->getMessage()]);
        }
    }

    private function broadcastMessage($facebookContact, $facebookChat, $isOutGoing)
    {
        try {
            $setting = getAdminAllSetting();

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

            $channel = "facebook-chat-" . $facebookContact->created_by;
            $event = 'facebook-chat-event-' . $facebookContact->created_by;

            $data = [
                'id' => $facebookContact->id,
                'facebook_sender_id' => $facebookContact->sender_id,
                'user_name' => !empty($facebookContact->name) ? $facebookContact->name : ($facebookContact->user_name ?? $facebookContact->sender_id),
                'message' => $facebookChat->message,
                'avatar' => $facebookContact->profile_image ?? (getImageUrlPrefix() . '/uploads/users-avatar/avatar.png'),
                'time' => \Carbon\Carbon::parse($facebookChat->created_at)->format('l h:i A'),
                'nav_time' => \Carbon\Carbon::parse($facebookChat->created_at)->format('h:i A'),
                'nav_date' => \Carbon\Carbon::parse($facebookChat->created_at)->format('d M'),
                'message_id' => $facebookChat->id,
                'is_send' => $isOutGoing,
                'image_path' => $facebookChat->image_path ? (getImageUrlPrefix() . '/' . $facebookChat->image_path) : null,
                'image_path_type' => $this->getImageType($facebookChat->image_path)
            ];

            $pusher->trigger($channel, $event, $data);
        } catch (\Exception $e) {
            \Log::error('Facebook Pusher: Broadcast failed', ['error' => $e->getMessage()]);
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

    private function getExtensionFromContentType($contentType)
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
            'video/quicktime' => 'mov',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
        ];

        return $extensions[$contentType] ?? null;
    }
}
