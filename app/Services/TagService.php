<?php

namespace App\Services;


use App\Models\Tag;
use App\Models\User;

class TagService{


    public function createTag(User $user, array $data): Tag
    {

        $validated = validator($data, [
            "name"        => "required|string",
            "description" => "nullable|string",
            "color"       => "nullable|hex_color",
        ])->validate();

        return $user->tags()->create($validated);
    }


    public function updateTag(Tag $tag, array $data): bool
    {

        if($tag->user_id != auth()->id()){
            return false;
        }


        $validated = validator($data, [
            "name"        => "required|string",
            "description" => "nullable",
            "color"       => "nullable|hex_color",
        ])->validate();

        return $tag->update($validated);

    }

    public function deleteTag(Tag $tag): bool
    {
        if($tag->user_id != auth()->id()){
            return false;
        }
        return $tag->delete();
    }



}
