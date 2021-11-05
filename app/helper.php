<?php

use App\Http\Resources\Conversation;
use App\Http\Resources\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use \FCM as firebase;
use Illuminate\Support\Facades\Validator;

if (!function_exists('getUsersDetail')) {
    function getUsersDetail($users_code)
    {
        $user = DB::table('users')->where('users_code', $users_code)->first();
        return new User($user);
    }
}


if (!function_exists('generateFiledCode')) {
    function generateFiledCode($code)
    {
        $result = $code . '-' . date('s') . date('y') . date('i') . date('m') . date('h') . date('d') . mt_rand(1000000, 9999999);

        return $result;
    }
}

// if (!function_exists('translate_message')) {
//     /**
//      * translate message return API.
//      *
//      * @param string $code
//      * @param string $lang
//      */
//     function translate_message($code = '0', $lang = 'indonesian')
//     {
//         if ($lang == 'indonesian') {
//             $message = array(
//                 '0' => 'sukses',
//                 '1' => 'Parameter error',
//                 '2' => 'Username atau password salah',
//                 '3' => 'Pencarian tidak ditemukan',
//                 '4' => 'Data tidak ditemukan',
//                 '5' => 'Register error',
//                 '6' => 'Forgot password error',
//                 '7' => 'Change password error',
//                 '8' => 'Update data error',
//                 '9' => 'Save data error',
//                 '10' => 'Delete data error',
//                 '11' => 'Invalid access token',
//                 '12' => 'Failed to send email',
//                 '13' => 'Invalid forgot token',
//                 '14' => 'Invalid Code',
//                 '15' => 'Failed to upload photo',
//                 '16' => 'Akun anda belum aktif',
//                 '17' => 'Akun Anda di-suspend',
//                 '18' => 'Session Anda telah habis',
//                 '19' => 'Data ditemukan',
//                 '20' => 'Data berhasil di buat',
//                 '21' => 'Data gagal di buat',
//                 '22' => 'Login berhasil',
//                 '23' => 'Logout berhasil',
//                 '24' => 'Logout gagal',
//                 '25' => 'Update data berhasil',
//                 '26' => 'exist',
//                 '27' => 'not exist',
//                 '28' => 'Update profile berhasil',
//                 '29' => 'Update profile gagal',
//                 '30' => 'Delete data berhasil',
//                 '31' => 'Valid Token',
//                 '32' => 'Invalid Token',
//                 '33' => 'Email tidak terdaftar',
//                 '34' => 'Link atur ulang kata sandi telah dikirim',
//                 '35' => 'Password lama tidak cocok',
//                 '36' => 'Email sudah terdaftar',
//                 '37' => 'Gagal',
//                 '38' => 'Akun sudah terdaftar, dan menunggu persetujuan Admin',
//                 '39' => 'Member Suspend',
//                 '40' => 'Member sudah terdaftar',
//                 '41' => 'Kuota Event telah penuh',
//                 '42' => 'Pendaftaran telah melebihi batas waktu',
//                 '43' => 'User yang sudah terdaftar melebihi batas maksimal',
//                 '44' => 'QR Generate required',
//                 '45' => 'Anda telah bergabung di event ini.',
//                 '46' => 'Anda belum bergabung di event ini.',
//             );
//
//             return isset($message[$code]) ? $message[$code] : $code.' - Kode tersebut belum terdefinisi di dalam sistem kami.';
//         }
//     }
// }

/*
 *  Encode base64 image and save to Storage
 */
if (!function_exists('uploadFotoWithFileName')) {
    function uploadFotoWithFileName($base64Data, $file_prefix_name, $dir = '')
    {
        $file_name = generateFiledCode($file_prefix_name) . '.png';
        $insert_image = Storage::disk('public')->put($dir . $file_name, normalizeAndDecodeBase64Photo($base64Data));

        if ($insert_image) {
            return $file_name;
        }

        return false;
    }

    function normalizeAndDecodeBase64Photo($base64Data)
    {
        $replaceList = array(
            'data:image/jpeg;base64,',
            'data:image/jpg;base64,',
            'data:image/png;base64,',
            '[protected]',
            '[removed]',
        );
        $base64Data = str_replace($replaceList, '', $base64Data);

        return base64_decode($base64Data);
    }
}

if (!function_exists('uploadFotoWithFileNameApi')) {
    function uploadFotoWithFileNameApi($base64Data, $file_prefix_name)
    {
        $file_name = generateFiledCode($file_prefix_name) . '.png';
        // dd($file_name);

        $insert_image = Storage::disk('public')->put($file_name, normalizeAndDecodeBase64PhotoApi($base64Data));

        if ($insert_image) {
            return $file_name;
        }

        return false;
    }

    function normalizeAndDecodeBase64PhotoApi($base64Data)
    {
        $replaceList = array(
            'data:image/jpeg;base64,',
            '/^data:image\/\w+;/^name=\/\w+;base64,/',
            'data:image/jpeg;base64,',
            'data:image/jpg;base64,',
            'data:image/png;base64,',
            'data:image/webp;base64,',
            '[protected]',
            '[removed]',
        );
        $exploded = explode(',', $base64Data);
        if (!isset($exploded[1])) {
            $exploded[1] = null;
        }

        $base64 = $exploded[1];
        // dd($base64);
        $base64Data = str_replace($replaceList, '', $base64Data);

        return base64_decode($base64);
    }
}

