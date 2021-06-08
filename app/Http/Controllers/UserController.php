<?php

namespace App\Http\Controllers;

use App\Abstracts\UserRoles;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'role' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($v->fails()) return vRes($v);

        $role = 0;
        switch ($request->role) {
            case 'contractor':
                $role = 0;
                break;
            case 'client':
                $role = 1;
                break;
            case 'buddy':
                $role = 2;
                break;
        }

        $u = User::where('email', $request->email)->where('role', $role)->first();
        if (!$u) return eRes('Invalid email');

        if ($u->ban == 1) return eRes('Account is banned');
        if ($u->status == 0) return eRes('Account is not active');
        if (!Hash::check($request->password, $u->password)) return eRes('Invalid password');

        $role = 'contractor';
        switch ($u->role) {
            case UserRoles::CONTRACTOR:
                $role = 'contractor';
                break;
            case UserRoles::CLIENT:
                $role = 'client';
                break;
            case UserRoles::BUDDY:
                $role = 'buddy';
                break;
        }

        $token = $u->createToken($role, [])->plainTextToken;
        $user = new UserResource($u);

        return res(compact('token', 'user'));
    }

    public function logout(): JsonResponse
    {
        $u = Auth::user();
        $u->tokens()->delete();

        return res();
    }

    public function register(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'role' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6'
        ]);
        if ($v->fails()) return vRes($v);

        $role = 0;
        switch ($request->role) {
            case 'contractor':
                $role = 0;
                break;
            case 'client':
                $role = 1;
                break;
            case 'buddy':
                $role = 2;
                break;
        }

        $u = new User;
        $u->name = $request->name;
        $u->email = $request->email;
        $u->password = bcrypt($request->password);
        $u->role = $role;
        $u->save();

        $this->_sendEmailVerification($u);

        return res();
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($v->fails()) return vRes($v);

        $u = User::where('token', $request->token)->first();
        if (!$u) return eRes('Invalid token');

        if ($u->ban == 1) return eRes('Account is banned');

        $u->email_verified_at = now();
        $u->status = 1;
        $u->token = null;
        $u->save();

        return res();
    }

    public function checkToken(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        if ($v->fails()) return vRes($v);

        $u = User::where('token', $request->token)->where('ban', 0)->first();
        if (!$u) return eRes('Invalid token');

        return res();
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($v->fails()) return vRes($v);

        $u = User::where('email', $request->email)->first();
        if (!$u) return eRes('Invalid email');

        if ($u->ban == 1) return eRes('Account is banned');

        $this->_sendEmailVerification($u);

        return res();
    }

    public function setNewPassword(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
        if ($v->fails()) return vRes($v);

        $u = User::where('token', $request->token)->first();
        if (!$u) return eRes('Invalid token');

        if ($u->ban == 1) return eRes('Account is banned');

        $u->password = bcrypt($request->password);
        $u->token = null;
        $u->save();

        return res();
    }

    private function _sendEmailVerification(User $user)
    {
        $u = User::find($user->id);
        $u->token = random_int(100000, 999999);
        $u->save();

        // TODO: Send Email Verification
    }
}
