<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Auth;

class ChatingController extends Controller
{

    protected $database;

    public function __construct()
    {
        $this->database = (new Factory)
            ->withServiceAccount(base_path('storage/app/firebase/credentials.json'))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'))
            ->createDatabase();
    }

    // 🔹 Show default chat page

    public function chatDefault()
    {
        $users = User::all();
        $authUser = auth()->user();

        if ($authUser) {
            $user = $authUser;
            $chatExists = Message::where(function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $authUser->id);
            })->exists();

            $messages = Message::where(function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $authUser->id);
            })->orderBy('created_at', 'asc')->get();
        } else {
            $user = null;
            $chatExists = false;
            $messages = [];
        }

        return view('chating.chat', compact('user', 'users', 'chatExists', 'messages'));
    }

    // 🔹 Show chat page with selected user

    public function chat($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $authUser = auth()->user();

        $users = User::whereHas('sentMessages', function ($query) use ($authUser) {
            $query->where('receiver_id', $authUser->id);
        })->orWhereHas('receivedMessages', function ($query) use ($authUser) {
            $query->where('sender_id', $authUser->id);
        })->get();

        return view('chating.chat', compact('user', 'users'));
    }


    public function getAttachments($userId)
    {
        $authUserId = auth()->id();

        $files = Message::where(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $authUserId)
                ->where('receiver_id', $userId)
                ->where('message_type', 'file');
        })
            ->orWhere(function ($query) use ($authUserId, $userId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $authUserId)
                    ->where('message_type', 'file');
            })
            ->whereNotNull('file_path')
            ->with('sender')
            ->get();



        if ($files->isEmpty()) {
            return response()->json(['files' => [], 'message' => 'No files available']);
        }

        $formattedFiles = $files->map(function ($file) use ($authUserId) {
            return [
                'url' => url($file->file_path),
                'type' => in_array(pathinfo($file->file_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'file',
                'sender_id' => $file->sender_id,
                'sender_name' => ($file->sender_id == $authUserId) ? "You" : ($file->sender->name ?? 'Unknown'),
                'name' => pathinfo($file->file_path, PATHINFO_BASENAME),
            ];
        });

        return response()->json(['files' => $formattedFiles]);
    }




    // 🔹 Send message to user

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file',
        ]);

        // dd($request->all());

        if (!$request->hasFile('file') && !$request->filled('message')) {
            return response()->json([
                'error' => 'You must send a file or a message.'
            ], 422);
        }

        $senderId = Auth::id();
        $receiverId = $request->receiver_id;

        $messageType = match (true) {
            $request->hasFile('file') && $request->filled('message') => 'file and message',
            $request->hasFile('file') => 'file',
            default => 'message',
        };

        // 🔹 Check if an existing chat exists
        $existingChat = Message::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', $senderId);
        })->first();

        $chatId = $existingChat ? $existingChat->chat_id : md5(min($senderId, $receiverId) . max($senderId, $receiverId));

        // 🔹 Handle file upload properly
        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/chat_file'), $fileName);
            $filePath = url('public/images/chat_file/' . $fileName);
        }

        // 🔹 Save message in database
        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $messageType !== 'file' ? $request->message : null,
            'message_type' => $messageType,
            'file_path' => $filePath,
            'is_read' => false,
        ]);

        // 🔹 Send message to Firebase
        $firebaseData = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $request->message ?? '',
            'file_url' => $filePath,
            'message_type' => $messageType,
            'timestamp' => now()->timestamp,
        ];

        $this->database->getReference("messages/{$message->id}")->set($firebaseData);


        $receiverFcmToken = FcmToken::where('user_id', $receiverId)
            ->latest()
            ->value('token');

        if ($receiverFcmToken) {
            $notificationMessage = $messageType === 'file' ? 'Sent a file 📎' : $request->message;
            $this->sendPushNotification($receiverFcmToken, Auth::user()->name, $notificationMessage);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'message' => $message->message,
                'file_path' => $filePath,
                'message_type' => $messageType,
            ]
        ]);
    }

    // Send Notification to Firebase

    private function sendPushNotification($deviceToken, $senderName, $message)
    {
        try {
            $firebase = (new Factory)
                ->withServiceAccount(base_path('storage/app/firebase/credentials.json'));

            $messaging = $firebase->createMessaging();

            $notification = \Kreait\Firebase\Messaging\Notification::create($senderName, $message);

            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification($notification)
                ->withData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
                ->withChangedTarget('token', $deviceToken);

            $response = $messaging->send($message);

            \Log::info("Push notification sent to $deviceToken: " . json_encode($response));
        } catch (\Exception $e) {
            \Log::error("Error sending push notification: " . $e->getMessage());
        }
    }

    public function getMessages($userId)
    {
        $authUserId = Auth::id();

        // Fetch chat messages between logged-in user and selected user
        $messages = Message::where(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $authUserId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $userId)->where('receiver_id', $authUserId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    public function recentChats()
    {
        $authUser = auth()->user();

        // Get all users with whom the authenticated user has exchanged messages
        $users = User::whereHas('sentMessages', function ($query) use ($authUser) {
            $query->where('receiver_id', $authUser->id);
        })->orWhereHas('receivedMessages', function ($query) use ($authUser) {
            $query->where('sender_id', $authUser->id);
        })->paginate(10);

        return response()->json([
            'success' => true,
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                ];
            }),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],

        ]);
    }


    // public function recentChats()
    // {
    //     $authUserId = auth()->id();

    //     // Firebase se messages fetch karna
    //     $messagesRef = $this->database->getReference('messages')->getValue();

    //     if (!$messagesRef) {
    //         return response()->json(['success' => false, 'message' => 'No chats found']);
    //     }

    //     $chatUsers = collect();

    //     foreach ($messagesRef as $message) {
    //         // Check karo ki yeh proper array hai ya nahi
    //         if (!is_array($message)) {
    //             continue;
    //         }

    //         // Ensure karo ki 'sender_id' aur 'receiver_id' exist kare
    //         if (isset($message['sender_id']) && isset($message['receiver_id'])) {
    //             if ($message['sender_id'] == $authUserId) {
    //                 $chatUsers->push($message['receiver_id']);
    //             } elseif ($message['receiver_id'] == $authUserId) {
    //                 $chatUsers->push($message['sender_id']);
    //             }
    //         }
    //     }

    //     // Unique user IDs nikalna
    //     $uniqueUserIds = $chatUsers->unique();

    //     // Database se users fetch karna
    //     $users = User::whereIn('id', $uniqueUserIds)->select('id', 'name', 'email', 'image')->paginate(10);

    //     return response()->json([
    //         'success' => true,
    //         'users' => $users
    //     ]);
    // }
}