if (!function_exists('validationMessage')) {
    function validationMessage($validation)
    {
        $validate = collect($validation)->flatten();

        return $validate->values()->all();
    }
}

if (!function_exists('validationMessage')) {
    function validateThis($request, $rules = array())
    {
        return Validator::make($request->all(), $rules);
    }
}

if (!function_exists('checkVerificationEmail')) {
    function checkVerificationEmail($hash, $email)
    {
        $where = ['hash' => $hash, 'email' => $email];
        $verify = DB::table('verification_email')->where($where)->orderBy('created_at', 'DESC')->first();

        if ($verify) {
            $checkExpires = (strtotime($verify->expires) <= strtotime('now') ? false : true);

            if (!$checkExpires) {
                return ['success' => false, 'msg' => 'TOKEN_EXPIRED'];
            } else {
                DB::table('verification_email')->where($where)->update(['verified_at' => now()]);
                return ['success' => true];
            }
        }
    }
}

if (!function_exists('checkVerificationByEmail')) {
    function checkVerificationByEmail($code, $email)
    {
        $where = ['email' => $email];
        $verify = DB::table('verification_email')->where($where)->orderBy('expires', 'DESC')->first();

        if ($verify) {
            $checkExpires = ($verify->expires <= now() ? false : true);

            if (!$checkExpires) {

                return "TOKEN_EXPIRED";
            } else if ($code != $verify->code) {
                return "CODE_NOT_MATCH";
            } else {
                DB::table('verification_email')->where($where)->update(['verified_at' => now()]);
                return true;
            }
        }
    }
}

if (!function_exists('checkVerificationReset')) {
    function checkVerificationReset($token, $code, $email)
    {
        $where = ['token' => $token, 'email' => $email];
        $verify = DB::table('verification_reset')->where($where)->first();

        if ($verify) {
            $checkExpires = ($verify->expires <= now() ? false : true);

            if (!$checkExpires) {

                return "TOKEN_EXPIRED";
            } else if ($code != $verify->code) {
                return "CODE_NOT_MATCH";
            } else {
                DB::table('verification_email')->where($where)->update(['verified_at' => now()]);
            }
        }
    }
}

if (!function_exists('sendNotifToUser')) {
    function sendNotifToUser($data)
    {
        try {
            $notif = [
                'notification_code' => generateFiledCode('NOTIF'),
                'notification_from' => $data['notification_from'],
                'notification_to' => $data['notification_to'],
                'notification_title' => $data['notification_title'],
                'notification_desc' => $data['notification_desc'],
                'notification_type' => $data['notification_type'],
                'notification_status' => 0,
            ];

            DB::table('notification_users')->insert($notif);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('sendNotifToAdmin')) {
    function sendNotifToAdmin($data)
    {
        try {
            $notif = [
                'notification_code' => generateFiledCode('NOTIF'),
                'notification_to' => 'admin',
                'notification_title' => $data['notification_title'],
                'notification_desc' => $data['notification_desc'],
                'notification_link' => $data['notification_link'],
                'notification_type' => $data['notification_type'],
                'notification_status' => 0,
            ];

            DB::table('notification')->insert($notif);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('checkIsMatched')) {
    function checkIsMatched($users_code, $friends_code)
    {
        try {
            $cek1 = DB::table('matched')
                ->where('from_user', $users_code)
                ->where('to_user', $friends_code)
                ->first();
            $cek2 = DB::table('matched')
                ->where('from_user', $friends_code)
                ->where('to_user', $users_code)
                ->first();

            if (!empty($cek1)) {
                return $cek1;
            } elseif (!empty($cek2)) {
                return $cek2;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('getConversation')) {
    function getConversation($parent_code)
    {
        try {
            $data = DB::table('messages')
                ->where('messages_parent_code', $parent_code)
                ->orderBy('created_at', 'ASC')
                ->get();
            $all = Conversation::collection($data);
            // dd($all);
            return $all;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('cekGroupAdmin')) {
    function cekGroupAdmin($group_code, $users_code)
    {
        try {
            $data = DB::table('group_member')
                ->where('group_code', $group_code)
                ->where('users_code', $users_code)
                ->first();

            $cek_admin = DB::table('group_member')
                ->where('users_code', '!=', $users_code)
                ->where('role', 1)
                ->count();

            $cek_member = DB::table('group_member')
                ->where('users_code', '!=', $users_code)
                ->where('role', 0)
                ->count();

            $cek_all = DB::table('group_member')
                ->where('users_code', '!=', $users_code)
                ->count();

            return (object)[
                'is_admin' => $data->role,
                'admin' => $cek_admin,
                'member' => $cek_member,
                'all' => $cek_all
            ];
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('setUnreadMessage')) {
    function setUnreadMessage($group_code, $sender)
    {
        try {
            $data = DB::table('group_member')
                ->where('group_code', $group_code);

            $get = $data->where('users_code', '!=', $sender)->get();
            for ($i = 0; $i < count($get); $i++) {
                $unread = $get[$i]->unread_message;
                $data->where('users_code', $get[$i]->users_code)->update(['unread_message' => $unread + 1]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('generatePasscode')) {
    function generatePasscode($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
