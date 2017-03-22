<?php

namespace VivienLN\Pilot;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PilotRole extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slug', 'label'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Return whether a user has a given role (with it slug)
     * @param $user the user to check
     * @param null $roleSlug the role to check. If null, this will check if the user has ANY role.
     */
    static public function contains($user, $roleSlug = null)
    {
        $roles = $roleSlug ? self::where('slug', $roleSlug)->get() : self::all();
        foreach($roles as $role) {
            if($role->users->contains($user)) {
                return true;
            }
        }
        return false;
    }

}
