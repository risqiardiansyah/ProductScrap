<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    public function toArray($request)
    {
        if (filter_var($this->profile_pict, FILTER_VALIDATE_URL)) {
            $pict = $this->profile_pict;
        } else {
            $pict = ($this->profile_pict == null ? asset('storage/img/default.png') : asset('storage/' . $this->profile_pict));
        }

        $topic = [];
        if (isset($this->topic)) {
            $topic = $this->topic;
        }

        $conversation = [];
        if (isset($this->conversation)) {
            $conversation = $this->conversation;
        }

        return  [
            'users_code' => $this->users_code,
            'username' => $this->username,
            'email' => $this->email,
            'fullname' => $this->fullname,
            'profile_pict' => $pict,
            'birthdate' => $this->birthdate,
            'gender' => $this->gender,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'register_via' => $this->register_via,
            'complete_profile' => $this->complete_profile,
            'verification_status' => $this->verification_status,
            'topic' => $topic,
            'conversation' => $conversation
        ];
    }
}
