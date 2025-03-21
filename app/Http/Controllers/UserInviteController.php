<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserInviteRequest;
use App\Http\Requests\UpdateUserInviteRequest;
use App\Models\UserInvite;

class UserInviteController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserInviteRequest $request)
    {
        $invite = UserInvite::create([
            'meeting_id' => $request->meeting_id,
            'user_id' => $request->user_id,
            'status' => 0,
        ]);

        return $invite;
    }

    /**
     * Display the specified resource.
     */
    public function show(UserInvite $userInvite)
    {
        return $userInvite;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserInviteRequest $request, UserInvite $userInvite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserInvite $userInvite)
    {
        //
    }
}
